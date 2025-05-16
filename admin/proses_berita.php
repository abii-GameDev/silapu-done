<?php
 session_start();
 require '../config/db.php'; // Sesuaikan path jika config/db.php ada di root atau level lain

 // Pastikan hanya admin yang bisa akses dan ada aksi yang dikirim
 if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
     // Jika akses tidak sah atau tidak ada aksi, kembalikan ke dashboard admin atau halaman login
     $_SESSION['berita_message'] = ['type' => 'error', 'text' => 'Aksi tidak diizinkan atau tidak valid.'];
     if (isset($_SESSION['user_id'])) { // Cek ini agar tidak error jika session sudah destroy
         header("Location: manajemen_berita.php");
     } else {
         header("Location: ../auth/login.php");
     }
     exit;
 }

 // Fungsi untuk membuat slug dari judul
 function createSlug($text) {
     // Replace non letter or digits by -
     $text = preg_replace('~[^\pL\d]+~u', '-', $text);
     // Transliterate
     $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
     // Remove unwanted characters
     $text = preg_replace('~[^-\w]+~', '', $text);
     // Trim
     $text = trim($text, '-');
     // Remove duplicate -
     $text = preg_replace('~-+~', '-', $text);
     // Lowercase
     $text = strtolower($text);
     if (empty($text)) {
         return 'n-a-' . time(); // Fallback jika judul hanya karakter aneh
     }
     return $text;
 }


 if (isset($_POST['action']) && $_POST['action'] == 'tambah' && isset($_POST['submit_berita'])) {
     $judul = trim($_POST['judul'] ?? '');
     $slug_input = trim($_POST['slug'] ?? '');
     $konten = trim($_POST['konten'] ?? ''); // Nanti bisa di-sanitize lebih lanjut jika perlu (misal, filter HTML)
     $status = trim($_POST['status'] ?? 'draft');
     $tanggal_publikasi_input = trim($_POST['tanggal_publikasi'] ?? '');
     $penulis_id = isset($_POST['penulis_id']) ? intval($_POST['penulis_id']) : null;

     $gambar_banner_path = null;

     // Validasi dasar
     if (empty($judul) || empty($konten)) {
         $_SESSION['berita_form_message'] = ['type' => 'error', 'text' => 'Judul dan Konten wajib diisi.'];
         $_SESSION['form_data_berita'] = $_POST; // Simpan input untuk diisi kembali
         header("Location: tambah_berita.php");
         exit;
     }

     // Generate slug jika kosong atau cek keunikan jika diisi manual
     if (empty($slug_input)) {
         $slug = createSlug($judul);
     } else {
         $slug = createSlug($slug_input); // Pastikan format slug benar
     }

     // Cek keunikan slug (penting!)
     $stmt_slug_check = $conn->prepare("SELECT id FROM berita_kegiatan WHERE slug = ?");
     if(!$stmt_slug_check) die("Error prepare slug check: " . $conn->error);
     $stmt_slug_check->bind_param("s", $slug);
     $stmt_slug_check->execute();
     $result_slug_check = $stmt_slug_check->get_result();
     if ($result_slug_check->num_rows > 0) {
         // Jika slug sudah ada, tambahkan suffix unik (misal, timestamp atau angka)
         $slug .= '-' . time(); 
     }
     $stmt_slug_check->close();


     // Tanggal publikasi
     $tanggal_publikasi_db = null;
     if ($status == 'published') {
         if (!empty($tanggal_publikasi_input)) {
             $tanggal_publikasi_db = date('Y-m-d H:i:s', strtotime($tanggal_publikasi_input));
         } else {
             $tanggal_publikasi_db = date('Y-m-d H:i:s'); // Waktu saat ini
         }
     }

     // --- Proses Upload File Gambar Banner (Jika ada) ---
     if (isset($_FILES['gambar_banner']) && $_FILES['gambar_banner']['error'] == UPLOAD_ERR_OK && $_FILES['gambar_banner']['size'] > 0) {
         $upload_dir_berita = '../uploads/berita_banner/'; // Buat folder ini
         if (!is_dir($upload_dir_berita)) {
             mkdir($upload_dir_berita, 0755, true);
         }
         
         $file_tmp_name = $_FILES['gambar_banner']['tmp_name'];
         $file_name = basename($_FILES['gambar_banner']['name']);
         $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
         $allowed_ext = ['jpg', 'jpeg', 'png', 'gif'];

         if (in_array($file_ext, $allowed_ext)) {
             if ($_FILES['gambar_banner']['size'] <= 2000000) { // Max 2MB
                 $new_file_name = $slug . '-' . uniqid() . '.' . $file_ext; // Nama file unik berdasarkan slug
                 $destination = $upload_dir_berita . $new_file_name;
                 if (move_uploaded_file($file_tmp_name, $destination)) {
                     $gambar_banner_path = 'uploads/berita_banner/' . $new_file_name; // Path relatif dari root
                 } else {
                     $_SESSION['berita_form_message'] = ['type' => 'error', 'text' => 'Gagal memindahkan file gambar banner.'];
                     $_SESSION['form_data_berita'] = $_POST;
                     header("Location: tambah_berita.php"); exit;
                 }
             } else {
                 $_SESSION['berita_form_message'] = ['type' => 'error', 'text' => 'Ukuran file gambar terlalu besar (maks 2MB).'];
                 $_SESSION['form_data_berita'] = $_POST;
                 header("Location: tambah_berita.php"); exit;
             }
         } else {
             $_SESSION['berita_form_message'] = ['type' => 'error', 'text' => 'Tipe file gambar tidak diizinkan (JPG, PNG, GIF).'];
             $_SESSION['form_data_berita'] = $_POST;
             header("Location: tambah_berita.php"); exit;
         }
     }
     // --- Akhir Proses Upload File ---


     // Simpan data berita ke database
     $stmt_insert = $conn->prepare("INSERT INTO berita_kegiatan 
                                   (judul, slug, konten, gambar_banner, penulis_id, status, tanggal_publikasi) 
                                   VALUES (?, ?, ?, ?, ?, ?, ?)");
     if (!$stmt_insert) {
         $_SESSION['berita_form_message'] = ['type' => 'error', 'text' => 'Database error (prepare insert): ' . $conn->error];
         $_SESSION['form_data_berita'] = $_POST;
     } else {
         // Tipe data: s (judul), s (slug), s (konten), s (gambar), i (penulis_id), s (status), s (tgl_pub)
         $stmt_insert->bind_param("ssssiss", 
             $judul, 
             $slug, 
             $konten, 
             $gambar_banner_path, // Bisa NULL
             $penulis_id,         // Bisa NULL
             $status, 
             $tanggal_publikasi_db // Bisa NULL jika status draft
         );

         if ($stmt_insert->execute()) {
             $_SESSION['berita_message'] = ['type' => 'success', 'text' => 'Berita/Kegiatan "' . htmlspecialchars($judul) . '" berhasil ditambahkan.'];
             unset($_SESSION['form_data_berita']); // Hapus data form dari session jika sukses
             header("Location: manajemen_berita.php");
             exit;
         } else {
             $_SESSION['berita_form_message'] = ['type' => 'error', 'text' => 'Gagal menyimpan berita: ' . $stmt_insert->error];
             $_SESSION['form_data_berita'] = $_POST;
         }
         $stmt_insert->close();
     }
     $conn->close();
     header("Location: tambah_berita.php"); // Redirect kembali ke form jika ada error di atas
     exit;

 } elseif (isset($_POST['action']) && $_POST['action'] == 'edit' && isset($_POST['submit_berita'], $_POST['berita_id'])) {
    $berita_id = intval($_POST['berita_id']);
    $judul = trim($_POST['judul'] ?? '');
    $slug_input = trim($_POST['slug'] ?? '');
    $konten = trim($_POST['konten'] ?? '');
    $status = trim($_POST['status'] ?? 'draft');
    $tanggal_publikasi_input = trim($_POST['tanggal_publikasi'] ?? '');
    $penulis_id = isset($_POST['penulis_id']) ? intval($_POST['penulis_id']) : $_SESSION['user_id']; // Ambil dari form atau default ke user login
    $hapus_gambar_lama_checkbox = isset($_POST['hapus_gambar_lama']) ? 1 : 0;

    // Validasi dasar
    if (empty($judul) || empty($konten)) {
        $_SESSION['berita_form_message'] = ['type' => 'error', 'text' => 'Judul dan Konten wajib diisi.'];
        header("Location: edit_berita.php?id=" . $berita_id);
        exit;
    }

    // Generate slug jika diubah atau cek keunikan
    if (empty($slug_input)) {
        $slug = createSlug($judul);
    } else {
        $slug = createSlug($slug_input);
    }

    // Cek keunikan slug (kecuali untuk berita ini sendiri)
    $stmt_slug_check = $conn->prepare("SELECT id FROM berita_kegiatan WHERE slug = ? AND id != ?");
    if(!$stmt_slug_check) die("Error prepare slug check (edit): " . $conn->error);
    $stmt_slug_check->bind_param("si", $slug, $berita_id);
    $stmt_slug_check->execute();
    $result_slug_check = $stmt_slug_check->get_result();
    if ($result_slug_check->num_rows > 0) {
        $slug .= '-' . $berita_id; // Tambahkan ID berita untuk memastikan unik jika ada konflik selain dirinya sendiri
    }
    $stmt_slug_check->close();

    // Tanggal publikasi
    $tanggal_publikasi_db = null;
    if ($status == 'published') {
        if (!empty($tanggal_publikasi_input)) {
            $tanggal_publikasi_db = date('Y-m-d H:i:s', strtotime($tanggal_publikasi_input));
        } else {
            // Jika status diubah jadi published dan tgl_pub kosong, cek apakah sudah ada tgl_pub lama
            $stmt_check_tgl_pub = $conn->prepare("SELECT tanggal_publikasi FROM berita_kegiatan WHERE id = ?");
            $stmt_check_tgl_pub->bind_param("i", $berita_id);
            $stmt_check_tgl_pub->execute();
            $result_tgl_pub = $stmt_check_tgl_pub->get_result()->fetch_assoc();
            $stmt_check_tgl_pub->close();
            if ($result_tgl_pub && $result_tgl_pub['tanggal_publikasi']) {
                $tanggal_publikasi_db = $result_tgl_pub['tanggal_publikasi']; // Gunakan tgl publikasi lama jika ada
            } else {
                $tanggal_publikasi_db = date('Y-m-d H:i:s'); // Atau set baru jika belum pernah publish
            }
        }
    } elseif ($status == 'draft' || $status == 'archived') {
        // Jika diubah ke draft/archived, tanggal publikasi bisa di-NULL-kan (opsional, atau biarkan saja)
        // $tanggal_publikasi_db = null; 
        // Untuk sekarang, kita biarkan tanggal publikasi lama jika ada, kecuali diisi baru.
        // Jika input tanggal publikasi dikosongkan user saat edit, maka jadi NULL.
         if (empty($tanggal_publikasi_input)) {
             $tanggal_publikasi_db = null;
         } else {
             $tanggal_publikasi_db = date('Y-m-d H:i:s', strtotime($tanggal_publikasi_input));
         }
    }


    // Ambil path gambar lama
    $stmt_gambar_lama = $conn->prepare("SELECT gambar_banner FROM berita_kegiatan WHERE id = ?");
    $stmt_gambar_lama->bind_param("i", $berita_id);
    $stmt_gambar_lama->execute();
    $result_gambar_lama = $stmt_gambar_lama->get_result()->fetch_assoc();
    $gambar_banner_lama_db = $result_gambar_lama['gambar_banner'] ?? null;
    $stmt_gambar_lama->close();

    $gambar_banner_path_update = $gambar_banner_lama_db; // Defaultnya pakai gambar lama

    // --- Proses Upload File Gambar Banner Baru (Jika ada) ---
    if (isset($_FILES['gambar_banner']) && $_FILES['gambar_banner']['error'] == UPLOAD_ERR_OK && $_FILES['gambar_banner']['size'] > 0) {
        $upload_dir_berita = '../uploads/berita_banner/';
        // ... (Logika upload file sama seperti di proses tambah, ganti $gambar_banner_path menjadi $gambar_banner_path_update)
        // ... (Jika upload baru berhasil, hapus gambar lama dari server)
        $file_tmp_name = $_FILES['gambar_banner']['tmp_name'];
        $file_name = basename($_FILES['gambar_banner']['name']);
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $allowed_ext = ['jpg', 'jpeg', 'png', 'gif'];

        if (in_array($file_ext, $allowed_ext) && $_FILES['gambar_banner']['size'] <= 2000000) {
            $new_file_name = $slug . '-' . uniqid() . '.' . $file_ext;
            $destination = $upload_dir_berita . $new_file_name;
            if (move_uploaded_file($file_tmp_name, $destination)) {
                // Hapus gambar lama jika ada dan gambar baru berhasil diupload
                if ($gambar_banner_lama_db && file_exists('../' . $gambar_banner_lama_db)) {
                    unlink('../' . $gambar_banner_lama_db);
                }
                $gambar_banner_path_update = 'uploads/berita_banner/' . $new_file_name;
            } else { 
                $_SESSION['berita_form_message'] = ['type'=>'error','text'=>'Gagal upload gambar baru.']; 
                header("Location: edit_berita.php?id=$berita_id"); 
                exit; 
            }
        } else { 
            $_SESSION['berita_form_message'] = ['type'=>'error','text'=>'File gambar tidak valid.']; 
            header("Location: edit_berita.php?id=$berita_id"); 
            exit; 
        }
    } elseif ($hapus_gambar_lama_checkbox && $gambar_banner_lama_db) {
        // Jika TIDAK ada upload baru TAPI user mencentang hapus gambar lama
        if (file_exists('../' . $gambar_banner_lama_db)) {
            unlink('../' . $gambar_banner_lama_db);
        }
        $gambar_banner_path_update = null; // Set jadi null di DB
    }
    // Jika tidak ada upload baru dan tidak dicentang hapus, $gambar_banner_path_update akan tetap $gambar_banner_lama_db
    // --- Akhir Proses Upload File ---

    // Update data berita di database
    $stmt_update = $conn->prepare("UPDATE berita_kegiatan SET 
                                judul = ?, slug = ?, konten = ?, gambar_banner = ?, 
                                penulis_id = ?, status = ?, tanggal_publikasi = ?
                                WHERE id = ?");
    if (!$stmt_update) {
        $_SESSION['berita_form_message'] = ['type' => 'error', 'text' => 'Database error (prepare update): ' . $conn->error];
    } else {
        $stmt_update->bind_param("ssssissi", 
            $judul, $slug, $konten, $gambar_banner_path_update,
            $penulis_id, $status, $tanggal_publikasi_db,
            $berita_id
        );

        if ($stmt_update->execute()) {
            $_SESSION['berita_message'] = ['type' => 'success', 'text' => 'Berita/Kegiatan "' . htmlspecialchars($judul) . '" berhasil diperbarui.'];
            header("Location: manajemen_berita.php");
            exit;
        } else {
            $_SESSION['berita_form_message'] = ['type' => 'error', 'text' => 'Gagal memperbarui berita: ' . $stmt_update->error];
        }
        $stmt_update->close();
    }
    $conn->close();
    header("Location: edit_berita.php?id=" . $berita_id); // Redirect kembali ke form edit jika ada error
    exit;
} elseif (isset($_GET['action']) && $_GET['action'] == 'hapus' && isset($_GET['id'])) {
    // Untuk hapus, kita menggunakan GET request dari link dengan konfirmasi JS
    $berita_id_to_delete = intval($_GET['id']);
    
    // 1. Ambil path gambar banner untuk dihapus dari server
    $stmt_get_gambar = $conn->prepare("SELECT gambar_banner FROM berita_kegiatan WHERE id = ?");
    if (!$stmt_get_gambar) {
        $_SESSION['berita_message'] = ['type' => 'error', 'text' => 'Database error (prepare get gambar): ' . $conn->error];
        header("Location: manajemen_berita.php");
        exit;
    }
    $stmt_get_gambar->bind_param("i", $berita_id_to_delete);
    $stmt_get_gambar->execute();
    $result_gambar = $stmt_get_gambar->get_result();
    $gambar_to_delete = null;
    if ($result_gambar->num_rows === 1) {
        $data_berita = $result_gambar->fetch_assoc();
        $gambar_to_delete = $data_berita['gambar_banner'];
    }
    $stmt_get_gambar->close();
    
    // 2. Hapus berita dari database
    $stmt_delete = $conn->prepare("DELETE FROM berita_kegiatan WHERE id = ?");
    if (!$stmt_delete) {
        $_SESSION['berita_message'] = ['type' => 'error', 'text' => 'Database error (prepare delete): ' . $conn->error];
    } else {
        $stmt_delete->bind_param("i", $berita_id_to_delete);
        if ($stmt_delete->execute()) {
            // 3. Jika berhasil hapus dari DB, hapus juga file gambar banner dari server (jika ada)
            if ($gambar_to_delete && file_exists('../' . $gambar_to_delete)) {
                // Pastikan path '../' sudah benar relatif terhadap posisi file proses_berita.php ke root
                if (!unlink('../' . $gambar_to_delete)) {
                    // Gagal menghapus file, bisa ditambahkan log error
                    error_log("Gagal menghapus file gambar berita: ../" . $gambar_to_delete);
                }
            }
            $_SESSION['berita_message'] = ['type' => 'success', 'text' => 'Berita/Kegiatan berhasil dihapus.'];
        } else {
            $_SESSION['berita_message'] = ['type' => 'error', 'text' => 'Gagal menghapus berita/kegiatan: ' . $stmt_delete->error];
        }
        $stmt_delete->close();
    }
    $conn->close();
    header("Location: manajemen_berita.php");
    exit;
} else {
    // Jika action tidak dikenali atau parameter kurang (untuk POST)
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $_SESSION['berita_message'] = ['type' => 'error', 'text' => 'Aksi tidak dikenal atau data tidak lengkap.'];
    }
    // Jika bukan POST dan bukan aksi hapus via GET, redirect saja
    header("Location: manajemen_berita.php");
    exit;
}
?>
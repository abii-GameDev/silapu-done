 <?php
 session_start();
 require '../config/db.php';

 // Pastikan pengguna sudah login
 if (!isset($_SESSION['user_id'])) {
     $_SESSION['login_messages'] = [['type' => 'error', 'text' => 'Anda harus login untuk melakukan aksi ini.']];
     header("Location: ../auth/login.php");
     exit;
 }
 $user_id = $_SESSION['user_id'];

 if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_tambah_produk'], $_POST['usaha_id'])) {
     $usaha_id = intval($_POST['usaha_id']);

     // Verifikasi lagi bahwa usaha ini milik user dan disetujui (penting untuk keamanan backend)
     $stmt_check = $conn->prepare("SELECT id FROM usaha_mahasiswa WHERE id = ? AND user_id = ? AND status_pengajuan = 'Disetujui'");
     if (!$stmt_check) {
         $_SESSION['produk_form_message'] = ['type' => 'error', 'text' => 'DB error (check).'];
         header("Location: tambah_produk.php?usaha_id=" . $usaha_id); exit;
     }
     $stmt_check->bind_param("ii", $usaha_id, $user_id);
     $stmt_check->execute();
     $result_check = $stmt_check->get_result();
     if ($result_check->num_rows !== 1) {
         $_SESSION['produk_form_message'] = ['type' => 'error', 'text' => 'Aksi tidak diizinkan untuk usaha ini.'];
         header("Location: usaha_saya.php"); exit; // Redirect ke daftar usaha utama user
     }
     $stmt_check->close();


     // Ambil data dari form
     $nama_produk_layanan = trim($_POST['nama_produk_layanan'] ?? '');
     $kategori_produk_layanan = trim($_POST['kategori_produk_layanan'] ?? null);
     $deskripsi_produk_layanan = trim($_POST['deskripsi_produk_layanan'] ?? null);
     $harga = floatval($_POST['harga'] ?? 0);
     $satuan = trim($_POST['satuan'] ?? 'pcs');
     $stok_input = trim($_POST['stok'] ?? '');
     $stok = ($stok_input === '' || !is_numeric($stok_input)) ? null : intval($stok_input); // Stok bisa NULL
     $is_tersedia = isset($_POST['is_tersedia']) ? intval($_POST['is_tersedia']) : 1; // Default 1 (TRUE)

     $foto_path_db = null;

     // Validasi dasar
     if (empty($nama_produk_layanan) || $harga < 0 || empty($satuan)) {
         $_SESSION['produk_form_message'] = ['type' => 'error', 'text' => 'Nama produk, harga, dan satuan wajib diisi dengan benar.'];
         header("Location: tambah_produk.php?usaha_id=" . $usaha_id);
         exit;
     }

     // --- Proses Upload File (Sama seperti di proses_ajukan_usaha.php) ---
     if (isset($_FILES['foto_produk_layanan']) && $_FILES['foto_produk_layanan']['error'] == UPLOAD_ERR_OK) {
         $upload_dir_produk = '../uploads/produk_layanan/'; // Buat folder ini
         if (!is_dir($upload_dir_produk)) {
             mkdir($upload_dir_produk, 0755, true);
         }
         // ... (Logika upload file lengkap seperti di proses_ajukan_usaha.php, simpan path ke $foto_path_db)
         // Untuk singkatnya, saya akan copy dari sana dan sesuaikan:
         $file_tmp_name = $_FILES['foto_produk_layanan']['tmp_name'];
         $file_name = basename($_FILES['foto_produk_layanan']['name']);
         $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
         $allowed_ext = ['jpg', 'jpeg', 'png', 'gif'];

         if (in_array($file_ext, $allowed_ext)) {
             if ($_FILES['foto_produk_layanan']['size'] <= 2000000) { // Max 2MB
                 $new_file_name = uniqid('produk_', true) . '.' . $file_ext;
                 $destination = $upload_dir_produk . $new_file_name;
                 if (move_uploaded_file($file_tmp_name, $destination)) {
                     $foto_path_db = 'uploads/produk_layanan/' . $new_file_name;
                 } else {
                     $_SESSION['produk_form_message'] = ['type' => 'error', 'text' => 'Gagal memindahkan file produk.'];
                     header("Location: tambah_produk.php?usaha_id=" . $usaha_id); exit;
                 }
             } else {
                 $_SESSION['produk_form_message'] = ['type' => 'error', 'text' => 'Ukuran file produk terlalu besar (maks 2MB).'];
                 header("Location: tambah_produk.php?usaha_id=" . $usaha_id); exit;
             }
         } else {
             $_SESSION['produk_form_message'] = ['type' => 'error', 'text' => 'Tipe file produk tidak diizinkan.'];
             header("Location: tambah_produk.php?usaha_id=" . $usaha_id); exit;
         }
     }
     // --- Akhir Proses Upload File ---

     // Simpan data produk ke database
     $stmt_insert = $conn->prepare("INSERT INTO produk_layanan 
                                   (usaha_id, nama_produk_layanan, kategori_produk_layanan, deskripsi_produk_layanan, harga, satuan, stok, foto_produk_layanan, is_tersedia) 
                                   VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
     if (!$stmt_insert) {
         $_SESSION['produk_form_message'] = ['type' => 'error', 'text' => 'Database error (prepare insert produk): ' . $conn->error];
     } else {
         // Tipe data: i (usaha_id), s, s, s, d (harga), s, i (stok, bisa null), s, i (is_tersedia)
         $stmt_insert->bind_param("isssdsssi", 
             $usaha_id, 
             $nama_produk_layanan, 
             $kategori_produk_layanan, 
             $deskripsi_produk_layanan, 
             $harga, 
             $satuan, 
             $stok, // Akan dikirim sebagai NULL jika $stok adalah NULL PHP
             $foto_path_db,
             $is_tersedia
         );

         if ($stmt_insert->execute()) {
             $_SESSION['produk_message'] = ['type' => 'success', 'text' => 'Produk/Layanan "' . htmlspecialchars($nama_produk_layanan) . '" berhasil ditambahkan.'];
             header("Location: kelola_produk_usaha.php?usaha_id=" . $usaha_id);
             exit;
         } else {
             $_SESSION['produk_form_message'] = ['type' => 'error', 'text' => 'Gagal menyimpan data produk: ' . $stmt_insert->error];
         }
         $stmt_insert->close();
     }
     $conn->close();
     
     // Jika ada error sebelum redirect sukses, redirect kembali ke form tambah
     header("Location: tambah_produk.php?usaha_id=" . $usaha_id);
     exit;

 } else {
     $_SESSION['usaha_message'] = ['type' => 'error', 'text' => 'Aksi tidak valid.'];
     header("Location: usaha_saya.php"); // Redirect ke daftar usaha utama user
     exit;
 }
 ?>

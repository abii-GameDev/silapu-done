<?php
session_start();
require '../config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php"); exit;
}
$user_id = $_SESSION['user_id'];

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_edit_produk'], $_POST['produk_id'], $_POST['usaha_id'])) {
    $produk_id = intval($_POST['produk_id']);
    $usaha_id = intval($_POST['usaha_id']);

    // Verifikasi kepemilikan usaha & produk, dan status usaha disetujui
    $stmt_check = $conn->prepare("SELECT um.id FROM usaha_mahasiswa um JOIN produk_layanan pl ON um.id = pl.usaha_id WHERE pl.id = ? AND um.id = ? AND um.user_id = ? AND um.status_pengajuan = 'Disetujui'");
    if (!$stmt_check) {
        $_SESSION['produk_form_message'] = ['type'=>'error','text'=>'Database Error (check): ' . $conn->error];
        header("Location: edit_produk.php?id=$produk_id&usaha_id=$usaha_id"); exit;
    }
    $stmt_check->bind_param("iii", $produk_id, $usaha_id, $user_id);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();
    if ($result_check->num_rows !== 1) {
        $_SESSION['produk_message'] = ['type' => 'error', 'text' => 'Aksi tidak diizinkan atau produk/usaha tidak ditemukan/disetujui.'];
        header("Location: kelola_produk_usaha.php?usaha_id=" . $usaha_id); exit;
    }
    $stmt_check->close();

    // Ambil data dari form
    $nama_produk_layanan = trim($_POST['nama_produk_layanan'] ?? '');
    $kategori_produk_layanan = trim($_POST['kategori_produk_layanan'] ?? null);
    $deskripsi_produk_layanan = trim($_POST['deskripsi_produk_layanan'] ?? null);
    $harga = floatval($_POST['harga'] ?? 0);
    $satuan = trim($_POST['satuan'] ?? 'pcs');
    $stok_input = trim($_POST['stok'] ?? '');
    $stok = ($stok_input === '' || !is_numeric($stok_input)) ? null : intval($stok_input);
    $is_tersedia = isset($_POST['is_tersedia']) ? intval($_POST['is_tersedia']) : 1;
    $hapus_foto_lama_checkbox = isset($_POST['hapus_foto_lama']) ? 1 : 0;

    // Validasi dasar
    if (empty($nama_produk_layanan) || $harga < 0 || empty($satuan)) {
        $_SESSION['produk_form_message'] = ['type' => 'error', 'text' => 'Nama produk, harga (tidak boleh negatif), dan satuan wajib diisi.'];
        header("Location: edit_produk.php?id=" . $produk_id . "&usaha_id=" . $usaha_id); exit;
    }

    // Ambil path foto lama dari DB
    $stmt_foto_lama = $conn->prepare("SELECT foto_produk_layanan FROM produk_layanan WHERE id = ?");
    if (!$stmt_foto_lama) { /* Handle error */ $_SESSION['produk_form_message'] = ['type'=>'error','text'=>'DB Error (get old photo)']; header("Location: edit_produk.php?id=$produk_id&usaha_id=$usaha_id"); exit;}
    $stmt_foto_lama->bind_param("i", $produk_id);
    $stmt_foto_lama->execute();
    $result_foto_lama = $stmt_foto_lama->get_result()->fetch_assoc();
    $foto_lama_db = $result_foto_lama['foto_produk_layanan'] ?? null;
    $stmt_foto_lama->close();

    $foto_path_db_update = $foto_lama_db; // Defaultnya pakai foto lama

    // --- Proses Upload File Baru (Jika ada) ---
    if (isset($_FILES['foto_produk_layanan']) && $_FILES['foto_produk_layanan']['error'] == UPLOAD_ERR_OK && $_FILES['foto_produk_layanan']['size'] > 0) {
        $upload_dir_produk = '../uploads/produk_layanan/';
        if (!is_dir($upload_dir_produk)) {
            mkdir($upload_dir_produk, 0755, true);
        }
        $file_tmp_name = $_FILES['foto_produk_layanan']['tmp_name'];
        $file_name_original = basename($_FILES['foto_produk_layanan']['name']);
        $file_ext = strtolower(pathinfo($file_name_original, PATHINFO_EXTENSION));
        $allowed_ext = ['jpg', 'jpeg', 'png', 'gif'];

        if (in_array($file_ext, $allowed_ext)) {
            if ($_FILES['foto_produk_layanan']['size'] <= 2000000) { // Max 2MB
                $new_file_name = uniqid('produk_', true) . '.' . $file_ext;
                $destination = $upload_dir_produk . $new_file_name;
                if (move_uploaded_file($file_tmp_name, $destination)) {
                    // Hapus foto lama jika ada dan foto baru berhasil diupload
                    if ($foto_lama_db && file_exists('../' . $foto_lama_db)) {
                        unlink('../' . $foto_lama_db);
                    }
                    $foto_path_db_update = 'uploads/produk_layanan/' . $new_file_name;
                } else { 
                    $_SESSION['produk_form_message'] = ['type'=>'error','text'=>'Gagal memindahkan file foto baru.']; 
                    header("Location: edit_produk.php?id=$produk_id&usaha_id=$usaha_id"); exit;
                }
            } else { 
                $_SESSION['produk_form_message'] = ['type'=>'error','text'=>'Ukuran file foto baru terlalu besar (maks 2MB).']; 
                header("Location: edit_produk.php?id=$produk_id&usaha_id=$usaha_id"); exit;
            }
        } else { 
            $_SESSION['produk_form_message'] = ['type'=>'error','text'=>'Tipe file foto baru tidak diizinkan (hanya JPG, PNG, GIF).']; 
            header("Location: edit_produk.php?id=$produk_id&usaha_id=$usaha_id"); exit;
        }
    } elseif ($hapus_foto_lama_checkbox && $foto_lama_db) {
        // Jika TIDAK ada upload baru TAPI user mencentang hapus foto lama
        if (file_exists('../' . $foto_lama_db)) {
            unlink('../' . $foto_lama_db);
        }
        $foto_path_db_update = null; // Set jadi null di DB
    }
    // Jika tidak ada upload baru dan tidak dicentang hapus, $foto_path_db_update akan tetap berisi $foto_lama_db (default).
    // --- Akhir Proses Upload File ---

    // Update data di database
    $stmt_update = $conn->prepare("UPDATE produk_layanan SET 
                                    nama_produk_layanan = ?, kategori_produk_layanan = ?, deskripsi_produk_layanan = ?, 
                                    harga = ?, satuan = ?, stok = ?, foto_produk_layanan = ?, is_tersedia = ?
                                  WHERE id = ? AND usaha_id = ?"); // usaha_id juga untuk keamanan tambahan
    if (!$stmt_update) { 
        $_SESSION['produk_form_message'] = ['type'=>'error','text'=>'Database Error (prepare update): ' . $conn->error]; 
        header("Location: edit_produk.php?id=$produk_id&usaha_id=$usaha_id"); exit;
    }
    
    $stmt_update->bind_param("sssdsisiii", 
        $nama_produk_layanan, $kategori_produk_layanan, $deskripsi_produk_layanan, 
        $harga, $satuan, $stok, $foto_path_db_update, $is_tersedia,
        $produk_id, $usaha_id
    );

    if ($stmt_update->execute()) {
        $_SESSION['produk_message'] = ['type' => 'success', 'text' => 'Produk/Layanan berhasil diperbarui.'];
        header("Location: kelola_produk_usaha.php?usaha_id=" . $usaha_id);
    } else {
        $_SESSION['produk_form_message'] = ['type' => 'error', 'text' => 'Gagal memperbarui produk: ' . $stmt_update->error];
        header("Location: edit_produk.php?id=" . $produk_id . "&usaha_id=" . $usaha_id);
    }
    $stmt_update->close();
    $conn->close();
    exit;

} else {
    $_SESSION['produk_message'] = ['type' => 'error', 'text' => 'Aksi tidak valid atau data tidak lengkap.'];
    // Redirect ke daftar usaha pengguna jika usaha_id tidak jelas atau tidak ada POST
    $redirect_usaha_id = isset($_POST['usaha_id']) ? intval($_POST['usaha_id']) : (isset($_GET['usaha_id']) ? intval($_GET['usaha_id']) : null);
    if ($redirect_usaha_id) {
        header("Location: kelola_produk_usaha.php?usaha_id=" . $redirect_usaha_id);
    } else {
        header("Location: usaha_saya.php");
    }
    exit;
}
?>
<?php
session_start();
require '../config/db.php'; // Sesuaikan path ke file koneksi database Anda

// Pastikan pengguna sudah login
if (!isset($_SESSION['user_id'])) {
    // Jika tidak ada pesan login, buat satu
    if (!isset($_SESSION['login_messages'])) {
        $_SESSION['login_messages'] = [['type' => 'error', 'text' => 'Anda harus login untuk melakukan aksi ini.']];
    }
    header("Location: ../auth/login.php"); 
    exit;
}
$user_id = $_SESSION['user_id'];

// Pastikan parameter ID produk dan ID usaha ada
if (isset($_GET['id']) && isset($_GET['usaha_id'])) {
    $produk_id = intval($_GET['id']);
    $usaha_id = intval($_GET['usaha_id']); // Digunakan untuk redirect dan verifikasi tambahan

    // 1. Verifikasi bahwa produk ini milik usaha yang benar, 
    //    usaha tersebut milik pengguna yang login, dan usaha tersebut disetujui
    $stmt_check = $conn->prepare("SELECT pl.foto_produk_layanan 
                                  FROM produk_layanan pl
                                  JOIN usaha_mahasiswa um ON pl.usaha_id = um.id
                                  WHERE pl.id = ? AND um.id = ? AND um.user_id = ? AND um.status_pengajuan = 'Disetujui'");
    
    if (!$stmt_check) {
        $_SESSION['produk_message'] = ['type' => 'error', 'text' => 'Database error (check): ' . $conn->error];
        header("Location: kelola_produk_usaha.php?usaha_id=" . $usaha_id);
        exit;
    }
    
    $stmt_check->bind_param("iii", $produk_id, $usaha_id, $user_id);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();

    if ($result_check->num_rows === 1) {
        $produk_data = $result_check->fetch_assoc();
        $foto_produk_to_delete = $produk_data['foto_produk_layanan'];

        // 2. Hapus produk dari database
        $stmt_delete = $conn->prepare("DELETE FROM produk_layanan WHERE id = ?");
        if (!$stmt_delete) {
            $_SESSION['produk_message'] = ['type' => 'error', 'text' => 'Database error (delete): ' . $conn->error];
        } else {
            $stmt_delete->bind_param("i", $produk_id);
            if ($stmt_delete->execute()) {
                // 3. Jika berhasil hapus dari DB, hapus juga file fotonya dari server (jika ada)
                if ($foto_produk_to_delete && file_exists('../' . $foto_produk_to_delete)) {
                    // Pastikan path '../' sudah benar relatif terhadap posisi file hapus_produk.php ke root
                    if (!unlink('../' . $foto_produk_to_delete)) {
                        // Gagal menghapus file, bisa ditambahkan log error tapi jangan sampai menghentikan proses
                        // Mungkin file tidak ada atau permission issue
                        // error_log("Gagal menghapus file produk: ../" . $foto_produk_to_delete);
                    }
                }
                $_SESSION['produk_message'] = ['type' => 'success', 'text' => 'Produk/Layanan berhasil dihapus.'];
            } else {
                $_SESSION['produk_message'] = ['type' => 'error', 'text' => 'Gagal menghapus produk: ' . $stmt_delete->error];
            }
            $stmt_delete->close();
        }
    } else {
        $_SESSION['produk_message'] = ['type' => 'error', 'text' => 'Produk tidak ditemukan, bukan milik Anda, atau aksi tidak diizinkan.'];
    }
    $stmt_check->close();
    $conn->close();
    
    // Redirect kembali ke halaman kelola produk usaha
    header("Location: kelola_produk_usaha.php?usaha_id=" . $usaha_id);
    exit;

} else {
    $_SESSION['produk_message'] = ['type' => 'error', 'text' => 'Parameter tidak lengkap untuk menghapus produk.'];
    // Jika usaha_id tidak ada sama sekali, mungkin lebih baik ke daftar usaha utama pengguna
    if (isset($_GET['usaha_id'])) {
        header("Location: kelola_produk_usaha.php?usaha_id=" . intval($_GET['usaha_id']));
    } else {
        header("Location: usaha_saya.php");
    }
    exit;
}
?>
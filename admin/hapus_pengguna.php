<?php
session_start();
$pathPrefix = '../'; // Path dari admin/ ke root
$adminPathPrefix = ''; // Path di dalam folder admin itu sendiri

// Cek jika pengguna belum login, redirect ke halaman login
if (!isset($_SESSION['user_id'])) {
    header("Location: " . $pathPrefix . "auth/login.php");
    exit;
}

// Cek jika pengguna bukan admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    $_SESSION['login_messages'] = [['type' => 'error', 'text' => 'Akses ditolak. Anda tidak memiliki izin untuk mengakses halaman ini.']];
    header("Location: " . $pathPrefix . "auth/login.php?form=login");
    exit;
}

// Include koneksi database
require $pathPrefix . 'config/db.php';

if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['admin_user_message'] = ['type' => 'error', 'text' => 'ID pengguna tidak valid.'];
    header("Location: " . $adminPathPrefix . "manajemen_pengguna.php");
    exit;
}

$user_id = intval($_GET['id']);

// Hapus pengguna dari database
$stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
if ($stmt->execute()) {
    $_SESSION['admin_user_message'] = ['type' => 'success', 'text' => 'Pengguna berhasil dihapus.'];
} else {
    $_SESSION['admin_user_message'] = ['type' => 'error', 'text' => 'Gagal menghapus pengguna: ' . $conn->error];
}
$stmt->close();

header("Location: " . $adminPathPrefix . "manajemen_pengguna.php");
exit;
?>

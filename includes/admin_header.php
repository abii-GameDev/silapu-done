<?php
// session_start(); // Diasumsikan sudah dimulai di file pemanggil
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title><?php echo $pageTitle ?? 'Admin Dashboard'; ?> - SILAPU</title>
    <link rel="stylesheet" href="<?php echo ($pathPrefix ?? '../'); ?>assets/css/admin_style.css">
    <!-- Tambahkan link ke Font Awesome jika Anda menggunakannya -->
     <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">


    <!-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css"> -->
</head>
<body class="admin-body"> <?php // Tambahkan class admin-body ?>

    <?php 
        // Definisikan $adminPathPrefix di file pemanggil (misal, admin/dashboard.php)
        // $adminPathPrefix = ''; // Jika di dashboard.php
        // $adminPathPrefix = '../'; // Jika di file dalam subfolder admin (jarang)
        include 'admin_sidebar.php'; 
    ?>

    <div class="main-content-area">
        <header class="admin-top-header">
            <button class="sidebar-toggle-btn" id="adminSidebarToggle">☰</button> <?php // Tombol hamburger ?>
            <h1 class="page-main-title"><?php echo $pageTitle ?? 'Dashboard Admin'; ?></h1>
            <?php if(isset($_SESSION['user_id']) && isset($_SESSION['username'])): ?>
            <div class="admin-user-info">
                <span>Halo, <?php echo htmlspecialchars($_SESSION['username']); ?>!</span>
            </div>
            <?php endif; ?>
        </header>

        <main class="page-content <?php // echo ($useBgImage ?? false) ? 'with-bg-image' : ''; ?>"> 
        <?php // Untuk background image, set $useBgImage = true; di file pemanggil jika perlu ?>
            <div class="content-wrapper">
                <!-- Konten spesifik halaman akan dimulai di sini setelah include header ini -->
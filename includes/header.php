<?php
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) . ' - ' : ''; ?>SILAPU Kopma UIN RIL</title>
    <link rel="stylesheet" href="<?php echo ($pathPrefix ?? ''); ?>assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<style>
    :root {
        --light-green: #8cd790;
        --dark-green: #5cab7d;
        --red: #e74c3c;
        --yellow: #f1c40f;
        --white: #ffffff;
        --dark: #333333;
        --light-gray: #f5f5f5;
        --hover-green: #7ac282;
        --shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        --transition: all 0.3s ease;
    }

    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
        font-family: 'Poppins', sans-serif;
    }

    body {
        background-color: #f8f9fa;
    }

    /* Navbar styles */
    header {
        background-color: #ffffff;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        position: sticky;
        top: 0;
        z-index: 1000;
    }

    .container {
        width: 100%;
        max-width: 1300px;
        margin: 0 auto;
        padding: 0 15px;
    }

    .navbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        height: 70px;
        position: relative;
    }

    .navbar-left {
        display: flex;
        align-items: center;
    }

    .brand {
        display: flex;
        align-items: center;
        text-decoration: none;
        margin-right: 30px;
    }

    .brand img {
        height: 40px;
        width: auto;
        margin-right: 10px;
    }

    .brand-text {
        color: #333;
        font-weight: 600;
        font-size: 1.1rem;
        line-height: 1.2;
    }

    .nav-main {
        display: flex;
        align-items: center;
    }

    .nav-item {
        display: flex;
        flex-direction: column;
        align-items: center;
        margin: 0 15px;
        text-decoration: none;
        color: #555;
        font-size: 0.8rem;
        transition: color 0.2s ease;
    }

    .nav-item:hover {
        color: var(--dark-green);
    }

    .nav-item i {
        font-size: 1.2rem;
        margin-bottom: 4px;
    }

    .navbar-right {
        display: flex;
        align-items: center;
    }

    .user-section {
        display: flex;
        align-items: center;
    }

    .user-greeting {
        display: flex;
        align-items: center;
        font-size: 0.9rem;
        color: #555;
        margin-right: 10px;
    }

    .user-greeting i {
        margin-right: 5px;
    }

    .btn-account {
        display: flex;
        align-items: center;
        justify-content: center;
        background-color: #dff0d8;
        color: #3c763d;
        border: none;
        padding: 8px 15px;
        border-radius: 5px;
        font-size: 0.9rem;
        cursor: pointer;
        margin-right: 10px;
        text-decoration: none;
        transition: background-color 0.2s ease;
    }

    .btn-account:hover {
        background-color: #c1e2b3;
    }

    .btn-account i {
        margin-right: 5px;
    }

    .btn-account .fa-chevron-down {
        margin-left: 5px;
        font-size: 0.8rem;
    }

    .btn-logout {
        display: flex;
        align-items: center;
        justify-content: center;
        background-color: var(--yellow);
        color: #333;
        border: none;
        padding: 8px 15px;
        border-radius: 5px;
        font-size: 0.9rem;
        cursor: pointer;
        text-decoration: none;
        transition: background-color 0.2s ease;
    }

    .btn-logout:hover {
        background-color: #e1b70c;
    }

    .btn-logout i {
        margin-right: 5px;
    }

    .cart-icon {
        position: relative;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 15px;
        text-decoration: none;
    }

    .cart-icon i {
        font-size: 1.2rem;
        color: #555;
    }

    .cart-count {
        position: absolute;
        top: -8px;
        right: -8px;
        background-color: var(--red);
        color: white;
        border-radius: 50%;
        width: 18px;
        height: 18px;
        font-size: 0.7rem;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    /* Dropdown menu */
    .dropdown {
        position: relative;
    }

    .dropdown-menu {
        position: absolute;
        top: 100%;
        right: 0;
        background-color: white;
        border-radius: 5px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        min-width: 200px;
        display: none;
        z-index: 1000;
        margin-top: 5px;
    }

    .dropdown-menu a {
        display: flex;
        align-items: center;
        padding: 10px 15px;
        color: #333;
        text-decoration: none;
        font-size: 0.9rem;
        transition: background-color 0.2s ease;
    }

    .dropdown-menu a:hover {
        background-color: #f5f5f5;
    }

    .dropdown-menu a i {
        margin-right: 10px;
        width: 16px;
        text-align: center;
    }

    .dropdown:hover .dropdown-menu {
        display: block;
    }

    /* Mobile menu */
    .hamburger {
        display: none;
        background: none;
        border: none;
        font-size: 1.5rem;
        cursor: pointer;
        color: #333;
    }

    /* Media queries */
    @media (max-width: 992px) {
        .nav-main {
            display: none;
            position: absolute;
            top: 70px;
            left: 0;
            width: 100%;
            background-color: white;
            flex-direction: column;
            align-items: flex-start;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            padding: 10px 0;
            z-index: 999;
        }

        .nav-main.active {
            display: flex;
        }

        .nav-item {
            flex-direction: row;
            width: 100%;
            padding: 12px 20px;
            margin: 0;
        }

        .nav-item i {
            margin-right: 15px;
            margin-bottom: 0;
        }

        .hamburger {
            display: block;
            margin-left: 10px;
        }
    }

    @media (max-width: 768px) {
        .navbar-right {
            display: flex;
            align-items: center;
        }

        .user-greeting {
            display: none;
        }

        .cart-icon {
            margin-right: 10px;
        }

        /* Make dropdown always visible and position it better */
        .dropdown {
            position: static;
        }

        .dropdown-menu {
            position: absolute;
            top: 100%;
            right: 15px;
            width: auto;
            min-width: 200px;
        }

        /* Move logout to dropdown menu */
        .mobile-hidden {
            display: none !important;
        }
    }
</style>

<body>
    <header>
        <div class="container">
            <nav class="navbar">
                <div class="navbar-left">
                    <a href="<?php echo ($pathPrefix ?? ''); ?>beranda.php" class="brand">
                        <img src="<?php echo ($pathPrefix ?? ''); ?>assets/images/logokopma.jpg" alt="Logo Kopma">
                        <div class="brand-text">SILAPU Kopma UIN RIL</div>
                    </a>

                    <button class="hamburger" id="hamburgerBtn">
                        <i class="fas fa-bars"></i>
                    </button>

                    <div class="nav-main" id="navMain">
                        <a href="<?php echo ($pathPrefix ?? ''); ?>beranda.php" class="nav-item">
                            <i class="fas fa-home"></i>
                            <span>Beranda</span>
                        </a>
                        <a href="<?php echo ($pathPrefix ?? ''); ?>beranda.php#layanan" class="nav-item">
                            <i class="fas fa-clipboard-list"></i>
                            <span>Layanan</span>
                        </a>
                        <a href="<?php echo ($pathPrefix ?? ''); ?>marketplace.php" class="nav-item">
                            <i class="fas fa-store"></i>
                            <span>Marketplace</span>
                        </a>
                        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'user'):  
                        ?>
                            <a href="<?php echo ($pathPrefix ?? ''); ?>pendaftaran_anggota.php" class="nav-item">
                                <i class="fas fa-user-plus"></i>
                                <span>Daftar Anggota</span>
                            </a>
                        <?php endif; ?>

                        <a href="<?php echo ($pathPrefix ?? ''); ?>beranda.php#tentang" class="nav-item">
                            <i class="fas fa-info-circle"></i>
                            <span>Tentang</span>
                        </a>
                        <a href="<?php echo ($pathPrefix ?? ''); ?>beranda.php#kontak" class="nav-item">
                            <i class="fas fa-envelope"></i>
                            <span>Kontak</span>
                        </a>
                    </div>
                </div>

                <div class="navbar-right" id="navRight">
                    <a href="<?php echo ($pathPrefix ?? ''); ?>cart.php" class="cart-icon">
                        <i class="fas fa-shopping-cart"></i>
                        <?php
                        $cart_count = 0;
                        if (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
                            foreach ($_SESSION['cart'] as $item_in_cart) {
                                $cart_count += $item_in_cart['quantity'];
                            }
                        }
                        if ($cart_count > 0):
                        ?>
                            <span class="cart-count"><?php echo $cart_count; ?></span>
                        <?php endif; ?>
                    </a>

                    <?php if (isset($_SESSION['user_id'])): // User sudah login 
                    ?>
                        <div class="user-section">


                            <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): // Admin user 
                            ?>
                                <a href="<?php echo ($pathPrefix ?? ''); ?>admin/dashboard.php" class="btn-account">
                                    <i class="fas fa-tachometer-alt"></i>
                                    <span>Dashboard Admin</span>
                                </a>
                                <a href="<?php echo ($pathPrefix ?? ''); ?>auth/logout.php" class="btn-logout mobile-hidden">
                                    <i class="fas fa-sign-out-alt"></i>
                                    <span>Logout</span>
                                </a>
                            <?php else: // Regular user 
                            ?>
                                <div class="dropdown">
                                    <a href="javascript:void(0)" class="btn-account">
                                        <i class="fas fa-user-cog"></i>
                                        <span>Akun Saya</span>
                                        <i class="fas fa-chevron-down"></i>
                                    </a>
                                    <div class="dropdown-menu">
                                        <a href="<?php echo ($pathPrefix ?? ''); ?>user/dashboard.php">
                                            <i class="fas fa-tachometer-alt"></i>
                                            <span>Dashboard</span>
                                        </a>
                                        <a href="<?php echo ($pathPrefix ?? ''); ?>user/riwayat_pesanan.php">
                                            <i class="fas fa-history"></i>
                                            <span>Pesanan</span>
                                        </a>
                                        <a href="<?php echo ($pathPrefix ?? ''); ?>auth/logout.php">
                                            <i class="fas fa-sign-out-alt"></i>
                                            <span>Logout</span>
                                        </a>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php else: // User belum login 
                    ?>
                        <a href="<?php echo ($pathPrefix ?? ''); ?>auth/login.php" class="btn-logout">
                            <i class="fas fa-sign-in-alt"></i>
                            <span>Login</span>
                        </a>
                    <?php endif; ?>
                </div>
            </nav>
        </div>
    </header>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const hamburgerBtn = document.getElementById('hamburgerBtn');
            const navMain = document.getElementById('navMain');

            hamburgerBtn.addEventListener('click', function() {
                navMain.classList.toggle('active');
            });

            // Handle dropdown visibility for mobile
            const dropdownToggle = document.querySelectorAll('.btn-account');
            dropdownToggle.forEach(function(toggle) {
                toggle.addEventListener('click', function(e) {
                    if (window.innerWidth <= 768) {
                        e.preventDefault();
                        const parent = this.closest('.dropdown');
                        const menu = parent.querySelector('.dropdown-menu');

                        // Close all other dropdowns
                        const allDropdowns = document.querySelectorAll('.dropdown-menu');
                        allDropdowns.forEach(function(dropdown) {
                            if (dropdown !== menu) {
                                dropdown.style.display = 'none';
                            }
                        });

                        // Toggle current dropdown
                        menu.style.display = menu.style.display === 'block' ? 'none' : 'block';
                    }
                });
            });

            // Tutup dropdown saat user klik di luar
            document.addEventListener('click', function(event) {
                if (!event.target.closest('.dropdown') && !event.target.closest('.hamburger')) {
                    const dropdowns = document.querySelectorAll('.dropdown-menu');
                    dropdowns.forEach(dropdown => {
                        dropdown.style.display = '';
                    });
                }
            });

            // Check window width on resize
            window.addEventListener('resize', function() {
                if (window.innerWidth > 992) {
                    navMain.classList.remove('active');
                }

                // Show/hide mobile elements based on screen width
                const mobileElements = document.querySelectorAll('.mobile-hidden');
                if (window.innerWidth <= 768) {
                    mobileElements.forEach(el => el.classList.add('mobile-hidden'));
                } else {
                    mobileElements.forEach(el => el.classList.remove('mobile-hidden'));
                }
            });

            // Add admin logout to dropdown for mobile
            if (window.innerWidth <= 768) {
                const adminSection = document.querySelector('.user-section');
                if (adminSection && document.querySelector('.btn-logout.mobile-hidden')) {
                    // Check if admin and create dropdown if needed
                    if (!adminSection.querySelector('.dropdown')) {
                        const adminBtn = adminSection.querySelector('.btn-account');
                        if (adminBtn) {
                            // Create dropdown wrapper
                            const dropdown = document.createElement('div');
                            dropdown.className = 'dropdown';

                            // Create dropdown menu
                            const dropdownMenu = document.createElement('div');
                            dropdownMenu.className = 'dropdown-menu';

                            // Add logout link to dropdown
                            const logoutLink = document.createElement('a');
                            logoutLink.href = adminSection.querySelector('.btn-logout').href;
                            logoutLink.innerHTML = '<i class="fas fa-sign-out-alt"></i><span>Logout</span>';

                            // Add dashboard link
                            const dashboardLink = document.createElement('a');
                            dashboardLink.href = adminBtn.href;
                            dashboardLink.innerHTML = '<i class="fas fa-tachometer-alt"></i><span>Dashboard Admin</span>';

                            // Assemble dropdown
                            dropdownMenu.appendChild(dashboardLink);
                            dropdownMenu.appendChild(logoutLink);

                            // Replace button with dropdown
                            adminBtn.addEventListener('click', function(e) {
                                if (window.innerWidth <= 768) {
                                    e.preventDefault();
                                    dropdownMenu.style.display = dropdownMenu.style.display === 'block' ? 'none' : 'block';
                                }
                            });

                            adminBtn.querySelector('span').insertAdjacentHTML('afterend', ' <i class="fas fa-chevron-down"></i>');
                            dropdown.appendChild(adminBtn.cloneNode(true));
                            dropdown.appendChild(dropdownMenu);

                            // Replace the admin button with dropdown
                            adminBtn.parentNode.replaceChild(dropdown, adminBtn);
                        }
                    }
                }
            }
        });
    </script>
    <!-- Konten halaman akan dimulai setelah ini -->
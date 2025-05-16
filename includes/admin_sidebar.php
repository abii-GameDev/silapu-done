<?php
$current_page = basename($_SERVER['PHP_SELF']);
// $adminPathPrefix didefinisikan di file pemanggil (admin/dashboard.php, admin/manajemen_pengguna.php, dll.)
// $pathPrefix juga didefinisikan di file pemanggil
?>
<div class="sidebar" id="adminSidebar">
    <div class="sidebar-header">
        <a href="<?php echo ($adminPathPrefix ?? ''); ?>dashboard.php" class="sidebar-brand-link">
            <i class="fas fa-layer-group"></i>
            <h2>Admin SILAPU</h2>
        </a>
    </div>

    <div class="sidebar-user">
        <div class="sidebar-user-avatar">
            <i class="fas fa-user-circle"></i>
        </div>
        <div class="sidebar-user-info">
            <span class="user-name"><?php echo isset($_SESSION['nama']) ? $_SESSION['nama'] : 'Admin'; ?></span>
            <span class="user-role">Administrator</span>
        </div>
    </div>

    <div class="sidebar-divider">
        <span>Menu Utama</span>
    </div>

    <nav class="sidebar-menu">
        <a href="<?php echo ($adminPathPrefix ?? ''); ?>dashboard.php" class="sidebar-item <?php echo ($current_page == 'dashboard.php') ? 'active' : ''; ?>">
            <i class="fas fa-tachometer-alt"></i>
            <span>Dashboard</span>
        </a>

        <a href="<?php echo ($adminPathPrefix ?? ''); ?>manajemen_pengguna.php" class="sidebar-item <?php echo ($current_page == 'manajemen_pengguna.php') ? 'active' : ''; ?>">
            <i class="fas fa-users-cog"></i>
            <span>Manajemen Pengguna</span>
        </a>

        <a href="<?php echo ($adminPathPrefix ?? ''); ?>manajemen_anggota.php" class="sidebar-item <?php echo ($current_page == 'manajemen_anggota.php' || $current_page == 'detail_anggota.php') ? 'active' : ''; ?>">
            <i class="fas fa-user-friends"></i>
            <span>Manajemen Anggota</span>
        </a>

        <a href="<?php echo ($adminPathPrefix ?? ''); ?>manajemen_usaha.php" class="sidebar-item <?php echo ($current_page == 'manajemen_usaha.php' || $current_page == 'detail_usaha.php') ? 'active' : ''; ?>">
            <i class="fas fa-store"></i>
            <span>Manajemen Usaha</span>
        </a>

        <a href="<?php echo ($adminPathPrefix ?? ''); ?>manajemen_pesanan.php" class="sidebar-item <?php echo ($current_page == 'manajemen_pesanan.php' || $current_page == 'detail_pesanan.php') ? 'active' : ''; ?>">
            <i class="fas fa-shopping-cart"></i>
            <span>Manajemen Pesanan</span>
        </a>
        <a href="<?php echo ($adminPathPrefix ?? ''); ?>manajemen_berita.php" class="sidebar-item <?php echo ($current_page == 'manajemen_berita.php' || $current_page == 'tambah_berita.php' || $current_page == 'edit_berita.php') ? 'active' : ''; ?>">
            <i class="fas fa-newspaper"></i> 
            <span>Manajemen Berita</span>
        </a>
    </nav>

    <div class="sidebar-divider">
        <span>Pengaturan</span>
    </div>

    <nav class="sidebar-menu">

        <a href="<?php echo ($pathPrefix ?? '../'); ?>auth/logout.php" class="sidebar-item logout-link">
            <i class="fas fa-sign-out-alt"></i>
            <span>Logout</span>
        </a>
    </nav>

    <div class="sidebar-footer">
        <p>SILAPU &copy; <?php echo date('Y'); ?></p>
    </div>
</div>

<style>
    /* Sidebar Styles */
    .sidebar {
        position: fixed;
        left: 0;
        top: 0;
        height: 100vh;
        width: 250px;
        background: linear-gradient(180deg, #2c3e50, #1a252f);
        color: #ecf0f1;
        box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
        transition: all 0.3s ease;
        z-index: 1000;
        overflow-y: auto;
        display: flex;
        flex-direction: column;
    }

    /* Sidebar Header */
    .sidebar-header {
        padding: 15px 20px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    }

    .sidebar-brand-link {
        display: flex;
        align-items: center;
        text-decoration: none;
        color: #ecf0f1;
    }

    .sidebar-brand-link i {
        font-size: 1.5rem;
        margin-right: 10px;
        color: #3498db;
    }

    .sidebar-brand-link h2 {
        font-size: 1.2rem;
        font-weight: 600;
        margin: 0;
    }

    .sidebar-toggle {
        background: transparent;
        border: none;
        color: #ecf0f1;
        font-size: 1.25rem;
        cursor: pointer;
    }

    /* User Info */
    .sidebar-user {
        padding: 20px;
        display: flex;
        align-items: center;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    }

    .sidebar-user-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: #3498db;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .sidebar-user-avatar i {
        font-size: 1.5rem;
        color: #fff;
    }

    .sidebar-user-info {
        margin-left: 10px;
        display: flex;
        flex-direction: column;
    }

    .user-name {
        font-weight: 600;
        font-size: 0.9rem;
    }

    .user-role {
        font-size: 0.75rem;
        color: #bdc3c7;
    }

    /* Divider */
    .sidebar-divider {
        padding: 10px 20px;
        font-size: 0.7rem;
        text-transform: uppercase;
        color: #95a5a6;
        font-weight: 600;
        letter-spacing: 1px;
    }

    /* Menu Items */
    .sidebar-menu {
        padding: 0;
        flex-grow: 1;
    }

    .sidebar-item {
        display: flex;
        align-items: center;
        padding: 12px 20px;
        color: #ecf0f1;
        text-decoration: none;
        transition: all 0.2s ease;
        border-left: 4px solid transparent;
    }

    .sidebar-item:hover {
        background-color: rgba(255, 255, 255, 0.05);
        border-left: 4px solid #3498db;
    }

    .sidebar-item.active {
        background-color: rgba(52, 152, 219, 0.2);
        border-left: 4px solid #3498db;
    }

    .sidebar-item i {
        font-size: 1rem;
        width: 20px;
        margin-right: 10px;
        text-align: center;
    }

    .sidebar-item span {
        font-size: 0.9rem;
    }

    /* Logout Link Special Styling */
    .sidebar-item.logout-link {
        margin-top: 10px;
        color: #e74c3c;
    }

    .sidebar-item.logout-link:hover {
        background: rgba(231, 76, 60, 0.1);
        border-left: 4px solid #e74c3c;
    }

    /* Footer */
    .sidebar-footer {
        padding: 15px 20px;
        text-align: center;
        font-size: 0.75rem;
        color: #95a5a6;
        border-top: 1px solid rgba(255, 255, 255, 0.1);
    }

    /* Responsive Behavior */
    @media (max-width: 768px) {
        .sidebar {
            transform: translateX(-100%);
        }

        .sidebar.show {
            transform: translateX(0);
        }

        /* Add this to your main content container 
    .main-content {
        margin-left: 0;
    }
    */
    }

    /* Optional: For when sidebar is collapsed */
    .sidebar.collapsed {
        width: 70px;
    }

    .sidebar.collapsed .sidebar-brand-link h2,
    .sidebar.collapsed .sidebar-user-info,
    .sidebar.collapsed .sidebar-item span,
    .sidebar.collapsed .sidebar-divider span,
    .sidebar.collapsed .sidebar-footer {
        display: none;
    }

    .sidebar.collapsed .sidebar-item {
        justify-content: center;
        padding: 15px 0;
    }

    .sidebar.collapsed .sidebar-user {
        justify-content: center;
    }

    .sidebar.collapsed .sidebar-user-avatar {
        margin: 0;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Toggle sidebar on mobile
        const sidebarToggle = document.getElementById('sidebarToggle');
        const sidebar = document.getElementById('adminSidebar');

        if (sidebarToggle) {
            sidebarToggle.addEventListener('click', function() {
                sidebar.classList.toggle('show');
            });
        }

        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', function(event) {
            const isClickInsideSidebar = sidebar.contains(event.target);
            const isClickOnToggleButton = sidebarToggle && sidebarToggle.contains(event.target);

            if (!isClickInsideSidebar && !isClickOnToggleButton && window.innerWidth <= 768 && sidebar.classList.contains('show')) {
                sidebar.classList.remove('show');
            }
        });

        // Optional: Add this if you want a collapsible sidebar on desktop too
        /*
        const collapseBtn = document.getElementById('collapseBtn');
        if (collapseBtn) {
            collapseBtn.addEventListener('click', function() {
                sidebar.classList.toggle('collapsed');
                // Also adjust your main content here if needed
                // document.querySelector('.main-content').classList.toggle('expanded');
            });
        }
        */
    });
</script>
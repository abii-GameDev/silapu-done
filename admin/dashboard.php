<?php
session_start();
$pathPrefix = '../'; // Path dari admin/ ke root
$adminPathPrefix = ''; // Path di dalam folder admin itu sendiri (untuk link antar halaman admin)
$pageTitle = "Dashboard Utama Admin";
$useBgImage = true; // Variabel untuk menandakan penggunaan background image di admin_header.php

// Cek jika pengguna belum login atau bukan admin (logika ini sudah ada dan tetap)
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    // ... (logika redirect) ...
    $_SESSION['login_messages'] = [['type' => 'error', 'text' => 'Akses ditolak. Anda tidak memiliki izin.']];
    header("Location: " . $pathPrefix . "auth/login.php");
    exit;
}

// Include koneksi database (jika belum di-include oleh admin_header.php atau jika diperlukan di sini)
require_once $pathPrefix . 'config/db.php';

// Ambil data statistik (contoh)
$total_users = 0;
$stmt_users = $conn->query("SELECT COUNT(id) as total FROM users");
if ($stmt_users) $total_users = $stmt_users->fetch_assoc()['total'];

$total_anggota_aktif = 0;
$stmt_anggota = $conn->query("SELECT COUNT(id) as total FROM data_anggota WHERE status_keanggotaan = 'Aktif'");
if ($stmt_anggota) $total_anggota_aktif = $stmt_anggota->fetch_assoc()['total'];

$total_usaha_disetujui = 0;
$stmt_usaha = $conn->query("SELECT COUNT(id) as total FROM usaha_mahasiswa WHERE status_pengajuan = 'Disetujui'");
if ($stmt_usaha) $total_usaha_disetujui = $stmt_usaha->fetch_assoc()['total'];

$total_pesanan_selesai = 0;
$stmt_pesanan = $conn->query("SELECT COUNT(id) as total FROM pesanan WHERE status_pesanan = 'Selesai'");
if ($stmt_pesanan) $total_pesanan_selesai = $stmt_pesanan->fetch_assoc()['total'];


// Include header admin
// Variabel $pageTitle dan $pathPrefix sudah didefinisikan di atas
// $useBgImage akan digunakan oleh admin_header.php untuk menambahkan class with-bg-image jika true
include $pathPrefix . 'includes/admin_header.php'; 
?>

<style>
    /* Style Internal Khusus untuk Konten admin/dashboard.php */
    .dashboard-greeting {
        padding: 25px;
        background-color: rgba(255, 255, 255, 0.9); /* Sedikit transparan di atas background image */
        border-radius: 8px;
        margin-bottom: 30px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    .dashboard-greeting h2 {
        color: var(--dark-green);
        margin-top: 0;
        margin-bottom: 10px;
        font-size: 1.8rem;
    }
    .dashboard-greeting p {
        color: var(--text-dark);
        font-size: 1.1rem;
        line-height: 1.6;
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        gap: 25px;
        margin-bottom: 30px;
    }
    .stat-card {
        background-color: var(--white);
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        display: flex;
        align-items: center;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 5px 15px rgba(0,0,0,0.12);
    }
    .stat-card .stat-icon {
        font-size: 2.8rem; /* Ukuran ikon */
        padding: 15px;
        border-radius: 50%; /* Lingkaran */
        margin-right: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        width: 70px; /* Lebar dan tinggi sama untuk lingkaran sempurna */
        height: 70px;
    }
    .stat-card .stat-icon.users { background-color: rgba(0, 123, 255, 0.15); color: var(--primary-blue); } /* Biru */
    .stat-card .stat-icon.anggota { background-color: rgba(40, 167, 69, 0.15); color: var(--success-green); } /* Hijau */
    .stat-card .stat-icon.usaha { background-color: rgba(255, 193, 7, 0.15); color: var(--warning-yellow); } /* Kuning */
    .stat-card .stat-icon.pesanan { background-color: rgba(23, 162, 184, 0.15); color: var(--info-blue); } /* Biru muda */
    
    .stat-card .stat-info h4 {
        font-size: 1rem;
        color: #6c757d; /* Abu-abu */
        margin-top: 0;
        margin-bottom: 5px;
        text-transform: uppercase;
        font-weight: 600;
    }
    .stat-card .stat-info p {
        font-size: 2rem;
        color: var(--text-dark);
        margin: 0;
        font-weight: 700;
    }

    .quick-links-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
    }
    .quick-link-card {
        display: block;
        background-color: var(--white);
        padding: 20px;
        border-radius: 8px;
        text-align: center;
        text-decoration: none;
        color: var(--text-dark);
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .quick-link-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 5px 15px rgba(0,0,0,0.12);
        color: var(--dark-green); /* Warna teks berubah saat hover */
    }
    .quick-link-card .quick-link-icon {
        font-size: 2.5rem;
        color: var(--dark-green);
        margin-bottom: 10px;
    }
    .quick-link-card h5 {
        margin: 0;
        font-size: 1.1rem;
        font-weight: 600;
    }

    /* Jika menggunakan background image dari admin_style.css, ini mungkin tidak perlu */
    /* Jika .page-content tidak memiliki background spesifik (misal putih) */
    /* .page-content.with-bg-image .dashboard-greeting,
    .page-content.with-bg-image .stat-card,
    .page-content.with-bg-image .quick-link-card {
        background-color: rgba(255, 255, 255, 0.9); 
    }
    .page-content.with-bg-image .stat-card .stat-info h4,
    .page-content.with-bg-image .stat-card .stat-info p {
        color: var(--text-dark); 
    } */

</style>

<!-- Konten spesifik untuk Dashboard Utama Admin -->
<div class="dashboard-greeting">
    <h2>Selamat Datang di Dashboard Admin!</h2>
    <p>
        Halo, <strong><?php echo htmlspecialchars($_SESSION['username']); ?></strong>! Dari sini Anda dapat mengelola berbagai aspek sistem SILAPU.
    </p>
</div>

<!-- Grid Statistik -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon users">👥</div> <!-- Ganti dengan ikon SVG/Font jika ada -->
        <div class="stat-info">
            <h4>Total Pengguna</h4>
            <p><?php echo $total_users; ?></p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon anggota">🤝</div>
        <div class="stat-info">
            <h4>Anggota Aktif</h4>
            <p><?php echo $total_anggota_aktif; ?></p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon usaha">💡</div>
        <div class="stat-info">
            <h4>Usaha Disetujui</h4>
            <p><?php echo $total_usaha_disetujui; ?></p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon pesanan">🛒</div>
        <div class="stat-info">
            <h4>Pesanan Selesai</h4>
            <p><?php echo $total_pesanan_selesai; ?></p>
        </div>
    </div>
</div>

<!-- Quick Links -->
<div>
    <h3 class="section-title" style="color:var(--dark-green); border-bottom-color:var(--light-green); text-shadow:none;">Akses Cepat</h3>
    <div class="quick-links-grid">
        <a href="<?php echo $adminPathPrefix; ?>manajemen_pengguna.php" class="quick-link-card">
            <div class="quick-link-icon">👤</div>
            <h5>Manajemen Pengguna</h5>
        </a>
        <a href="<?php echo $adminPathPrefix; ?>manajemen_anggota.php" class="quick-link-card">
            <div class="quick-link-icon">🧑‍🤝‍🧑</div>
            <h5>Manajemen Anggota</h5>
        </a>
        <a href="<?php echo $adminPathPrefix; ?>manajemen_usaha.php" class="quick-link-card">
            <div class="quick-link-icon">🏪</div>
            <h5>Manajemen Usaha</h5>
        </a>
        <a href="<?php echo $adminPathPrefix; ?>manajemen_pesanan.php" class="quick-link-card">
            <div class="quick-link-icon">📦</div>
            <h5>Manajemen Pesanan</h5>
        </a>
        
        <!-- Tambahkan link cepat lainnya jika perlu -->
    </div>
</div>


<?php
// Include footer admin
// Variabel $pathPrefix sudah didefinisikan di atas
if(isset($conn)) $conn->close(); // Pastikan koneksi ditutup jika dibuka di file ini
include $pathPrefix . 'includes/admin_footer.php';
?>
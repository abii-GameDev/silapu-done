<?php
session_start();
$pathPrefix = '../'; // Path dari user/ ke root
$pageTitle = "Dashboard Mahasiswa";

// Cek jika pengguna belum login
if (!isset($_SESSION['user_id'])) {
    $_SESSION['login_messages'] = [['type' => 'error', 'text' => 'Anda harus login untuk mengakses halaman ini.']];
    header("Location: " . $pathPrefix . "auth/login.php");
    exit;
}

$user_id = $_SESSION['user_id']; // Ambil user_id dari session

// Include koneksi database
require $pathPrefix . 'config/db.php';

// Cek apakah pengguna memiliki usaha yang sudah disetujui
$punya_usaha_disetujui = false;
$usaha_pertama_id = null; // Bisa digunakan jika ingin link langsung ke usaha pertama yang disetujui

$stmt_cek_usaha_user = $conn->prepare("SELECT id FROM usaha_mahasiswa WHERE user_id = ? AND status_pengajuan = 'Disetujui' LIMIT 1");
if ($stmt_cek_usaha_user) {
    $stmt_cek_usaha_user->bind_param("i", $user_id);
    $stmt_cek_usaha_user->execute();
    $result_cek_usaha_user = $stmt_cek_usaha_user->get_result();
    if ($result_cek_usaha_user->num_rows > 0) {
        $punya_usaha_disetujui = true;
        // $usaha_pertama_data = $result_cek_usaha_user->fetch_assoc();
        // $usaha_pertama_id = $usaha_pertama_data['id'];
    }
    $stmt_cek_usaha_user->close();
}


// Cek apakah pengguna sudah terdaftar sebagai anggota koperasi
$is_anggota_koperasi_aktif = false;
$stmt_cek_anggota = $conn->prepare("SELECT id FROM data_anggota WHERE email = ? AND status_keanggotaan = 'Aktif'");
// Asumsi email di session adalah email yang dipakai untuk daftar anggota. Jika beda, perlu penyesuaian.
if ($stmt_cek_anggota && isset($_SESSION['email'])) {
    $stmt_cek_anggota->bind_param("s", $_SESSION['email']);
    $stmt_cek_anggota->execute();
    $result_cek_anggota = $stmt_cek_anggota->get_result();
    if ($result_cek_anggota->num_rows > 0) {
        $is_anggota_koperasi_aktif = true;
    }
    $stmt_cek_anggota->close();
}


// Include header publik
include $pathPrefix . 'includes/header.php';
?>

<section class="user-dashboard-section" style="padding: 40px 20px; background-color: var(--light-gray);">
    <div class="container">
        <h2 class="section-title" style="color: var(--dark-green); text-shadow: none; margin-bottom: 15px; text-align:left;">
            <?php echo $pageTitle; ?>
        </h2>
        <p style="font-size: 1.2rem; color: var(--dark); margin-bottom: 30px;">
            Selamat datang kembali, <?php echo htmlspecialchars($_SESSION['username']); ?>!
        </p>

        <div style="background-color: var(--white); padding: 25px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
            <h3 style="color: var(--dark); margin-bottom: 20px; border-bottom: 1px solid #eee; padding-bottom: 10px;">Menu Cepat Anda:</h3>
            
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px;">
                
                <a href="<?php echo $pathPrefix; ?>user/profil_saya.php" class="dashboard-card" style="background-color: var(--light-green);">
                    <div class="card-icon" style="font-size: 2.5rem;">👤</div>
                    <h4>Profil Saya</h4>
                    <p>Lihat dan perbarui informasi profil Anda.</p>
                </a>

                <?php if (!$is_anggota_koperasi_aktif): ?>
                    <a href="<?php echo $pathPrefix; ?>pendaftaran_anggota.php" class="dashboard-card" style="background-color: var(--red); color:white;">
                        <div class="card-icon" style="font-size: 2.5rem;">📝</div>
                        <h4>Daftar Jadi Anggota Koperasi Dulu!</h4>
                        <p>Anda harus menjadi anggota aktif untuk bisa mengajukan usaha.</p>
                    </a>
                <?php elseif ($punya_usaha_disetujui): ?>
                    <a href="<?php echo $pathPrefix; ?>user/usaha_saya.php" class="dashboard-card" style="background-color: var(--yellow);">
                        <div class="card-icon" style="font-size: 2.5rem;">🏪</div>
                        <h4>Kelola Usaha Saya</h4>
                        <p>Lihat daftar usaha Anda dan kelola produk/layanan.</p>
                    </a>
                    <a href="<?php echo $pathPrefix; ?>user/ajukan_usaha.php" class="dashboard-card" style="background-color: #B2DFDB;">
                        <div class="card-icon" style="font-size: 2.5rem;">➕💡</div>
                        <h4>Ajukan Usaha Lain</h4>
                        <p>Daftarkan ide bisnis baru Anda.</p>
                    </a>
                <?php else: // Anggota aktif, tapi belum punya usaha disetujui ?>
                    <a href="<?php echo $pathPrefix; ?>user/ajukan_usaha.php" class="dashboard-card" style="background-color: var(--yellow);">
                        <div class="card-icon" style="font-size: 2.5rem;">💡</div>
                        <h4>Ajukan Usaha</h4>
                        <p>Daftarkan usaha baru Anda untuk mendapatkan dukungan.</p>
                    </a>
                <?php endif; ?>

                <a href="<?php echo $pathPrefix; ?>marketplace.php" class="dashboard-card" style="background-color: #AEC6CF;"> <!-- Warna biru muda -->
                    <div class="card-icon" style="font-size: 2.5rem;">🛍️</div>
                    <h4>Marketplace KOPMA</h4>
                    <p>Jelajahi produk dan jasa dari anggota koperasi.</p>
                </a>
                
                <a href="<?php echo $pathPrefix; ?>user/riwayat_pesanan.php" class="dashboard-card" style="background-color: #FFB347;"> <!-- Warna oranye muda -->
                    <div class="card-icon" style="font-size: 2.5rem;">🛒</div>
                    <h4>Riwayat Pesanan</h4>
                    <p>Lihat semua pesanan yang telah Anda buat.</p>
                </a>

            </div>
        </div>
    </div>
</section>

<style>
    /* Pastikan style .dashboard-card sudah ada di assets/css/style.css */
    .dashboard-card {
        display: block;
        padding: 20px;
        border-radius: 8px;
        text-decoration: none;
        color: var(--dark);
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
        text-align: center;
    }
    .dashboard-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 4px 10px rgba(0,0,0,0.15);
    }
    .dashboard-card h4 {
        margin-top: 10px;
        margin-bottom: 8px;
        font-size: 1.3rem;
    }
    .dashboard-card p {
        font-size: 0.9rem;
        color: #555; /* Disesuaikan agar kontras dengan berbagai background card */
    }
    .dashboard-card[style*="color: white;"] p { /* Khusus untuk card dengan teks putih */
        color: #f0f0f0;
    }
    .dashboard-card .card-icon {
       margin-bottom: 10px;
    }
</style>

<?php
if(isset($conn)) $conn->close();
// Include footer
include $pathPrefix . 'includes/footer.php';
?>
<?php
session_start();
$pathPrefix = '../'; // Path dari user/ ke root
$pageTitle = "Detail Usaha Saya"; // Judul akan disesuaikan

// Cek jika pengguna belum login
if (!isset($_SESSION['user_id'])) {
    $_SESSION['login_messages'] = [['type' => 'error', 'text' => 'Anda harus login untuk mengakses halaman ini.']];
    header("Location: " . $pathPrefix . "auth/login.php");
    exit;
}

$user_id = $_SESSION['user_id']; // Ambil user_id dari session

// Include koneksi database
require $pathPrefix . 'config/db.php';

$usaha_detail = null;
$error_message = '';

if (!isset($_GET['id'])) {
    $error_message = "ID usaha tidak disediakan.";
} else {
    $usaha_id = intval($_GET['id']);

    // Ambil detail usaha, pastikan usaha ini milik user yang login
    $stmt = $conn->prepare("SELECT * FROM usaha_mahasiswa WHERE id = ? AND user_id = ?");
    if (!$stmt) {
        $error_message = "Database error (prepare): " . $conn->error;
    } else {
        $stmt->bind_param("ii", $usaha_id, $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows === 1) {
            $usaha_detail = $result->fetch_assoc();
            $pageTitle = "Detail Usaha: " . htmlspecialchars($usaha_detail['nama_usaha']);
        } else {
            // Bisa jadi usaha tidak ditemukan atau bukan milik user ini
            $error_message = "Data usaha tidak ditemukan atau Anda tidak memiliki akses.";
        }
        $stmt->close();
    }
}

// Include header
include $pathPrefix . 'includes/header.php';
?>

<style>
    :root {
        --light-green: #8cd790;
        --dark-green: #5cab7d;
        --red: #e74c3c;
        --yellow: #f1c40f;
        --white: #ffffff;
        --dark: #333333;
        --light-gray: #f9f9f9;
        --hover-green: #7ac282;
        --shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        --border-radius: 10px;
        --transition: all 0.3s ease;
    }
    
    .detail-usaha-container {
        max-width: 1140px;
        margin: 0 auto;
        padding: 35px;
        background-color: var(--white);
        border-radius: var(--border-radius);
        box-shadow: var(--shadow);
    }
    
    .page-title {
        font-size: 2.2rem;
        color: var(--dark-green);
        margin-bottom: 25px;
        font-weight: 700;
        position: relative;
        padding-bottom: 10px;
    }
    
    .page-title:after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        width: 80px;
        height: 4px;
        background-color: var(--dark-green);
        border-radius: 2px;
    }
    
    .back-button {
        display: inline-flex;
        align-items: center;
        margin-bottom: 25px;
        text-decoration: none;
        background-color: var(--dark-green);
        color: white;
        padding: 10px 20px;
        border-radius: 6px;
        font-weight: 600;
        transition: var(--transition);
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }
    
    .back-button:hover {
        background-color: var(--hover-green);
        transform: translateY(-2px);
    }
    
    .back-button:before {
        content: '«';
        margin-right: 8px;
        font-size: 18px;
    }
    
    .error-message {
        background-color: #f8d7da;
        color: #721c24;
        padding: 15px;
        border-radius: 8px;
        border-left: 5px solid #f5c6cb;
        margin-bottom: 25px;
    }
    
    .detail-card {
        background-color: var(--white);
        padding: 30px;
        border-radius: var(--border-radius);
        box-shadow: 0 2px 15px rgba(0,0,0,0.05);
        border: 1px solid #eaeaea;
    }
    
    .detail-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 40px;
    }
    
    .info-section {
        margin-bottom: 25px;
    }
    
    .section-title {
        color: var(--dark-green);
        font-size: 1.3rem;
        margin-bottom: 20px;
        padding-bottom: 10px;
        border-bottom: 2px solid #eaeaea;
        position: relative;
    }
    
    .section-title:after {
        content: '';
        position: absolute;
        bottom: -2px;
        left: 0;
        width: 60px;
        height: 2px;
        background-color: var(--dark-green);
    }
    
    .info-item {
        display: flex;
        margin-bottom: 18px;
        align-items: flex-start;
    }
    
    .info-label {
        font-weight: 600;
        min-width: 140px;
        color: var(--dark);
        padding-right: 10px;
    }
    
    .info-value {
        flex: 1;
        color: #555;
        line-height: 1.5;
    }
    
    .status-badge {
        display: inline-block;
        padding: 8px 15px;
        border-radius: 50px;
        font-weight: 600;
        font-size: 0.9rem;
        margin-top: 5px;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }
    
    .status-approved {
        background-color: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
    }
    
    .status-rejected {
        background-color: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
    }
    
    .status-pending {
        background-color: #fff3cd;
        color: #856404;
        border: 1px solid #ffeeba;
    }
    
    .admin-note {
        margin-top: 20px;
        padding: 15px;
        background-color: #fff3cd;
        border: 1px solid #ffeeba;
        border-radius: 8px;
        color: #856404;
    }
    
    .admin-note-title {
        font-weight: 600;
        margin-bottom: 8px;
        display: block;
    }
    
    .image-section {
        margin-top: 30px;
    }
    
    .product-image {
        width: 100%;
        max-height: 400px;
        object-fit: contain;
        border-radius: 8px;
        border: 1px solid #eaeaea;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        padding: 10px;
        background-color: #fafafa;
    }
    
    .divider {
        margin: 35px 0;
        height: 1px;
        background-color: #eaeaea;
        border: none;
    }
    
    .action-buttons {
        text-align: right;
        margin-top: 20px;
    }
    
    .btn {
        display: inline-block;
        text-decoration: none;
        padding: 12px 24px;
        border-radius: 6px;
        font-weight: 600;
        margin-left: 10px;
        transition: var(--transition);
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    
    .btn-edit {
        background-color: var(--yellow);
        color: var(--dark);
    }
    
    .btn-edit:hover {
        background-color: #e6b800;
        transform: translateY(-2px);
    }
    
    .btn-manage {
        background-color: var(--dark-green);
        color: var(--white);
    }
    
    .btn-manage:hover {
        background-color: var(--hover-green);
        transform: translateY(-2px);
    }
    
    .btn-delete {
        background-color: var(--red);
        color: var(--white);
    }
    
    .btn-delete:hover {
        background-color: #c0392b;
        transform: translateY(-2px);
    }
    
    @media (max-width: 992px) {
        .detail-grid {
            grid-template-columns: 1fr;
        }
        
        .info-section {
            margin-bottom: 30px;
        }
    }
    
    @media (max-width: 768px) {
        .action-buttons {
            text-align: center;
        }
        
        .btn {
            margin: 8px 0;
            display: block;
            width: 100%;
        }
        
        .info-label {
            min-width: 110px;
        }
    }
    
    @media (max-width: 576px) {
        .detail-usaha-container {
            padding: 20px 15px;
        }
        
        .info-item {
            flex-direction: column;
        }
        
        .info-label {
            margin-bottom: 5px;
        }
    }
</style>

<section class="detail-usaha-saya-section" style="padding: 40px 20px; background-color: var(--light-gray);">
    <div class="detail-usaha-container">
        <h2 class="page-title"><?php echo $pageTitle; ?></h2>

        <a href="<?php echo $pathPrefix; ?>user/usaha_saya.php" class="back-button">Kembali ke Daftar Usaha Saya</a>

        <?php if ($error_message): ?>
            <div class="error-message"><?php echo htmlspecialchars($error_message); ?></div>
        <?php elseif ($usaha_detail): ?>
            <div class="detail-card">
                <div class="detail-grid">
                    <div class="info-section">
                        <h3 class="section-title">Informasi Usaha</h3>
                        
                        <div class="info-item">
                            <span class="info-label">Nama Usaha:</span>
                            <span class="info-value"><?php echo htmlspecialchars($usaha_detail['nama_usaha']); ?></span>
                        </div>
                        
                        <div class="info-item">
                            <span class="info-label">Kategori:</span>
                            <span class="info-value"><?php echo htmlspecialchars($usaha_detail['kategori_usaha']); ?></span>
                        </div>
                        
                        <div class="info-item">
                            <span class="info-label">Alamat Usaha:</span>
                            <span class="info-value"><?php echo htmlspecialchars($usaha_detail['alamat_usaha'] ?? '-'); ?></span>
                        </div>
                        
                        <div class="info-item">
                            <span class="info-label">Kontak Usaha:</span>
                            <span class="info-value"><?php echo htmlspecialchars($usaha_detail['kontak_usaha'] ?? '-'); ?></span>
                        </div>
                    </div>
                    
                    <div class="info-section">
                        <h3 class="section-title">Status & Pengajuan</h3>
                        
                        <div class="info-item">
                            <span class="info-label">Tanggal Pengajuan:</span>
                            <span class="info-value"><?php echo date('d F Y, H:i', strtotime($usaha_detail['tanggal_pengajuan'])); ?></span>
                        </div>
                        
                        <div class="info-item" style="display: block;">
                            <span class="info-label" style="display: block; margin-bottom: 8px;">Status Pengajuan:</span>
                            <span class="status-badge <?php 
                                if ($usaha_detail['status_pengajuan'] == 'Disetujui') echo 'status-approved';
                                elseif ($usaha_detail['status_pengajuan'] == 'Ditolak') echo 'status-rejected';
                                else echo 'status-pending'; // Menunggu Persetujuan atau Ditangguhkan
                            ?>">
                                <?php echo htmlspecialchars($usaha_detail['status_pengajuan']); ?>
                            </span>
                        </div>
                        
                        <?php if (!empty($usaha_detail['catatan_admin'])): ?>
                            <div class="admin-note">
                                <span class="admin-note-title">Catatan dari Admin:</span>
                                <p style="margin:0;"><?php echo nl2br(htmlspecialchars($usaha_detail['catatan_admin'])); ?></p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="description-section" style="margin-top: 30px;">
                    <h3 class="section-title">Deskripsi Usaha</h3>
                    <div class="description-content" style="background-color: #f9f9f9; padding: 20px; border-radius: 8px; border: 1px solid #eaeaea; line-height: 1.6;">
                        <?php echo nl2br(htmlspecialchars($usaha_detail['deskripsi_usaha'])); ?>
                    </div>
                </div>

                <?php if ($usaha_detail['foto_produk_atau_logo']): ?>
                    <div class="image-section">
                        <h3 class="section-title">Foto Produk/Logo</h3>
                        <img src="<?php echo $pathPrefix . htmlspecialchars($usaha_detail['foto_produk_atau_logo']); ?>" 
                             alt="Foto Usaha <?php echo htmlspecialchars($usaha_detail['nama_usaha']); ?>" 
                             class="product-image">
                    </div>
                <?php endif; ?>

                <hr class="divider">

                <div class="action-buttons">
                    <?php if ($usaha_detail['status_pengajuan'] == 'Ditolak' || $usaha_detail['status_pengajuan'] == 'Menunggu Persetujuan'): ?>
                        <a href="<?php echo $pathPrefix; ?>user/edit_usaha_saya.php?id=<?php echo $usaha_detail['id']; ?>" class="btn btn-edit">
                            <i class="fas fa-edit"></i> Edit Pengajuan
                        </a>
                    <?php endif; ?>
                    
                    <?php if ($usaha_detail['status_pengajuan'] == 'Disetujui'): ?>
                        <a href="<?php echo $pathPrefix; ?>user/kelola_produk_usaha.php?usaha_id=<?php echo $usaha_detail['id']; ?>" class="btn btn-manage">
                            <i class="fas fa-tasks"></i> Kelola Produk/Layanan
                        </a>
                    <?php endif; ?>
                    
                    <!-- Tombol untuk menghapus pengajuan usaha (perlu konfirmasi dan backend) -->
                    <!-- 
                    <a href="<?php echo $pathPrefix; ?>user/hapus_usaha_saya.php?id=<?php echo $usaha_detail['id']; ?>" 
                       onclick="return confirm('Anda yakin ingin menghapus pengajuan usaha ini? Aksi ini tidak dapat diurungkan.');" 
                       class="btn btn-delete">
                        <i class="fas fa-trash"></i> Hapus Pengajuan
                    </a>
                    -->
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php
if (isset($conn)) $conn->close(); // Tutup koneksi jika masih terbuka
// Include footer
include $pathPrefix . 'includes/footer.php';
?>
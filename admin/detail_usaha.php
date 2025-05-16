<?php
 session_start();
 $pathPrefix = '../'; // Path dari admin/ ke root
 $adminPathPrefix = ''; // Path di dalam folder admin itu sendiri
 $pageTitle = "Detail Pengajuan Usaha"; // Judul akan disesuaikan

 // Cek jika pengguna belum login atau bukan admin
 if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
     $_SESSION['login_messages'] = [['type' => 'error', 'text' => 'Akses ditolak. Anda tidak memiliki izin.']];
     header("Location: " . $pathPrefix . "auth/login.php");
     exit;
 }

 // Include koneksi database
 require $pathPrefix . 'config/db.php';

 $usaha_detail = null;
 $error_message = '';
 $success_message = ''; // Untuk pesan sukses update catatan

 if (!isset($_GET['id'])) {
     $error_message = "ID pengajuan usaha tidak disediakan.";
 } else {
     $usaha_id = intval($_GET['id']);

     // Proses jika ada submit form update catatan admin
     if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['catatan_admin'])) {
         $catatan_admin = trim($_POST['catatan_admin']);
         $stmt_update_catatan = $conn->prepare("UPDATE usaha_mahasiswa SET catatan_admin = ? WHERE id = ?");
         if ($stmt_update_catatan) {
             $stmt_update_catatan->bind_param("si", $catatan_admin, $usaha_id);
             if ($stmt_update_catatan->execute()) {
                 $success_message = "Catatan admin berhasil diperbarui.";
             } else {
                 $error_message = "Gagal memperbarui catatan admin: " . $stmt_update_catatan->error;
             }
             $stmt_update_catatan->close();
         } else {
             $error_message = "Database error (prepare update catatan): " . $conn->error;
         }
     }


     // Ambil detail usaha (setelah kemungkinan update catatan)
     $stmt = $conn->prepare("SELECT um.*, u.username AS nama_pengaju, u.email AS email_pengaju 
                             FROM usaha_mahasiswa um
                             JOIN users u ON um.user_id = u.id
                             WHERE um.id = ?");
     if (!$stmt) {
         $error_message = "Database error (prepare select): " . $conn->error;
     } else {
         $stmt->bind_param("i", $usaha_id);
         $stmt->execute();
         $result = $stmt->get_result();
         if ($result->num_rows === 1) {
             $usaha_detail = $result->fetch_assoc();
             $pageTitle = "Detail Usaha: " . htmlspecialchars($usaha_detail['nama_usaha']);
         } else {
             $error_message = "Data pengajuan usaha tidak ditemukan.";
         }
         $stmt->close();
     }
 }
 
 // Include header admin
 include $pathPrefix . 'includes/admin_header.php';
 ?>

<style>
    .detail-container {
        background-color: #fff;
        padding: 35px;
        border-radius: 12px;
        box-shadow: 0 3px 15px rgba(0,0,0,0.1);
        margin-bottom: 30px;
        max-width: 1200px;
        margin-left: auto;
        margin-right: auto;
    }
    .section-header {
        color: var(--dark);
        margin-bottom: 20px;
        border-bottom: 2px solid var(--dark-green);
        padding-bottom: 10px;
        font-size: 1.3rem;
    }
    .info-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 40px;
    }
    @media (max-width: 992px) {
        .info-grid {
            grid-template-columns: 1fr;
        }
    }
    .info-item {
        margin-bottom: 15px;
        line-height: 1.6;
        display: flex;
        flex-direction: column;
    }
    .info-item strong {
        margin-bottom: 5px;
        color: var(--dark);
        font-size: 0.95rem;
    }
    .info-item-inline {
        display: flex;
        flex-direction: row;
        align-items: center;
    }
    .info-item-inline strong {
        min-width: 160px;
        margin-bottom: 0;
    }
    .status-badge {
        padding: 6px 12px;
        border-radius: 50px;
        color: white;
        font-weight: 600;
        font-size: 0.9rem;
        display: inline-block;
        text-align: center;
        min-width: 120px;
    }
    .status-pending {
        background-color: var(--yellow);
        color: var(--dark);
    }
    .status-approved {
        background-color: var(--dark-green);
    }
    .status-rejected {
        background-color: var(--red);
    }
    .back-button {
        display: inline-block;
        margin-bottom: 25px;
        text-decoration: none;
        background-color: var(--dark-green);
        color: white;
        padding: 10px 18px;
        border-radius: 6px;
        font-weight: 600;
        transition: all 0.3s ease;
    }
    .back-button:hover {
        background-color: #0f5132;
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }
    .message {
        padding: 15px;
        border-radius: 6px;
        margin-bottom: 20px;
        font-weight: 500;
    }
    .message.error {
        background-color: #f8d7da;
        color: #842029;
        border: 1px solid #f5c2c7;
    }
    .message.success {
        background-color: #d1e7dd;
        color: #0f5132;
        border: 1px solid #badbcc;
    }
    .image-container {
        margin-top: 40px;
        grid-column: 1 / -1;
        display: flex;
        flex-direction: column;
        align-items: center;
    }
    .product-image {
        max-width: 350px;
        max-height: 350px;
        border-radius: 8px;
        border: 1px solid #eee;
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        transition: transform 0.3s ease;
        margin-top: 15px;
    }
    .product-image:hover {
        transform: scale(1.03);
    }
    .divider {
        height: 1px;
        background-color: #e9ecef;
        margin: 35px 0;
        width: 100%;
    }
    .form-group label {
        display: block;
        margin-bottom: 8px;
        font-weight: 600;
        color: var(--dark);
    }
    .form-control {
        width: 100%;
        padding: 12px;
        border: 1px solid #ddd;
        border-radius: 6px;
        font-size: 1rem;
        transition: border-color 0.3s;
    }
    .form-control:focus {
        border-color: var(--dark-green);
        outline: none;
        box-shadow: 0 0 0 3px rgba(25, 135, 84, 0.25);
    }
    .btn {
        cursor: pointer;
        padding: 10px 20px;
        border: none;
        border-radius: 6px;
        font-weight: 600;
        transition: all 0.3s ease;
    }
    .btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }
    .btn-primary {
        background-color: var(--dark-green);
        color: white;
    }
    .btn-primary:hover {
        background-color: #0f5132;
    }
    .admin-content {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 30px;
    }
    @media (max-width: 992px) {
        .admin-content {
            grid-template-columns: 1fr;
        }
    }
    .action-buttons-container {
        background-color: #f8f9fa;
        padding: 20px;
        border-radius: 8px;
        border: 1px solid #e9ecef;
    }
    .action-buttons {
        margin-top: 20px;
        display: flex;
        flex-direction: column;
        gap: 15px;
    }
    .btn-action {
        text-decoration: none;
        color: white;
        padding: 12px 20px;
        border-radius: 6px;
        font-weight: 600;
        display: block;
        transition: all 0.3s ease;
        text-align: center;
        width: 100%;
    }
    .btn-action:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }
    .btn-action.setujui { 
        background-color: var(--dark-green); 
    }
    .btn-action.setujui:hover { 
        background-color: #0f5132; 
    }
    .btn-action.tolak { 
        background-color: var(--red); 
    }
    .btn-action.tolak:hover { 
        background-color: #bb2d3b; 
    }
    .btn-action.tangguhkan { 
        background-color: var(--yellow); 
        color: var(--dark); 
    }
    .btn-action.tangguhkan:hover { 
        background-color: #ffca2c; 
    }
    .btn-action.proses-ulang { 
        background-color: #6c757d; 
    }
    .btn-action.proses-ulang:hover { 
        background-color: #5c636a; 
    }
    .description-text {
        line-height: 1.8;
        white-space: pre-line;
        padding: 15px;
        background-color: #f8f9fa;
        border-radius: 6px;
        margin-top: 8px;
        border-left: 4px solid var(--dark-green);
    }
</style>

<h2 class="section-title" style="color: var(--dark-green); margin-bottom: 25px; font-size: 1.8rem;">
    <?php echo $pageTitle; ?>
</h2>

<a href="<?php echo $adminPathPrefix; ?>manajemen_usaha.php" class="back-button">
    « Kembali ke Manajemen Usaha
</a>

<?php if ($error_message): ?>
    <div class="message error"><?php echo htmlspecialchars($error_message); ?></div>
<?php endif; ?>
<?php if ($success_message): ?>
    <div class="message success"><?php echo htmlspecialchars($success_message); ?></div>
<?php endif; ?>

<?php if ($usaha_detail): ?>
    <div class="detail-container">
        <div class="info-grid">
            <div>
                <h3 class="section-header">Informasi Usaha</h3>
                <div class="info-item info-item-inline"><strong>ID Pengajuan:</strong> <?php echo htmlspecialchars($usaha_detail['id']); ?></div>
                <div class="info-item info-item-inline"><strong>Nama Usaha:</strong> <?php echo htmlspecialchars($usaha_detail['nama_usaha']); ?></div>
                <div class="info-item info-item-inline"><strong>Kategori:</strong> <?php echo htmlspecialchars($usaha_detail['kategori_usaha']); ?></div>
                <div class="info-item">
                    <strong>Deskripsi:</strong>
                    <div class="description-text"><?php echo nl2br(htmlspecialchars($usaha_detail['deskripsi_usaha'])); ?></div>
                </div>
                <div class="info-item info-item-inline"><strong>Alamat Usaha:</strong> <?php echo htmlspecialchars($usaha_detail['alamat_usaha'] ?? '-'); ?></div>
                <div class="info-item info-item-inline"><strong>Kontak Usaha:</strong> <?php echo htmlspecialchars($usaha_detail['kontak_usaha'] ?? '-'); ?></div>
            </div>
            
            <div>
                <h3 class="section-header">Informasi Pengaju</h3>
                <div class="info-item info-item-inline"><strong>Nama Pengaju:</strong> <?php echo htmlspecialchars($usaha_detail['nama_pengaju']); ?> (ID: <?php echo $usaha_detail['user_id']; ?>)</div>
                <div class="info-item info-item-inline"><strong>Email Pengaju:</strong> <?php echo htmlspecialchars($usaha_detail['email_pengaju']); ?></div>
                <div class="info-item info-item-inline"><strong>Tanggal Pengajuan:</strong> <?php echo date('d F Y, H:i', strtotime($usaha_detail['tanggal_pengajuan'])); ?></div>
                <div class="info-item info-item-inline">
                    <strong>Status Pengajuan:</strong> 
                    <span class="status-badge <?php 
                        if ($usaha_detail['status_pengajuan'] == 'Disetujui') echo 'status-approved';
                        elseif ($usaha_detail['status_pengajuan'] == 'Ditolak') echo 'status-rejected';
                        else echo 'status-pending'; 
                    ?>">
                        <?php echo htmlspecialchars($usaha_detail['status_pengajuan']); ?>
                    </span>
                </div>
            </div>
        </div>

        <?php if ($usaha_detail['foto_produk_atau_logo']): ?>
            <div class="divider"></div>
            <div class="image-container">
                <h3 class="section-header">Foto Produk/Logo</h3>
                <img src="<?php echo $pathPrefix . htmlspecialchars($usaha_detail['foto_produk_atau_logo']); ?>" 
                     alt="Foto Usaha <?php echo htmlspecialchars($usaha_detail['nama_usaha']); ?>" 
                     class="product-image">
            </div>
        <?php endif; ?>

        <div class="divider"></div>

        <div class="admin-actions-container">
            <h3 class="section-header">Aksi & Catatan Admin</h3>
            <div class="admin-content">
                <div class="catatan-form">
                    <form action="<?php echo $adminPathPrefix; ?>detail_usaha.php?id=<?php echo $usaha_detail['id']; ?>" method="POST" style="margin-bottom: 25px;">
                        <div class="form-group" style="margin-bottom: 15px;">
                            <label for="catatan_admin">Catatan Admin (jika ditolak atau perlu revisi):</label>
                            <textarea class="form-control" id="catatan_admin" name="catatan_admin" rows="5"><?php echo htmlspecialchars($usaha_detail['catatan_admin'] ?? ''); ?></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">Simpan Catatan</button>
                    </form>
                </div>

                <div class="action-buttons-container">
                    <h4 style="margin-bottom: 15px; font-size: 1.1rem; color: var(--dark);">Tindakan:</h4>
                    <div class="action-buttons">
                        <?php if ($usaha_detail['status_pengajuan'] == 'Menunggu Persetujuan'): ?>
                            <a href="<?php echo $adminPathPrefix; ?>proses_update_status_usaha.php?id=<?php echo $usaha_detail['id']; ?>&status=Disetujui" 
                               onclick="return confirm('Anda yakin ingin menyetujui pengajuan usaha ini?');" 
                               class="btn-action setujui">Setujui</a>
                            <a href="<?php echo $adminPathPrefix; ?>proses_update_status_usaha.php?id=<?php echo $usaha_detail['id']; ?>&status=Ditolak" 
                               onclick="return confirm('Pastikan Anda sudah mengisi catatan jika menolak. Lanjutkan?');" 
                               class="btn-action tolak">Tolak</a>
                        <?php elseif ($usaha_detail['status_pengajuan'] == 'Disetujui'): ?>
                            <a href="<?php echo $adminPathPrefix; ?>proses_update_status_usaha.php?id=<?php echo $usaha_detail['id']; ?>&status=Ditangguhkan" 
                               onclick="return confirm('Anda yakin ingin menangguhkan usaha ini?');" 
                               class="btn-action tangguhkan">Tangguhkan</a>
                        <?php elseif ($usaha_detail['status_pengajuan'] == 'Ditolak' || $usaha_detail['status_pengajuan'] == 'Ditangguhkan'): ?>
                            <a href="<?php echo $adminPathPrefix; ?>proses_update_status_usaha.php?id=<?php echo $usaha_detail['id']; ?>&status=Menunggu Persetujuan" 
                               onclick="return confirm('Kembalikan status ke Menunggu Persetujuan?');" 
                               class="btn-action proses-ulang">Proses Ulang</a>
                            <a href="<?php echo $adminPathPrefix; ?>proses_update_status_usaha.php?id=<?php echo $usaha_detail['id']; ?>&status=Disetujui" 
                               onclick="return confirm('Anda yakin ingin langsung menyetujui pengajuan usaha ini?');" 
                               class="btn-action setujui">Setujui Langsung</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php
if(isset($conn)) $conn->close(); // Tutup koneksi jika masih terbuka
// Include footer admin
include $pathPrefix . 'includes/admin_footer.php';
?>
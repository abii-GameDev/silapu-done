<?php
 session_start();
 $pathPrefix = '../'; // Path dari admin/ ke root
 $adminPathPrefix = ''; // Path di dalam folder admin itu sendiri
 $pageTitle = "Detail Pesanan"; // Akan diupdate

 // Cek jika pengguna belum login atau bukan admin
 if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
     $_SESSION['login_messages'] = [['type' => 'error', 'text' => 'Akses ditolak. Anda tidak memiliki izin.']];
     header("Location: " . $pathPrefix . "auth/login.php");
     exit;
 }

 // Include koneksi database
 require $pathPrefix . 'config/db.php';

 $pesanan_detail = null;
 $item_pesanan_list = [];
 $error_message = '';
 $success_message = '';

 if (!isset($_GET['nomor_pesanan'])) {
     $error_message = "Nomor pesanan tidak disediakan.";
 } else {
     $nomor_pesanan_url = trim($_GET['nomor_pesanan']);

     // Proses jika ada submit form update status pesanan
     if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_status_pesanan'], $_POST['status_baru'], $_POST['pesanan_id_hidden'])) {
         $pesanan_id_to_update = intval($_POST['pesanan_id_hidden']);
         $status_baru = trim($_POST['status_baru']);
         $allowed_statuses_update = ['Menunggu Pembayaran', 'Pembayaran Dikonfirmasi', 'Diproses', 'Dikirim', 'Selesai', 'Dibatalkan'];

         if (in_array($status_baru, $allowed_statuses_update)) {
             $stmt_update_status = $conn->prepare("UPDATE pesanan SET status_pesanan = ? WHERE id = ?");
             if ($stmt_update_status) {
                 $stmt_update_status->bind_param("si", $status_baru, $pesanan_id_to_update);
                 if ($stmt_update_status->execute()) {
                     $success_message = "Status pesanan berhasil diperbarui menjadi " . htmlspecialchars($status_baru) . ".";
                     // Di sini bisa ditambahkan logika notifikasi ke pengguna
                 } else {
                     $error_message = "Gagal memperbarui status pesanan: " . $stmt_update_status->error;
                 }
                 $stmt_update_status->close();
             } else {
                 $error_message = "Database error (prepare update status): " . $conn->error;
             }
         } else {
             $error_message = "Status baru tidak valid.";
         }
     }


     // Ambil detail pesanan utama (setelah kemungkinan update status)
     $stmt_pesanan = $conn->prepare("SELECT p.*, u.username AS nama_pemesan, u.email AS email_pemesan 
                                     FROM pesanan p 
                                     LEFT JOIN users u ON p.user_id = u.id 
                                     WHERE p.nomor_pesanan = ?");
     if (!$stmt_pesanan) {
         $error_message = "Database error (prepare pesanan): " . $conn->error;
     } else {
         $stmt_pesanan->bind_param("s", $nomor_pesanan_url);
         $stmt_pesanan->execute();
         $result_pesanan = $stmt_pesanan->get_result();
         if ($result_pesanan->num_rows === 1) {
             $pesanan_detail = $result_pesanan->fetch_assoc();
             $pageTitle = "Detail Pesanan: " . htmlspecialchars($pesanan_detail['nomor_pesanan']);

             // Ambil item-item dalam pesanan ini
             $stmt_items = $conn->prepare("SELECT dp.*, pl.foto_produk_layanan 
                                           FROM detail_pesanan dp
                                           LEFT JOIN produk_layanan pl ON dp.produk_id = pl.id
                                           WHERE dp.pesanan_id = ?");
             if (!$stmt_items) {
                 $error_message = ($error_message ? $error_message . "<br>" : "") . "Database error (prepare items): " . $conn->error;
             } else {
                 $stmt_items->bind_param("i", $pesanan_detail['id']);
                 $stmt_items->execute();
                 $result_items = $stmt_items->get_result();
                 while ($row = $result_items->fetch_assoc()) {
                     $item_pesanan_list[] = $row;
                 }
                 $stmt_items->close();
             }
         } else {
             $error_message = "Pesanan tidak ditemukan.";
         }
         $stmt_pesanan->close();
     }
 }
 
 // Include header admin
 include $pathPrefix . 'includes/admin_header.php';
 ?>

<style>
    .card {
        background-color: #fff;
        border-radius: 10px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        margin-bottom: 25px;
        overflow: hidden;
        transition: transform 0.2s, box-shadow 0.2s;
    }
    
    .card:hover {
        box-shadow: 0 8px 16px rgba(0,0,0,0.12);
    }
    
    .card-header {
        background-color: #f8f9fa;
        padding: 18px 25px;
        border-bottom: 1px solid #edf2f7;
    }
    
    .card-title {
        font-size: 1.4rem;
        color: var(--dark);
        margin: 0;
        font-weight: 600;
    }
    
    .card-body {
        padding: 25px;
    }
    
    .btn {
        display: inline-block;
        font-weight: 500;
        text-align: center;
        vertical-align: middle;
        cursor: pointer;
        user-select: none;
        border: 1px solid transparent;
        padding: 10px 16px;
        font-size: 1rem;
        line-height: 1.5;
        border-radius: 5px;
        transition: all 0.2s;
    }
    
    .btn-primary {
        background-color: var(--dark-green);
        color: white;
        border-color: var(--dark-green);
    }
    
    .btn-primary:hover {
        background-color: #155d27;
        border-color: #155d27;
    }
    
    .btn-secondary {
        background-color: #6c757d;
        color: white;
        border-color: #6c757d;
    }
    
    .btn-secondary:hover {
        background-color: #5a6268;
        border-color: #545b62;
    }
    
    .table {
        width: 100%;
        margin-bottom: 1rem;
        color: #212529;
        border-collapse: collapse;
    }
    
    .table thead th {
        vertical-align: bottom;
        border-bottom: 2px solid #dee2e6;
        padding: 12px;
        text-align: left;
        font-weight: 600;
        background-color: #f8f9fc;
    }
    
    .table td {
        padding: 14px 12px;
        vertical-align: middle;
        border-bottom: 1px solid #edf2f7;
    }
    
    .table tbody tr:hover {
        background-color: #f9fafb;
    }
    
    .badge {
        display: inline-block;
        padding: 5px 10px;
        font-size: 0.85em;
        font-weight: 600;
        line-height: 1;
        text-align: center;
        white-space: nowrap;
        vertical-align: baseline;
        border-radius: 50px;
        transition: color .15s ease-in-out,background-color .15s ease-in-out,border-color .15s ease-in-out,box-shadow .15s ease-in-out;
    }
    
    .info-group {
        margin-bottom: 16px;
    }
    
    .info-label {
        font-weight: 600;
        color: #525f7f;
        margin-bottom: 4px;
        display: block;
        font-size: 0.9rem;
    }
    
    .info-value {
        color: #32325d;
        font-size: 1rem;
    }
    
    .info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 20px;
    }
    
    .message {
        padding: 16px;
        border-radius: 8px;
        margin-bottom: 20px;
        font-weight: 500;
    }
    
    .message.error {
        background-color: #fee2e2;
        color: #b91c1c;
        border: 1px solid #fecaca;
    }
    
    .message.success {
        background-color: #d1fae5;
        color: #065f46;
        border: 1px solid #a7f3d0;
    }
    
    .section-title {
        font-size: 1.75rem;
        font-weight: 700;
        margin-top: 10px;
        margin-bottom: 25px;
        color: var(--dark-green);
        position: relative;
        display: inline-block;
    }
    
    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 30px;
        flex-wrap: wrap;
        gap: 15px;
    }
    
    .form-control {
        display: block;
        width: 100%;
        padding: 12px;
        font-size: 1rem;
        line-height: 1.5;
        color: #495057;
        background-color: #fff;
        background-clip: padding-box;
        border: 1px solid #ced4da;
        border-radius: 6px;
        transition: border-color .15s ease-in-out,box-shadow .15s ease-in-out;
    }
    
    .form-control:focus {
        color: #495057;
        background-color: #fff;
        border-color: var(--dark-green);
        outline: 0;
        box-shadow: 0 0 0 0.2rem rgba(21, 128, 61, 0.25);
    }
    
    .form-group {
        margin-bottom: 20px;
    }
    
    .form-label {
        display: block;
        margin-bottom: 8px;
        font-weight: 600;
        color: #525f7f;
    }
    
    .product-image {
        width: 70px;
        height: 70px;
        object-fit: cover;
        border-radius: 8px;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }
    
    .total-price {
        font-size: 1.2rem;
        font-weight: 700;
        color: var(--dark-green);
    }
    
    @media (max-width: 768px) {
        .info-grid {
            grid-template-columns: 1fr;
        }
        
        .page-header {
            flex-direction: column;
            align-items: flex-start;
        }
    }
</style>

<div class="page-header">
    <h2 class="section-title"><?php echo $pageTitle; ?></h2>
    <a href="<?php echo $adminPathPrefix; ?>manajemen_pesanan.php" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Kembali ke Manajemen Pesanan
    </a>
</div>

<?php if ($error_message): ?>
    <div class="message error"><?php echo $error_message; ?></div>
<?php endif; ?>

<?php if ($success_message): ?>
    <div class="message success"><?php echo $success_message; ?></div>
<?php endif; ?>

<?php if ($pesanan_detail): ?>
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Ringkasan Pesanan</h3>
        </div>
        <div class="card-body">
            <div class="info-grid">
                <div>
                    <div class="info-group">
                        <span class="info-label">Nomor Pesanan</span>
                        <div class="info-value"><?php echo htmlspecialchars($pesanan_detail['nomor_pesanan']); ?></div>
                    </div>
                    
                    <div class="info-group">
                        <span class="info-label">Pemesan</span>
                        <div class="info-value">
                            <?php echo htmlspecialchars($pesanan_detail['nama_pemesan'] ?? '[User Dihapus]'); ?>
                        </div>
                    </div>
                    
                    <div class="info-group">
                        <span class="info-label">Email Pemesan</span>
                        <div class="info-value"><?php echo htmlspecialchars($pesanan_detail['email_pemesan'] ?? '-'); ?></div>
                    </div>
                    
                    <div class="info-group">
                        <span class="info-label">Tanggal Pesanan</span>
                        <div class="info-value"><?php echo date('d F Y, H:i', strtotime($pesanan_detail['tanggal_pesanan'])); ?></div>
                    </div>
                    
                    <div class="info-group">
                        <span class="info-label">Status Pesanan</span>
                        <div class="info-value">
                            <span class="badge" style="background-color: <?php 
                                $status_colors = [
                                    'Menunggu Pembayaran' => '#f59e0b', 
                                    'Pembayaran Dikonfirmasi' => '#10b981', 
                                    'Diproses' => '#3b82f6', 
                                    'Dikirim' => '#8b5cf6', 
                                    'Selesai' => '#16a34a', 
                                    'Dibatalkan' => '#ef4444'
                                ];
                                echo $status_colors[$pesanan_detail['status_pesanan']] ?? '#6c757d'; 
                            ?>; color: white;">
                                <?php echo htmlspecialchars($pesanan_detail['status_pesanan']); ?>
                            </span>
                        </div>
                    </div>
                </div>
                <div>
                    <div class="info-group">
                        <span class="info-label">Total Harga</span>
                        <div class="info-value total-price">Rp <?php echo number_format($pesanan_detail['total_harga'], 0, ',', '.'); ?></div>
                    </div>
                    
                    <div class="info-group">
                        <span class="info-label">Metode Pembayaran</span>
                        <div class="info-value"><?php echo htmlspecialchars($pesanan_detail['metode_pembayaran']); ?></div>
                    </div>
                    
                    <div class="info-group">
                        <span class="info-label">Nama Penerima</span>
                        <div class="info-value"><?php echo htmlspecialchars($pesanan_detail['nama_penerima']); ?></div>
                    </div>
                    
                    <div class="info-group">
                        <span class="info-label">Telepon Penerima</span>
                        <div class="info-value"><?php echo htmlspecialchars($pesanan_detail['telepon_penerima']); ?></div>
                    </div>
                </div>
            </div>
            
            <div class="info-group" style="margin-top: 15px;">
                <span class="info-label">Alamat Pengiriman</span>
                <div class="info-value" style="background-color: #f8f9fa; padding: 12px; border-radius: 6px; border: 1px solid #edf2f7;">
                    <?php echo nl2br(htmlspecialchars($pesanan_detail['alamat_pengiriman'])); ?>
                </div>
            </div>
            
            <?php if (!empty($pesanan_detail['catatan_pembeli'])): ?>
                <div class="info-group">
                    <span class="info-label">Catatan Pembeli</span>
                    <div class="info-value" style="background-color: #f8f9fa; padding: 12px; border-radius: 6px; border: 1px solid #edf2f7;">
                        <?php echo nl2br(htmlspecialchars($pesanan_detail['catatan_pembeli'])); ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Item Pesanan</h3>
        </div>
        <div class="card-body">
            <?php if (!empty($item_pesanan_list)): ?>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th colspan="2">Produk</th>
                                <th style="text-align: right;">Harga Satuan</th>
                                <th style="text-align: center;">Jumlah</th>
                                <th style="text-align: right;">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($item_pesanan_list as $item): ?>
                                <tr>
                                    <td style="width: 90px;">
                                        <?php if ($item['foto_produk_layanan']): ?>
                                            <img src="<?php echo $pathPrefix . htmlspecialchars($item['foto_produk_layanan']); ?>" 
                                                 alt="<?php echo htmlspecialchars($item['nama_produk']); ?>" 
                                                 class="product-image">
                                        <?php else: ?>
                                            <img src="<?php echo $pathPrefix; ?>assets/images/placeholder_produk.png" 
                                                 alt="Placeholder" 
                                                 class="product-image" style="opacity:0.5;">
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div style="font-weight: 500;"><?php echo htmlspecialchars($item['nama_produk']); ?></div>
                                        <?php if ($item['produk_id'] == NULL): ?>
                                            <span style="display: inline-block; font-size: 0.85rem; color: #ef4444; background-color: #fee2e2; padding: 3px 8px; border-radius: 4px; margin-top: 5px;">
                                                Produk telah dihapus
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td style="text-align: right; white-space: nowrap;">
                                        Rp <?php echo number_format($item['harga_produk'], 0, ',', '.'); ?>
                                    </td>
                                    <td style="text-align: center;">
                                        <span style="display: inline-block; background-color: #f1f5f9; padding: 4px 10px; border-radius: 4px; font-weight: 500;">
                                            <?php echo $item['jumlah']; ?>
                                        </span>
                                    </td>
                                    <td style="text-align: right; white-space: nowrap; font-weight: 600; color: var(--dark-green);">
                                        Rp <?php echo number_format($item['subtotal_item'], 0, ',', '.'); ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            <!-- Total row -->
                            <tr style="background-color: #f8f9fa;">
                                <td colspan="4" style="text-align: right; font-weight: 600; padding: 15px;">Total Pesanan:</td>
                                <td style="text-align: right; font-weight: 700; font-size: 1.1rem; color: var(--dark-green);">
                                    Rp <?php echo number_format($pesanan_detail['total_harga'], 0, ',', '.'); ?>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div style="text-align: center; padding: 30px 0;">
                    <div style="font-size: 3rem; color: #d1d5db; margin-bottom: 15px;">
                        <i class="fas fa-box-open"></i>
                    </div>
                    <p style="color: #6b7280; font-size: 1.1rem;">Tidak ada item detail untuk pesanan ini.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Update Status Pesanan</h3>
        </div>
        <div class="card-body">
            <form action="<?php echo $adminPathPrefix; ?>detail_pesanan.php?nomor_pesanan=<?php echo urlencode($pesanan_detail['nomor_pesanan']); ?>" method="POST">
                <input type="hidden" name="pesanan_id_hidden" value="<?php echo $pesanan_detail['id']; ?>">
                <div class="form-group">
                    <label for="status_baru" class="form-label">Ubah Status Menjadi:</label>
                    <select name="status_baru" id="status_baru" class="form-control" style="max-width: 300px;">
                        <?php
                        $all_statuses = ['Menunggu Pembayaran', 'Pembayaran Dikonfirmasi', 'Diproses', 'Dikirim', 'Selesai', 'Dibatalkan'];
                        foreach ($all_statuses as $status_option) {
                            $selected = ($pesanan_detail['status_pesanan'] == $status_option) ? 'selected' : '';
                            $status_color = '';
                            switch ($status_option) {
                                case 'Menunggu Pembayaran': $status_color = '#f59e0b'; break;
                                case 'Pembayaran Dikonfirmasi': $status_color = '#10b981'; break;
                                case 'Diproses': $status_color = '#3b82f6'; break;
                                case 'Dikirim': $status_color = '#8b5cf6'; break;
                                case 'Selesai': $status_color = '#16a34a'; break;
                                case 'Dibatalkan': $status_color = '#ef4444'; break;
                            }
                            echo "<option value=\"$status_option\" $selected style=\"color: $status_color; font-weight: 500;\">" . 
                                    htmlspecialchars($status_option) . 
                                 "</option>";
                        }
                        ?>
                    </select>
                </div>
                <button type="submit" name="update_status_pesanan" class="btn btn-primary">
                    <i class="fas fa-sync-alt"></i> Update Status
                </button>
            </form>
        </div>
    </div>
<?php endif; ?>

<?php
if(isset($conn)) $conn->close();
// Include footer admin
include $pathPrefix . 'includes/admin_footer.php';
?>
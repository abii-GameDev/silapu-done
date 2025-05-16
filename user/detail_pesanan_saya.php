<?php
 session_start();
 $pathPrefix = '../'; // Path dari user/ ke root
 $pageTitle = "Detail Pesanan Saya"; // Akan diupdate

 // Cek jika pengguna belum login
 if (!isset($_SESSION['user_id'])) {
     $_SESSION['login_messages'] = [['type' => 'error', 'text' => 'Anda harus login untuk mengakses halaman ini.']];
     header("Location: " . $pathPrefix . "auth/login.php");
     exit;
 }

 $user_id = $_SESSION['user_id'];

 // Include koneksi database
 require $pathPrefix . 'config/db.php';

 $pesanan_detail = null;
 $item_pesanan_list = [];
 $error_message = '';

 if (!isset($_GET['nomor_pesanan'])) {
     $error_message = "Nomor pesanan tidak disediakan.";
 } else {
     $nomor_pesanan = trim($_GET['nomor_pesanan']);

     // 1. Ambil detail pesanan utama
     $stmt_pesanan = $conn->prepare("SELECT * FROM pesanan WHERE nomor_pesanan = ? AND user_id = ?");
     if (!$stmt_pesanan) {
         $error_message = "Database error (prepare pesanan): " . $conn->error;
     } else {
         $stmt_pesanan->bind_param("si", $nomor_pesanan, $user_id);
         $stmt_pesanan->execute();
         $result_pesanan = $stmt_pesanan->get_result();
         if ($result_pesanan->num_rows === 1) {
             $pesanan_detail = $result_pesanan->fetch_assoc();
             $pageTitle = "Detail Pesanan: " . htmlspecialchars($pesanan_detail['nomor_pesanan']);

             // 2. Ambil item-item dalam pesanan ini
             $stmt_items = $conn->prepare("SELECT dp.*, pl.foto_produk_layanan 
                                           FROM detail_pesanan dp
                                           LEFT JOIN produk_layanan pl ON dp.produk_id = pl.id
                                           WHERE dp.pesanan_id = ?");
             // Menggunakan LEFT JOIN agar item tetap tampil meskipun produk master sudah dihapus (produk_id jadi NULL)
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
             $error_message = "Pesanan tidak ditemukan atau Anda tidak memiliki akses.";
         }
         $stmt_pesanan->close();
     }
 }
 
 // Include header
 include $pathPrefix . 'includes/header.php';
 ?>

<style>
    :root {
        --primary: #5cab7d;
        --primary-light: #8cd790;
        --primary-dark: #4a9868;
        --secondary: #f1c40f;
        --danger: #e74c3c;
        --info: #17a2b8;
        --primary-text: #333333;
        --secondary-text: #555555;
        --light-text: #777777;
        --light-bg: #f9f9f9;
        --white: #ffffff;
        --border-color: #e8e8e8;
        --shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        --border-radius: 8px;
        --transition: all 0.3s ease;
    }

    .detail-pesanan-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 20px;
    }

    .page-title {
        color: var(--primary);
        font-size: 1.8rem;
        margin-bottom: 25px;
        font-weight: 600;
    }

    .back-button {
        display: inline-flex;
        align-items: center;
        text-decoration: none;
        background-color: var(--primary);
        color: var(--white);
        padding: 8px 16px;
        border-radius: var(--border-radius);
        font-weight: 500;
        margin-bottom: 25px;
        transition: var(--transition);
        border: none;
        cursor: pointer;
    }

    .back-button:hover {
        background-color: var(--primary-dark);
        transform: translateY(-2px);
    }

    .back-button i {
        margin-right: 8px;
    }

    .error-message {
        background-color: #fdeded;
        border-left: 4px solid var(--danger);
        color: #5f2120;
        padding: 16px;
        border-radius: var(--border-radius);
        margin-bottom: 20px;
    }

    .order-card {
        background-color: var(--white);
        border-radius: var(--border-radius);
        box-shadow: var(--shadow);
        overflow: hidden;
        margin-bottom: 30px;
    }

    .order-header {
        background-color: #f5f9f7;
        padding: 20px;
        border-bottom: 1px solid var(--border-color);
    }

    .order-title {
        margin: 0;
        color: var(--primary-text);
        font-size: 1.4rem;
        font-weight: 600;
    }

    .order-body {
        padding: 25px;
    }

    .order-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 30px;
    }

    .order-section {
        margin-bottom: 10px;
    }

    .order-label {
        color: var(--light-text);
        font-size: 0.9rem;
        margin-bottom: 5px;
        display: block;
    }

    .order-value {
        color: var(--primary-text);
        font-weight: 500;
        font-size: 1rem;
    }

    .status-badge {
        display: inline-block;
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 600;
        color: white;
    }

    .status-waiting {
        background-color: var(--secondary);
    }

    .status-processing {
        background-color: var(--info);
    }

    .status-shipped {
        background-color: #007bff;
    }

    .status-complete {
        background-color: var(--primary);
    }

    .status-canceled {
        background-color: var(--danger);
    }

    .price-value {
        color: var(--primary);
        font-weight: 700;
        font-size: 1.1rem;
    }

    .items-card {
        background-color: var(--white);
        border-radius: var(--border-radius);
        box-shadow: var(--shadow);
        overflow: hidden;
        margin-bottom: 30px;
    }

    .items-header {
        background-color: #f5f9f7;
        padding: 20px;
        border-bottom: 1px solid var(--border-color);
    }

    .items-title {
        margin: 0;
        color: var(--primary-text);
        font-size: 1.4rem;
        font-weight: 600;
    }

    .items-body {
        padding: 0;
    }

    .items-table {
        width: 100%;
        border-collapse: collapse;
    }

    .items-table th {
        background-color: #f5f9f7;
        color: var(--primary-text);
        font-weight: 600;
        text-align: left;
        padding: 15px;
        border-bottom: 1px solid var(--border-color);
    }

    .items-table td {
        padding: 15px;
        border-bottom: 1px solid var(--border-color);
        vertical-align: middle;
    }

    .items-table tr:last-child td {
        border-bottom: none;
    }

    .items-table th:last-child,
    .items-table td:last-child {
        text-align: right;
    }

    .items-table th:nth-child(4),
    .items-table td:nth-child(4) {
        text-align: center;
    }

    .items-table th:nth-child(3),
    .items-table td:nth-child(3) {
        text-align: right;
    }

    .product-image {
        width: 70px;
        height: 70px;
        object-fit: cover;
        border-radius: 4px;
        border: 1px solid var(--border-color);
    }

    .product-name {
        font-weight: 500;
        color: var(--primary-text);
    }

    .payment-instruction {
        background-color: #fff8e1;
        border-left: 4px solid var(--secondary);
        padding: 20px;
        border-radius: var(--border-radius);
        margin-top: 30px;
    }

    .payment-title {
        color: var(--primary-text);
        font-weight: 600;
        margin-top: 0;
        margin-bottom: 15px;
        font-size: 1.2rem;
    }

    .payment-details {
        background-color: var(--white);
        border: 1px dashed var(--secondary);
        padding: 15px;
        border-radius: var(--border-radius);
        margin: 15px 0;
    }

    @media (max-width: 768px) {
        .order-grid {
            grid-template-columns: 1fr;
            gap: 20px;
        }
        
        .product-image {
            width: 50px;
            height: 50px;
        }
        
        .items-table {
            font-size: 0.9rem;
        }
        
        .items-table th, 
        .items-table td {
            padding: 10px;
        }
    }
</style>

<section class="detail-pesanan-section" style="padding: 40px 0; background-color: var(--light-bg);">
    <div class="detail-pesanan-container">
        <h2 class="page-title"><?php echo $pageTitle; ?></h2>

        <a href="<?php echo $pathPrefix; ?>user/riwayat_pesanan.php" class="back-button">
            <i class="fas fa-arrow-left"></i> Kembali ke Riwayat Pesanan
        </a>

        <?php if ($error_message): ?>
            <div class="error-message"><?php echo htmlspecialchars($error_message); ?></div>
        <?php elseif ($pesanan_detail): ?>
            <div class="order-card">
                <div class="order-header">
                    <h3 class="order-title">Ringkasan Pesanan</h3>
                </div>
                <div class="order-body">
                    <div class="order-grid">
                        <div>
                            <div class="order-section">
                                <span class="order-label">Nomor Pesanan</span>
                                <div class="order-value"><?php echo htmlspecialchars($pesanan_detail['nomor_pesanan']); ?></div>
                            </div>
                            
                            <div class="order-section">
                                <span class="order-label">Tanggal Pesanan</span>
                                <div class="order-value"><?php echo date('d F Y, H:i', strtotime($pesanan_detail['tanggal_pesanan'])); ?></div>
                            </div>
                            
                            <div class="order-section">
                                <span class="order-label">Status Pesanan</span>
                                <div>
                                    <?php 
                                    $status_class = '';
                                    switch ($pesanan_detail['status_pesanan']) {
                                        case 'Menunggu Pembayaran':
                                            $status_class = 'status-waiting';
                                            break;
                                        case 'Diproses':
                                            $status_class = 'status-processing';
                                            break;
                                        case 'Dikirim':
                                            $status_class = 'status-shipped';
                                            break;
                                        case 'Selesai':
                                            $status_class = 'status-complete';
                                            break;
                                        case 'Dibatalkan':
                                            $status_class = 'status-canceled';
                                            break;
                                        default:
                                            $status_class = '';
                                    }
                                    ?>
                                    <span class="status-badge <?php echo $status_class; ?>">
                                        <?php echo htmlspecialchars($pesanan_detail['status_pesanan']); ?>
                                    </span>
                                </div>
                            </div>
                            
                            <div class="order-section">
                                <span class="order-label">Total Harga</span>
                                <div class="order-value price-value">Rp <?php echo number_format($pesanan_detail['total_harga'], 0, ',', '.'); ?></div>
                            </div>
                        </div>
                        
                        <div>
                            <div class="order-section">
                                <span class="order-label">Metode Pembayaran</span>
                                <div class="order-value"><?php echo htmlspecialchars($pesanan_detail['metode_pembayaran']); ?></div>
                            </div>
                            
                            <div class="order-section">
                                <span class="order-label">Nama Penerima</span>
                                <div class="order-value"><?php echo htmlspecialchars($pesanan_detail['nama_penerima']); ?></div>
                            </div>
                            
                            <div class="order-section">
                                <span class="order-label">Telepon Penerima</span>
                                <div class="order-value"><?php echo htmlspecialchars($pesanan_detail['telepon_penerima']); ?></div>
                            </div>
                            
                            <div class="order-section">
                                <span class="order-label">Alamat Pengiriman</span>
                                <div class="order-value"><?php echo nl2br(htmlspecialchars($pesanan_detail['alamat_pengiriman'])); ?></div>
                            </div>
                            
                            <?php if (!empty($pesanan_detail['catatan_pembeli'])): ?>
                                <div class="order-section">
                                    <span class="order-label">Catatan Pembeli</span>
                                    <div class="order-value"><?php echo nl2br(htmlspecialchars($pesanan_detail['catatan_pembeli'])); ?></div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="items-card">
                <div class="items-header">
                    <h3 class="items-title">Detail Item Pesanan</h3>
                </div>
                <div class="items-body">
                    <?php if (!empty($item_pesanan_list)): ?>
                        <table class="items-table">
                            <thead>
                                <tr>
                                    <th colspan="2">Produk</th>
                                    <th>Harga Satuan</th>
                                    <th>Jumlah</th>
                                    <th>Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($item_pesanan_list as $item): ?>
                                    <tr>
                                        <td style="width: 90px;">
                                            <?php if ($item['foto_produk_layanan']): ?>
                                                <img src="<?php echo $pathPrefix . htmlspecialchars($item['foto_produk_layanan']); ?>" alt="<?php echo htmlspecialchars($item['nama_produk']); ?>" class="product-image">
                                            <?php else: ?>
                                                <img src="<?php echo $pathPrefix; ?>assets/images/placeholder_produk.png" alt="Placeholder" class="product-image" style="opacity:0.5;">
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="product-name"><?php echo htmlspecialchars($item['nama_produk']); ?></span>
                                        </td>
                                        <td>Rp <?php echo number_format($item['harga_produk'], 0, ',', '.'); ?></td>
                                        <td><?php echo $item['jumlah']; ?></td>
                                        <td><strong>Rp <?php echo number_format($item['subtotal_item'], 0, ',', '.'); ?></strong></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <div style="padding: 20px; text-align: center; color: var(--light-text);">
                            Tidak ada item detail untuk pesanan ini.
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <?php if ($pesanan_detail['status_pesanan'] == 'Menunggu Pembayaran'): ?>
                <div class="payment-instruction">
                    <h4 class="payment-title"><i class="fas fa-info-circle"></i> Instruksi Pembayaran</h4>
                    <p>Silakan lakukan pembayaran sejumlah <strong>Rp <?php echo number_format($pesanan_detail['total_harga'], 0, ',', '.'); ?></strong> ke rekening/metode berikut:</p>
                    
                    <div class="payment-details">
                        <strong>[Informasi Rekening Tujuan atau Instruksi QRIS akan ditampilkan di sini sesuai metode yang dipilih]</strong>
                    </div>
                    
                    <p>Setelah melakukan pembayaran, silakan lakukan konfirmasi pembayaran dengan mengklik tombol di bawah ini:</p>
                    
                    <button type="button" class="back-button" style="background-color: var(--secondary); margin-top: 10px;">
                        <i class="fas fa-check-circle"></i> Konfirmasi Pembayaran
                    </button>
                </div>
            <?php endif; ?>

        <?php endif; ?>
    </div>
</section>

<?php
if(isset($conn)) $conn->close();
// Include footer
include $pathPrefix . 'includes/footer.php';
?>
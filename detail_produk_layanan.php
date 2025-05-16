<?php
 session_start();
 $pathPrefix = ''; // Karena file ini ada di root project
 $pageTitle = "Detail Usaha"; // Akan diupdate dengan nama usaha

 // Include koneksi database
 require 'config/db.php';

 $usaha_detail = null;
 $produk_list = [];
 $error_message = '';

 if (!isset($_GET['usaha_id'])) {
     $error_message = "ID Usaha tidak disediakan.";
 } else {
     $usaha_id = intval($_GET['usaha_id']);

     // 1. Ambil detail usaha
     $stmt_usaha = $conn->prepare("SELECT um.*, u.username AS nama_penjual 
                                   FROM usaha_mahasiswa um
                                   JOIN users u ON um.user_id = u.id
                                   WHERE um.id = ? AND um.status_pengajuan = 'Disetujui'");
     if (!$stmt_usaha) {
         $error_message = "Database error (prepare usaha): " . $conn->error;
     } else {
         $stmt_usaha->bind_param("i", $usaha_id);
         $stmt_usaha->execute();
         $result_usaha = $stmt_usaha->get_result();
         if ($result_usaha->num_rows === 1) {
             $usaha_detail = $result_usaha->fetch_assoc();
             $pageTitle = "Marketplace: " . htmlspecialchars($usaha_detail['nama_usaha']);

             // 2. Ambil produk/layanan dari usaha ini
             $stmt_produk = $conn->prepare("SELECT * FROM produk_layanan WHERE usaha_id = ? AND is_tersedia = TRUE ORDER BY nama_produk_layanan ASC");
             if (!$stmt_produk) {
                 $error_message = ($error_message ? $error_message . "<br>" : "") . "Database error (prepare produk): " . $conn->error;
             } else {
                 $stmt_produk->bind_param("i", $usaha_id);
                 $stmt_produk->execute();
                 $result_produk = $stmt_produk->get_result();
                 while ($row = $result_produk->fetch_assoc()) {
                     $produk_list[] = $row;
                 }
                 $stmt_produk->close();
             }
         } else {
             $error_message = "Usaha tidak ditemukan atau belum disetujui.";
         }
         $stmt_usaha->close();
     }
 }

 // Include header
 include 'includes/header.php';
 ?>

<style>
    :root {
        --primary: #5cab7d;
        --primary-light: #8cd790;
        --primary-dark: #3d8a5f;
        --accent: #f1c40f;
        --accent-dark: #e0b30b;
        --white: #ffffff;
        --dark: #333333;
        --gray: #777777;
        --light-gray: #f8f9fa;
        --error: #e74c3c;
        --shadow: 0 8px 20px rgba(0, 0, 0, 0.08);
        --card-shadow: 0 6px 18px rgba(0, 0, 0, 0.06);
        --transition: all 0.3s ease;
        --border-radius: 12px;
        --card-border-radius: 10px;
    }

    /* Global Styles */
    body {
        font-family: 'Poppins', 'Segoe UI', sans-serif;
        color: var(--dark);
        background-color: var(--light-gray);
        margin: 0;
        padding: 0;
    }

    /* Detail Section */
    .detail-section {
        padding: 60px 20px;
        background-color: var(--light-gray);
        min-height: 70vh;
    }

    .container {
        max-width: 1200px;
        margin: 0 auto;
    }

    /* Back Button */
    .btn-back {
        display: inline-flex;
        align-items: center;
        background-color: var(--primary-dark);
        color: var(--white);
        text-decoration: none;
        padding: 10px 18px;
        border-radius: 6px;
        font-weight: 500;
        margin-bottom: 25px;
        transition: var(--transition);
        box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
    }

    .btn-back:hover {
        background-color: var(--primary);
        transform: translateY(-2px);
    }

    .btn-back i {
        margin-right: 8px;
    }

    /* Error Message */
    .message {
        padding: 20px;
        border-radius: var(--border-radius);
        margin-bottom: 30px;
        text-align: center;
        font-weight: 500;
    }

    .message.error {
        background-color: rgba(231, 76, 60, 0.1);
        border-left: 4px solid var(--error);
        color: #c0392b;
    }

    /* Usaha Detail Card */
    .usaha-detail-card {
        background-color: var(--white);
        padding: 35px;
        border-radius: var(--border-radius);
        box-shadow: var(--shadow);
        margin-bottom: 40px;
        border: 1px solid rgba(0, 0, 0, 0.05);
    }

    .usaha-detail-content {
        display: flex;
        flex-wrap: wrap;
        gap: 30px;
        align-items: flex-start;
    }

    .usaha-logo-container {
        flex-shrink: 0;
        max-width: 200px;
        position: relative;
    }

    .usaha-logo {
        width: 200px;
        height: 200px;
        object-fit: cover;
        border-radius: 10px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
        border: 3px solid var(--white);
    }

    .usaha-info-text {
        flex-grow: 1;
        flex-basis: 60%;
    }

    .usaha-title {
        font-size: 2.4rem;
        color: var(--primary-dark);
        margin-top: 0;
        margin-bottom: 12px;
        line-height: 1.2;
    }

    .usaha-meta {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 8px;
        font-size: 1rem;
        color: var(--gray);
        margin-bottom: 15px;
        padding-bottom: 15px;
        border-bottom: 1px solid #eeeeee;
    }

    .usaha-meta-item {
        display: flex;
        align-items: center;
    }

    .usaha-meta-item i {
        margin-right: 6px;
        color: var(--primary);
    }

    .usaha-meta .separator {
        color: #ddd;
    }

    .usaha-description {
        font-size: 1.05rem;
        color: var(--dark);
        line-height: 1.7;
        margin-bottom: 20px;
    }

    .usaha-contact {
        background-color: rgba(140, 215, 144, 0.1);
        padding: 15px;
        border-radius: 8px;
        margin-top: 20px;
    }

    .contact-item {
        display: flex;
        align-items: center;
        margin-bottom: 8px;
        font-size: 0.95rem;
        color: var(--gray);
    }

    .contact-item:last-child {
        margin-bottom: 0;
    }

    .contact-item i {
        width: 20px;
        margin-right: 8px;
        color: var(--primary);
    }

    /* Products Section */
    .products-section-title {
        font-size: 1.8rem;
        color: var(--primary-dark);
        margin-bottom: 25px;
        position: relative;
        display: inline-block;
    }

    .products-section-title::after {
        content: '';
        display: block;
        height: 3px;
        width: 60px;
        background-color: var(--accent);
        margin-top: 8px;
        border-radius: 2px;
    }

    .products-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 25px;
    }

    /* Product Card */
    .product-card {
        background-color: var(--white);
        border-radius: var(--card-border-radius);
        box-shadow: var(--card-shadow);
        overflow: hidden;
        display: flex;
        flex-direction: column;
        transition: var(--transition);
        border: 1px solid rgba(0, 0, 0, 0.05);
        height: 100%;
    }

    .product-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 12px 25px rgba(0, 0, 0, 0.1);
    }

    .product-card-image {
        height: 200px;
        overflow: hidden;
        position: relative;
        background-color: #f5f5f5;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .product-card-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: var(--transition);
    }

    .product-card:hover .product-card-image img {
        transform: scale(1.05);
    }

    .product-card-content {
        padding: 20px;
        flex-grow: 1;
        display: flex;
        flex-direction: column;
    }

    .product-title {
        font-size: 1.2rem;
        font-weight: 600;
        color: var(--dark);
        margin-top: 0;
        margin-bottom: 10px;
        line-height: 1.3;
    }

    .product-price {
        font-size: 1.3rem;
        font-weight: 700;
        color: var(--primary-dark);
        margin-bottom: 12px;
        display: flex;
        align-items: baseline;
    }

    .price-unit {
        font-size: 0.85rem;
        color: var(--gray);
        margin-left: 5px;
        font-weight: normal;
    }

    .product-description {
        font-size: 0.95rem;
        color: var(--dark);
        margin-bottom: 15px;
        line-height: 1.6;
        flex-grow: 1;
    }

    .product-stock {
        font-size: 0.9rem;
        display: flex;
        align-items: center;
        margin-bottom: 15px;
    }

    .stock-label {
        color: var(--gray);
        margin-right: 5px;
    }

    .stock-value {
        font-weight: 500;
    }

    .stock-empty {
        color: var(--error);
        font-weight: 500;
    }

    /* Form Styles */
    .add-to-cart-form {
        margin-top: auto;
    }

    .quantity-input-group {
        display: flex;
        align-items: center;
        margin-bottom: 12px;
    }

    .quantity-label {
        margin-right: 10px;
        font-size: 0.9rem;
        color: var(--dark);
    }

    .quantity-input {
        width: 70px;
        padding: 8px;
        border: 1px solid #ddd;
        border-radius: 4px;
        font-size: 0.9rem;
    }

    .btn-add-cart {
        width: 100%;
        padding: 12px;
        border: none;
        border-radius: 6px;
        font-weight: 500;
        text-transform: uppercase;
        font-size: 0.9rem;
        letter-spacing: 0.5px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: var(--transition);
    }

    .btn-add-cart.available {
        background-color: var(--primary);
        color: var(--white);
    }

    .btn-add-cart.available:hover {
        background-color: var(--primary-dark);
    }

    .btn-add-cart.unavailable {
        background-color: #e0e0e0;
        color: #888;
        cursor: not-allowed;
    }

    .btn-add-cart i {
        margin-right: 6px;
    }

    /* Empty Products State */
    .empty-products {
        background-color: var(--white);
        padding: 30px;
        border-radius: var(--border-radius);
        text-align: center;
        box-shadow: var(--card-shadow);
    }

    .empty-products-icon {
        font-size: 2.5rem;
        color: var(--gray);
        margin-bottom: 15px;
        opacity: 0.5;
    }

    .empty-products-text {
        font-size: 1.1rem;
        color: var(--dark);
        margin-bottom: 8px;
    }

    .empty-products-subtext {
        font-size: 0.95rem;
        color: var(--gray);
    }

    /* Responsive Styles */
    @media (max-width: 768px) {
        .detail-section {
            padding: 40px 15px;
        }
        
        .usaha-detail-card {
            padding: 25px;
        }
        
        .usaha-title {
            font-size: 1.8rem;
        }
        
        .usaha-logo-container {
            margin: 0 auto 20px;
        }
        
        .usaha-detail-content {
            flex-direction: column;
            gap: 15px;
        }
        
        .products-grid {
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
        }
    }

    @media (max-width: 576px) {
        .products-grid {
            grid-template-columns: 1fr;
        }
        
        .usaha-logo {
            width: 180px;
            height: 180px;
        }
    }
</style>

<section class="detail-section">
    <div class="container">
        <a href="<?php echo $pathPrefix; ?>marketplace.php" class="btn-back">
            <i class="fas fa-arrow-left"></i> Kembali ke Marketplace
        </a>

        <?php if ($error_message): ?>
            <div class="message error"><?php echo $error_message; ?></div>
        <?php elseif ($usaha_detail): ?>
            <!-- Informasi Detail Usaha -->
            <div class="usaha-detail-card">
                <div class="usaha-detail-content">
                    <?php if ($usaha_detail['foto_produk_atau_logo']): ?>
                        <div class="usaha-logo-container">
                            <img class="usaha-logo" src="<?php echo htmlspecialchars($usaha_detail['foto_produk_atau_logo']); ?>" alt="Logo <?php echo htmlspecialchars($usaha_detail['nama_usaha']); ?>">
                        </div>
                    <?php endif; ?>
                    <div class="usaha-info-text">
                        <h2 class="usaha-title"><?php echo htmlspecialchars($usaha_detail['nama_usaha']); ?></h2>
                        <div class="usaha-meta">
                            <div class="usaha-meta-item">
                                <i class="fas fa-user"></i>
                                <strong><?php echo htmlspecialchars($usaha_detail['nama_penjual']); ?></strong>
                            </div>
                            <span class="separator">|</span>
                            <div class="usaha-meta-item">
                                <i class="fas fa-tag"></i>
                                <span><?php echo htmlspecialchars($usaha_detail['kategori_usaha']); ?></span>
                            </div>
                        </div>
                        <p class="usaha-description"><?php echo nl2br(htmlspecialchars($usaha_detail['deskripsi_usaha'])); ?></p>
                        
                        <?php if ($usaha_detail['alamat_usaha'] || $usaha_detail['kontak_usaha']): ?>
                            <div class="usaha-contact">
                                <?php if ($usaha_detail['alamat_usaha']): ?>
                                    <div class="contact-item">
                                        <i class="fas fa-map-marker-alt"></i>
                                        <span><?php echo htmlspecialchars($usaha_detail['alamat_usaha']); ?></span>
                                    </div>
                                <?php endif; ?>
                                <?php if ($usaha_detail['kontak_usaha']): ?>
                                    <div class="contact-item">
                                        <i class="fas fa-phone-alt"></i>
                                        <span><?php echo htmlspecialchars($usaha_detail['kontak_usaha']); ?></span>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Daftar Produk/Layanan -->
            <h3 class="products-section-title">Produk & Layanan Tersedia</h3>
            
            <?php if (!empty($produk_list)): ?>
                <div class="products-grid">
                    <?php foreach ($produk_list as $produk): ?>
                        <div class="product-card">
                            <div class="product-card-image">
                                <?php if ($produk['foto_produk_layanan']): ?>
                                    <img src="<?php echo htmlspecialchars($produk['foto_produk_layanan']); ?>" alt="<?php echo htmlspecialchars($produk['nama_produk_layanan']); ?>">
                                <?php else: ?>
                                    <img src="<?php echo $pathPrefix; ?>assets/images/placeholder_produk.png" alt="Placeholder Produk" style="width: 80px; height: 80px; object-fit: contain; opacity: 0.4;">
                                <?php endif; ?>
                            </div>
                            <div class="product-card-content">
                                <h4 class="product-title"><?php echo htmlspecialchars($produk['nama_produk_layanan']); ?></h4>
                                <div class="product-price">
                                    Rp <?php echo number_format($produk['harga'], 0, ',', '.'); ?>
                                    <span class="price-unit">/<?php echo htmlspecialchars($produk['satuan']); ?></span>
                                </div>
                                <p class="product-description">
                                    <?php 
                                    $desk_produk = $produk['deskripsi_produk_layanan'];
                                    if (strlen($desk_produk) > 100) {
                                        $desk_produk = substr($desk_produk, 0, 100) . "...";
                                    }
                                    echo htmlspecialchars($desk_produk); 
                                    ?>
                                </p>
                                
                                <?php if ($produk['stok'] !== null): ?>
                                    <div class="product-stock">
                                        <span class="stock-label">Stok:</span>
                                        <?php if ($produk['stok'] > 0): ?>
                                            <span class="stock-value"><?php echo htmlspecialchars($produk['stok']); ?></span>
                                        <?php else: ?>
                                            <span class="stock-empty">Habis</span>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                                
                                <!-- Form untuk Tambah ke Keranjang -->
                                <form action="<?php echo $pathPrefix; ?>cart_process.php" method="POST" class="add-to-cart-form">
                                    <input type="hidden" name="produk_id" value="<?php echo $produk['id']; ?>">
                                    <input type="hidden" name="action" value="add">
                                    <?php if ($produk['stok'] === null || $produk['stok'] > 0): ?>
                                        <div class="quantity-input-group">
                                            <label for="qty_<?php echo $produk['id']; ?>" class="quantity-label">Jumlah:</label>
                                            <input type="number" name="quantity" id="qty_<?php echo $produk['id']; ?>" value="1" min="1" <?php if ($produk['stok'] !== null) echo 'max="'.$produk['stok'].'"'; ?> class="quantity-input">
                                        </div>
                                        <button type="submit" class="btn-add-cart available">
                                            <i class="fas fa-shopping-cart"></i> Tambah ke Keranjang
                                        </button>
                                    <?php else: ?>
                                        <button type="button" class="btn-add-cart unavailable" disabled>
                                            <i class="fas fa-times-circle"></i> Stok Habis
                                        </button>
                                    <?php endif; ?>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="empty-products">
                    <div class="empty-products-icon">
                        <i class="fas fa-box-open"></i>
                    </div>
                    <p class="empty-products-text">Usaha ini belum memiliki produk atau layanan yang ditampilkan.</p>
                    <p class="empty-products-subtext">Silakan cek kembali nanti untuk update produk terbaru.</p>
                </div>
            <?php endif; ?>

        <?php endif; ?>
    </div>
</section>

<?php
if(isset($conn)) $conn->close();
// Include footer
include 'includes/footer.php';
?>
<?php
    session_start(); // Meskipun publik, session mungkin berguna untuk info user jika login
    $pathPrefix = ''; // Karena file ini ada di root project
    $pageTitle = "Marketplace KOPMA UIN RIL";

    // Include koneksi database
    require 'config/db.php'; // Path langsung karena file ini di root

    // Ambil semua data usaha yang statusnya 'Disetujui'
    // Kita juga join dengan tabel users untuk info kontak atau nama pengaju jika diperlukan
    $sql_marketplace = "SELECT um.id as usaha_id, um.nama_usaha, um.deskripsi_usaha, um.kategori_usaha, um.foto_produk_atau_logo, u.username as nama_penjual
                     FROM usaha_mahasiswa um
                     JOIN users u ON um.user_id = u.id
                     WHERE um.status_pengajuan = 'Disetujui'
                     ORDER BY um.nama_usaha ASC"; // Atau um.tanggal_update DESC

    $result_marketplace = $conn->query($sql_marketplace);

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
        --shadow: 0 8px 20px rgba(0, 0, 0, 0.08);
        --card-shadow: 0 6px 18px rgba(0, 0, 0, 0.06);
        --transition: all 0.3s ease;
        --border-radius: 12px;
        --card-border-radius: 10px;
    }

    /* Global Styles & Reset */
    body {
        font-family: 'Poppins', 'Segoe UI', sans-serif;
        color: var(--dark);
        background-color: var(--light-gray);
        margin: 0;
        padding: 0;
    }

    /* Marketplace Section */
    .marketplace-section {
        padding: 60px 20px;
        background-color: var(--light-gray);
        min-height: 80vh;
    }

    .container {
        max-width: 1200px;
        margin: 0 auto;
    }

    /* Header Styles */
    .marketplace-header {
        text-align: center;
        margin-bottom: 50px;
        position: relative;
    }

    .section-title {
        color: var(--primary-dark);
        font-size: 2.4rem;
        font-weight: 700;
        margin-bottom: 8px;
        position: relative;
        display: inline-block;
    }

    .section-title::after {
        content: '';
        display: block;
        height: 4px;
        width: 60px;
        background-color: var(--accent);
        margin: 8px auto 0;
        border-radius: 2px;
    }

    .section-subtitle {
        font-size: 1.1rem;
        color: var(--gray);
        max-width: 700px;
        margin: 15px auto 0;
        line-height: 1.6;
    }

    /* Card Styles */
    .usaha-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 30px;
    }

    .usaha-card {
        background-color: var(--white);
        border-radius: var(--card-border-radius);
        box-shadow: var(--card-shadow);
        overflow: hidden;
        display: flex;
        flex-direction: column;
        transition: var(--transition);
        border: 1px solid rgba(0,0,0,0.05);
        height: 100%;
    }

    .usaha-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 12px 25px rgba(0, 0, 0, 0.1);
    }

    .usaha-card-image {
        height: 220px;
        overflow: hidden;
        position: relative;
    }

    .usaha-card-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: var(--transition);
    }

    .usaha-card:hover .usaha-card-image img {
        transform: scale(1.05);
    }

    .category-badge {
        position: absolute;
        top: 15px;
        right: 15px;
        background-color: var(--accent);
        color: var(--dark);
        font-size: 0.75rem;
        font-weight: 600;
        padding: 6px 12px;
        border-radius: 30px;
        box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
    }

    .usaha-card-content {
        padding: 24px;
        flex-grow: 1;
        display: flex;
        flex-direction: column;
    }

    .usaha-title {
        font-size: 1.3rem;
        font-weight: 600;
        color: var(--primary-dark);
        margin-bottom: 10px;
        line-height: 1.3;
    }

    .usaha-meta {
        display: flex;
        align-items: center;
        font-size: 0.85rem;
        color: var(--gray);
        margin-bottom: 15px;
    }

    .usaha-meta i {
        margin-right: 5px;
        color: var(--primary);
    }

    .usaha-meta .separator {
        margin: 0 10px;
        color: #ddd;
    }

    .usaha-seller {
        font-weight: 500;
    }

    .usaha-description {
        font-size: 0.95rem;
        color: var(--dark);
        margin-bottom: 20px;
        line-height: 1.6;
        flex-grow: 1;
    }

    .btn-detail {
        background-color: var(--primary);
        color: white;
        text-decoration: none;
        padding: 12px 20px;
        border-radius: 6px;
        text-align: center;
        font-weight: 500;
        transition: var(--transition);
        display: block;
        border: none;
        cursor: pointer;
        margin-top: auto;
        text-transform: uppercase;
        font-size: 0.9rem;
        letter-spacing: 0.5px;
    }

    .btn-detail:hover {
        background-color: var(--primary-dark);
        transform: translateY(-2px);
    }

    /* Empty State */
    .empty-state {
        background-color: var(--white);
        padding: 50px 30px;
        border-radius: var(--border-radius);
        box-shadow: var(--card-shadow);
        text-align: center;
        max-width: 600px;
        margin: 0 auto;
    }

    .empty-state img {
        max-width: 180px;
        margin-bottom: 25px;
        opacity: 0.9;
    }

    .empty-state-title {
        font-size: 1.4rem;
        color: var(--primary-dark);
        margin-bottom: 15px;
    }

    .empty-state-subtitle {
        color: var(--gray);
        font-size: 1rem;
        line-height: 1.6;
    }

    /* Alert/Message Styles */
    .message {
        padding: 15px 20px;
        border-radius: var(--border-radius);
        margin-bottom: 30px;
        display: flex;
        align-items: center;
        font-weight: 500;
    }

    .message.success {
        background-color: rgba(140, 215, 144, 0.2);
        border-left: 4px solid var(--primary);
        color: var(--primary-dark);
    }

    .message.error {
        background-color: rgba(231, 76, 60, 0.1);
        border-left: 4px solid #e74c3c;
        color: #c0392b;
    }

    .message.info {
        background-color: rgba(52, 152, 219, 0.1);
        border-left: 4px solid #3498db;
        color: #2980b9;
    }

    .message.warning {
        background-color: rgba(241, 196, 15, 0.1);
        border-left: 4px solid var(--accent);
        color: #d35400;
    }

    /* Responsive Adjustments */
    @media (max-width: 768px) {
        .section-title {
            font-size: 1.8rem;
        }
        
        .usaha-grid {
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 20px;
        }
        
        .marketplace-section {
            padding: 40px 15px;
        }
    }

    @media (max-width: 576px) {
        .usaha-grid {
            grid-template-columns: 1fr;
        }
        
        .usaha-card-image {
            height: 200px;
        }
    }
</style>

<section class="marketplace-section">
    <div class="container">
        <div class="marketplace-header">
            <h2 class="section-title"><?php echo $pageTitle; ?></h2>
            <p class="section-subtitle">
                Temukan produk dan layanan unggulan dari para wirausahawan muda Koperasi Mahasiswa UIN Raden Intan Lampung!
            </p>
        </div>

        <?php
        if (isset($_SESSION['cart_message'])) { // Pesan terkait keranjang
            $messageType = $_SESSION['cart_message']['type'] ?? 'info';
            echo '<div class="message ' . htmlspecialchars($messageType) . '">' . htmlspecialchars($_SESSION['cart_message']['text']) . '</div>';
            unset($_SESSION['cart_message']);
        }
        ?>

        <?php if ($result_marketplace && $result_marketplace->num_rows > 0): ?>
            <div class="usaha-grid">
                <?php while ($usaha = $result_marketplace->fetch_assoc()): ?>
                    <div class="usaha-card">
                        <div class="usaha-card-image">
                            <?php if ($usaha['foto_produk_atau_logo']): ?>
                                <img src="<?php echo htmlspecialchars($usaha['foto_produk_atau_logo']); ?>" alt="<?php echo htmlspecialchars($usaha['nama_usaha']); ?>">
                            <?php else: ?>
                                <img src="<?php echo $pathPrefix; ?>assets/images/placeholder_usaha.png" alt="Placeholder Usaha">
                            <?php endif; ?>
                            <div class="category-badge"><?php echo htmlspecialchars($usaha['kategori_usaha']); ?></div>
                        </div>
                        <div class="usaha-card-content">
                            <h3 class="usaha-title"><?php echo htmlspecialchars($usaha['nama_usaha']); ?></h3>
                            <div class="usaha-meta">
                                <i class="fas fa-user"></i> <!-- Asumsikan menggunakan Font Awesome -->
                                <span class="usaha-seller"><?php echo htmlspecialchars($usaha['nama_penjual']); ?></span>
                            </div>
                            <p class="usaha-description">
                                <?php
                                // Potong deskripsi jika terlalu panjang
                                $deskripsi_singkat = $usaha['deskripsi_usaha'];
                                if (strlen($deskripsi_singkat) > 120) {
                                    $deskripsi_singkat = substr($deskripsi_singkat, 0, 120) . "...";
                                }
                                echo htmlspecialchars($deskripsi_singkat);
                                ?>
                            </p>
                            <a href="<?php echo $pathPrefix; ?>detail_produk_layanan.php?usaha_id=<?php echo $usaha['usaha_id']; ?>" class="btn-detail">Lihat Detail & Produk</a>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <img src="<?php echo $pathPrefix; ?>assets/images/kopma chibi.png" alt="Marketplace Kosong">
                <h3 class="empty-state-title">Saat ini belum ada usaha yang tersedia</h3>
                <p class="empty-state-subtitle">Silakan cek kembali nanti atau dukung mahasiswa untuk mendaftarkan usahanya!</p>
            </div>
        <?php endif; ?>
        <?php if (isset($result_marketplace) && $result_marketplace) $result_marketplace->free(); ?>
    </div>
</section>

<?php
    if (isset($conn)) $conn->close();
    // Include footer
    include 'includes/footer.php';
?>
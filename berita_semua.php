<?php
    session_start();
    $pathPrefix = ''; // Karena file ini ada di root project
    $pageTitle = "Semua Berita & Kegiatan";

    // Include koneksi database
    require 'config/db.php';

    // --- Pengaturan Paginasi ---
    $berita_per_halaman = 6; // Jumlah berita yang ditampilkan per halaman
    $halaman_saat_ini = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
    $offset = ($halaman_saat_ini - 1) * $berita_per_halaman;

    // Query untuk menghitung total berita yang published
    $sql_total_berita = "SELECT COUNT(id) AS total 
                      FROM berita_kegiatan 
                      WHERE status = 'published' AND tanggal_publikasi <= NOW()";
    $result_total = $conn->query($sql_total_berita);
    $total_berita = 0;
    if ($result_total) {
        $total_berita = $result_total->fetch_assoc()['total'];
    }
    $total_halaman = ceil($total_berita / $berita_per_halaman);


    // Ambil data berita untuk halaman saat ini
    $sql_berita_halaman = "SELECT id, judul, slug, konten, gambar_banner, tanggal_publikasi 
                        FROM berita_kegiatan 
                        WHERE status = 'published' AND tanggal_publikasi <= NOW()
                        ORDER BY tanggal_publikasi DESC 
                        LIMIT ? OFFSET ?";

    $berita_list = [];
    $stmt_berita_halaman = $conn->prepare($sql_berita_halaman);
    if ($stmt_berita_halaman) {
        $stmt_berita_halaman->bind_param("ii", $berita_per_halaman, $offset);
        $stmt_berita_halaman->execute();
        $result_berita_halaman = $stmt_berita_halaman->get_result();
        while ($row = $result_berita_halaman->fetch_assoc()) {
            $berita_list[] = $row;
        }
        $stmt_berita_halaman->close();
    } else {
        // Handle error prepare statement
        $error_db = "Gagal menyiapkan query berita: " . $conn->error;
    }


    // Include header
    include 'includes/header.php';
    ?>

<style>
    /* Gaya untuk section utama */
    .semua-berita-section {
        padding: 60px 20px;
        background-color: #f8f9fa;
        min-height: 90vh;
    }
    
    /* Gaya untuk judul section */
    .section-title {
        color: var(--dark-green);
        font-size: 2.2rem;
        margin-bottom: 15px;
        font-weight: 700;
        position: relative;
        text-align: center;
    }
    
    .section-title::after {
        content: "";
        display: block;
        height: 4px;
        width: 80px;
        background-color: var(--yellow);
        margin: 15px auto 0;
        border-radius: 2px;
    }
    
    /* Tombol kembali */
    .btn-back {
        display: inline-flex;
        align-items: center;
        margin-bottom: 35px;
        text-decoration: none;
        background-color: var(--dark);
        color: white;
        padding: 10px 18px;
        border-radius: 5px;
        font-weight: 500;
        transition: all 0.3s ease;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }
    
    .btn-back:hover {
        background-color: #333;
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.2);
    }
    
    /* Gaya untuk grid berita */
    .news-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
        gap: 30px;
        margin-bottom: 40px;
    }
    
    /* Gaya untuk card berita */
    .news-card {
        background-color: #fff;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        transition: all 0.3s ease;
        height: 100%;
        display: flex;
        flex-direction: column;
    }
    
    .news-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 15px 30px rgba(0,0,0,0.15);
    }
    
    .news-card-link {
        text-decoration: none;
        color: inherit;
        display: flex;
        flex-direction: column;
        height: 100%;
    }
    
    .news-img {
        height: 220px;
        background-size: cover;
        background-position: center;
        position: relative;
    }
    
    .news-img::before {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        height: 50%;
        background: linear-gradient(to bottom, rgba(0,0,0,0), rgba(0,0,0,0.5));
        z-index: 1;
    }
    
    .news-content {
        padding: 20px;
        display: flex;
        flex-direction: column;
        flex-grow: 1;
        position: relative;
    }
    
    .news-date {
        color: #6c757d;
        font-size: 0.85rem;
        margin-bottom: 10px;
        display: inline-block;
        font-weight: 500;
        background-color: #f8f9fa;
        padding: 4px 10px;
        border-radius: 20px;
    }
    
    .news-card h3 {
        font-size: 1.25rem;
        margin-bottom: 10px;
        color: #333;
        font-weight: 600;
        line-height: 1.4;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        transition: color 0.3s ease;
    }
    
    .news-card-link:hover h3 {
        color: var(--dark-green);
    }
    
    .news-card p {
        color: #6c757d;
        margin-bottom: 15px;
        line-height: 1.6;
        display: -webkit-box;
        -webkit-line-clamp: 3;
        -webkit-box-orient: vertical;
        overflow: hidden;
        flex-grow: 1;
    }
    
    .read-more {
        display: inline-block;
        color: var(--dark-green);
        font-weight: 600;
        position: relative;
        padding-bottom: 2px;
        align-self: flex-start;
        margin-top: auto;
    }
    
    .read-more::after {
        content: "";
        position: absolute;
        bottom: 0;
        left: 0;
        width: 0;
        height: 2px;
        background-color: var(--dark-green);
        transition: width 0.3s ease;
    }
    
    .news-card-link:hover .read-more::after {
        width: 100%;
    }
    
    /* Gaya untuk kategori berita (bila ditambahkan) */
    .news-category {
        position: absolute;
        top: -15px;
        left: 20px;
        background-color: var(--yellow);
        color: #333;
        padding: 5px 15px;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 600;
        z-index: 2;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }
    
    /* Gaya untuk paginasi */
    .pagination {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 8px;
        margin-top: 50px;
    }
    
    .pagination a,
    .pagination strong {
        padding: 10px 15px;
        font-size: 0.95rem;
        text-decoration: none;
        border-radius: 5px;
        transition: all 0.3s ease;
    }
    
    .pagination a:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }
    
    .pagination strong {
        background-color: var(--dark-green);
        color: white;
        font-weight: 600;
        box-shadow: 0 3px 5px rgba(0,0,0,0.1);
    }
    
    /* Pesan kosong */
    .empty-message {
        text-align: center;
        padding: 40px;
        background-color: #fff;
        border-radius: 12px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        font-size: 1.1rem;
        color: #6c757d;
    }
    
    /* Responsif untuk perangkat mobile */
    @media (max-width: 768px) {
        .semua-berita-section {
            padding: 40px 15px;
        }
        
        .section-title {
            font-size: 1.8rem;
        }
        
        .news-grid {
            grid-template-columns: 1fr;
            gap: 25px;
        }
        
        .news-img {
            height: 200px;
        }
    }
</style>

<section class="semua-berita-section">
    <div class="container">
        <h2 class="section-title"><?php echo $pageTitle; ?></h2>

        <a href="<?php echo $pathPrefix; ?>beranda.php#berita" class="btn-back">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" style="margin-right: 8px;" viewBox="0 0 16 16">
                <path fill-rule="evenodd" d="M15 8a.5.5 0 0 0-.5-.5H2.707l3.147-3.146a.5.5 0 1 0-.708-.708l-4 4a.5.5 0 0 0 0 .708l4 4a.5.5 0 0 0 .708-.708L2.707 8.5H14.5A.5.5 0 0 0 15 8z"/>
            </svg>
            Kembali ke Beranda
        </a>

        <?php if (isset($error_db)): ?>
            <div class="message error"><?php echo htmlspecialchars($error_db); ?></div>
        <?php elseif (!empty($berita_list)): ?>
            <div class="news-grid">
                <?php foreach ($berita_list as $berita): ?>
                    <div class="news-card">
                        <a href="<?php echo $pathPrefix; ?>berita_detail.php?slug=<?php echo htmlspecialchars($berita['slug']); ?>" class="news-card-link">
                            <div class="news-img" style="
                                <?php if ($berita['gambar_banner']): ?>
                                    background-image: url('<?php echo $pathPrefix . htmlspecialchars($berita['gambar_banner']); ?>');
                                <?php else: ?>
                                    background-image: url('<?php echo $pathPrefix; ?>assets/images/placeholder_news.png');
                                <?php endif; ?>
                            ">
                                <!-- Bisa tambahkan kategori di sini jika ada dalam database -->
                                <!-- <span class="news-category">Kegiatan</span> -->
                            </div>
                            <div class="news-content">
                                <span class="news-date">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="currentColor" style="margin-right: 4px; margin-bottom: -1px;" viewBox="0 0 16 16">
                                        <path d="M8 3.5a.5.5 0 0 0-1 0V9a.5.5 0 0 0 .252.434l3.5 2a.5.5 0 0 0 .496-.868L8 8.71V3.5z"/>
                                        <path d="M8 16A8 8 0 1 0 8 0a8 8 0 0 0 0 16zm7-8A7 7 0 1 1 1 8a7 7 0 0 1 14 0z"/>
                                    </svg>
                                    <?php echo $berita['tanggal_publikasi'] ? date('d F Y', strtotime($berita['tanggal_publikasi'])) : 'Tanggal tidak tersedia'; ?>
                                </span>
                                <h3><?php echo htmlspecialchars($berita['judul']); ?></h3>
                                <p>
                                    <?php
                                    $konten_teaser = strip_tags($berita['konten']);
                                    if (strlen($konten_teaser) > 120) {
                                        $konten_teaser = substr($konten_teaser, 0, 120) . "...";
                                    }
                                    echo htmlspecialchars($konten_teaser);
                                    ?>
                                </p>
                                <span class="read-more">Baca Selengkapnya →</span>
                            </div>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- --- Paginasi --- -->
            <?php if ($total_halaman > 1): ?>
                <div class="pagination">
                    <?php if ($halaman_saat_ini > 1): ?>
                        <a href="?page=<?php echo $halaman_saat_ini - 1; ?>" class="btn-admin btn-admin-secondary">« Sebelumnya</a>
                    <?php endif; ?>

                    <?php for ($i = 1; $i <= $total_halaman; $i++): ?>
                        <?php if ($i == $halaman_saat_ini): ?>
                            <strong class="btn-admin btn-admin-primary"><?php echo $i; ?></strong>
                        <?php else: ?>
                            <a href="?page=<?php echo $i; ?>" class="btn-admin btn-admin-light"><?php echo $i; ?></a>
                        <?php endif; ?>
                    <?php endfor; ?>

                    <?php if ($halaman_saat_ini < $total_halaman): ?>
                        <a href="?page=<?php echo $halaman_saat_ini + 1; ?>" class="btn-admin btn-admin-secondary">Berikutnya »</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            <!-- --- Akhir Paginasi --- -->

        <?php else: ?>
            <div class="empty-message">
                <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" fill="currentColor" style="margin-bottom: 15px; opacity: 0.5;" viewBox="0 0 16 16">
                    <path d="M14 1a1 1 0 0 1 1 1v12a1 1 0 0 1-1 1H2a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1h12zM2 0a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2H2z"/>
                    <path d="M8 4a.5.5 0 0 1 .5.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3A.5.5 0 0 1 8 4z"/>
                </svg>
                <p>Tidak ada berita atau kegiatan yang dipublikasikan saat ini.</p>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php
    if (isset($conn)) $conn->close();
    // Include footer
    include 'includes/footer.php';
?>
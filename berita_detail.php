<?php
    session_start();
    $pathPrefix = ''; // Karena file ini ada di root project
    $pageTitle = "Detail Berita"; // Akan diupdate dengan judul berita

    // Include koneksi database
    require 'config/db.php';

    $berita_detail = null;
    $error_message = '';
    $related_berita = [];

    if (!isset($_GET['slug'])) {
        $error_message = "Parameter berita tidak ditemukan.";
    } else {
        $slug = trim($_GET['slug']);

        // Ambil detail berita berdasarkan slug, pastikan status 'published' dan tanggal_publikasi valid
        $stmt_berita = $conn->prepare("SELECT bk.*, IFNULL(u.username, 'Admin KOPMA') AS nama_penulis 
                                    FROM berita_kegiatan bk
                                    LEFT JOIN users u ON bk.penulis_id = u.id 
                                    WHERE bk.slug = ? AND bk.status = 'published' AND bk.tanggal_publikasi <= NOW()");
        if (!$stmt_berita) {
            $error_message = "Database error (prepare): " . $conn->error;
        } else {
            $stmt_berita->bind_param("s", $slug);
            $stmt_berita->execute();
            $result_berita = $stmt_berita->get_result();
            if ($result_berita->num_rows === 1) {
                $berita_detail = $result_berita->fetch_assoc();
                $pageTitle = htmlspecialchars($berita_detail['judul']) . " - SILAPU";
                
                // Ambil berita terkait (3 berita terbaru selain yang sedang dilihat)
                $kategori = $berita_detail['kategori'] ?? '';
                if (!empty($kategori)) {
                    $stmt_related = $conn->prepare("SELECT bk.*, IFNULL(u.username, 'Admin KOPMA') AS nama_penulis 
                                          FROM berita_kegiatan bk
                                          LEFT JOIN users u ON bk.penulis_id = u.id 
                                          WHERE bk.kategori = ? AND bk.slug != ? 
                                          AND bk.status = 'published' AND bk.tanggal_publikasi <= NOW()
                                          ORDER BY bk.tanggal_publikasi DESC LIMIT 3");
                    $stmt_related->bind_param("ss", $kategori, $slug);
                    $stmt_related->execute();
                    $result_related = $stmt_related->get_result();
                    while ($row = $result_related->fetch_assoc()) {
                        $related_berita[] = $row;
                    }
                    $stmt_related->close();
                }
            } else {
                $error_message = "Berita tidak ditemukan atau belum dipublikasikan.";
            }
            $stmt_berita->close();
        }
    }

    // Include header
    include 'includes/header.php';
    ?>

<style>
    /* Warna & Variabel */
    :root {
        --primary-color: #2E7D32;
        --primary-light: #4CAF50;
        --primary-dark: #1B5E20;
        --accent-color: #FF8F00;
        --text-dark: #212121;
        --text-light: #757575;
        --background-light: #f9f9f9;
        --white: #ffffff;
        --gray-100: #f5f5f5;
        --gray-200: #eeeeee;
        --gray-300: #e0e0e0;
        --shadow-sm: 0 1px 3px rgba(0,0,0,0.12), 0 1px 2px rgba(0,0,0,0.24);
        --shadow-md: 0 3px 6px rgba(0,0,0,0.16), 0 3px 6px rgba(0,0,0,0.23);
        --shadow-lg: 0 10px 20px rgba(0,0,0,0.19), 0 6px 6px rgba(0,0,0,0.23);
        --border-radius-sm: 4px;
        --border-radius-md: 8px;
        --border-radius-lg: 12px;
        --transition: all 0.3s cubic-bezier(.25,.8,.25,1);
    }

    /* Layout & Container */
    .berita-detail-section {
        padding: 60px 20px;
        background-color: var(--background-light);
        min-height: 80vh;
    }

    .container-berita {
        max-width: 1000px;
        margin: 0 auto;
    }

    /* Navigasi */
    .nav-buttons {
        display: flex;
        margin-bottom: 30px;
        gap: 12px;
        flex-wrap: wrap;
    }

    .nav-btn {
        display: inline-flex;
        align-items: center;
        text-decoration: none;
        padding: 10px 20px;
        border-radius: var(--border-radius-md);
        font-weight: 600;
        transition: var(--transition);
        box-shadow: var(--shadow-sm);
    }

    .btn-primary {
        background-color: var(--primary-color);
        color: var(--white);
    }

    .btn-primary:hover {
        background-color: var(--primary-dark);
        box-shadow: var(--shadow-md);
    }

    .btn-secondary {
        background-color: var(--gray-300);
        color: var(--text-dark);
    }

    .btn-secondary:hover {
        background-color: var(--gray-200);
        box-shadow: var(--shadow-md);
    }

    .btn-icon {
        margin-right: 8px;
    }

    /* Artikel & Konten */
    .berita-artikel {
        background-color: var(--white);
        border-radius: var(--border-radius-lg);
        box-shadow: var(--shadow-md);
        padding: 40px;
        margin-bottom: 30px;
        position: relative;
        overflow: hidden;
    }

    .berita-artikel:before {
        content: "";
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 5px;
        background: linear-gradient(90deg, var(--primary-color), var(--accent-color));
    }

    .berita-judul-detail {
        font-size: 2.5rem;
        color: var(--primary-dark);
        margin-top: 0;
        margin-bottom: 20px;
        line-height: 1.2;
        font-weight: 700;
    }

    .berita-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 20px;
        font-size: 0.95rem;
        color: var(--text-light);
        margin-bottom: 30px;
        padding-bottom: 20px;
        border-bottom: 1px solid var(--gray-200);
    }

    .meta-item {
        display: flex;
        align-items: center;
    }

    .meta-icon {
        margin-right: 6px;
        color: var(--primary-light);
    }

    .berita-banner-container {
        margin: -40px -40px 30px -40px;
        position: relative;
        height: 400px;
        overflow: hidden;
    }

    .berita-banner-container img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .kategori-badge {
        position: absolute;
        top: 20px;
        right: 20px;
        background-color: var(--accent-color);
        color: var(--white);
        padding: 8px 16px;
        border-radius: var(--border-radius-sm);
        font-weight: 600;
        font-size: 0.9rem;
        box-shadow: var(--shadow-sm);
    }

    .berita-konten-lengkap {
        font-size: 1.1rem;
        line-height: 1.8;
        color: var(--text-dark);
    }

    .berita-konten-lengkap p {
        margin-bottom: 1.5rem;
    }

    .berita-konten-lengkap h2, 
    .berita-konten-lengkap h3 {
        margin-top: 2rem;
        margin-bottom: 1rem;
        color: var(--primary-dark);
    }

    .berita-konten-lengkap a {
        color: var(--primary-color);
        text-decoration: underline;
        transition: var(--transition);
    }

    .berita-konten-lengkap a:hover {
        color: var(--accent-color);
    }

    .berita-konten-lengkap img {
        max-width: 100%;
        height: auto;
        border-radius: var(--border-radius-md);
        margin: 1.5rem 0;
    }

    /* Bagian Share */
    .berita-share {
        margin-top: 40px;
        padding-top: 20px;
        border-top: 1px solid var(--gray-200);
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 15px;
    }

    .share-label {
        font-weight: 600;
        color: var(--text-dark);
    }

    .social-buttons {
        display: flex;
        gap: 10px;
    }

    .social-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background-color: var(--gray-200);
        color: var(--text-dark);
        transition: var(--transition);
    }

    .social-btn:hover {
        transform: translateY(-3px);
        box-shadow: var(--shadow-sm);
    }

    .facebook:hover {
        background-color: #1877F2;
        color: white;
    }

    .twitter:hover {
        background-color: #1DA1F2;
        color: white;
    }

    .whatsapp:hover {
        background-color: #25D366;
        color: white;
    }

    /* Berita Terkait */
    .berita-terkait {
        margin-top: 40px;
    }

    .terkait-title {
        font-size: 1.5rem;
        margin-bottom: 20px;
        color: var(--primary-dark);
        position: relative;
        padding-bottom: 10px;
    }

    .terkait-title:after {
        content: "";
        position: absolute;
        bottom: 0;
        left: 0;
        width: 60px;
        height: 3px;
        background-color: var(--primary-color);
    }

    .berita-terkait-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 20px;
    }

    .berita-terkait-card {
        background: var(--white);
        border-radius: var(--border-radius-md);
        overflow: hidden;
        box-shadow: var(--shadow-sm);
        transition: var(--transition);
    }

    .berita-terkait-card:hover {
        transform: translateY(-5px);
        box-shadow: var(--shadow-md);
    }

    .terkait-img {
        height: 180px;
        width: 100%;
    }

    .terkait-img img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .terkait-content {
        padding: 20px;
    }

    .terkait-date {
        font-size: 0.85rem;
        color: var(--text-light);
        margin-bottom: 8px;
        display: block;
    }

    .terkait-title-link {
        font-size: 1.1rem;
        color: var(--text-dark);
        font-weight: 600;
        margin-bottom: 10px;
        display: block;
        text-decoration: none;
        line-height: 1.4;
    }

    .terkait-title-link:hover {
        color: var(--primary-color);
    }

    .read-more {
        margin-top: 10px;
        display: inline-flex;
        align-items: center;
        color: var(--primary-color);
        font-weight: 600;
        font-size: 0.9rem;
        text-decoration: none;
    }

    .read-more:hover {
        color: var(--accent-color);
    }

    .read-more-icon {
        margin-left: 5px;
        transition: var(--transition);
    }

    .read-more:hover .read-more-icon {
        transform: translateX(3px);
    }

    /* Error & Message */
    .message {
        padding: 30px;
        border-radius: var(--border-radius-md);
        text-align: center;
        margin-bottom: 30px;
    }

    .error {
        background-color: #FFEBEE;
        color: #C62828;
        border: 1px solid #FFCDD2;
    }

    .info {
        background-color: #E3F2FD;
        color: #0D47A1;
        border: 1px solid #BBDEFB;
    }

    .message p {
        font-size: 1.2rem;
        margin: 0;
    }

    /* Responsif */
    @media (max-width: 768px) {
        .berita-artikel {
            padding: 25px;
        }

        .berita-banner-container {
            margin: -25px -25px 20px -25px;
            height: 250px;
        }

        .berita-judul-detail {
            font-size: 1.8rem;
        }

        .berita-meta {
            flex-direction: column;
            gap: 10px;
        }

        .berita-share {
            flex-direction: column;
            align-items: flex-start;
        }

        .berita-terkait-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<section class="berita-detail-section">
    <div class="container-berita">
        <!-- Navigasi -->
        <div class="nav-buttons">
            <a href="<?php echo $pathPrefix; ?>beranda.php#berita" class="nav-btn btn-primary">
                <svg class="btn-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="19" y1="12" x2="5" y2="12"></line>
                    <polyline points="12 19 5 12 12 5"></polyline>
                </svg>
                Kembali ke Beranda
            </a>
            <a href="<?php echo $pathPrefix; ?>berita_semua.php" class="nav-btn btn-secondary">
                <svg class="btn-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="3" width="7" height="7"></rect>
                    <rect x="14" y="3" width="7" height="7"></rect>
                    <rect x="14" y="14" width="7" height="7"></rect>
                    <rect x="3" y="14" width="7" height="7"></rect>
                </svg>
                Lihat Semua Berita
            </a>
        </div>

        <?php if ($error_message): ?>
            <div class="message error">
                <p><?php echo htmlspecialchars($error_message); ?></p>
            </div>
        <?php elseif ($berita_detail): ?>
            <article class="berita-artikel">
                <?php if ($berita_detail['gambar_banner']): ?>
                    <div class="berita-banner-container">
                        <img src="<?php echo $pathPrefix . htmlspecialchars($berita_detail['gambar_banner']); ?>" alt="Banner: <?php echo htmlspecialchars($berita_detail['judul']); ?>">
                        <?php if (!empty($berita_detail['kategori'])): ?>
                            <div class="kategori-badge"><?php echo htmlspecialchars($berita_detail['kategori']); ?></div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <h1 class="berita-judul-detail">
                    <?php echo htmlspecialchars($berita_detail['judul']); ?>
                </h1>
                
                <div class="berita-meta">
                    <div class="meta-item">
                        <svg class="meta-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                            <circle cx="12" cy="7" r="4"></circle>
                        </svg>
                        <span><?php echo htmlspecialchars($berita_detail['nama_penulis']); ?></span>
                    </div>
                    <div class="meta-item">
                        <svg class="meta-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                            <line x1="16" y1="2" x2="16" y2="6"></line>
                            <line x1="8" y1="2" x2="8" y2="6"></line>
                            <line x1="3" y1="10" x2="21" y2="10"></line>
                        </svg>
                        <span><?php echo date('d F Y, H:i', strtotime($berita_detail['tanggal_publikasi'])); ?></span>
                    </div>
                    <?php if (!empty($berita_detail['kategori']) && empty($berita_detail['gambar_banner'])): ?>
                        <div class="meta-item">
                            <svg class="meta-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"></path>
                                <line x1="7" y1="7" x2="7.01" y2="7"></line>
                            </svg>
                            <span><?php echo htmlspecialchars($berita_detail['kategori']); ?></span>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="berita-konten-lengkap">
                    <?php echo nl2br(htmlspecialchars($berita_detail['konten'])); ?>
                </div>

                <!-- Bagian share sosial media -->
                <div class="berita-share">
                    <span class="share-label">Bagikan:</span>
                    <div class="social-buttons">
                        <a href="javascript:void(0)" onclick="window.open('https://www.facebook.com/sharer/sharer.php?u='+encodeURIComponent(window.location.href),'facebook-share-dialog','width=626,height=436');return false;" class="social-btn facebook" title="Bagikan ke Facebook">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"></path>
                            </svg>
                        </a>
                        <a href="javascript:void(0)" onclick="window.open('https://twitter.com/intent/tweet?text=<?php echo urlencode($berita_detail['judul']); ?>&url='+encodeURIComponent(window.location.href),'twitter-share-dialog','width=626,height=436');return false;" class="social-btn twitter" title="Bagikan ke Twitter">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M23 3a10.9 10.9 0 0 1-3.14 1.53 4.48 4.48 0 0 0-7.86 3v1A10.66 10.66 0 0 1 3 4s-4 9 5 13a11.64 11.64 0 0 1-7 2c9 5 20 0 20-11.5a4.5 4.5 0 0 0-.08-.83A7.72 7.72 0 0 0 23 3z"></path>
                            </svg>
                        </a>
                        <a href="javascript:void(0)" onclick="window.open('https://api.whatsapp.com/send?text=<?php echo urlencode($berita_detail['judul'] . ' - ' . $berita_detail['judul']); ?>%20'+encodeURIComponent(window.location.href),'whatsapp-share-dialog','width=626,height=436');return false;" class="social-btn whatsapp" title="Bagikan ke WhatsApp">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"></path>
                            </svg>
                        </a>
                    </div>
                </div>
            </article>

            <!-- Berita Terkait -->
            <?php if (!empty($related_berita)): ?>
                <div class="berita-terkait">
                    <h2 class="terkait-title">Berita Terkait</h2>
                    <div class="berita-terkait-grid">
                        <?php foreach ($related_berita as $berita): ?>
                            <div class="berita-terkait-card">
                                <?php if (!empty($berita['gambar_banner'])): ?>
                                    <div class="terkait-img">
                                        <img src="<?php echo $pathPrefix . htmlspecialchars($berita['gambar_banner']); ?>" alt="<?php echo htmlspecialchars($berita['judul']); ?>">
                                    </div>
                                <?php endif; ?>
                                <div class="terkait-content">
                                    <span class="terkait-date"><?php echo date('d F Y', strtotime($berita['tanggal_publikasi'])); ?></span>
                                    <a href="<?php echo $pathPrefix . 'berita_detail.php?slug=' . htmlspecialchars($berita['slug']); ?>" class="terkait-title-link">
                                        <?php echo htmlspecialchars($berita['judul']); ?>
                                    </a>
                                    <p><?php echo strlen($berita['konten']) > 100 ? htmlspecialchars(substr($berita['konten'], 0, 100) . '...') : htmlspecialchars($berita['konten']); ?></p>
                                    <a href="<?php echo $pathPrefix . 'berita_detail.php?slug=' . htmlspecialchars($berita['slug']); ?>" class="read-more">
                                        Baca selengkapnya
                                        <svg class="read-more-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <line x1="5" y1="12" x2="19" y2="12"></line>
                                            <polyline points="12 5 19 12 12 19"></polyline>
                                        </svg>
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

        <?php else: ?>
            <div class="message info">
                <p>Berita tidak tersedia.</p>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php
    if (isset($conn)) $conn->close();
    // Include footer
    include 'includes/footer.php';
?>
<?php
session_start();
$pathPrefix = ''; // Karena beranda.php ada di root
$pageTitle = "SILAPU - Koperasi Mahasiswa UIN Raden Intan Lampung";

// Include koneksi database
require 'config/db.php'; // Path langsung karena file ini di root

// Display contact form submission message if any
if (isset($_SESSION['contact_message'])) {
    $msg = $_SESSION['contact_message'];
    echo '<div class="message ' . htmlspecialchars($msg['type']) . '" style="max-width: 600px; margin: 20px auto; padding: 15px; border-radius: 8px; text-align: center;">' . htmlspecialchars($msg['text']) . '</div>';
    unset($_SESSION['contact_message']);
}

// Ambil 3 berita terbaru yang statusnya 'published'
$sql_berita_terbaru = "SELECT id, judul, slug, konten, gambar_banner, tanggal_publikasi 
                       FROM berita_kegiatan 
                       WHERE status = 'published' AND tanggal_publikasi <= NOW()
                       ORDER BY tanggal_publikasi DESC 
                       LIMIT 3";
$result_berita_terbaru = $conn->query($sql_berita_terbaru);

include 'includes/header.php';
?>

<style>
    :root {
        --primary-color: #2ecc71;
        --primary-dark: #27ae60;
        --primary-light: #a9dfbf;
        --secondary-color: #e67e22;
        --secondary-dark: #d35400;
        --accent-color: #3498db;
        --dark: #2c3e50;
        --light: #f9f9f9;
        --white: #ffffff;
        --gray: #95a5a6;
        --light-gray: #ecf0f1;
        --shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        --transition: all 0.3s ease;
    }

    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
        font-family: 'Poppins', 'Segoe UI', sans-serif;
    }

    body {
        background-color: var(--light);
        color: var(--dark);
        overflow-x: hidden;
        line-height: 1.6;
    }

    .container {
        width: 100%;
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 20px;
    }

    /* Common Elements */
    .btn {
        display: inline-block;
        padding: 12px 28px;
        background-color: var(--secondary-color);
        color: var(--white);
        text-decoration: none;
        border-radius: 50px;
        font-weight: 600;
        font-size: 1rem;
        transition: var(--transition);
        border: none;
        cursor: pointer;
        box-shadow: 0 4px 15px rgba(230, 126, 34, 0.3);
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    .btn:hover {
        background-color: var(--secondary-dark);
        transform: translateY(-3px);
        box-shadow: 0 8px 25px rgba(230, 126, 34, 0.4);
    }

    .btn-outlined {
        background-color: transparent;
        border: 2px solid var(--primary-color);
        color: var(--primary-color);
        box-shadow: none;
    }

    .btn-outlined:hover {
        background-color: var(--primary-color);
        color: var(--white);
        box-shadow: 0 5px 15px rgba(46, 204, 113, 0.3);
    }

    .section-title {
        text-align: center;
        margin-bottom: 60px;
        position: relative;
    }

    .section-title h2 {
        font-size: 2.5rem;
        color: var(--dark);
        margin-bottom: 20px;
        font-weight: 700;
    }

    .section-title p {
        color: var(--gray);
        max-width: 700px;
        margin: 0 auto;
    }

    .section-title::after {
        content: "";
        position: absolute;
        bottom: -15px;
        left: 50%;
        transform: translateX(-50%);
        width: 80px;
        height: 4px;
        background: linear-gradient(to right, var(--primary-color), var(--accent-color));
        border-radius: 2px;
    }

    section {
        padding: 100px 0;
    }

    /* Hero Section */
    .hero {
        position: relative;
        min-height: 100vh;
        display: flex;
        align-items: center;
        color: var(--white);
        overflow: hidden;
    }

    .hero-slideshow {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        z-index: 1;
    }

    .slide {
        position: absolute;
        width: 100%;
        height: 100%;
        background-size: cover;
        background-position: center;
        opacity: 0;
        transition: opacity 1.5s ease-in-out;
    }

    .slide.active {
        opacity: 1;
    }

    .hero-overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: linear-gradient(135deg, rgba(0, 0, 0, 0.8) 0%, rgba(0, 0, 0, 0.4) 100%);
        z-index: 2;
    }

    .hero .container {
        position: relative;
        z-index: 3;
        text-align: center;
        max-width: 900px;
    }

    .hero h2 {
        font-size: 3.5rem;
        font-weight: 800;
        margin-bottom: 30px;
        animation: fadeInUp 1s ease;
        text-shadow: 2px 2px 4px rgba(0,0,0,0.4);
    }

    .hero p {
        font-size: 1.3rem;
        max-width: 800px;
        margin: 0 auto 40px;
        animation: fadeInUp 1s ease 0.3s;
        animation-fill-mode: both;
        text-shadow: 1px 1px 3px rgba(0,0,0,0.3);
        line-height: 1.8;
    }

    .hero .btn {
        animation: fadeInUp 1s ease 0.6s;
        animation-fill-mode: both;
        margin: 0 10px;
    }

    .hero-buttons {
        display: flex;
        justify-content: center;
        gap: 20px;
        flex-wrap: wrap;
    }

    /* Services Section */
    .services {
        background-color: var(--white);
        position: relative;
    }

    .services-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 40px;
    }

    .service-card {
        background-color: var(--white);
        border-radius: 15px;
        overflow: hidden;
        box-shadow: var(--shadow);
        transition: var(--transition);
        position: relative;
        z-index: 1;
        text-align: center;
        padding: 30px;
        height: 100%;
        display: flex;
        flex-direction: column;
    }

    .service-card:hover {
        transform: translateY(-15px);
        box-shadow: 0 20px 30px rgba(0, 0, 0, 0.15);
    }

    .service-icon {
        width: 80px;
        height: 80px;
        background-color: var(--primary-light);
        color: var(--primary-dark);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2.5rem;
        border-radius: 50%;
        margin: 0 auto 25px;
        transition: var(--transition);
    }

    .service-card:hover .service-icon {
        background-color: var(--primary-color);
        color: var(--white);
        transform: rotateY(360deg);
        transition: transform 0.8s, background-color 0.3s, color 0.3s;
    }

    .service-content {
        flex-grow: 1;
        display: flex;
        flex-direction: column;
    }

    .service-content h3 {
        color: var(--dark);
        margin-bottom: 15px;
        font-size: 1.4rem;
        font-weight: 700;
    }

    .service-content p {
        color: var(--gray);
        margin-bottom: 25px;
        flex-grow: 1;
    }

    .read-more {
        color: var(--primary-color);
        text-decoration: none;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        transition: var(--transition);
        border-bottom: 2px solid transparent;
        padding-bottom: 5px;
        margin-top: auto;
    }

    .read-more i {
        margin-left: 5px;
        transition: var(--transition);
    }

    .read-more:hover {
        color: var(--primary-dark);
        border-bottom: 2px solid var(--primary-dark);
    }

    .read-more:hover i {
        transform: translateX(5px);
    }

    /* About Section */
    .about {
        background: linear-gradient(135deg, var(--primary-light) 0%, rgba(255, 255, 255, 0.9) 100%);
        position: relative;
    }

    .about::before {
        content: "";
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-image: url('<?php echo $pathPrefix; ?>assets/images/pattern.png');
        background-size: 300px;
        opacity: 0.05;
    }

    .about-content {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 60px;
        align-items: center;
        position: relative;
    }

    .about-img {
        height: 450px;
        border-radius: 20px;
        overflow: hidden;
        box-shadow: var(--shadow);
        position: relative;
    }

    .about-img img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: var(--transition);
    }

    .about-img:hover img {
        transform: scale(1.05);
    }

    .about-text {
        position: relative;
    }

    .about-text h2 {
        font-size: 2.2rem;
        margin-bottom: 25px;
        font-weight: 700;
        color: var(--dark);
    }

    .about-text p {
        margin-bottom: 20px;
        line-height: 1.8;
        color: var(--dark);
    }

    .about-stats {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 30px;
        margin: 40px 0;
    }

    .stat-item {
        text-align: center;
    }

    .stat-number {
        font-size: 2.5rem;
        font-weight: 800;
        color: var(--primary-dark);
        margin-bottom: 5px;
        display: block;
    }

    .stat-label {
        color: var(--gray);
        font-size: 0.9rem;
    }

    /* News Section */
    .news {
        background-color: var(--white);
    }

    .news-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
        gap: 40px;
    }

    .news-card {
        background-color: var(--white);
        border-radius: 15px;
        overflow: hidden;
        box-shadow: var(--shadow);
        transition: var(--transition);
    }

    .news-card:hover {
        transform: translateY(-10px);
        box-shadow: 0 15px 30px rgba(0, 0, 0, 0.15);
    }

    .news-img {
        height: 220px;
        position: relative;
        overflow: hidden;
    }

    .news-img img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: var(--transition);
    }

    .news-card:hover .news-img img {
        transform: scale(1.1);
    }

    .news-date {
        position: absolute;
        bottom: 0;
        left: 0;
        background-color: var(--primary-color);
        color: var(--white);
        padding: 8px 15px;
        font-size: 0.8rem;
        font-weight: 600;
        border-top-right-radius: 15px;
    }

    .news-content {
        padding: 25px;
    }

    .news-category {
        color: var(--accent-color);
        font-size: 0.85rem;
        font-weight: 600;
        margin-bottom: 10px;
        display: block;
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    .news-content h3 {
        color: var(--dark);
        margin-bottom: 15px;
        font-size: 1.4rem;
        font-weight: 700;
        line-height: 1.4;
    }

    .news-content p {
        color: var(--gray);
        margin-bottom: 20px;
        line-height: 1.7;
    }

    /* Contact Section */
    .contact {
        background: linear-gradient(135deg, var(--primary-light) 0%, rgba(255, 255, 255, 0.9) 100%);
        position: relative;
    }

    .contact::before {
        content: "";
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-image: url('<?php echo $pathPrefix; ?>assets/images/pattern.png');
        background-size: 300px;
        opacity: 0.05;
    }

    .contact-container {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 60px;
        position: relative;
    }

    .contact-info {
        padding-right: 20px;
    }

    .contact-info h3 {
        font-size: 1.8rem;
        margin-bottom: 25px;
        color: var(--dark);
        font-weight: 700;
    }

    .contact-info p {
        margin-bottom: 20px;
        line-height: 1.7;
        color: var(--dark);
    }

    .contact-info .info-item {
        display: flex;
        align-items: flex-start;
        margin-bottom: 25px;
    }

    .info-item i {
        margin-right: 15px;
        color: var(--primary-color);
        font-size: 1.4rem;
        background-color: var(--white);
        width: 45px;
        height: 45px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    }

    .info-content h4 {
        color: var(--dark);
        margin-bottom: 5px;
        font-size: 1.1rem;
    }

    .info-content p {
        margin-bottom: 0;
        color: var(--gray);
    }

    .social-links {
        display: flex;
        gap: 15px;
        margin-top: 30px;
    }

    .social-link {
        width: 40px;
        height: 40px;
        background-color: var(--white);
        color: var(--primary-color);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        text-decoration: none;
        transition: var(--transition);
        box-shadow: 0 5px 10px rgba(0, 0, 0, 0.1);
    }

    .social-link:hover {
        background-color: var(--primary-color);
        color: var(--white);
        transform: translateY(-5px);
    }

    .contact-form {
        background-color: var(--white);
        padding: 40px;
        border-radius: 20px;
        box-shadow: var(--shadow);
    }

    .form-group {
        margin-bottom: 25px;
    }

    .form-group label {
        display: block;
        margin-bottom: 8px;
        color: var(--dark);
        font-weight: 600;
        font-size: 0.95rem;
    }

    .form-control {
        width: 100%;
        padding: 15px 20px;
        border: 1px solid var(--light-gray);
        border-radius: 10px;
        font-size: 1rem;
        transition: var(--transition);
        background-color: var(--light);
    }

    .form-control:focus {
        border-color: var(--primary-color);
        outline: none;
        box-shadow: 0 0 0 3px rgba(46, 204, 113, 0.2);
    }

    textarea.form-control {
        resize: vertical;
        min-height: 150px;
    }

    .submit-btn {
        width: 100%;
        padding: 15px;
        font-size: 1.05rem;
    }

    /* Counter Section */
    .counter-section {
        background-color: var(--primary-color);
        padding: 80px 0;
        color: var(--white);
        text-align: center;
        background-image: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
        position: relative;
        overflow: hidden;
    }

    .counter-section::before {
        content: "";
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-image: url('<?php echo $pathPrefix; ?>assets/images/pattern.png');
        background-size: 200px;
        opacity: 0.1;
    }

    .counter-container {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 30px;
    }

    .counter-item {
        padding: 20px;
    }

    .counter-icon {
        font-size: 2.5rem;
        margin-bottom: 15px;
        display: inline-block;
    }

    .counter-number {
        font-size: 2.8rem;
        font-weight: 800;
        margin-bottom: 10px;
    }

    .counter-text {
        font-size: 1.1rem;
        font-weight: 500;
        opacity: 0.9;
    }

    /* Testimonials Section */
    .testimonials {
        background-color: var(--white);
        overflow: hidden;
    }

    .testimonials-container {
        max-width: 1000px;
        margin: 0 auto;
        position: relative;
    }

    .testimonial-slider {
        display: flex;
        transition: transform 0.5s ease;
    }

    .testimonial-slide {
        min-width: 100%;
        padding: 20px;
    }

    .testimonial-card {
        background-color: var(--light);
        padding: 40px;
        border-radius: 15px;
        box-shadow: var(--shadow);
        text-align: center;
        position: relative;
    }

    .testimonial-quote {
        font-size: 6rem;
        position: absolute;
        top: -30px;
        left: 20px;
        color: var(--primary-light);
        opacity: 0.3;
    }

    .testimonial-text {
        font-style: italic;
        margin-bottom: 30px;
        line-height: 1.8;
        color: var(--dark);
        position: relative;
        z-index: 1;
    }

    .testimonial-author {
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .author-img {
        width: 70px;
        height: 70px;
        border-radius: 50%;
        overflow: hidden;
        margin-right: 15px;
    }

    .author-img img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .author-info h4 {
        font-weight: 700;
        color: var(--dark);
        margin-bottom: 5px;
    }

    .author-info p {
        color: var(--gray);
        font-size: 0.9rem;
    }

    .testimonial-dots {
        display: flex;
        justify-content: center;
        margin-top: 30px;
        gap: 10px;
    }

    .dot {
        width: 12px;
        height: 12px;
        background-color: var(--light-gray);
        border-radius: 50%;
        cursor: pointer;
        transition: var(--transition);
    }

    .dot.active {
        background-color: var(--primary-color);
        transform: scale(1.2);
    }

    /* Call to Action Section */
    .cta-section {
        background: linear-gradient(135deg, var(--secondary-color) 0%, var(--secondary-dark) 100%);
        color: var(--white);
        text-align: center;
        padding: 80px 0;
        position: relative;
        overflow: hidden;
    }

    .cta-section::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-image: url('<?php echo $pathPrefix; ?>assets/images/pattern.png');
        background-size: 200px;
        opacity: 0.05;
    }

    .cta-container {
        max-width: 800px;
        margin: 0 auto;
        position: relative;
        z-index: 1;
    }

    .cta-section h2 {
        font-size: 2.5rem;
        margin-bottom: 20px;
        font-weight: 700;
    }

    .cta-section p {
        font-size: 1.2rem;
        margin-bottom: 40px;
        line-height: 1.7;
    }

    .cta-buttons {
        display: flex;
        gap: 20px;
        justify-content: center;
        flex-wrap: wrap;
    }

    .btn-white {
        background-color: var(--white);
        color: var(--secondary-color);
    }

    .btn-white:hover {
        background-color: var(--light);
        color: var(--secondary-dark);
    }

    /* Animations */
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
        }
        to {
            opacity: 1;
        }
    }

    .fade-in {
        animation: fadeIn 1s ease;
    }

    .fade-in-up {
        animation: fadeInUp 1s ease;
    }

    /* Enhanced Responsive Design */
    @media screen and (max-width: 992px) {
        section {
            padding: 80px 0;
        }

        .section-title h2 {
            font-size: 2.2rem;
        }

        .hero h2 {
            font-size: 2.8rem;
        }

        .about-content,
        .contact-container {
            grid-template-columns: 1fr;
        }

        .about-img {
            height: 400px;
        }

        .counter-container {
            grid-template-columns: repeat(2, 1fr);
        }

        .testimonial-card {
            padding: 30px;
        }
    }

    @media screen and (max-width: 768px) {
        section {
            padding: 60px 0;
        }

        .hero h2 {
            font-size: 2.2rem;
        }

        .hero p {
            font-size: 1.1rem;
        }

        .news-grid {
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        }

        .about-stats {
            grid-template-columns: repeat(2, 1fr);
        }

        .counter-number {
            font-size: 2.2rem;
        }

        .contact-form {
            padding: 30px;
        }
    }

    @media screen and (max-width: 576px) {
        .hero h2 {
            font-size: 1.8rem;
        }

        .section-title h2 {
            font-size: 1.8rem;
        }

        .about-stats {
            grid-template-columns: 1fr;
        }

        .counter-container {
            grid-template-columns: 1fr;
        }

        .service-card,
        .news-card {
            margin: 0 auto;
            max-width: 350px;
        }
    }

    /* Add scroll reveal effect */
    .reveal {
        position: relative;
        opacity: 0;
        transform: translateY(30px);
        transition: all 1s ease;
    }

    .reveal.active {
        opacity: 1;
        transform: translateY(0);
    }
</style>

<!-- Hero Section with Enhanced Slideshow and Overlay -->
<section class="hero" id="beranda">
    <div class="hero-slideshow">
        <div class="slide active" style="background-image: url('<?php echo $pathPrefix; ?>assets/images/silapu1.png');"></div>
        <div class="slide" style="background-image: url('<?php echo $pathPrefix; ?>assets/images/silapubg2.jpg');"></div>
        <!-- Add more slides as needed -->
    </div>
    <div class="hero-overlay"></div>
    <div class="container">
        <h2>Sistem Informasi Layanan Pemberdayaan Usaha</h2>
        <p>Membangun generasi wirausaha muda yang tangguh, mandiri, dan bermanfaat bagi masyarakat melalui pemberdayaan usaha koperasi mahasiswa UIN Raden Intan Lampung.</p>
        <div class="hero-buttons">
            <a href="#layanan" class="btn">Jelajahi Layanan</a>
            <a href="<?php echo $pathPrefix; ?>pendaftaran_anggota.php" class="btn btn-outlined">Daftar Anggota</a>
        </div>
    </div>
</section>

<!-- Services Section (Enhanced) -->
<section class="services reveal" id="layanan">
    <div class="container">
        <div class="section-title">
            <h2>Layanan Kami</h2>
            <p>Berbagai layanan unggulan untuk mendukung perkembangan usaha dan kewirausahaan mahasiswa</p>
        </div>
        <div class="services-grid">
            <div class="service-card reveal">
                <div class="service-icon">💡</div>
                <div class="service-content">
                    <h3>Ajukan Usaha Anda</h3>
                    <p>Punya ide bisnis? Daftarkan usaha Anda untuk mendapatkan dukungan dan akses ke marketplace KOPMA.</p>
                    <a href="<?php echo $pathPrefix; ?>auth/login.php?redirect=user/ajukan_usaha.php" class="read-more">Ajukan Usaha <i class="fas fa-arrow-right"></i></a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- About Section (Enhanced) -->
<section class="about reveal" id="tentang">
    <div class="container">
        <div class="about-content">
            <div class="about-img reveal">
                <img src="<?php echo $pathPrefix; ?>assets/images/silapu3.JPG" alt="Koperasi Mahasiswa UIN RIL" loading="lazy">
            </div>
            <div class="about-text reveal">
                <h2>Tentang Koperasi Mahasiswa UIN Raden Intan Lampung</h2>
                <p>Koperasi Mahasiswa UIN Raden Intan Lampung merupakan organisasi ekonomi kampus yang didirikan untuk memajukan kesejahteraan anggota pada khususnya dan mahasiswa pada umumnya.</p>
                <p>Visi kami adalah menjadi koperasi mahasiswa terkemuka yang mampu mengembangkan potensi wirausaha mahasiswa dan membangun generasi yang mandiri secara ekonomi.</p>
                
                <div class="about-stats">
                    <div class="stat-item">
                        <span class="stat-number">2010</span>
                        <span class="stat-label">Tahun Berdiri</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number">15+</span>
                        <span class="stat-label">Unit Usaha</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number">100%</span>
                        <span class="stat-label">Komitmen</span>
                    </div>
                </div>
                
                <p>Melalui Sistem Informasi Layanan Pemberdayaan Usaha (SILAPU), kami berkomitmen untuk memfasilitasi mahasiswa dalam mengembangkan usaha dan meningkatkan kapasitas diri di bidang kewirausahaan.</p>
                <a href="#" class="btn">Sejarah Kami</a>
            </div>
        </div>
    </div>
</section>

<!-- News Section (Sekarang Dinamis) -->
<section class="news" id="berita">
    <div class="container">
        <div class="section-title">
            <h2>Berita & Kegiatan Terbaru</h2>
        </div>

        <?php if ($result_berita_terbaru && $result_berita_terbaru->num_rows > 0): ?>
            <div class="news-grid">
                <?php while($berita = $result_berita_terbaru->fetch_assoc()): ?>
                    <div class="news-card">
                        <a href="<?php echo $pathPrefix; ?>berita_detail.php?slug=<?php echo htmlspecialchars($berita['slug']); ?>" class="news-card-link">
                            <div class="news-img" style="height: 200px; background-color: var(--light-gray); background-size: cover; background-position: center; border-top-left-radius: 10px; border-top-right-radius: 10px;
                                <?php if ($berita['gambar_banner']): ?>
                                    background-image: url('<?php echo $pathPrefix . htmlspecialchars($berita['gambar_banner']); ?>');
                                <?php else: ?>
                                    background-image: url('<?php echo $pathPrefix; ?>assets/images/placeholder_news.png'); /* Buat placeholder_news.png */
                                <?php endif; ?>
                            ">
                                <!-- Tidak perlu tag img di sini jika menggunakan background-image -->
                            </div>
                            <div class="news-content">
                                <span class="news-date"><?php echo $berita['tanggal_publikasi'] ? date('d F Y', strtotime($berita['tanggal_publikasi'])) : 'Tanggal tidak tersedia'; ?></span>
                                <h3><?php echo htmlspecialchars($berita['judul']); ?></h3>
                                <p>
                                    <?php
                                    // Ambil potongan konten untuk teaser
                                    $konten_teaser = strip_tags($berita['konten']); // Hapus tag HTML
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
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div style="text-align:center; padding: 20px; background-color: #fff; border-radius:8px;">
                <p>Belum ada berita atau kegiatan terbaru yang dipublikasikan.</p>
            </div>
        <?php endif; ?>

        <?php if ($result_berita_terbaru && $result_berita_terbaru->num_rows > 0): // Tampilkan tombol jika ada berita ?>
        <div style="text-align: center; margin-top: 40px;">
            <a href="<?php echo $pathPrefix; ?>berita_semua.php" class="btn" style="background-color: var(--dark-green);">Lihat Semua Berita & Kegiatan</a>
        </div>
        <?php endif; ?>
    </div>
</section>


<!-- Call to Action Section (New) -->
<section class="cta-section">
    <div class="cta-container">
        <h2>Siap Memulai Perjalanan Wirausaha Anda?</h2>
        <p>Bergabunglah dengan Koperasi Mahasiswa UIN Raden Intan Lampung dan kembangkan potensi kewirausahaan Anda bersama kami.</p>
        <div class="cta-buttons">
            <a href="<?php echo $pathPrefix; ?>pendaftaran_anggota.php" class="btn btn-white">Daftar Sekarang</a>
            <a href="<?php echo $pathPrefix; ?>konsultasi_usaha.php" class="btn">Konsultasi Gratis</a>
        </div>
    </div>
</section>

<!-- Contact Section (Enhanced) -->
<section class="contact reveal" id="kontak">
    <div class="container">
        <div class="section-title">
            <h2>Hubungi Kami</h2>
            <p>Jangan ragu untuk menghubungi kami jika Anda memiliki pertanyaan atau ingin informasi lebih lanjut</p>
        </div>
        <div class="contact-container">
            <div class="contact-info reveal">
                <h3>Informasi Kontak</h3>
                <p>Kami siap membantu Anda dengan segala pertanyaan terkait keanggotaan, program, dan layanan Koperasi Mahasiswa UIN Raden Intan Lampung.</p>
                
                <div class="info-item">
                    <i class="fas fa-map-marker-alt"></i>
                    <div class="info-content">
                        <h4>Lokasi</h4>
                        <p>Gedung UKM Lt. 1, Kampus UIN Raden Intan Lampung<br>Jl. Letkol H. Endro Suratmin, Sukarame, Bandar Lampung</p>
                    </div>
                </div>
                
                <div class="info-item">
                    <i class="fas fa-envelope"></i>
                    <div class="info-content">
                        <h4>Email</h4>
                        <p>kopma@radenintan.ac.id</p>
                    </div>
                </div>
                
                <div class="info-item">
                    <i class="fas fa-phone-alt"></i>
                    <div class="info-content">
                        <h4>Telepon</h4>
                        <p>+62 721 123456</p>
                    </div>
                </div>
                
                <div class="info-item">
                    <i class="fas fa-clock"></i>
                    <div class="info-content">
                        <h4>Jam Operasional</h4>
                        <p>Senin - Jumat: 08.00 - 16.00 WIB</p>
                    </div>
                </div>
                
                <div class="social-links">
                    <a href="#" class="social-link"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" class="social-link"><i class="fab fa-instagram"></i></a>
                    <a href="#" class="social-link"><i class="fab fa-twitter"></i></a>
                    <a href="#" class="social-link"><i class="fab fa-youtube"></i></a>
                </div>
            </div>
            
            <div class="contact-form reveal">
                <form id="contactForm" action="<?php echo $pathPrefix; ?>proses_kontak.php" method="post">
                    <div class="form-group">
                        <label for="name">Nama Lengkap</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    <div class="form-group">
                        <label for="subject">Subjek</label>
                        <input type="text" class="form-control" id="subject" name="subject" required>
                    </div>
                    <div class="form-group">
                        <label for="message">Pesan</label>
                        <textarea class="form-control" id="message" name="message" required></textarea>
                    </div>
                    <button type="submit" class="btn submit-btn">Kirim Pesan</button>
                </form>
            </div>
        </div>
    </div>
</section>

<!-- JavaScript for Enhanced Features -->
<script>
    // Slideshow functionality
    document.addEventListener('DOMContentLoaded', function() {
        // Hero Slideshow
        const slides = document.querySelectorAll('.hero-slideshow .slide');
        let currentSlide = 0;
        
        function nextSlide() {
            slides[currentSlide].classList.remove('active');
            currentSlide = (currentSlide + 1) % slides.length;
            slides[currentSlide].classList.add('active');
        }
        
        // Change slide every 5 seconds
        setInterval(nextSlide, 5000);
        
        // Testimonial Slider
        const testimonialSlider = document.querySelector('.testimonial-slider');
        const dots = document.querySelectorAll('.testimonial-dots .dot');
        let currentTestimonial = 0;
        
        dots.forEach((dot, index) => {
            dot.addEventListener('click', () => {
                goToTestimonial(index);
            });
        });
        
        function goToTestimonial(index) {
            currentTestimonial = index;
            testimonialSlider.style.transform = `translateX(-${currentTestimonial * 100}%)`;
            
            // Update active dot
            dots.forEach(d => d.classList.remove('active'));
            dots[currentTestimonial].classList.add('active');
        }
        
        // Auto slide testimonials
        setInterval(() => {
            currentTestimonial = (currentTestimonial + 1) % dots.length;
            goToTestimonial(currentTestimonial);
        }, 6000);
        
        // Scroll reveal animation
        function revealOnScroll() {
            const reveals = document.querySelectorAll('.reveal');
            
            reveals.forEach(element => {
                const windowHeight = window.innerHeight;
                const elementTop = element.getBoundingClientRect().top;
                const elementVisible = 150;
                
                if (elementTop < windowHeight - elementVisible) {
                    element.classList.add('active');
                }
            });
        }
        
        window.addEventListener('scroll', revealOnScroll);
        revealOnScroll(); // Initial check on load
        
        // Smooth scrolling for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                e.preventDefault();
                
                const targetId = this.getAttribute('href');
                if (targetId === '#') return;
                
                const targetElement = document.querySelector(targetId);
                if (targetElement) {
                    window.scrollTo({
                        top: targetElement.offsetTop - 80, // Adjust for header height
                        behavior: 'smooth'
                    });
                }
            });
        });
    });

    // Add AJAX form submission for contact form
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('contactForm');
        if (form) {
            form.addEventListener('submit', function(e) {
                e.preventDefault();

                const formData = new FormData(form);
                const submitButton = form.querySelector('button[type="submit"]');
                submitButton.disabled = true;
                submitButton.textContent = 'Mengirim...';

                fetch(form.action, {
                    method: 'POST',
                    body: formData,
                })
                .then(response => {
                    return response.json();
                })
                .then(data => {
                    submitButton.disabled = false;
                    submitButton.textContent = 'Kirim Pesan';

                    // Remove existing message if any
                    const existingMsg = document.querySelector('.contact-message');
                    if (existingMsg) {
                        existingMsg.remove();
                    }

                    // Create message div
                    const msgDiv = document.createElement('div');
                    msgDiv.className = 'contact-message ' + (data.status === 'success' ? 'success' : 'error');
                    msgDiv.style.maxWidth = '600px';
                    msgDiv.style.margin = '20px auto';
                    msgDiv.style.padding = '15px';
                    msgDiv.style.borderRadius = '8px';
                    msgDiv.style.textAlign = 'center';
                    msgDiv.textContent = data.message;

                    // Insert message above the form
                    form.parentNode.insertBefore(msgDiv, form);

                    if (data.status === 'success') {
                        form.reset();
                        // Optionally keep the form visible
                    }
                })
                .catch(error => {
                    submitButton.disabled = false;
                    submitButton.textContent = 'Kirim Pesan';

                    const existingMsg = document.querySelector('.contact-message');
                    if (existingMsg) {
                        existingMsg.remove();
                    }

                    const msgDiv = document.createElement('div');
                    msgDiv.className = 'contact-message error';
                    msgDiv.style.maxWidth = '600px';
                    msgDiv.style.margin = '20px auto';
                    msgDiv.style.padding = '15px';
                    msgDiv.style.borderRadius = '8px';
                    msgDiv.style.textAlign = 'center';
                    msgDiv.textContent = 'Pesan berhasil terkirim !';

                    form.parentNode.insertBefore(msgDiv, form);
                });
            });
        }
    });
</script>

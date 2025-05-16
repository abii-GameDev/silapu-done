<?php
session_start();
$pathPrefix = '../'; // Path dari user/ ke root
$pageTitle = "Ajukan Usaha Baru";

// Cek jika pengguna belum login
if (!isset($_SESSION['user_id'])) {
    $_SESSION['login_messages'] = [['type' => 'error', 'text' => 'Anda harus login untuk mengajukan usaha.']];
    header("Location: " . $pathPrefix . "auth/login.php?redirect=user/ajukan_usaha.php"); // Arahkan kembali ke sini setelah login
    exit;
}

$user_id = $_SESSION['user_id'];

// Include koneksi database
require $pathPrefix . 'config/db.php';

// --- PENGECEKAN STATUS KEANGGOTAAN AKTIF ---
$is_anggota_koperasi_aktif = false;
// Prioritaskan cek berdasarkan user_id jika ada di tabel data_anggota, fallback ke email jika tidak
$stmt_cek_anggota = $conn->prepare("SELECT id FROM data_anggota WHERE user_id = ? AND status_keanggotaan = 'Aktif'");
if ($stmt_cek_anggota) {
    $stmt_cek_anggota->bind_param("i", $user_id);
    $stmt_cek_anggota->execute();
    $result_cek_anggota = $stmt_cek_anggota->get_result();
    if ($result_cek_anggota->num_rows > 0) {
        $is_anggota_koperasi_aktif = true;
    }
    $stmt_cek_anggota->close();
} else {
    // Handle error prepare jika perlu, atau biarkan $is_anggota_koperasi_aktif false
    error_log("Gagal prepare statement cek anggota by user_id: " . $conn->error);
}

// Jika tidak ditemukan berdasarkan user_id, dan kita punya email di session, coba cek via email
// (Ini berguna jika pendaftaran anggota tidak selalu menyimpan user_id)
if (!$is_anggota_koperasi_aktif && isset($_SESSION['email'])) {
    $stmt_cek_email = $conn->prepare("SELECT id FROM data_anggota WHERE email = ? AND status_keanggotaan = 'Aktif'");
    if ($stmt_cek_email) {
        $stmt_cek_email->bind_param("s", $_SESSION['email']);
        $stmt_cek_email->execute();
        $result_cek_email = $stmt_cek_email->get_result();
        if ($result_cek_email->num_rows > 0) {
            $is_anggota_koperasi_aktif = true;
        }
        $stmt_cek_email->close();
    } else {
        error_log("Gagal prepare statement cek anggota by email: " . $conn->error);
    }
}
// --- AKHIR PENGECEKAN STATUS KEANGGOTAAN ---

// Include header
include $pathPrefix . 'includes/header.php';
?>

<section class="ajukan-usaha-section" style="padding: 40px 20px; background-color: var(--light-gray);">
    <div class="container">
        <h2 class="section-title" style="color: var(--dark-green); text-shadow: none; margin-bottom: 25px; text-align:left;">
            <?php echo $pageTitle; ?>
        </h2>

        <a href="<?php echo $pathPrefix; ?>user/dashboard.php" style="display: inline-block; margin-bottom: 20px; text-decoration: none; background-color: var(--dark-green); color: white; padding: 8px 15px; border-radius: 5px;">« Kembali ke Dashboard</a>


        <div style="background-color: var(--white); padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">

            <?php
            // Tampilkan pesan dari proses pengajuan (jika ada)
            if (isset($_SESSION['usaha_message'])) {
                $message = $_SESSION['usaha_message'];
                echo '<div class="message ' . htmlspecialchars($message['type']) . '" style="margin-bottom: 20px;">' . htmlspecialchars($message['text']) . '</div>';
                unset($_SESSION['usaha_message']);
            }
            ?>

            <form action="<?php echo $pathPrefix; ?>user/proses_ajukan_usaha.php" method="POST" enctype="multipart/form-data">
                <div class="form-group" style="margin-bottom: 20px;">
                    <label for="nama_usaha" style="display: block; margin-bottom: 8px; font-weight: 600;">Nama Usaha:</label>
                    <input type="text" class="form-control" id="nama_usaha" name="nama_usaha" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;">
                </div>

                <div class="form-group" style="margin-bottom: 20px;">
                    <label for="kategori_usaha" style="display: block; margin-bottom: 8px; font-weight: 600;">Kategori Usaha:</label>
                    <select class="form-control" id="kategori_usaha" name="kategori_usaha" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;">
                        <option value="">Pilih Kategori</option>
                        <option value="Makanan">Makanan</option>
                        <option value="Minuman">Minuman</option>
                        <option value="Jasa">Jasa</option>
                        <option value="Kerajinan">Kerajinan</option>
                        <option value="Fashion">Fashion</option>
                        <option value="Digital">Produk Digital</option>
                        <option value="Lainnya">Lainnya</option>
                    </select>
                </div>

                <div class="form-group" style="margin-bottom: 20px;">
                    <label for="deskripsi_usaha" style="display: block; margin-bottom: 8px; font-weight: 600;">Deskripsi Singkat Usaha:</label>
                    <textarea class="form-control" id="deskripsi_usaha" name="deskripsi_usaha" rows="5" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;"></textarea>
                </div>

                <div class="form-group" style="margin-bottom: 20px;">
                    <label for="alamat_usaha" style="display: block; margin-bottom: 8px; font-weight: 600;">Alamat Usaha (Opsional):</label>
                    <input type="text" class="form-control" id="alamat_usaha" name="alamat_usaha" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;">
                </div>

                <div class="form-group" style="margin-bottom: 20px;">
                    <label for="kontak_usaha" style="display: block; margin-bottom: 8px; font-weight: 600;">Kontak Usaha (No. HP/WA/IG, Opsional):</label>
                    <input type="text" class="form-control" id="kontak_usaha" name="kontak_usaha" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;">
                </div>

                <div class="form-group" style="margin-bottom: 20px;">
                    <label for="foto_usaha" style="display: block; margin-bottom: 8px; font-weight: 600;">Foto Produk/Logo Usaha (Opsional, max 2MB):</label>
                    <input type="file" class="form-control" id="foto_usaha" name="foto_usaha" accept="image/jpeg, image/png, image/gif" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;">
                    <small style="display:block; margin-top:5px; color:#777;">Tipe file yang diizinkan: JPG, PNG, GIF.</small>
                </div>

                <button type="submit" class="btn" style="background-color: var(--dark-green); color: white; padding: 12px 25px; border: none; border-radius: 5px; cursor: pointer; font-size: 1rem;">Ajukan Usaha</button>
            </form>
        </div>
    </div>
</section>

<?php
// Include footer
include $pathPrefix . 'includes/footer.php';
?>
<?php
session_start();
$pathPrefix = '../';
$pageTitle = "Edit Produk/Layanan";

if (!isset($_SESSION['user_id'])) {
    $_SESSION['login_messages'] = [['type' => 'error', 'text' => 'Anda harus login.']];
    header("Location: " . $pathPrefix . "auth/login.php");
    exit;
}
$user_id = $_SESSION['user_id'];

if (!isset($_GET['id']) || !isset($_GET['usaha_id'])) {
    $_SESSION['produk_message'] = ['type' => 'error', 'text' => 'Parameter tidak lengkap untuk mengedit produk.'];
    // Arahkan ke daftar usaha pengguna jika usaha_id tidak jelas
    header("Location: " . ($pathPrefix ?? '') . "user/usaha_saya.php"); 
    exit;
}
$produk_id_url = intval($_GET['id']);
$usaha_id_url = intval($_GET['usaha_id']);

require $pathPrefix . 'config/db.php';

// 1. Verifikasi usaha ini milik user & disetujui
$stmt_check_usaha = $conn->prepare("SELECT nama_usaha FROM usaha_mahasiswa WHERE id = ? AND user_id = ? AND status_pengajuan = 'Disetujui'");
if (!$stmt_check_usaha) { /* Handle error */ die("Error prepare usaha: " . $conn->error); }
$stmt_check_usaha->bind_param("ii", $usaha_id_url, $user_id);
$stmt_check_usaha->execute();
$result_check_usaha = $stmt_check_usaha->get_result();
if ($result_check_usaha->num_rows !== 1) {
    $_SESSION['produk_message'] = ['type' => 'error', 'text' => 'Akses tidak diizinkan atau usaha belum disetujui.'];
    header("Location: kelola_produk_usaha.php?usaha_id=" . $usaha_id_url);
    exit;
}
$usaha_data_for_title = $result_check_usaha->fetch_assoc();
$stmt_check_usaha->close();

// 2. Ambil detail produk yang akan diedit
$produk_detail = null;
$stmt_produk = $conn->prepare("SELECT * FROM produk_layanan WHERE id = ? AND usaha_id = ?");
if (!$stmt_produk) { /* Handle error */ die("Error prepare produk: " . $conn->error); }
$stmt_produk->bind_param("ii", $produk_id_url, $usaha_id_url); // Pastikan produk ini milik usaha yang diverifikasi
$stmt_produk->execute();
$result_produk = $stmt_produk->get_result();
if ($result_produk->num_rows === 1) {
    $produk_detail = $result_produk->fetch_assoc();
    $pageTitle = "Edit: " . htmlspecialchars($produk_detail['nama_produk_layanan']);
} else {
    $_SESSION['produk_message'] = ['type' => 'error', 'text' => 'Produk tidak ditemukan.'];
    header("Location: kelola_produk_usaha.php?usaha_id=" . $usaha_id_url);
    exit;
}
$stmt_produk->close();

include $pathPrefix . 'includes/header.php';
?>
<section class="edit-produk-section" style="padding: 40px 20px; background-color: var(--light-gray);">
    <div class="container">
        <h2 class="section-title" style="color: var(--dark-green); text-shadow: none; margin-bottom: 25px; text-align:left;">
            <?php echo $pageTitle; ?>
            <small style="display:block; font-size:0.9rem; color:#555;">Untuk Usaha: <?php echo htmlspecialchars($usaha_data_for_title['nama_usaha']); ?></small>
        </h2>
        <a href="<?php echo $pathPrefix; ?>user/kelola_produk_usaha.php?usaha_id=<?php echo $usaha_id_url; ?>" style="display: inline-block; margin-bottom: 20px; text-decoration: none; background-color: var(--dark); color: white; padding: 8px 15px; border-radius: 5px;">« Kembali ke Kelola Produk</a>

        <div style="background-color: var(--white); padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
            <?php
            if (isset($_SESSION['produk_form_message'])) {
                $message = $_SESSION['produk_form_message'];
                echo '<div class="message ' . htmlspecialchars($message['type']) . '" style="margin-bottom: 20px;">' . htmlspecialchars($message['text']) . '</div>';
                unset($_SESSION['produk_form_message']);
            }
            ?>
            <?php if ($produk_detail): ?>
            <form action="<?php echo $pathPrefix; ?>user/proses_edit_produk.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="produk_id" value="<?php echo htmlspecialchars($produk_detail['id']); ?>">
                <input type="hidden" name="usaha_id" value="<?php echo htmlspecialchars($produk_detail['usaha_id']); ?>">

                <div class="form-group" style="margin-bottom: 20px;">
                    <label for="nama_produk_layanan" style="display: block; margin-bottom: 8px; font-weight: 600;">Nama Produk/Layanan:</label>
                    <input type="text" class="form-control" id="nama_produk_layanan" name="nama_produk_layanan" required value="<?php echo htmlspecialchars($produk_detail['nama_produk_layanan']); ?>" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;">
                </div>
                <div class="form-group" style="margin-bottom: 20px;">
                    <label for="kategori_produk_layanan" style="display: block; margin-bottom: 8px; font-weight: 600;">Kategori Produk/Layanan (Opsional):</label>
                    <input type="text" class="form-control" id="kategori_produk_layanan" name="kategori_produk_layanan" value="<?php echo htmlspecialchars($produk_detail['kategori_produk_layanan'] ?? ''); ?>" placeholder="Misal: Makanan Ringan, Jasa Ketik" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;">
                </div>
                <div class="form-group" style="margin-bottom: 20px;">
                    <label for="deskripsi_produk_layanan" style="display: block; margin-bottom: 8px; font-weight: 600;">Deskripsi Produk/Layanan:</label>
                    <textarea class="form-control" id="deskripsi_produk_layanan" name="deskripsi_produk_layanan" rows="4" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;"><?php echo htmlspecialchars($produk_detail['deskripsi_produk_layanan'] ?? ''); ?></textarea>
                </div>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 20px;">
                    <div class="form-group">
                        <label for="harga" style="display: block; margin-bottom: 8px; font-weight: 600;">Harga (Rp):</label>
                        <input type="number" class="form-control" id="harga" name="harga" required min="0" step="any" value="<?php echo htmlspecialchars($produk_detail['harga']); ?>" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;">
                    </div>
                    <div class="form-group">
                        <label for="satuan" style="display: block; margin-bottom: 8px; font-weight: 600;">Satuan:</label>
                        <input type="text" class="form-control" id="satuan" name="satuan" required value="<?php echo htmlspecialchars($produk_detail['satuan']); ?>" placeholder="pcs, kg, jam" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;">
                    </div>
                    <div class="form-group">
                        <label for="stok" style="display: block; margin-bottom: 8px; font-weight: 600;">Stok (Kosongkan jika Jasa/Tak Terbatas):</label>
                        <input type="number" class="form-control" id="stok" name="stok" min="0" value="<?php echo htmlspecialchars($produk_detail['stok'] ?? ''); ?>" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;">
                    </div>
                </div>
                <div class="form-group" style="margin-bottom: 20px;">
                    <label for="foto_produk_layanan" style="display: block; margin-bottom: 8px; font-weight: 600;">Ganti Foto Produk/Layanan (Opsional, max 2MB):</label>
                    <?php if ($produk_detail['foto_produk_layanan']): ?>
                        <p style="margin-bottom: 10px;">Foto saat ini: <br>
                            <img src="<?php echo $pathPrefix . htmlspecialchars($produk_detail['foto_produk_layanan']); ?>" alt="Foto Saat Ini" style="max-width: 150px; max-height: 150px; border-radius: 5px; border: 1px solid #ddd; margin-top:5px;">
                            <br><input type="checkbox" name="hapus_foto_lama" value="1" id="hapus_foto_lama"> <label for="hapus_foto_lama" style="font-weight:normal; font-size:0.9rem;">Hapus foto saat ini (jika mengupload baru atau ingin menghapus)</label>
                        </p>
                    <?php endif; ?>
                    <input type="file" class="form-control" id="foto_produk_layanan" name="foto_produk_layanan" accept="image/jpeg, image/png, image/gif" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;">
                    <small style="display:block; margin-top:5px; color:#777;">Kosongkan jika tidak ingin mengganti foto. Tipe file yang diizinkan: JPG, PNG, GIF.</small>
                </div>
                <div class="form-group" style="margin-bottom: 20px;">
                   <label style="display: block; margin-bottom: 8px; font-weight: 600;">Status Ketersediaan:</label>
                   <select name="is_tersedia" class="form-control" style="width: 100%; max-width:200px; padding: 10px; border: 1px solid #ddd; border-radius: 5px;">
                       <option value="1" <?php echo ($produk_detail['is_tersedia'] == 1) ? 'selected' : ''; ?>>Tersedia</option>
                       <option value="0" <?php echo ($produk_detail['is_tersedia'] == 0) ? 'selected' : ''; ?>>Tidak Tersedia</option>
                   </select>
                </div>
                <button type="submit" name="submit_edit_produk" class="btn" style="background-color: var(--dark-green); color: white; padding: 12px 25px; border: none; border-radius: 5px; cursor: pointer; font-size: 1rem;">Simpan Perubahan</button>
            </form>
            <?php endif; ?>
        </div>
    </div>
</section>
<?php
if(isset($conn)) $conn->close();
include $pathPrefix . 'includes/footer.php';
?>
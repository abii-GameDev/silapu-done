 <?php
 session_start();
 $pathPrefix = '../'; // Path dari user/ ke root
 $pageTitle = "Tambah Produk/Layanan Baru";

 // Cek jika pengguna belum login
 if (!isset($_SESSION['user_id'])) {
     $_SESSION['login_messages'] = [['type' => 'error', 'text' => 'Anda harus login untuk mengakses halaman ini.']];
     header("Location: " . $pathPrefix . "auth/login.php");
     exit;
 }

 $user_id = $_SESSION['user_id'];

 // Wajib ada parameter usaha_id
 if (!isset($_GET['usaha_id'])) {
     $_SESSION['usaha_message'] = ['type' => 'error', 'text' => 'ID Usaha tidak valid untuk menambahkan produk.'];
     header("Location: usaha_saya.php");
     exit;
 }
 $usaha_id_url = intval($_GET['usaha_id']);

 // Include koneksi database
 require $pathPrefix . 'config/db.php';

 // Verifikasi usaha ini milik user yang login dan statusnya disetujui
 $stmt_check_usaha = $conn->prepare("SELECT nama_usaha FROM usaha_mahasiswa WHERE id = ? AND user_id = ? AND status_pengajuan = 'Disetujui'");
 if (!$stmt_check_usaha) {
     die("Error preparing statement: " . $conn->error); // Sebaiknya log error, bukan die() di produksi
 }
 $stmt_check_usaha->bind_param("ii", $usaha_id_url, $user_id);
 $stmt_check_usaha->execute();
 $result_check_usaha = $stmt_check_usaha->get_result();
 if ($result_check_usaha->num_rows !== 1) {
     $_SESSION['usaha_message'] = ['type' => 'error', 'text' => 'Usaha tidak ditemukan, bukan milik Anda, atau belum disetujui.'];
     header("Location: usaha_saya.php");
     exit;
 }
 $usaha_data_for_title = $result_check_usaha->fetch_assoc();
 $pageTitle = "Tambah Produk untuk: " . htmlspecialchars($usaha_data_for_title['nama_usaha']);
 $stmt_check_usaha->close();

 // Include header
 include $pathPrefix . 'includes/header.php';
 ?>

 <section class="tambah-produk-section" style="padding: 40px 20px; background-color: var(--light-gray);">
     <div class="container">
         <h2 class="section-title" style="color: var(--dark-green); text-shadow: none; margin-bottom: 25px; text-align:left;">
             <?php echo $pageTitle; ?>
         </h2>

         <a href="<?php echo $pathPrefix; ?>user/kelola_produk_usaha.php?usaha_id=<?php echo $usaha_id_url; ?>" style="display: inline-block; margin-bottom: 20px; text-decoration: none; background-color: var(--dark); color: white; padding: 8px 15px; border-radius: 5px;">« Kembali ke Kelola Produk</a>

         <div style="background-color: var(--white); padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
             
             <?php
             // Tampilkan pesan error dari proses sebelumnya (jika ada dari redirect)
             if (isset($_SESSION['produk_form_message'])) {
                 $message = $_SESSION['produk_form_message'];
                 echo '<div class="message ' . htmlspecialchars($message['type']) . '" style="margin-bottom: 20px;">' . htmlspecialchars($message['text']) . '</div>';
                 unset($_SESSION['produk_form_message']);
             }
             ?>

             <form action="<?php echo $pathPrefix; ?>user/proses_tambah_produk.php" method="POST" enctype="multipart/form-data">
                 <input type="hidden" name="usaha_id" value="<?php echo $usaha_id_url; ?>">

                 <div class="form-group" style="margin-bottom: 20px;">
                     <label for="nama_produk_layanan" style="display: block; margin-bottom: 8px; font-weight: 600;">Nama Produk/Layanan:</label>
                     <input type="text" class="form-control" id="nama_produk_layanan" name="nama_produk_layanan" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;">
                 </div>

                 <div class="form-group" style="margin-bottom: 20px;">
                     <label for="kategori_produk_layanan" style="display: block; margin-bottom: 8px; font-weight: 600;">Kategori Produk/Layanan (Opsional):</label>
                     <input type="text" class="form-control" id="kategori_produk_layanan" name="kategori_produk_layanan" placeholder="Misal: Makanan Ringan, Jasa Ketik, Aksesoris" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;">
                 </div>

                 <div class="form-group" style="margin-bottom: 20px;">
                     <label for="deskripsi_produk_layanan" style="display: block; margin-bottom: 8px; font-weight: 600;">Deskripsi Produk/Layanan:</label>
                     <textarea class="form-control" id="deskripsi_produk_layanan" name="deskripsi_produk_layanan" rows="4" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;"></textarea>
                 </div>

                 <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 20px;">
                     <div class="form-group">
                         <label for="harga" style="display: block; margin-bottom: 8px; font-weight: 600;">Harga (Rp):</label>
                         <input type="number" class="form-control" id="harga" name="harga" required min="0" step="100" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;">
                     </div>
                     <div class="form-group">
                         <label for="satuan" style="display: block; margin-bottom: 8px; font-weight: 600;">Satuan:</label>
                         <input type="text" class="form-control" id="satuan" name="satuan" value="pcs" required placeholder="pcs, kg, jam, porsi" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;">
                     </div>
                     <div class="form-group">
                         <label for="stok" style="display: block; margin-bottom: 8px; font-weight: 600;">Stok (Kosongkan jika Jasa/Tak Terbatas):</label>
                         <input type="number" class="form-control" id="stok" name="stok" min="0" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;">
                     </div>
                 </div>
                 
                 <div class="form-group" style="margin-bottom: 20px;">
                     <label for="foto_produk_layanan" style="display: block; margin-bottom: 8px; font-weight: 600;">Foto Produk/Layanan (Opsional, max 2MB):</label>
                     <input type="file" class="form-control" id="foto_produk_layanan" name="foto_produk_layanan" accept="image/jpeg, image/png, image/gif" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;">
                     <small style="display:block; margin-top:5px; color:#777;">Tipe file yang diizinkan: JPG, PNG, GIF.</small>
                 </div>
                 
                 <div class="form-group" style="margin-bottom: 20px;">
                    <label style="display: block; margin-bottom: 8px; font-weight: 600;">Status Ketersediaan:</label>
                    <select name="is_tersedia" class="form-control" style="width: 100%; max-width:200px; padding: 10px; border: 1px solid #ddd; border-radius: 5px;">
                        <option value="1" selected>Tersedia</option>
                        <option value="0">Tidak Tersedia</option>
                    </select>
                 </div>


                 <button type="submit" name="submit_tambah_produk" class="btn" style="background-color: var(--dark-green); color: white; padding: 12px 25px; border: none; border-radius: 5px; cursor: pointer; font-size: 1rem;">Tambah Produk/Layanan</button>
             </form>
         </div>
     </div>
 </section>

 <?php
 if(isset($conn)) $conn->close();
 // Include footer
 include $pathPrefix . 'includes/footer.php';
 ?>

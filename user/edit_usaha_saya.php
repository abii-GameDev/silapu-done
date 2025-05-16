 <?php
 session_start();
 $pathPrefix = '../'; // Path dari user/ ke root
 $pageTitle = "Edit Pengajuan Usaha";

 // Cek jika pengguna belum login
 if (!isset($_SESSION['user_id'])) {
     $_SESSION['login_messages'] = [['type' => 'error', 'text' => 'Anda harus login untuk mengakses halaman ini.']];
     header("Location: " . $pathPrefix . "auth/login.php");
     exit;
 }

 $user_id = $_SESSION['user_id'];

 // Include koneksi database
 require $pathPrefix . 'config/db.php';

 $usaha_detail = null;
 $error_message = '';

 if (!isset($_GET['id'])) {
     $_SESSION['usaha_message'] = ['type' => 'error', 'text' => 'ID usaha tidak disediakan untuk diedit.'];
     header("Location: usaha_saya.php"); // Redirect ke daftar usaha jika ID tidak ada
     exit;
 }

 $usaha_id = intval($_GET['id']);

 // Ambil detail usaha yang akan diedit, pastikan milik user yang login
 // dan statusnya memungkinkan untuk diedit
 $stmt = $conn->prepare("SELECT * FROM usaha_mahasiswa WHERE id = ? AND user_id = ?");
 if (!$stmt) {
     $error_message = "Database error (prepare select): " . $conn->error;
 } else {
     $stmt->bind_param("ii", $usaha_id, $user_id);
     $stmt->execute();
     $result = $stmt->get_result();
     if ($result->num_rows === 1) {
         $usaha_detail = $result->fetch_assoc();
         // Cek apakah status memungkinkan untuk diedit
         $editable_statuses = ['Menunggu Persetujuan', 'Ditolak'];
         if (!in_array($usaha_detail['status_pengajuan'], $editable_statuses)) {
             $_SESSION['usaha_message'] = ['type' => 'error', 'text' => 'Usaha dengan status "' . htmlspecialchars($usaha_detail['status_pengajuan']) . '" tidak dapat diedit saat ini.'];
             header("Location: detail_usaha_saya.php?id=" . $usaha_id);
             exit;
         }
         $pageTitle = "Edit Usaha: " . htmlspecialchars($usaha_detail['nama_usaha']);
     } else {
         $_SESSION['usaha_message'] = ['type' => 'error', 'text' => 'Usaha tidak ditemukan atau Anda tidak berhak mengeditnya.'];
         header("Location: usaha_saya.php");
         exit;
     }
     $stmt->close();
 }

 // Include header
 include $pathPrefix . 'includes/header.php';
 ?>

 <section class="edit-usaha-section" style="padding: 40px 20px; background-color: var(--light-gray);">
     <div class="container">
         <h2 class="section-title" style="color: var(--dark-green); text-shadow: none; margin-bottom: 25px; text-align:left;">
             <?php echo $pageTitle; ?>
         </h2>
         
         <a href="<?php echo $pathPrefix; ?>user/detail_usaha_saya.php?id=<?php echo $usaha_id; ?>" style="display: inline-block; margin-bottom: 20px; text-decoration: none; background-color: var(--dark); color: white; padding: 8px 15px; border-radius: 5px;">« Batal & Kembali ke Detail</a>

         <div style="background-color: var(--white); padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
             
             <?php
             if ($error_message) {
                 echo '<div class="message error" style="margin-bottom: 20px;">' . htmlspecialchars($error_message) . '</div>';
             }
             // Tampilkan pesan dari proses update (jika ada dari redirect setelah gagal)
             if (isset($_SESSION['usaha_edit_message'])) {
                 $message = $_SESSION['usaha_edit_message'];
                 echo '<div class="message ' . htmlspecialchars($message['type']) . '" style="margin-bottom: 20px;">' . htmlspecialchars($message['text']) . '</div>';
                 unset($_SESSION['usaha_edit_message']);
             }
             ?>

             <?php if ($usaha_detail): ?>
             <form action="<?php echo $pathPrefix; ?>user/proses_edit_usaha.php" method="POST" enctype="multipart/form-data">
                 <input type="hidden" name="usaha_id" value="<?php echo htmlspecialchars($usaha_detail['id']); ?>">

                 <div class="form-group" style="margin-bottom: 20px;">
                     <label for="nama_usaha" style="display: block; margin-bottom: 8px; font-weight: 600;">Nama Usaha:</label>
                     <input type="text" class="form-control" id="nama_usaha" name="nama_usaha" required value="<?php echo htmlspecialchars($usaha_detail['nama_usaha']); ?>" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;">
                 </div>

                 <div class="form-group" style="margin-bottom: 20px;">
                     <label for="kategori_usaha" style="display: block; margin-bottom: 8px; font-weight: 600;">Kategori Usaha:</label>
                     <select class="form-control" id="kategori_usaha" name="kategori_usaha" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;">
                         <option value="">Pilih Kategori</option>
                         <?php 
                         $kategori_options = ['Makanan', 'Minuman', 'Jasa', 'Kerajinan', 'Fashion', 'Digital', 'Lainnya'];
                         foreach ($kategori_options as $kategori) {
                             $selected = ($usaha_detail['kategori_usaha'] == $kategori) ? 'selected' : '';
                             echo "<option value=\"$kategori\" $selected>" . htmlspecialchars($kategori) . "</option>";
                         }
                         ?>
                     </select>
                 </div>

                 <div class="form-group" style="margin-bottom: 20px;">
                     <label for="deskripsi_usaha" style="display: block; margin-bottom: 8px; font-weight: 600;">Deskripsi Singkat Usaha:</label>
                     <textarea class="form-control" id="deskripsi_usaha" name="deskripsi_usaha" rows="5" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;"><?php echo htmlspecialchars($usaha_detail['deskripsi_usaha']); ?></textarea>
                 </div>

                 <div class="form-group" style="margin-bottom: 20px;">
                     <label for="alamat_usaha" style="display: block; margin-bottom: 8px; font-weight: 600;">Alamat Usaha (Opsional):</label>
                     <input type="text" class="form-control" id="alamat_usaha" name="alamat_usaha" value="<?php echo htmlspecialchars($usaha_detail['alamat_usaha'] ?? ''); ?>" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;">
                 </div>

                 <div class="form-group" style="margin-bottom: 20px;">
                     <label for="kontak_usaha" style="display: block; margin-bottom: 8px; font-weight: 600;">Kontak Usaha (No. HP/WA/IG, Opsional):</label>
                     <input type="text" class="form-control" id="kontak_usaha" name="kontak_usaha" value="<?php echo htmlspecialchars($usaha_detail['kontak_usaha'] ?? ''); ?>" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;">
                 </div>
                 
                 <div class="form-group" style="margin-bottom: 20px;">
                     <label for="foto_usaha" style="display: block; margin-bottom: 8px; font-weight: 600;">Ganti Foto Produk/Logo Usaha (Opsional, max 2MB):</label>
                     <?php if ($usaha_detail['foto_produk_atau_logo']): ?>
                         <p style="margin-bottom: 10px;">Foto saat ini: <br>
                             <img src="<?php echo $pathPrefix . htmlspecialchars($usaha_detail['foto_produk_atau_logo']); ?>" alt="Foto Saat Ini" style="max-width: 150px; max-height: 150px; border-radius: 5px; border: 1px solid #ddd; margin-top:5px;">
                             <br><input type="checkbox" name="hapus_foto_lama" value="1" id="hapus_foto_lama"> <label for="hapus_foto_lama">Hapus foto saat ini (jika mengupload baru atau ingin menghapus)</label>
                         </p>
                     <?php endif; ?>
                     <input type="file" class="form-control" id="foto_usaha" name="foto_usaha" accept="image/jpeg, image/png, image/gif" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;">
                     <small style="display:block; margin-top:5px; color:#777;">Kosongkan jika tidak ingin mengganti foto. Tipe file yang diizinkan: JPG, PNG, GIF.</small>
                 </div>

                 <button type="submit" name="submit_edit_usaha" class="btn" style="background-color: var(--dark-green); color: white; padding: 12px 25px; border: none; border-radius: 5px; cursor: pointer; font-size: 1rem;">Simpan Perubahan</button>
             </form>
             <?php endif; ?>
         </div>
     </div>
 </section>

 <?php
 if(isset($conn)) $conn->close();
 // Include footer
 include $pathPrefix . 'includes/footer.php';
 ?>

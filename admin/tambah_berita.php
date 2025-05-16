 <?php
 session_start();
 $pathPrefix = '../'; // Path dari admin/ ke root
 $adminPathPrefix = ''; // Path di dalam folder admin itu sendiri
 $pageTitle = "Tambah Berita/Kegiatan Baru";

 // Cek jika pengguna belum login atau bukan admin
 if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
     $_SESSION['login_messages'] = [['type' => 'error', 'text' => 'Akses ditolak. Anda tidak memiliki izin.']];
     header("Location: " . $pathPrefix . "auth/login.php");
     exit;
 }
 $penulis_id = $_SESSION['user_id']; // ID admin yang sedang login sebagai penulis

 // Include header admin
 include $pathPrefix . 'includes/admin_header.php';
 ?>

 <h2 class="section-title">
     <?php echo $pageTitle; ?>
 </h2>
 
 <a href="<?php echo $adminPathPrefix; ?>manajemen_berita.php" class="btn-admin btn-admin-secondary" style="margin-bottom: 20px;">
     « Kembali ke Manajemen Berita
 </a>

 <div style="background-color: var(--white); padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
     <?php
     // Tampilkan pesan error dari proses sebelumnya (jika ada dari redirect)
     if (isset($_SESSION['berita_form_message'])) {
         $message = $_SESSION['berita_form_message'];
         echo '<div class="message ' . htmlspecialchars($message['type']) . '" style="margin-bottom: 20px;">' . htmlspecialchars($message['text']) . '</div>';
         unset($_SESSION['berita_form_message']);
     }
     ?>
     <form action="<?php echo $adminPathPrefix; ?>proses_berita.php" method="POST" enctype="multipart/form-data" class="admin-form">
         <input type="hidden" name="action" value="tambah">
         <input type="hidden" name="penulis_id" value="<?php echo $penulis_id; ?>">

         <div class="form-group">
             <label for="judul">Judul Berita/Kegiatan:</label>
             <input type="text" class="form-control" id="judul" name="judul" required 
                    value="<?php echo isset($_SESSION['form_data_berita']['judul']) ? htmlspecialchars($_SESSION['form_data_berita']['judul']) : ''; ?>">
         </div>

         <div class="form-group">
             <label for="slug">Slug (URL Friendly, akan digenerate otomatis jika kosong):</label>
             <input type="text" class="form-control" id="slug" name="slug" 
                    value="<?php echo isset($_SESSION['form_data_berita']['slug']) ? htmlspecialchars($_SESSION['form_data_berita']['slug']) : ''; ?>"
                    placeholder="Contoh: judul-berita-keren-banget">
             <small class="form-text">Gunakan huruf kecil, angka, dan tanda hubung (-). Biarkan kosong untuk generate otomatis dari judul.</small>
         </div>

         <div class="form-group">
             <label for="konten">Konten Lengkap:</label>
             <textarea class="form-control" id="konten" name="konten" rows="10" required><?php echo isset($_SESSION['form_data_berita']['konten']) ? htmlspecialchars($_SESSION['form_data_berita']['konten']) : ''; ?></textarea>
             <small class="form-text">Anda bisa menggunakan HTML dasar di sini. Untuk editor WYSIWYG, perlu integrasi tambahan.</small>
         </div>

         <div class="form-group">
             <label for="gambar_banner">Gambar Banner (Opsional, max 2MB):</label>
             <input type="file" class="form-control" id="gambar_banner" name="gambar_banner" accept="image/jpeg, image/png, image/gif">
             <small class="form-text">Rekomendasi rasio aspek 16:9 atau yang landscape.</small>
         </div>

         <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px;">
             <div class="form-group">
                 <label for="status">Status Publikasi:</label>
                 <select name="status" id="status" class="form-control">
                     <option value="draft" <?php echo (isset($_SESSION['form_data_berita']['status']) && $_SESSION['form_data_berita']['status'] == 'draft') ? 'selected' : ''; ?>>Draft (Simpan Dulu)</option>
                     <option value="published" <?php echo (isset($_SESSION['form_data_berita']['status']) && $_SESSION['form_data_berita']['status'] == 'published') ? 'selected' : ''; ?>>Published (Terbitkan)</option>
                 </select>
             </div>

             <div class="form-group">
                 <label for="tanggal_publikasi">Tanggal Publikasi (Opsional):</label>
                 <input type="datetime-local" class="form-control" id="tanggal_publikasi" name="tanggal_publikasi"
                        value="<?php echo isset($_SESSION['form_data_berita']['tanggal_publikasi']) ? htmlspecialchars($_SESSION['form_data_berita']['tanggal_publikasi']) : ''; ?>">
                 <small class="form-text">Jika status "Published" dan tanggal kosong, akan menggunakan tanggal saat ini.</small>
             </div>
         </div>

         <button type="submit" name="submit_berita" class="btn-admin btn-admin-primary" style="margin-top: 20px;">
             Simpan Berita/Kegiatan
         </button>
     </form>
     <?php if(isset($_SESSION['form_data_berita'])) unset($_SESSION['form_data_berita']); ?>
 </div>

 <?php
 if(isset($conn)) $conn->close(); // Tutup koneksi jika dibuka di sini (seharusnya tidak perlu jika sudah di header/footer)
 // Include footer admin
 include $pathPrefix . 'includes/admin_footer.php';
 ?>

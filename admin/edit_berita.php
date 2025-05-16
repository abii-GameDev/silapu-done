 <?php
 session_start();
 $pathPrefix = '../';
 $adminPathPrefix = '';
 $pageTitle = "Edit Berita/Kegiatan";

 if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
     $_SESSION['login_messages'] = [['type' => 'error', 'text' => 'Akses ditolak.']];
     header("Location: " . $pathPrefix . "auth/login.php");
     exit;
 }
 $current_admin_id = $_SESSION['user_id']; // ID admin yang sedang login, bisa dipakai untuk update penulis_id jika perlu

 if (!isset($_GET['id'])) {
     $_SESSION['berita_message'] = ['type' => 'error', 'text' => 'ID Berita/Kegiatan tidak ditemukan untuk diedit.'];
     header("Location: manajemen_berita.php");
     exit;
 }
 $berita_id = intval($_GET['id']);

 require $pathPrefix . 'config/db.php';

 // Ambil data berita yang akan diedit
 $berita_detail = null;
 $stmt_berita = $conn->prepare("SELECT * FROM berita_kegiatan WHERE id = ?");
 if (!$stmt_berita) die("Error prepare: " . $conn->error);
 $stmt_berita->bind_param("i", $berita_id);
 $stmt_berita->execute();
 $result_berita = $stmt_berita->get_result();
 if ($result_berita->num_rows === 1) {
     $berita_detail = $result_berita->fetch_assoc();
     $pageTitle = "Edit: " . htmlspecialchars($berita_detail['judul']);
 } else {
     $_SESSION['berita_message'] = ['type' => 'error', 'text' => 'Berita/Kegiatan tidak ditemukan.'];
     header("Location: manajemen_berita.php");
     exit;
 }
 $stmt_berita->close();

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
     if (isset($_SESSION['berita_form_message'])) {
         $message = $_SESSION['berita_form_message'];
         echo '<div class="message ' . htmlspecialchars($message['type']) . '" style="margin-bottom: 20px;">' . htmlspecialchars($message['text']) . '</div>';
         unset($_SESSION['berita_form_message']);
     }
     ?>
     <?php if ($berita_detail): ?>
     <form action="<?php echo $adminPathPrefix; ?>proses_berita.php" method="POST" enctype="multipart/form-data" class="admin-form">
         <input type="hidden" name="action" value="edit">
         <input type="hidden" name="berita_id" value="<?php echo htmlspecialchars($berita_detail['id']); ?>">
         <input type="hidden" name="penulis_id" value="<?php echo $current_admin_id; ?>"> <?php // Admin yang mengedit terakhir ?>

         <div class="form-group">
             <label for="judul">Judul Berita/Kegiatan:</label>
             <input type="text" class="form-control" id="judul" name="judul" required 
                    value="<?php echo htmlspecialchars($berita_detail['judul']); ?>">
         </div>

         <div class="form-group">
             <label for="slug">Slug (URL Friendly):</label>
             <input type="text" class="form-control" id="slug" name="slug" 
                    value="<?php echo htmlspecialchars($berita_detail['slug']); ?>"
                    placeholder="Contoh: judul-berita-keren-banget">
             <small class="form-text">Gunakan huruf kecil, angka, dan tanda hubung (-). Jika diubah, pastikan unik.</small>
         </div>

         <div class="form-group">
             <label for="konten">Konten Lengkap:</label>
             <textarea class="form-control" id="konten" name="konten" rows="10" required><?php echo htmlspecialchars($berita_detail['konten']); ?></textarea>
             <small class="form-text">Anda bisa menggunakan HTML dasar.</small>
         </div>

         <div class="form-group">
             <label for="gambar_banner">Ganti Gambar Banner (Opsional, max 2MB):</label>
             <?php if ($berita_detail['gambar_banner']): ?>
                 <p style="margin-bottom: 10px;">Gambar saat ini: <br>
                     <img src="<?php echo $pathPrefix . htmlspecialchars($berita_detail['gambar_banner']); ?>" alt="Banner Saat Ini" style="max-width: 200px; max-height: 150px; border-radius: 5px; border: 1px solid #ddd; margin-top:5px;">
                     <br><input type="checkbox" name="hapus_gambar_lama" value="1" id="hapus_gambar_lama"> <label for="hapus_gambar_lama" style="font-weight:normal; font-size:0.9rem;">Hapus gambar saat ini (jika mengupload baru atau ingin menghapus)</label>
                 </p>
             <?php endif; ?>
             <input type="file" class="form-control" id="gambar_banner" name="gambar_banner" accept="image/jpeg, image/png, image/gif">
             <small class="form-text">Kosongkan jika tidak ingin mengganti gambar. Rekomendasi rasio aspek 16:9.</small>
         </div>

         <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px;">
             <div class="form-group">
                 <label for="status">Status Publikasi:</label>
                 <select name="status" id="status" class="form-control">
                     <option value="draft" <?php echo ($berita_detail['status'] == 'draft') ? 'selected' : ''; ?>>Draft</option>
                     <option value="published" <?php echo ($berita_detail['status'] == 'published') ? 'selected' : ''; ?>>Published</option>
                     <option value="archived" <?php echo ($berita_detail['status'] == 'archived') ? 'selected' : ''; ?>>Archived</option>
                 </select>
             </div>

             <div class="form-group">
                 <label for="tanggal_publikasi">Tanggal Publikasi (Opsional):</label>
                 <input type="datetime-local" class="form-control" id="tanggal_publikasi" name="tanggal_publikasi"
                        value="<?php echo $berita_detail['tanggal_publikasi'] ? date('Y-m-d\TH:i', strtotime($berita_detail['tanggal_publikasi'])) : ''; ?>">
                 <small class="form-text">Jika status "Published" dan tanggal kosong, akan menggunakan tanggal saat ini jika belum ada, atau tanggal publikasi lama jika sudah ada.</small>
             </div>
         </div>

         <button type="submit" name="submit_berita" class="btn-admin btn-admin-primary" style="margin-top: 20px;">
             Simpan Perubahan
         </button>
     </form>
     <?php endif; ?>
 </div>

 <?php
 if(isset($conn)) $conn->close();
 include $pathPrefix . 'includes/admin_footer.php';
 ?>
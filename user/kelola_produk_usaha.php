 <?php
 session_start();
 $pathPrefix = '../'; // Path dari user/ ke root
 $pageTitle = "Kelola Produk/Layanan Usaha"; // Akan diupdate

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
 $produk_list = [];
 $error_message = '';
 $usaha_id_url = null;

 if (!isset($_GET['usaha_id'])) {
     $_SESSION['usaha_message'] = ['type' => 'error', 'text' => 'ID Usaha tidak disediakan.'];
     header("Location: usaha_saya.php"); // Redirect ke daftar usaha jika ID tidak ada
     exit;
 }
 
 $usaha_id_url = intval($_GET['usaha_id']);

 // 1. Ambil detail usaha dan pastikan milik user & disetujui
 $stmt_usaha = $conn->prepare("SELECT id, nama_usaha, status_pengajuan FROM usaha_mahasiswa WHERE id = ? AND user_id = ?");
 if (!$stmt_usaha) {
     $error_message = "Database error (prepare usaha): " . $conn->error;
 } else {
     $stmt_usaha->bind_param("ii", $usaha_id_url, $user_id);
     $stmt_usaha->execute();
     $result_usaha = $stmt_usaha->get_result();
     if ($result_usaha->num_rows === 1) {
         $usaha_detail = $result_usaha->fetch_assoc();
         if ($usaha_detail['status_pengajuan'] !== 'Disetujui') {
             $_SESSION['usaha_message'] = ['type' => 'error', 'text' => 'Usaha ini belum disetujui atau statusnya tidak memungkinkan untuk dikelola produknya.'];
             header("Location: detail_usaha_saya.php?id=" . $usaha_id_url);
             exit;
         }
         $pageTitle = "Kelola Produk: " . htmlspecialchars($usaha_detail['nama_usaha']);

         // 2. Ambil produk/layanan dari usaha ini
         $stmt_produk = $conn->prepare("SELECT * FROM produk_layanan WHERE usaha_id = ? ORDER BY nama_produk_layanan ASC");
         if (!$stmt_produk) {
             $error_message = ($error_message ? $error_message . "<br>" : "") . "Database error (prepare produk): " . $conn->error;
         } else {
             $stmt_produk->bind_param("i", $usaha_id_url);
             $stmt_produk->execute();
             $result_produk = $stmt_produk->get_result();
             while ($row = $result_produk->fetch_assoc()) {
                 $produk_list[] = $row;
             }
             $stmt_produk->close();
         }
     } else {
         $_SESSION['usaha_message'] = ['type' => 'error', 'text' => 'Usaha tidak ditemukan atau Anda tidak memiliki akses.'];
         header("Location: usaha_saya.php");
         exit;
     }
     $stmt_usaha->close();
 }
 
 // Include header
 include $pathPrefix . 'includes/header.php';
 ?>

 <section class="kelola-produk-section" style="padding: 40px 20px; background-color: var(--light-gray);">
     <div class="container">
         <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; flex-wrap:wrap;">
             <h2 class="section-title" style="color: var(--dark-green); text-shadow: none; margin-bottom: 10px; text-align:left;">
                 <?php echo $pageTitle; ?>
             </h2>
             <a href="<?php echo $pathPrefix; ?>user/tambah_produk.php?usaha_id=<?php echo $usaha_id_url; ?>" class="btn" style="background-color: var(--dark-green); color: white; text-decoration: none; padding: 10px 20px; border-radius: 5px;">+ Tambah Produk/Layanan</a>
         </div>
         
         <a href="<?php echo $pathPrefix; ?>user/detail_usaha_saya.php?id=<?php echo $usaha_id_url; ?>" style="display: inline-block; margin-bottom: 20px; text-decoration: none; background-color: var(--dark); color: white; padding: 8px 15px; border-radius: 5px;">« Kembali ke Detail Usaha</a>

         <?php
         if ($error_message) {
             echo '<div class="message error" style="margin-bottom:20px;">' . htmlspecialchars($error_message) . '</div>';
         }
         // Tampilkan pesan dari proses tambah/edit/hapus produk
         if (isset($_SESSION['produk_message'])) {
             $message = $_SESSION['produk_message'];
             echo '<div class="message ' . htmlspecialchars($message['type']) . '" style="margin-bottom:20px;">' . htmlspecialchars($message['text']) . '</div>';
             unset($_SESSION['produk_message']);
         }
         ?>

         <?php if ($usaha_detail && empty($error_message)): ?>
             <?php if (!empty($produk_list)): ?>
                 <div class="table-responsive" style="overflow-x: auto; background-color:white; padding:20px; border-radius:8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
                     <table style="width: 100%; border-collapse: collapse; margin-top: 0;">
                         <thead>
                             <tr style="background-color: var(--light-green); color: var(--dark);">
                                 <th style="padding: 12px; border: 1px solid #ddd; text-align: left; width:80px;">Foto</th>
                                 <th style="padding: 12px; border: 1px solid #ddd; text-align: left;">Nama Produk/Layanan</th>
                                 <th style="padding: 12px; border: 1px solid #ddd; text-align: right;">Harga</th>
                                 <th style="padding: 12px; border: 1px solid #ddd; text-align: center;">Stok</th>
                                 <th style="padding: 12px; border: 1px solid #ddd; text-align: center;">Tersedia</th>
                                 <th style="padding: 12px; border: 1px solid #ddd; text-align: center;">Aksi</th>
                             </tr>
                         </thead>
                         <tbody>
                             <?php foreach ($produk_list as $produk): ?>
                                 <tr>
                                     <td style="padding: 8px; border: 1px solid #ddd;">
                                         <?php if ($produk['foto_produk_layanan']): ?>
                                             <img src="<?php echo $pathPrefix . htmlspecialchars($produk['foto_produk_layanan']); ?>" alt="<?php echo htmlspecialchars($produk['nama_produk_layanan']); ?>" style="width: 60px; height: 60px; object-fit: cover; border-radius: 4px;">
                                         <?php else: ?>
                                             <img src="<?php echo $pathPrefix; ?>assets/images/placeholder_produk.png" alt="N/A" style="width: 60px; height: 60px; object-fit: contain; opacity:0.4; border:1px solid #eee; border-radius:4px;">
                                         <?php endif; ?>
                                     </td>
                                     <td style="padding: 10px; border: 1px solid #ddd;"><?php echo htmlspecialchars($produk['nama_produk_layanan']); ?></td>
                                     <td style="padding: 10px; border: 1px solid #ddd; text-align: right;">Rp <?php echo number_format($produk['harga'], 0, ',', '.'); ?></td>
                                     <td style="padding: 10px; border: 1px solid #ddd; text-align: center;"><?php echo ($produk['stok'] !== null) ? htmlspecialchars($produk['stok']) : '-'; ?></td>
                                     <td style="padding: 10px; border: 1px solid #ddd; text-align: center;">
                                         <span style="color: <?php echo $produk['is_tersedia'] ? 'var(--dark-green)' : 'var(--red)'; ?>; font-weight:bold;">
                                             <?php echo $produk['is_tersedia'] ? 'Ya' : 'Tidak'; ?>
                                         </span>
                                     </td>
                                     <td style="padding: 10px; border: 1px solid #ddd; text-align: center; white-space: nowrap;">
                                         <a href="<?php echo $pathPrefix; ?>user/edit_produk.php?id=<?php echo $produk['id']; ?>&usaha_id=<?php echo $usaha_id_url; ?>" style="text-decoration: none; color: var(--dark); background-color:var(--yellow); padding:6px 10px; border-radius:3px; margin-right:5px; font-size:0.9rem;">Edit</a>
                                         <a href="<?php echo $pathPrefix; ?>user/hapus_produk.php?id=<?php echo $produk['id']; ?>&usaha_id=<?php echo $usaha_id_url; ?>" onclick="return confirm('Anda yakin ingin menghapus produk/layanan ini?');" style="text-decoration: none; color:white; background-color:var(--red); padding:6px 10px; border-radius:3px; font-size:0.9rem;">Hapus</a>
                                     </td>
                                 </tr>
                             <?php endforeach; ?>
                         </tbody>
                     </table>
                 </div>
             <?php else: ?>
                 <div style="background-color:white; padding:25px; border-radius:8px; text-align:center; box-shadow: 0 2px 10px rgba(0,0,0,0.08);">
                     <p style="font-size: 1rem; color: var(--dark);">Anda belum menambahkan produk atau layanan untuk usaha ini.</p>
                     <img src="<?php echo $pathPrefix; ?>assets/images/design grafis.jpg" alt="Belum ada produk" style="max-width:180px; margin:15px auto; display:block; border-radius:5px;"> <!-- Ganti gambar jika perlu -->
                     <p><a href="<?php echo $pathPrefix; ?>user/tambah_produk.php?usaha_id=<?php echo $usaha_id_url; ?>" class="btn" style="background-color: var(--dark-green); color: white; text-decoration: none; padding: 10px 20px; border-radius: 5px;">+ Tambah Produk/Layanan Sekarang</a></p>
                 </div>
             <?php endif; ?>
         <?php endif; ?>
         <?php if(isset($result_produk) && $result_produk) $result_produk->free(); ?>
     </div>
 </section>

 <?php
 if(isset($conn)) $conn->close();
 // Include footer
 include $pathPrefix . 'includes/footer.php';
 ?>

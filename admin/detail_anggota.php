 <?php
 session_start();
 $pathPrefix = '../'; // Path dari admin/ ke root
 $adminPathPrefix = ''; // Path di dalam folder admin itu sendiri
 $pageTitle = "Detail Pendaftar Anggota"; // Judul akan disesuaikan nanti

 // Cek jika pengguna belum login atau bukan admin
 if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
     $_SESSION['login_messages'] = [['type' => 'error', 'text' => 'Akses ditolak. Anda tidak memiliki izin.']];
     header("Location: " . $pathPrefix . "auth/login.php");
     exit;
 }

 // Include koneksi database
 require $pathPrefix . 'config/db.php';

 $anggota_detail = null;
 $error_message = '';

 if (isset($_GET['id'])) {
     $anggota_id = intval($_GET['id']);

     $stmt = $conn->prepare("SELECT * FROM data_anggota WHERE id = ?");
     if (!$stmt) {
         $error_message = "Database error (prepare): " . $conn->error;
     } else {
         $stmt->bind_param("i", $anggota_id);
         $stmt->execute();
         $result = $stmt->get_result();
         if ($result->num_rows === 1) {
             $anggota_detail = $result->fetch_assoc();
             $pageTitle = "Detail Anggota: " . htmlspecialchars($anggota_detail['nama_lengkap']); // Update judul halaman
         } else {
             $error_message = "Data anggota tidak ditemukan.";
         }
         $stmt->close();
     }
 } else {
     $error_message = "ID anggota tidak disediakan.";
 }

 // Include header admin
 include $pathPrefix . 'includes/admin_header.php';
 ?>

 <h2 class="section-title" style="color: var(--dark-green); text-shadow: none; margin-bottom: 20px;">
     <?php echo $pageTitle; ?>
 </h2>

 <a href="<?php echo $adminPathPrefix; ?>manajemen_anggota.php" style="display: inline-block; margin-bottom: 20px; text-decoration: none; background-color: var(--dark-green); color: white; padding: 8px 15px; border-radius: 5px;">« Kembali ke Manajemen Anggota</a>

 <?php if ($error_message): ?>
     <div class="message error"><?php echo htmlspecialchars($error_message); ?></div>
 <?php elseif ($anggota_detail): ?>
     <div style="background-color: #fff; padding: 25px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
         <h3 style="color: var(--dark); margin-bottom: 20px; border-bottom: 2px solid var(--light-green); padding-bottom: 10px;">Informasi Personal</h3>
         <table style="width: 100%; border-collapse: collapse;">
             <tr>
                 <td style="padding: 10px; border-bottom: 1px solid #eee; font-weight: 600; width: 30%;">ID Anggota:</td>
                 <td style="padding: 10px; border-bottom: 1px solid #eee;"><?php echo htmlspecialchars($anggota_detail['id']); ?></td>
             </tr>
             <tr>
                 <td style="padding: 10px; border-bottom: 1px solid #eee; font-weight: 600;">Nama Lengkap:</td>
                 <td style="padding: 10px; border-bottom: 1px solid #eee;"><?php echo htmlspecialchars($anggota_detail['nama_lengkap']); ?></td>
             </tr>
             <tr>
                 <td style="padding: 10px; border-bottom: 1px solid #eee; font-weight: 600;">NIM:</td>
                 <td style="padding: 10px; border-bottom: 1px solid #eee;"><?php echo htmlspecialchars($anggota_detail['nim']); ?></td>
             </tr>
             <tr>
                 <td style="padding: 10px; border-bottom: 1px solid #eee; font-weight: 600;">Semester:</td>
                 <td style="padding: 10px; border-bottom: 1px solid #eee;"><?php echo htmlspecialchars($anggota_detail['semester']); ?></td>
             </tr>
             <tr>
                 <td style="padding: 10px; border-bottom: 1px solid #eee; font-weight: 600;">Program Studi:</td>
                 <td style="padding: 10px; border-bottom: 1px solid #eee;"><?php echo htmlspecialchars($anggota_detail['program_studi']); ?></td>
             </tr>
             <tr>
                 <td style="padding: 10px; border-bottom: 1px solid #eee; font-weight: 600;">Email:</td>
                 <td style="padding: 10px; border-bottom: 1px solid #eee;"><?php echo htmlspecialchars($anggota_detail['email']); ?></td>
             </tr>
             <tr>
                 <td style="padding: 10px; border-bottom: 1px solid #eee; font-weight: 600;">Nomor HP/WhatsApp:</td>
                 <td style="padding: 10px; border-bottom: 1px solid #eee;"><?php echo htmlspecialchars($anggota_detail['nomor_hp'] ?? '-'); ?></td>
             </tr>
         </table>

         <h3 style="color: var(--dark); margin-top: 30px; margin-bottom: 20px; border-bottom: 2px solid var(--light-green); padding-bottom: 10px;">Informasi Keanggotaan</h3>
         <table style="width: 100%; border-collapse: collapse;">
             <tr>
                 <td style="padding: 10px; border-bottom: 1px solid #eee; font-weight: 600; width: 30%;">Tanggal Pendaftaran:</td>
                 <td style="padding: 10px; border-bottom: 1px solid #eee;"><?php echo date('d F Y, H:i', strtotime($anggota_detail['tanggal_pendaftaran'])); ?></td>
             </tr>
             <tr>
                 <td style="padding: 10px; border-bottom: 1px solid #eee; font-weight: 600;">Status Keanggotaan:</td>
                 <td style="padding: 10px; border-bottom: 1px solid #eee;">
                     <span style="padding: 5px 10px; border-radius: 5px; color: white; 
                                  background-color: <?php 
                                                        if ($anggota_detail['status_keanggotaan'] == 'Aktif') echo 'var(--dark-green)';
                                                        elseif ($anggota_detail['status_keanggotaan'] == 'Ditolak') echo 'var(--red)';
                                                        else echo 'var(--yellow)'; 
                                                    ?>;">
                         <?php echo htmlspecialchars($anggota_detail['status_keanggotaan']); ?>
                     </span>
                 </td>
             </tr>
             <tr>
                 <td style="padding: 10px; border-bottom: 1px solid #eee; font-weight: 600; vertical-align: top;">Alasan Bergabung:</td>
                 <td style="padding: 10px; border-bottom: 1px solid #eee;"><?php echo nl2br(htmlspecialchars($anggota_detail['alasan_bergabung'] ?? '-')); ?></td>
             </tr>
         </table>

         <h3 style="color: var(--dark); margin-top: 30px; margin-bottom: 20px; border-bottom: 2px solid var(--light-green); padding-bottom: 10px;">Aksi Cepat</h3>
         <div style="margin-top: 20px;">
             <?php if ($anggota_detail['status_keanggotaan'] == 'Menunggu Konfirmasi'): ?>
                 <a href="<?php echo $adminPathPrefix; ?>proses_update_status_anggota.php?id=<?php echo $anggota_detail['id']; ?>&status=Aktif" onclick="return confirm('Anda yakin ingin menyetujui anggota ini?');" style="text-decoration: none; color: var(--white); background-color: var(--dark-green); padding: 10px 15px; border-radius:5px; margin-right: 10px;">Setujui Pendaftaran</a>
                 <a href="<?php echo $adminPathPrefix; ?>proses_update_status_anggota.php?id=<?php echo $anggota_detail['id']; ?>&status=Ditolak" onclick="return confirm('Anda yakin ingin menolak anggota ini?');" style="text-decoration: none; color: var(--white); background-color: var(--red); padding: 10px 15px; border-radius:5px;">Tolak Pendaftaran</a>
             <?php elseif ($anggota_detail['status_keanggotaan'] == 'Aktif'): ?>
                 <a href="<?php echo $adminPathPrefix; ?>proses_update_status_anggota.php?id=<?php echo $anggota_detail['id']; ?>&status=Nonaktif" onclick="return confirm('Anda yakin ingin menonaktifkan anggota ini?');" style="text-decoration: none; color: var(--white); background-color: var(--yellow); padding: 10px 15px; border-radius:5px;">Nonaktifkan Anggota</a>
             <?php elseif ($anggota_detail['status_keanggotaan'] == 'Nonaktif' || $anggota_detail['status_keanggotaan'] == 'Ditolak'): ?>
                 <a href="<?php echo $adminPathPrefix; ?>proses_update_status_anggota.php?id=<?php echo $anggota_detail['id']; ?>&status=Aktif" onclick="return confirm('Anda yakin ingin mengaktifkan kembali anggota ini?');" style="text-decoration: none; color: var(--white); background-color: var(--dark-green); padding: 10px 15px; border-radius:5px;">Aktifkan Kembali</a>
             <?php endif; ?>
             <!-- Tambahkan aksi lain jika perlu, misal Edit Data Anggota, Kirim Notifikasi, dll. -->
         </div>

     </div>
 <?php endif; ?>

 <?php
 $conn->close(); // Tutup koneksi database
 // Include footer admin
 include $pathPrefix . 'includes/admin_footer.php';
 ?>

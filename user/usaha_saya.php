 <?php
    session_start();
    $pathPrefix = '../'; // Path dari user/ ke root
    $pageTitle = "Usaha Saya";

    // Cek jika pengguna belum login
    if (!isset($_SESSION['user_id'])) {
        $_SESSION['login_messages'] = [['type' => 'error', 'text' => 'Anda harus login untuk mengakses halaman ini.']];
        header("Location: " . $pathPrefix . "auth/login.php");
        exit;
    }

    $user_id = $_SESSION['user_id']; // Ambil user_id dari session

    // Include koneksi database
    require $pathPrefix . 'config/db.php';

    // Ambil semua data usaha milik pengguna yang sedang login
    $stmt = $conn->prepare("SELECT id, nama_usaha, kategori_usaha, status_pengajuan, tanggal_pengajuan, catatan_admin 
                         FROM usaha_mahasiswa 
                         WHERE user_id = ? 
                         ORDER BY tanggal_pengajuan DESC");
    if (!$stmt) {
        // Handle error prepare statement
        $usaha_list_error = "Gagal menyiapkan query: " . $conn->error;
        $result_usaha = false;
    } else {
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result_usaha = $stmt->get_result();
        if (!$result_usaha) {
            $usaha_list_error = "Gagal mengeksekusi query: " . $stmt->error;
        }
        $stmt->close();
    }


    // Include header
    include $pathPrefix . 'includes/header.php';
    ?>

 <section class="usaha-saya-section" style="padding: 40px 20px; background-color: var(--light-gray);">
     <div class="container">
         <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
             <h2 class="section-title" style="color: var(--dark-green); text-shadow: none; margin-bottom: 0; text-align:left;">
                 <?php echo $pageTitle; ?>
             </h2>
             <a href="<?php echo $pathPrefix; ?>user/ajukan_usaha.php" class="btn" style="background-color: var(--dark-green); color: white; text-decoration: none; padding: 10px 20px; border-radius: 5px;">+ Ajukan Usaha Baru</a>
         </div>

         <a href="<?php echo $pathPrefix; ?>user/dashboard.php" style="display: inline-block; margin-bottom: 20px; text-decoration: none; background-color: var(--dark); color: white; padding: 8px 15px; border-radius: 5px;">« Kembali ke Dashboard</a>


         <?php
            // Tampilkan pesan dari proses pengajuan atau update (jika ada)
            if (isset($_SESSION['usaha_message'])) {
                $message = $_SESSION['usaha_message'];
                echo '<div class="message ' . htmlspecialchars($message['type']) . '" style="margin-bottom: 20px;">' . htmlspecialchars($message['text']) . '</div>';
                unset($_SESSION['usaha_message']);
            }
            if (isset($usaha_list_error)):
            ?>
             <div class="message error" style="margin-bottom: 20px;"><?php echo htmlspecialchars($usaha_list_error); ?></div>
         <?php elseif ($result_usaha && $result_usaha->num_rows > 0): ?>
             <div class="table-responsive" style="overflow-x: auto; background-color:white; padding:20px; border-radius:8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
                 <table style="width: 100%; border-collapse: collapse; margin-top: 0;">
                     <thead>
                         <tr style="background-color: var(--light-green); color: var(--dark);">
                             <th style="padding: 12px; border: 1px solid #ddd; text-align: left;">Nama Usaha</th>
                             <th style="padding: 12px; border: 1px solid #ddd; text-align: left;">Kategori</th>
                             <th style="padding: 12px; border: 1px solid #ddd; text-align: left;">Status</th>
                             <th style="padding: 12px; border: 1px solid #ddd; text-align: left;">Tgl Pengajuan</th>
                             <th style="padding: 12px; border: 1px solid #ddd; text-align: left;">Aksi</th>
                         </tr>
                     </thead>
                     <tbody>
                         <?php while ($usaha = $result_usaha->fetch_assoc()): ?>
                             <tr>
                                 <td style="padding: 10px; border: 1px solid #ddd;"><?php echo htmlspecialchars($usaha['nama_usaha']); ?></td>
                                 <td style="padding: 10px; border: 1px solid #ddd;"><?php echo htmlspecialchars($usaha['kategori_usaha']); ?></td>
                                 <td style="padding: 10px; border: 1px solid #ddd;">
                                     <span style="padding: 5px 8px; border-radius: 4px; color: white; 
                                                  background-color: <?php
                                                                    if ($usaha['status_pengajuan'] == 'Disetujui') echo 'var(--dark-green)';
                                                                    elseif ($usaha['status_pengajuan'] == 'Ditolak') echo 'var(--red)';
                                                                    else echo 'var(--yellow)'; // Menunggu Persetujuan atau Ditangguhkan
                                                                    ?>;">
                                         <?php echo htmlspecialchars($usaha['status_pengajuan']); ?>
                                     </span>
                                     <?php if ($usaha['status_pengajuan'] == 'Ditolak' && !empty($usaha['catatan_admin'])): ?>
                                         <small style="display:block; margin-top:5px; color:var(--red); cursor:help;" title="Catatan Admin: <?php echo htmlspecialchars($usaha['catatan_admin']); ?>">Ada catatan!</small>
                                     <?php endif; ?>
                                 </td>
                                 <td style="padding: 10px; border: 1px solid #ddd;"><?php echo date('d M Y', strtotime($usaha['tanggal_pengajuan'])); ?></td>
                                 <td style="padding: 10px; border: 1px solid #ddd; white-space: nowrap;">
                                     <a href="<?php echo $pathPrefix; ?>user/detail_usaha_saya.php?id=<?php echo $usaha['id']; ?>" style="text-decoration: none; color: var(--dark-green); margin-right: 5px; padding:5px 8px; border:1px solid var(--dark-green); border-radius:3px">Lihat Detail</a>
                                     <?php // Tombol Edit bisa ditambahkan di sini jika status memungkinkan 
                                        ?>
                                     <?php /* if ($usaha['status_pengajuan'] == 'Ditolak' || $usaha['status_pengajuan'] == 'Menunggu Persetujuan'): ?>
                                         <a href="<?php echo $pathPrefix; ?>user/edit_usaha_saya.php?id=<?php echo $usaha['id']; ?>" style="text-decoration: none; color: var(--dark); background-color:var(--yellow); padding:5px 8px; border-radius:3px">Edit</a>
                                     <?php endif; */ ?>
                                 </td>
                             </tr>
                         <?php endwhile; ?>
                     </tbody>
                 </table>
             </div>
         <?php else: ?>
             <div style="background-color:white; padding:20px; border-radius:8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); text-align:center;">
                 <p style="color: var(--dark); margin-bottom:15px;">Anda belum mengajukan usaha apapun.</p>
                 <img src="<?php echo $pathPrefix; ?>assets/images/konsul chibi.png" alt="Belum ada usaha" style="max-width:150px; margin-bottom:15px;"> <!-- Ganti dengan gambar yang sesuai -->
             </div>
         <?php endif; ?>
         <?php if (isset($result_usaha) && $result_usaha) $result_usaha->free(); ?>
     </div>
 </section>

 <?php
    if (isset($conn)) $conn->close(); // Tutup koneksi database
    // Include footer
    include $pathPrefix . 'includes/footer.php';
    ?>
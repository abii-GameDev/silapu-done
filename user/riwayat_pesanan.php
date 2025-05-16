 <?php
 session_start();
 $pathPrefix = '../'; // Path dari user/ ke root
 $pageTitle = "Riwayat Pesanan Saya";

 // Cek jika pengguna belum login
 if (!isset($_SESSION['user_id'])) {
     $_SESSION['login_messages'] = [['type' => 'error', 'text' => 'Anda harus login untuk mengakses halaman ini.']];
     header("Location: " . $pathPrefix . "auth/login.php");
     exit;
 }

 $user_id = $_SESSION['user_id'];

 // Include koneksi database
 require $pathPrefix . 'config/db.php';

 // Ambil semua data pesanan milik pengguna yang sedang login
 $stmt = $conn->prepare("SELECT id, nomor_pesanan, total_harga, status_pesanan, tanggal_pesanan 
                         FROM pesanan 
                         WHERE user_id = ? 
                         ORDER BY tanggal_pesanan DESC");
 if (!$stmt) {
     $pesanan_error = "Gagal menyiapkan query: " . $conn->error;
     $result_pesanan = false;
 } else {
     $stmt->bind_param("i", $user_id);
     $stmt->execute();
     $result_pesanan = $stmt->get_result();
     if (!$result_pesanan) {
         $pesanan_error = "Gagal mengeksekusi query: " . $stmt->error;
     }
     $stmt->close();
 }

 // Include header
 include $pathPrefix . 'includes/header.php';
 ?>

 <section class="riwayat-pesanan-section" style="padding: 40px 20px; background-color: var(--light-gray);">
     <div class="container">
         <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
             <h2 class="section-title" style="color: var(--dark-green); text-shadow: none; margin-bottom: 0; text-align:left;">
                 <?php echo $pageTitle; ?>
             </h2>
             <a href="<?php echo $pathPrefix; ?>marketplace.php" class="btn" style="background-color: var(--dark-green); color: white; text-decoration: none; padding: 10px 20px; border-radius: 5px;">« Belanja Lagi</a>
         </div>
         
         <a href="<?php echo $pathPrefix; ?>user/dashboard.php" style="display: inline-block; margin-bottom: 20px; text-decoration: none; background-color: var(--dark); color: white; padding: 8px 15px; border-radius: 5px;">« Kembali ke Dashboard</a>

         <?php if (isset($pesanan_error)): ?>
             <div class="message error" style="margin-bottom: 20px;"><?php echo htmlspecialchars($pesanan_error); ?></div>
         <?php elseif ($result_pesanan && $result_pesanan->num_rows > 0): ?>
             <div class="table-responsive" style="overflow-x: auto; background-color:white; padding:20px; border-radius:8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
                 <table style="width: 100%; border-collapse: collapse; margin-top: 0;">
                     <thead>
                         <tr style="background-color: var(--light-green); color: var(--dark);">
                             <th style="padding: 12px; border: 1px solid #ddd; text-align: left;">No. Pesanan</th>
                             <th style="padding: 12px; border: 1px solid #ddd; text-align: left;">Tanggal</th>
                             <th style="padding: 12px; border: 1px solid #ddd; text-align: right;">Total Harga</th>
                             <th style="padding: 12px; border: 1px solid #ddd; text-align: center;">Status</th>
                             <th style="padding: 12px; border: 1px solid #ddd; text-align: center;">Aksi</th>
                         </tr>
                     </thead>
                     <tbody>
                         <?php while($pesanan = $result_pesanan->fetch_assoc()): ?>
                             <tr>
                                 <td style="padding: 10px; border: 1px solid #ddd;"><?php echo htmlspecialchars($pesanan['nomor_pesanan']); ?></td>
                                 <td style="padding: 10px; border: 1px solid #ddd;"><?php echo date('d M Y, H:i', strtotime($pesanan['tanggal_pesanan'])); ?></td>
                                 <td style="padding: 10px; border: 1px solid #ddd; text-align: right; font-weight:bold;">Rp <?php echo number_format($pesanan['total_harga'], 0, ',', '.'); ?></td>
                                 <td style="padding: 10px; border: 1px solid #ddd; text-align: center;">
                                     <span style="padding: 5px 10px; border-radius: 4px; color: white; font-size:0.85rem;
                                                  background-color: <?php 
                                                                        // Anda bisa membuat fungsi atau array untuk mapping status ke warna
                                                                        $status_colors = [
                                                                            'Menunggu Pembayaran' => 'var(--yellow)',
                                                                            'Diproses' => '#17a2b8', // Info blue
                                                                            'Dikirim' => '#007bff', // Primary blue
                                                                            'Selesai' => 'var(--dark-green)',
                                                                            'Dibatalkan' => 'var(--red)'
                                                                        ];
                                                                        echo $status_colors[$pesanan['status_pesanan']] ?? '#6c757d'; // Default abu-abu
                                                                    ?>;">
                                         <?php echo htmlspecialchars($pesanan['status_pesanan']); ?>
                                     </span>
                                 </td>
                                 <td style="padding: 10px; border: 1px solid #ddd; text-align: center;">
                                     <a href="<?php echo $pathPrefix; ?>user/detail_pesanan_saya.php?nomor_pesanan=<?php echo urlencode($pesanan['nomor_pesanan']); ?>" style="text-decoration: none; color: var(--dark-green); padding:5px 8px; border:1px solid var(--dark-green); border-radius:3px">Lihat Detail</a>
                                     <?php // Tombol "Konfirmasi Pembayaran" bisa ditambahkan jika status 'Menunggu Pembayaran' ?>
                                 </td>
                             </tr>
                         <?php endwhile; ?>
                     </tbody>
                 </table>
             </div>
         <?php else: ?>
             <div style="background-color:white; padding:30px; border-radius:8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); text-align:center;">
                  <img src="<?php echo $pathPrefix; ?>assets/images/kopma chibi.png" alt="Belum ada pesanan" style="max-width:150px; margin-bottom:15px;">
                 <p style="color: var(--dark); margin-bottom:15px;">Anda belum memiliki riwayat pesanan.</p>
             </div>
         <?php endif; ?>
         <?php if(isset($result_pesanan) && $result_pesanan) $result_pesanan->free(); ?>
     </div>
 </section>

 <?php
 if(isset($conn)) $conn->close();
 // Include footer
 include $pathPrefix . 'includes/footer.php';
 ?>

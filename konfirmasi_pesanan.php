 <?php
session_start();
$pathPrefix = ''; // Karena file ini ada di root project
$pageTitle = "Konfirmasi Pesanan";
 // Cek jika tidak ada info pesanan sukses di session, redirect
 if (!isset($_SESSION['pesanan_sukses_id']) || !isset($_SESSION['pesanan_sukses_nomor'])) {
     header("Location: marketplace.php"); // Atau ke riwayat pesanan jika ada
     exit;
 }

 $nomor_pesanan_sukses = $_SESSION['pesanan_sukses_nomor'];
 // Hapus info dari session setelah ditampilkan agar tidak muncul lagi jika halaman di-refresh
 unset($_SESSION['pesanan_sukses_id']);
 unset($_SESSION['pesanan_sukses_nomor']);

 // Include header
 include 'includes/header.php';
 ?>

 <section class="konfirmasi-section" style="padding: 60px 20px; background-color: #f0fdf4; text-align:center;">
     <div class="container" style="max-width: 700px;">
         <img src="<?php echo $pathPrefix; ?>assets/images/success_order.png" alt="Pesanan Berhasil" style="max-width: 150px; margin-bottom: 25px;"> <!-- Buat gambar success_order.png -->
         
         <h2 style="font-size: 2.5rem; color: var(--dark-green); margin-bottom: 15px;">Pesanan Anda Berhasil Dibuat!</h2>
         <p style="font-size: 1.2rem; color: var(--dark); margin-bottom: 10px;">
             Terima kasih telah berbelanja di KOPMA UIN RIL.
         </p>
         <p style="font-size: 1.1rem; color: #555; margin-bottom: 25px;">
             Nomor pesanan Anda adalah: <strong style="color:var(--dark-green); font-size:1.3rem;"><?php echo htmlspecialchars($nomor_pesanan_sukses); ?></strong>
         </p>
         <p style="color: #666; margin-bottom: 15px;">
             Silakan lakukan pembayaran sesuai dengan metode yang Anda pilih. Informasi lebih lanjut mengenai pembayaran akan dikirimkan ke email Anda atau bisa dilihat di halaman detail pesanan Anda (jika ada).
         </p>
         <p style="color: #666; margin-bottom: 30px;">
             Untuk sekarang, pesanan dengan metode COD akan diproses langsung oleh penjual. Untuk Transfer Bank/E-Wallet, harap konfirmasi pembayaran Anda.
         </p>

         <div>
             <a href="<?php echo $pathPrefix; ?>marketplace.php" class="btn" style="background-color: var(--dark-green); color: white; text-decoration: none; padding: 12px 25px; border-radius: 5px; margin-right:10px;">Lanjut Belanja</a>
             <a href="<?php echo $pathPrefix; ?>user/riwayat_pesanan.php" class="btn" style="background-color: var(--yellow); color: var(--dark); text-decoration: none; padding: 12px 25px; border-radius: 5px;">Lihat Riwayat Pesanan</a> <!-- Halaman ini belum dibuat -->
         </div>
     </div>
 </section>

 <?php
 // Include footer
 include 'includes/footer.php';
 ?>
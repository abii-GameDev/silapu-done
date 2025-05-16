 <?php
 session_start();
 $pathPrefix = ''; // Karena file ini ada di root project
 $pageTitle = "Checkout Pesanan";

 // Cek jika pengguna belum login
 if (!isset($_SESSION['user_id'])) {
     $_SESSION['login_messages'] = [['type' => 'error', 'text' => 'Anda harus login untuk melakukan checkout.']];
     header("Location: " . $pathPrefix . "auth/login.php?redirect=checkout.php"); // Redirect kembali ke checkout setelah login
     exit;
 }

 // Cek jika keranjang kosong, redirect ke halaman keranjang atau marketplace
 if (empty($_SESSION['cart'])) {
     $_SESSION['cart_page_message'] = ['type' => 'info', 'text' => 'Keranjang Anda kosong. Silakan tambahkan produk terlebih dahulu.'];
     header("Location: cart.php");
     exit;
 }

 $cart_items = $_SESSION['cart'];
 $total_harga_checkout = 0;
 foreach ($cart_items as $item) {
     $total_harga_checkout += $item['harga'] * $item['quantity'];
 }

 // Include koneksi database (jika perlu mengambil data user untuk pre-fill)
 require 'config/db.php';
 $user_id = $_SESSION['user_id'];
 $user_data = null;
 $stmt_user = $conn->prepare("SELECT username, email FROM users WHERE id = ?");
 if ($stmt_user) {
     $stmt_user->bind_param("i", $user_id);
     $stmt_user->execute();
     $result_user = $stmt_user->get_result();
     if ($result_user->num_rows === 1) {
         $user_data = $result_user->fetch_assoc();
     }
     $stmt_user->close();
 }


 // Include header
 include 'includes/header.php';
 ?>

 <section class="checkout-section" style="padding: 40px 20px; background-color: #f9f9f9;">
     <div class="container">
         <h2 class="section-title" style="color: var(--dark-green); text-shadow: none; margin-bottom: 25px; text-align:left;">
             <?php echo $pageTitle; ?>
         </h2>

         <?php
         // Tampilkan pesan dari proses checkout sebelumnya (jika ada error)
         if (isset($_SESSION['checkout_message'])) {
             echo '<div class="message ' . htmlspecialchars($_SESSION['checkout_message']['type']) . '" style="margin-bottom:20px;">' . htmlspecialchars($_SESSION['checkout_message']['text']) . '</div>';
             unset($_SESSION['checkout_message']);
         }
         ?>
         
         <form action="proses_checkout.php" method="POST">
             <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 30px;">
                 <!-- Kolom Kiri: Informasi Pembeli & Pengiriman -->
                 <div class="checkout-form-details" style="background-color: #fff; padding: 25px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.08);">
                     <h3 style="margin-top:0; margin-bottom:20px; color:var(--dark);">Informasi Pembeli</h3>
                     <div class="form-group" style="margin-bottom:15px;">
                         <label for="nama_penerima" style="display:block; margin-bottom:5px; font-weight:600;">Nama Lengkap Penerima:</label>
                         <input type="text" name="nama_penerima" id="nama_penerima" value="<?php echo htmlspecialchars($user_data['username'] ?? ''); ?>" required class="form-control" style="width:100%; padding:10px; border:1px solid #ddd; border-radius:4px;">
                     </div>
                     <div class="form-group" style="margin-bottom:15px;">
                         <label for="telepon_penerima" style="display:block; margin-bottom:5px; font-weight:600;">Nomor Telepon Penerima:</label>
                         <input type="tel" name="telepon_penerima" id="telepon_penerima" required class="form-control" style="width:100%; padding:10px; border:1px solid #ddd; border-radius:4px;">
                     </div>
                     <div class="form-group" style="margin-bottom:15px;">
                         <label for="alamat_pengiriman" style="display:block; margin-bottom:5px; font-weight:600;">Alamat Lengkap Pengiriman:</label>
                         <textarea name="alamat_pengiriman" id="alamat_pengiriman" rows="4" required class="form-control" style="width:100%; padding:10px; border:1px solid #ddd; border-radius:4px;"></textarea>
                     </div>
                     <div class="form-group" style="margin-bottom:15px;">
                         <label for="catatan_pembeli" style="display:block; margin-bottom:5px; font-weight:600;">Catatan untuk Penjual (Opsional):</label>
                         <textarea name="catatan_pembeli" id="catatan_pembeli" rows="3" class="form-control" style="width:100%; padding:10px; border:1px solid #ddd; border-radius:4px;"></textarea>
                     </div>

                     <h3 style="margin-top:25px; margin-bottom:15px; color:var(--dark);">Metode Pembayaran</h3>
                     <div class="form-group">
                         <select name="metode_pembayaran" required class="form-control" style="width:100%; padding:10px; border:1px solid #ddd; border-radius:4px;">
                             <option value="">Pilih Metode Pembayaran</option>
                             <option value="COD">Bayar di Tempat (COD) - Khusus Area Kampus</option>
                             <option value="Transfer Bank">Transfer Bank (BNI/BRI/Mandiri - Konfirmasi Manual)</option>
                             <option value="E-Wallet">E-Wallet (OVO/GoPay/Dana - Via QRIS)</option>
                         </select>
                         <small style="display:block; margin-top:8px; color:#666;">Detail pembayaran akan diberikan setelah Anda menyelesaikan pesanan.</small>
                     </div>
                 </div>

                 <!-- Kolom Kanan: Ringkasan Pesanan -->
                 <div class="checkout-summary" style="background-color: #fff; padding: 25px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.08); position:sticky; top:100px;">
                     <h3 style="margin-top:0; margin-bottom:20px; color:var(--dark); border-bottom:1px solid #eee; padding-bottom:10px;">Ringkasan Pesanan</h3>
                     <?php foreach ($cart_items as $item_id => $item_data): ?>
                         <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px; font-size:0.9rem;">
                             <span style="max-width:180px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;"><?php echo htmlspecialchars($item_data['nama']); ?> (x<?php echo $item_data['quantity']; ?>)</span>
                             <span style="white-space:nowrap;">Rp <?php echo number_format($item_data['harga'] * $item_data['quantity'], 0, ',', '.'); ?></span>
                         </div>
                     <?php endforeach; ?>
                     <hr style="margin:15px 0;">
                     <div style="display:flex; justify-content:space-between; align-items:center; font-weight:bold; font-size:1.1rem;">
                         <span>Total Harga:</span>
                         <span style="color:var(--dark-green);">Rp <?php echo number_format($total_harga_checkout, 0, ',', '.'); ?></span>
                     </div>
                     <input type="hidden" name="total_harga_pesanan" value="<?php echo $total_harga_checkout; ?>">
                     <button type="submit" name="submit_checkout" class="btn" style="background-color: var(--dark-green); color: white; width:100%; padding: 12px; border:none; border-radius:5px; margin-top:25px; font-size:1.1rem; cursor:pointer;">Buat Pesanan & Bayar</button>
                     <p style="font-size:0.85rem; color:#777; text-align:center; margin-top:15px;">Dengan membuat pesanan, Anda menyetujui Syarat & Ketentuan kami.</p>
                 </div>
             </div>
         </form>

     </div>
 </section>

 <?php
 if(isset($conn)) $conn->close();
 // Include footer
 include 'includes/footer.php';
 ?>

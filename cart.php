 <?php
 session_start();
 $pathPrefix = ''; // Karena file ini ada di root project
 $pageTitle = "Keranjang Belanja Anda";

 // Inisialisasi keranjang jika belum ada (untuk jaga-jaga, meski cart_process juga melakukan ini)
 if (!isset($_SESSION['cart'])) {
     $_SESSION['cart'] = [];
 }
 $cart_items = $_SESSION['cart'];
 $subtotal_keranjang = 0;

 // Include header
 include 'includes/header.php';
 ?>

 <section class="cart-section" style="padding: 40px 20px; background-color: var(--light-gray);">
     <div class="container">
         <h2 class="section-title" style="color: var(--dark-green); text-shadow: none; margin-bottom: 25px; text-align:left;">
             <?php echo $pageTitle; ?>
         </h2>

         <a href="<?php echo $pathPrefix; ?>marketplace.php" style="display: inline-block; margin-bottom: 20px; text-decoration: none; background-color: var(--dark); color: white; padding: 8px 15px; border-radius: 5px;">« Lanjut Belanja</a>

         <?php
         // Tampilkan pesan dari aksi keranjang (update, remove, clear)
         if (isset($_SESSION['cart_page_message'])) {
             echo '<div class="message ' . htmlspecialchars($_SESSION['cart_page_message']['type']) . '" style="margin-bottom:20px;">' . htmlspecialchars($_SESSION['cart_page_message']['text']) . '</div>';
             unset($_SESSION['cart_page_message']);
         }
         ?>

         <?php if (!empty($cart_items)): ?>
             <div style="background-color: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.08);">
                 <div class="table-responsive" style="overflow-x: auto;">
                     <table style="width: 100%; border-collapse: collapse;">
                         <thead>
                             <tr style="background-color: var(--light-green); color: var(--dark);">
                                 <th style="padding: 12px; border-bottom: 1px solid #ddd; text-align: left;" colspan="2">Produk</th>
                                 <th style="padding: 12px; border-bottom: 1px solid #ddd; text-align: right;">Harga</th>
                                 <th style="padding: 12px; border-bottom: 1px solid #ddd; text-align: center;">Jumlah</th>
                                 <th style="padding: 12px; border-bottom: 1px solid #ddd; text-align: right;">Subtotal</th>
                                 <th style="padding: 12px; border-bottom: 1px solid #ddd; text-align: center;">Aksi</th>
                             </tr>
                         </thead>
                         <tbody>
                             <?php foreach ($cart_items as $item): ?>
                                 <?php 
                                     $item_subtotal = $item['harga'] * $item['quantity'];
                                     $subtotal_keranjang += $item_subtotal;
                                 ?>
                                 <tr>
                                     <td style="padding: 15px 10px; border-bottom: 1px solid #eee; width:80px;">
                                         <?php if ($item['foto']): ?>
                                             <img src="<?php echo htmlspecialchars($item['foto']); ?>" alt="<?php echo htmlspecialchars($item['nama']); ?>" style="width: 70px; height: 70px; object-fit: cover; border-radius: 5px;">
                                         <?php else: ?>
                                             <img src="<?php echo $pathPrefix; ?>assets/images/placeholder_produk.png" alt="Placeholder" style="width: 70px; height: 70px; object-fit: cover; border-radius: 5px; opacity:0.5;">
                                         <?php endif; ?>
                                     </td>
                                     <td style="padding: 15px 10px; border-bottom: 1px solid #eee; vertical-align:top;">
                                         <a href="detail_produk_layanan.php?usaha_id=<?php /* Anda perlu cara untuk mendapatkan usaha_id dari produk_id jika mau link ini */ echo ''; ?>&produk_id=<?php echo $item['id']; ?>" style="text-decoration:none; color:var(--dark-green); font-weight:600;"><?php echo htmlspecialchars($item['nama']); ?></a>
                                         <?php if ($item['stok_tersedia'] !== null && $item['quantity'] > $item['stok_tersedia']): ?>
                                            <p style="color:var(--red); font-size:0.85rem; margin-top:5px;">Stok tidak cukup (tersisa <?php echo $item['stok_tersedia']; ?>). Harap kurangi jumlah.</p>
                                         <?php endif; ?>
                                     </td>
                                     <td style="padding: 15px 10px; border-bottom: 1px solid #eee; text-align: right; white-space:nowrap;">Rp <?php echo number_format($item['harga'], 0, ',', '.'); ?></td>
                                     <td style="padding: 15px 10px; border-bottom: 1px solid #eee; text-align: center;">
                                         <form action="cart_process.php" method="POST" style="display: inline-flex; align-items:center;">
                                             <input type="hidden" name="action" value="update">
                                             <input type="hidden" name="produk_id" value="<?php echo $item['id']; ?>">
                                             <input type="number" name="quantity" value="<?php echo $item['quantity']; ?>" min="0" <?php if ($item['stok_tersedia'] !== null) echo 'max="'.$item['stok_tersedia'].'"'; ?> style="width: 60px; padding: 5px; text-align: center; border:1px solid #ccc; border-radius:3px; margin-right:5px;">
                                             <button type="submit" style="padding: 5px 8px; background-color:var(--yellow); color:var(--dark); border:none; border-radius:3px; cursor:pointer; font-size:0.8rem;">Update</button>
                                         </form>
                                     </td>
                                     <td style="padding: 15px 10px; border-bottom: 1px solid #eee; text-align: right; white-space:nowrap; font-weight:bold;">Rp <?php echo number_format($item_subtotal, 0, ',', '.'); ?></td>
                                     <td style="padding: 15px 10px; border-bottom: 1px solid #eee; text-align: center;">
                                         <form action="cart_process.php" method="POST" style="display: inline;">
                                             <input type="hidden" name="action" value="remove">
                                             <input type="hidden" name="produk_id" value="<?php echo $item['id']; ?>">
                                             <button type="submit" onclick="return confirm('Yakin ingin menghapus item ini dari keranjang?');" style="background:none; border:none; color:var(--red); cursor:pointer; font-size:1.2rem;" title="Hapus Item">×</button>
                                         </form>
                                     </td>
                                 </tr>
                             <?php endforeach; ?>
                         </tbody>
                     </table>
                 </div>

                 <div style="margin-top: 25px; text-align: right; padding-right:10px;">
                     <a href="cart_process.php?action=clear" onclick="return confirm('Anda yakin ingin mengosongkan keranjang?');" style="text-decoration: none; color: var(--red); margin-right: 20px; font-size:0.9rem;">Kosongkan Keranjang</a>
                     <h3 style="font-size: 1.5rem; color: var(--dark); margin-bottom: 15px;">
                         Total Belanja: <span style="color: var(--dark-green); font-weight:bold;">Rp <?php echo number_format($subtotal_keranjang, 0, ',', '.'); ?></span>
                     </h3>
                     <a href="<?php echo $pathPrefix; ?>checkout.php" class="btn" style="background-color: var(--dark-green); color: white; text-decoration: none; padding: 12px 25px; border-radius: 5px; font-size:1.1rem;">Lanjutkan ke Pembayaran »</a>
                 </div>
             </div>
         <?php else: ?>
             <div style="background-color: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.08); text-align: center;">
                 <img src="<?php echo $pathPrefix; ?>assets/images/cart_empty.png" alt="Keranjang Kosong" style="max-width: 150px; margin-bottom: 20px;"> <!-- Buat gambar cart_empty.png -->
                 <p style="font-size: 1.2rem; color: var(--dark); margin-bottom:10px;">Keranjang belanja Anda masih kosong.</p>
                 <p style="color: #666;">Yuk, mulai belanja di marketplace kami!</p>
             </div>
         <?php endif; ?>
     </div>
 </section>

 <?php
 // Include footer
 include 'includes/footer.php';
 ?>

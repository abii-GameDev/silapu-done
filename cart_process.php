 <?php
 session_start();
 require 'config/db.php'; // Diperlukan untuk mengambil detail produk

 // Inisialisasi keranjang jika belum ada di session
 if (!isset($_SESSION['cart'])) {
     $_SESSION['cart'] = []; // Array untuk menyimpan item keranjang
 }

 if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action'])) {
     $action = $_POST['action'];
     $produk_id = isset($_POST['produk_id']) ? intval($_POST['produk_id']) : null;

     switch ($action) {
         case 'add':
             if ($produk_id && isset($_POST['quantity'])) {
                 $quantity = intval($_POST['quantity']);
                 if ($quantity > 0) {
                     // Cek apakah produk sudah ada di keranjang
                     if (isset($_SESSION['cart'][$produk_id])) {
                         // Jika sudah ada, tambahkan quantity-nya
                         $_SESSION['cart'][$produk_id]['quantity'] += $quantity;
                         // Di sini bisa ditambahkan pengecekan stok jika perlu
                     } else {
                         // Jika belum ada, ambil detail produk dari DB dan tambahkan ke keranjang
                         $stmt = $conn->prepare("SELECT id, nama_produk_layanan, harga, foto_produk_layanan, stok FROM produk_layanan WHERE id = ? AND is_tersedia = TRUE");
                         if ($stmt) {
                             $stmt->bind_param("i", $produk_id);
                             $stmt->execute();
                             $result = $stmt->get_result();
                             if ($product_data = $result->fetch_assoc()) {
                                 // Validasi stok sebelum menambahkan ke keranjang
                                 if ($product_data['stok'] !== null && $quantity > $product_data['stok']) {
                                     $_SESSION['cart_message'] = ['type' => 'error', 'text' => 'Stok produk "' . htmlspecialchars($product_data['nama_produk_layanan']) . '" tidak mencukupi (tersisa ' . $product_data['stok'] . ').'];
                                 } else {
                                     $_SESSION['cart'][$produk_id] = [
                                         'id' => $product_data['id'],
                                         'nama' => $product_data['nama_produk_layanan'],
                                         'harga' => $product_data['harga'],
                                         'foto' => $product_data['foto_produk_layanan'],
                                         'quantity' => $quantity,
                                         'stok_tersedia' => $product_data['stok'] // Simpan info stok
                                     ];
                                     $_SESSION['cart_message'] = ['type' => 'success', 'text' => '"' . htmlspecialchars($product_data['nama_produk_layanan']) . '" berhasil ditambahkan ke keranjang.'];
                                 }
                             } else {
                                 $_SESSION['cart_message'] = ['type' => 'error', 'text' => 'Produk tidak ditemukan atau tidak tersedia.'];
                             }
                             $stmt->close();
                         } else {
                             $_SESSION['cart_message'] = ['type' => 'error', 'text' => 'Database error.'];
                         }
                     }
                 } else {
                     $_SESSION['cart_message'] = ['type' => 'error', 'text' => 'Jumlah tidak valid.'];
                 }
             } else {
                 $_SESSION['cart_message'] = ['type' => 'error', 'text' => 'Informasi produk tidak lengkap.'];
             }
             // Redirect kembali ke halaman produk detail atau marketplace
             // Lebih baik redirect ke halaman tempat user menekan tombol tambah
             $redirect_url = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'marketplace.php';
             header("Location: " . $redirect_url);
             exit;
             break;

         case 'update':
             if ($produk_id && isset($_POST['quantity'])) {
                 $quantity = intval($_POST['quantity']);
                 if ($quantity > 0) {
                     if (isset($_SESSION['cart'][$produk_id])) {
                         // Validasi stok sebelum update
                         $stok_tersedia = $_SESSION['cart'][$produk_id]['stok_tersedia'];
                         if ($stok_tersedia !== null && $quantity > $stok_tersedia) {
                             $_SESSION['cart_page_message'] = ['type' => 'error', 'text' => 'Stok produk "' . htmlspecialchars($_SESSION['cart'][$produk_id]['nama']) . '" tidak mencukupi (tersisa ' . $stok_tersedia . '). Jumlah tidak diubah.'];
                         } else {
                             $_SESSION['cart'][$produk_id]['quantity'] = $quantity;
                             $_SESSION['cart_page_message'] = ['type' => 'success', 'text' => 'Jumlah produk berhasil diperbarui.'];
                         }
                     }
                 } else {
                     // Jika quantity 0 atau kurang, hapus item
                     unset($_SESSION['cart'][$produk_id]);
                     $_SESSION['cart_page_message'] = ['type' => 'success', 'text' => 'Produk berhasil dihapus dari keranjang.'];
                 }
             }
             header("Location: cart.php"); // Redirect kembali ke halaman keranjang
             exit;
             break;

         case 'remove':
             if ($produk_id && isset($_SESSION['cart'][$produk_id])) {
                 unset($_SESSION['cart'][$produk_id]);
                 $_SESSION['cart_page_message'] = ['type' => 'success', 'text' => 'Produk berhasil dihapus dari keranjang.'];
             }
             header("Location: cart.php"); // Redirect kembali ke halaman keranjang
             exit;
             break;
        
         case 'clear':
             $_SESSION['cart'] = [];
             $_SESSION['cart_page_message'] = ['type' => 'success', 'text' => 'Keranjang berhasil dikosongkan.'];
             header("Location: cart.php");
             exit;
             break;

         default:
             $_SESSION['cart_message'] = ['type' => 'error', 'text' => 'Aksi tidak dikenal.'];
             header("Location: marketplace.php"); // Redirect default
             exit;
     }
 } else {
     // Jika bukan POST request atau tidak ada aksi, redirect
     header("Location: marketplace.php");
     exit;
 }

 if (isset($conn)) $conn->close();
 ?>
 
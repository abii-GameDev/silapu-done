 <?php
    session_start();
    require 'config/db.php'; // Koneksi ke database

    // Pastikan pengguna sudah login
    if (!isset($_SESSION['user_id'])) {
        $_SESSION['login_messages'] = [['type' => 'error', 'text' => 'Anda harus login untuk melakukan checkout.']];
        header("Location: auth/login.php?redirect=checkout.php");
        exit;
    }

    // Pastikan keranjang tidak kosong
    if (empty($_SESSION['cart'])) {
        $_SESSION['cart_page_message'] = ['type' => 'info', 'text' => 'Keranjang Anda kosong. Tidak ada yang bisa di-checkout.'];
        header("Location: cart.php");
        exit;
    }

    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_checkout'])) {
        $user_id = $_SESSION['user_id'];
        $cart_items = $_SESSION['cart'];
        $total_harga_pesanan_form = floatval($_POST['total_harga_pesanan'] ?? 0); // Ambil dari form

        // Ambil data dari form checkout
        $nama_penerima = trim($_POST['nama_penerima'] ?? '');
        $telepon_penerima = trim($_POST['telepon_penerima'] ?? '');
        $alamat_pengiriman = trim($_POST['alamat_pengiriman'] ?? '');
        $catatan_pembeli = trim($_POST['catatan_pembeli'] ?? null);
        $metode_pembayaran = trim($_POST['metode_pembayaran'] ?? '');

        // Validasi data form checkout
        if (empty($nama_penerima) || empty($telepon_penerima) || empty($alamat_pengiriman) || empty($metode_pembayaran)) {
            $_SESSION['checkout_message'] = ['type' => 'error', 'text' => 'Harap lengkapi semua informasi pembeli, pengiriman, dan metode pembayaran.'];
            header("Location: checkout.php");
            exit;
        }

        // Hitung ulang total harga dari session cart untuk keamanan (mencegah manipulasi di sisi klien)
        $total_harga_server = 0;
        foreach ($cart_items as $item) {
            $total_harga_server += $item['harga'] * $item['quantity'];
        }

        // Bandingkan total harga dari form dengan yang dihitung dari session
        if (abs($total_harga_server - $total_harga_pesanan_form) > 0.01) { // Toleransi kecil untuk float
            $_SESSION['checkout_message'] = ['type' => 'error', 'text' => 'Terjadi masalah dengan total harga. Silakan coba lagi.'];
            header("Location: checkout.php");
            exit;
        }

        // Generate Nomor Pesanan Unik
        // Format: INV-YYYYMMDD-XXXX (XXXX adalah nomor urut atau random)
        $nomor_pesanan = "INV-" . date("Ymd") . "-" . strtoupper(substr(uniqid(), -4));


        // Mulai transaksi database
        $conn->begin_transaction();

        try {
            // 1. Simpan ke tabel 'pesanan'
            $stmt_pesanan = $conn->prepare("INSERT INTO pesanan 
                                         (user_id, nomor_pesanan, total_harga, metode_pembayaran, alamat_pengiriman, nama_penerima, telepon_penerima, catatan_pembeli, status_pesanan) 
                                         VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'Menunggu Pembayaran')");
            if (!$stmt_pesanan) {
                throw new Exception("Database error (prepare pesanan): " . $conn->error);
            }
            $stmt_pesanan->bind_param(
                "isdsssss",
                $user_id,
                $nomor_pesanan,
                $total_harga_server,
                $metode_pembayaran,
                $alamat_pengiriman,
                $nama_penerima,
                $telepon_penerima,
                $catatan_pembeli
            );
            $stmt_pesanan->execute();
            $pesanan_id = $stmt_pesanan->insert_id; // Dapatkan ID pesanan yang baru saja dibuat
            $stmt_pesanan->close();

            if (!$pesanan_id) {
                throw new Exception("Gagal mendapatkan ID pesanan.");
            }

            // 2. Simpan setiap item di keranjang ke tabel 'detail_pesanan' dan update stok
            $stmt_detail = $conn->prepare("INSERT INTO detail_pesanan 
                                       (pesanan_id, produk_id, nama_produk, harga_produk, jumlah, subtotal_item) 
                                       VALUES (?, ?, ?, ?, ?, ?)");
            $stmt_update_stok = $conn->prepare("UPDATE produk_layanan SET stok = stok - ? WHERE id = ? AND stok >= ?");

            if (!$stmt_detail || !$stmt_update_stok) {
                throw new Exception("Database error (prepare detail/stok): " . $conn->error);
            }

            foreach ($cart_items as $produk_id_cart => $item) {
                $subtotal_item = $item['harga'] * $item['quantity'];

                $stmt_detail->bind_param(
                    "iisdis",
                    $pesanan_id,
                    $item['id'], // produk_id
                    $item['nama'],
                    $item['harga'],
                    $item['quantity'],
                    $subtotal_item
                );
                $stmt_detail->execute();

                // Update stok jika produk memiliki manajemen stok
                if ($item['stok_tersedia'] !== null) {
                    $stmt_update_stok->bind_param("iii", $item['quantity'], $item['id'], $item['quantity']);
                    $stmt_update_stok->execute();
                    if ($stmt_update_stok->affected_rows === 0) {
                        // Stok tidak cukup atau produk tidak ditemukan saat update, rollback!
                        throw new Exception("Stok produk " . htmlspecialchars($item['nama']) . " tidak mencukupi saat proses checkout.");
                    }
                }
            }
            $stmt_detail->close();
            $stmt_update_stok->close();

            // 3. Jika semua berhasil, commit transaksi
            $conn->commit();

            // 4. Kosongkan keranjang belanja
            $_SESSION['cart'] = [];

            // 5. Set pesan sukses dan redirect ke halaman konfirmasi atau riwayat pesanan
            $_SESSION['pesanan_sukses_id'] = $pesanan_id;
            $_SESSION['pesanan_sukses_nomor'] = $nomor_pesanan;
            header("Location: konfirmasi_pesanan.php"); // Buat halaman ini
            exit;
        } catch (Exception $e) {
            $conn->rollback(); // Batalkan semua query jika ada error
            $_SESSION['checkout_message'] = ['type' => 'error', 'text' => 'Terjadi kesalahan saat memproses pesanan: ' . $e->getMessage()];
            header("Location: checkout.php");
            exit;
        }
    } else {
        // Jika bukan POST request atau tidak ada submit_checkout, redirect
        header("Location: checkout.php");
        exit;
    }

    if (isset($conn)) $conn->close();
    ?>

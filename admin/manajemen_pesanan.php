 <?php
 session_start();
 $pathPrefix = '../'; // Path dari admin/ ke root
 $adminPathPrefix = ''; // Path di dalam folder admin itu sendiri
 $pageTitle = "Manajemen Pesanan";

 // Cek jika pengguna belum login atau bukan admin
 if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
     $_SESSION['login_messages'] = [['type' => 'error', 'text' => 'Akses ditolak. Anda tidak memiliki izin.']];
     header("Location: " . $pathPrefix . "auth/login.php");
     exit;
 }

 // Include koneksi database
 require $pathPrefix . 'config/db.php';

 // Ambil semua data pesanan, join dengan tabel users untuk mendapatkan nama pemesan
 $sql_pesanan = "SELECT p.*, u.username AS nama_pemesan 
                 FROM pesanan p
                 JOIN users u ON p.user_id = u.id 
                 ORDER BY p.tanggal_pesanan DESC";
 // Jika user_id di tabel pesanan bisa NULL (karena ON DELETE SET NULL)
 // $sql_pesanan = "SELECT p.*, IFNULL(u.username, '[User Dihapus]') AS nama_pemesan 
 //                FROM pesanan p
 //                LEFT JOIN users u ON p.user_id = u.id 
 //                ORDER BY p.tanggal_pesanan DESC";

 $result_pesanan = $conn->query($sql_pesanan);

include $pathPrefix . 'includes/admin_header.php';
?>

<h2 class="section-title">
    <?php echo $pageTitle; ?>
</h2>

<?php
if (isset($_SESSION['admin_pesanan_message'])) { /* ... tampilkan pesan ... */ }
?>

<?php if ($result_pesanan && $result_pesanan->num_rows > 0): ?>
    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>No. Pesanan</th>
                    <th>Pemesan</th>
                    <th>Tanggal</th>
                    <th style="text-align: right;">Total</th>
                    <th style="text-align: center;">Status</th>
                    <th style="text-align: center;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php while($pesanan = $result_pesanan->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($pesanan['nomor_pesanan']); ?></td>
                        <td><?php echo htmlspecialchars($pesanan['nama_pemesan']); ?></td>
                        <td><?php echo date('d M Y, H:i', strtotime($pesanan['tanggal_pesanan'])); ?></td>
                        <td style="text-align: right; font-weight:bold;">Rp <?php echo number_format($pesanan['total_harga'], 0, ',', '.'); ?></td>
                        <td style="text-align: center;">
                            <span class="status-badge <?php 
                                $status_pesanan_class = 'status-menunggu'; // Default
                                if ($pesanan['status_pesanan'] == 'Selesai') $status_pesanan_class = 'status-selesai';
                                elseif ($pesanan['status_pesanan'] == 'Dibatalkan') $status_pesanan_class = 'status-dibatalkan';
                                elseif ($pesanan['status_pesanan'] == 'Dikirim') $status_pesanan_class = 'status-diproses'; // Contoh mapping warna
                                elseif ($pesanan['status_pesanan'] == 'Diproses') $status_pesanan_class = 'status-diproses';
                                elseif ($pesanan['status_pesanan'] == 'Pembayaran Dikonfirmasi') $status_pesanan_class = 'status-aktif'; // Contoh mapping warna
                                echo $status_pesanan_class;
                            ?>">
                                <?php echo htmlspecialchars($pesanan['status_pesanan']); ?>
                            </span>
                        </td>
                        <td class="actions" style="text-align: center;">
                            <a href="<?php echo $adminPathPrefix; ?>detail_pesanan.php?nomor_pesanan=<?php echo urlencode($pesanan['nomor_pesanan']); ?>" class="btn-admin btn-admin-info btn-sm">Lihat Detail</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
 <?php elseif ($result_pesanan): ?>
     <p style="margin-top: 20px; color: var(--dark);">Belum ada pesanan yang masuk.</p>
 <?php else: ?>
     <p style="margin-top: 20px; color: var(--red);">Gagal mengambil data pesanan: <?php echo $conn->error; ?></p>
 <?php endif; ?>

 <?php
 if(isset($result_pesanan) && $result_pesanan) $result_pesanan->free();
 if(isset($conn)) $conn->close();
 // Include footer admin
 include $pathPrefix . 'includes/admin_footer.php';
 ?>

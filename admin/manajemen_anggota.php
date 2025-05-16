 <?php
 session_start();
 $pathPrefix = '../'; // Path dari admin/ ke root
 $adminPathPrefix = ''; // Path di dalam folder admin itu sendiri
 $pageTitle = "Manajemen Pendaftaran Anggota"; // Judul spesifik untuk halaman ini

 // Cek jika pengguna belum login, redirect ke halaman login
 if (!isset($_SESSION['user_id'])) {
     header("Location: " . $pathPrefix . "auth/login.php");
     exit;
 }

 // Cek jika pengguna bukan admin
 if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
     $_SESSION['login_messages'] = [['type' => 'error', 'text' => 'Akses ditolak. Anda tidak memiliki izin untuk mengakses halaman ini.']];
     header("Location: " . $pathPrefix . "auth/login.php?form=login");
     exit;
 }

 // Include koneksi database
 require $pathPrefix . 'config/db.php';

 // Ambil semua data pendaftaran anggota dari database
 $sql_anggota = "SELECT id, nama_lengkap, nim, semester, program_studi, email, nomor_hp, status_keanggotaan, tanggal_pendaftaran 
                 FROM data_anggota 
                 ORDER BY tanggal_pendaftaran DESC";
 $result_anggota = $conn->query($sql_anggota);

include $pathPrefix . 'includes/admin_header.php';
?>

<h2 class="section-title">
    <?php echo $pageTitle; ?>
</h2>

<?php
if (isset($_SESSION['admin_message'])) {
    $message = $_SESSION['admin_message'];
    echo '<div class="message ' . htmlspecialchars($message['type']) . '">' . htmlspecialchars($message['text']) . '</div>';
    unset($_SESSION['admin_message']);
}
?>

<?php if ($result_anggota && $result_anggota->num_rows > 0): ?>
    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nama Lengkap</th>
                    <th>NIM</th>
                    <th>Prodi</th>
                    <th>Email</th>
                    <th>Status</th>
                    <th>Tgl Daftar</th>
                    <th style="text-align: center;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php while($anggota = $result_anggota->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($anggota['id']); ?></td>
                        <td><?php echo htmlspecialchars($anggota['nama_lengkap']); ?></td>
                        <td><?php echo htmlspecialchars($anggota['nim']); ?></td>
                        <td><?php echo htmlspecialchars($anggota['program_studi']); ?></td>
                        <td><?php echo htmlspecialchars($anggota['email']); ?></td>
                        <td>
                            <span class="status-badge <?php 
                                $status_class = 'status-menunggu'; // Default
                                if ($anggota['status_keanggotaan'] == 'Aktif') $status_class = 'status-aktif';
                                elseif ($anggota['status_keanggotaan'] == 'Ditolak') $status_class = 'status-ditolak';
                                echo $status_class;
                            ?>">
                                <?php echo htmlspecialchars($anggota['status_keanggotaan']); ?>
                            </span>
                        </td>
                        <td><?php echo date('d M Y', strtotime($anggota['tanggal_pendaftaran'])); ?></td>
                        <td class="actions" style="text-align: center;">
                            <a href="<?php echo $adminPathPrefix; ?>detail_anggota.php?id=<?php echo $anggota['id']; ?>" class="btn-admin btn-admin-info btn-sm">Detail</a>
                            <?php if ($anggota['status_keanggotaan'] == 'Menunggu Konfirmasi'): ?>
                                <a href="<?php echo $adminPathPrefix; ?>proses_update_status_anggota.php?id=<?php echo $anggota['id']; ?>&status=Aktif" onclick="return confirm('Setujui?');" class="btn-admin btn-admin-success btn-sm">Setujui</a>
                                <a href="<?php echo $adminPathPrefix; ?>proses_update_status_anggota.php?id=<?php echo $anggota['id']; ?>&status=Ditolak" onclick="return confirm('Tolak?');" class="btn-admin btn-admin-danger btn-sm">Tolak</a>
                            <?php elseif ($anggota['status_keanggotaan'] == 'Aktif'): ?>
                                <a href="<?php echo $adminPathPrefix; ?>proses_update_status_anggota.php?id=<?php echo $anggota['id']; ?>&status=Nonaktif" onclick="return confirm('Nonaktifkan?');" class="btn-admin btn-admin-warning btn-sm">Nonaktifkan</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
 <?php elseif ($result_anggota): ?>
     <p style="margin-top: 20px; color: var(--dark);">Belum ada pendaftar anggota.</p>
 <?php else: ?>
     <p style="margin-top: 20px; color: var(--red);">Gagal mengambil data pendaftar anggota: <?php echo $conn->error; ?></p>
 <?php endif; ?>

 <?php
 $conn->close(); // Tutup koneksi database
 // Include footer admin
 include $pathPrefix . 'includes/admin_footer.php';
 ?>

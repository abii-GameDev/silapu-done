 <?php
    session_start();
    $pathPrefix = '../'; // Path dari admin/ ke root
    $adminPathPrefix = ''; // Path di dalam folder admin itu sendiri
    $pageTitle = "Manajemen Pengajuan Usaha";

    // Cek jika pengguna belum login atau bukan admin
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
        $_SESSION['login_messages'] = [['type' => 'error', 'text' => 'Akses ditolak. Anda tidak memiliki izin.']];
        header("Location: " . $pathPrefix . "auth/login.php");
        exit;
    }

    // Include koneksi database
    require $pathPrefix . 'config/db.php';

    // Ambil semua data pengajuan usaha, join dengan tabel users untuk mendapatkan nama pengaju
    $sql_usaha = "SELECT um.*, u.username AS nama_pengaju 
               FROM usaha_mahasiswa um
               JOIN users u ON um.user_id = u.id
               ORDER BY um.tanggal_pengajuan DESC";
    $result_usaha = $conn->query($sql_usaha);

    include $pathPrefix . 'includes/admin_header.php';
    ?>

 <h2 class="section-title">
     <?php echo $pageTitle; ?>
 </h2>

 <?php
    if (isset($_SESSION['admin_usaha_message'])) { /* ... tampilkan pesan ... */
    }
    ?>

 <?php if ($result_usaha && $result_usaha->num_rows > 0): ?>
     <div class="table-responsive">
         <table class="data-table">
             <thead>
                 <tr>
                     <th>ID</th>
                     <th>Nama Usaha</th>
                     <th>Kategori</th>
                     <th>Pengaju</th>
                     <th>Status</th>
                     <th>Tgl Pengajuan</th>
                     <th style="text-align: center;">Aksi</th>
                 </tr>
             </thead>
             <tbody>
                 <?php while ($usaha = $result_usaha->fetch_assoc()): ?>
                     <tr>
                         <td><?php echo htmlspecialchars($usaha['id']); ?></td>
                         <td><?php echo htmlspecialchars($usaha['nama_usaha']); ?></td>
                         <td><?php echo htmlspecialchars($usaha['kategori_usaha']); ?></td>
                         <td><?php echo htmlspecialchars($usaha['nama_pengaju']); ?></td>
                         <td>
                             <span class="status-badge <?php
                                                        $status_class_usaha = 'status-menunggu'; // Default
                                                        if ($usaha['status_pengajuan'] == 'Disetujui') $status_class_usaha = 'status-disetujui';
                                                        elseif ($usaha['status_pengajuan'] == 'Ditolak') $status_class_usaha = 'status-ditolak';
                                                        elseif ($usaha['status_pengajuan'] == 'Ditangguhkan') $status_class_usaha = 'status-ditangguhkan';
                                                        echo $status_class_usaha;
                                                        ?>">
                                 <?php echo htmlspecialchars($usaha['status_pengajuan']); ?>
                             </span>
                         </td>
                         <td><?php echo date('d M Y', strtotime($usaha['tanggal_pengajuan'])); ?></td>
                         <td class="actions" style="text-align: center;">
                             <a href="<?php echo $adminPathPrefix; ?>detail_usaha.php?id=<?php echo $usaha['id']; ?>" class="btn-admin btn-admin-info btn-sm">Detail</a>
                             <?php if ($usaha['status_pengajuan'] == 'Menunggu Persetujuan'): ?>
                                 <a href="<?php echo $adminPathPrefix; ?>proses_update_status_usaha.php?id=<?php echo $usaha['id']; ?>&status=Disetujui" onclick="return confirm('Setujui?');" class="btn-admin btn-admin-success btn-sm">Setujui</a>
                                 <a href="<?php echo $adminPathPrefix; ?>proses_update_status_usaha.php?id=<?php echo $usaha['id']; ?>&status=Ditolak" onclick="return confirm('Tolak?');" class="btn-admin btn-admin-danger btn-sm">Tolak</a>
                             <?php elseif ($usaha['status_pengajuan'] == 'Disetujui'): ?>
                                 <a href="<?php echo $adminPathPrefix; ?>proses_update_status_usaha.php?id=<?php echo $usaha['id']; ?>&status=Ditangguhkan" onclick="return confirm('Tangguhkan?');" class="btn-admin btn-admin-warning btn-sm">Tangguhkan</a>
                             <?php endif; ?>
                         </td>
                     </tr>
                 <?php endwhile; ?>
             </tbody>
         </table>
     </div>
 <?php elseif ($result_usaha): ?>
     <p style="margin-top: 20px; color: var(--dark);">Belum ada pengajuan usaha dari mahasiswa.</p>
 <?php else: ?>
     <p style="margin-top: 20px; color: var(--red);">Gagal mengambil data pengajuan usaha: <?php echo $conn->error; ?></p>
 <?php endif; ?>

 <?php
    $conn->close(); // Tutup koneksi database
    // Include footer admin
    include $pathPrefix . 'includes/admin_footer.php';
    ?>
 <?php
    session_start();
    $pathPrefix = '../'; // Path dari admin/ ke root
    $adminPathPrefix = ''; // Path di dalam folder admin itu sendiri
    $pageTitle = "Manajemen Berita & Kegiatan";

    // Cek jika pengguna belum login atau bukan admin
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
        $_SESSION['login_messages'] = [['type' => 'error', 'text' => 'Akses ditolak. Anda tidak memiliki izin.']];
        header("Location: " . $pathPrefix . "auth/login.php");
        exit;
    }

    // Include koneksi database
    require $pathPrefix . 'config/db.php';

    // Ambil semua data berita/kegiatan, join dengan tabel users untuk nama penulis (jika penulis_id ada)
    $sql_berita = "SELECT bk.*, IFNULL(u.username, 'N/A') AS nama_penulis 
                FROM berita_kegiatan bk
                LEFT JOIN users u ON bk.penulis_id = u.id 
                ORDER BY bk.tanggal_dibuat DESC";
    $result_berita = $conn->query($sql_berita);

    // Include header admin
    include $pathPrefix . 'includes/admin_header.php';
    ?>

 <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
     <h2 class="section-title" style="margin-bottom: 0;">
         <?php echo $pageTitle; ?>
     </h2>
     <a href="<?php echo $adminPathPrefix; ?>tambah_berita.php" class="btn-admin btn-admin-primary">
         <i class="fas fa-plus" style="margin-right: 5px;"></i> Tambah Berita/Kegiatan
     </a>
 </div>


 <?php
    // Tampilkan pesan dari proses (jika ada)
    if (isset($_SESSION['berita_message'])) {
        $message = $_SESSION['berita_message'];
        echo '<div class="message ' . htmlspecialchars($message['type']) . '" style="margin-bottom: 20px;">' . htmlspecialchars($message['text']) . '</div>';
        unset($_SESSION['berita_message']);
    }
    ?>

 <?php if ($result_berita && $result_berita->num_rows > 0): ?>
     <div class="table-responsive">
         <table class="data-table">
             <thead>
                 <tr>
                     <th style="width:50px;">ID</th>
                     <th>Judul</th>
                     <th>Status</th>
                     <th>Penulis</th>
                     <th>Tgl Publikasi</th>
                     <th>Tgl Dibuat</th>
                     <th style="text-align: center; width: 150px;">Aksi</th>
                 </tr>
             </thead>
             <tbody>
                 <?php while ($berita = $result_berita->fetch_assoc()): ?>
                     <tr>
                         <td><?php echo htmlspecialchars($berita['id']); ?></td>
                         <td>
                             <strong><?php echo htmlspecialchars($berita['judul']); ?></strong>
                             <small style="display:block; color:#777;">Slug: <?php echo htmlspecialchars($berita['slug']); ?></small>
                         </td>
                         <td>
                             <span class="status-badge <?php
                                                        $status_class_berita = 'status-menunggu'; // Default untuk draft
                                                        if ($berita['status'] == 'published') $status_class_berita = 'status-disetujui';
                                                        elseif ($berita['status'] == 'archived') $status_class_berita = 'status-ditolak';
                                                        echo $status_class_berita;
                                                        ?>">
                                 <?php echo htmlspecialchars(ucfirst($berita['status'])); ?>
                             </span>
                         </td>
                         <td><?php echo htmlspecialchars($berita['nama_penulis']); ?></td>
                         <td><?php echo $berita['tanggal_publikasi'] ? date('d M Y, H:i', strtotime($berita['tanggal_publikasi'])) : '-'; ?></td>
                         <td><?php echo date('d M Y, H:i', strtotime($berita['tanggal_dibuat'])); ?></td>
                         <td class="actions" style="text-align: center;">
                             <a href="<?php echo $adminPathPrefix; ?>edit_berita.php?id=<?php echo $berita['id']; ?>" class="btn-admin btn-admin-warning btn-sm" title="Edit">
                                 <!-- <i class="fas fa-edit"></i> --> Edit
                             </a>
                             <a href="<?php echo $adminPathPrefix; ?>proses_berita.php?action=hapus&id=<?php echo $berita['id']; ?>"
                                 onclick="return confirm('Anda yakin ingin menghapus berita/kegiatan ini? Aksi ini tidak dapat diurungkan.');"
                                 class="btn-admin btn-admin-danger btn-sm" title="Hapus">
                                 <!-- <i class="fas fa-trash"></i> --> Hapus
                             </a>
                             <?php // Tombol untuk lihat di frontend (jika status published) 
                                ?>
                             <?php if ($berita['status'] == 'published'): ?>
                                 <a href="<?php echo $pathPrefix; ?>berita_detail.php?slug=<?php echo $berita['slug']; ?>" target="_blank" class="btn-admin btn-admin-info btn-sm" title="Lihat">
                                     <!-- <i class="fas fa-eye"></i> --> Lihat
                                 </a>
                             <?php endif; ?>
                         </td>
                     </tr>
                 <?php endwhile; ?>
             </tbody>
         </table>
     </div>
 <?php elseif ($result_berita): ?>
     <div class="message info">Belum ada berita atau kegiatan yang ditambahkan. <a href="<?php echo $adminPathPrefix; ?>tambah_berita.php">Tambah Sekarang</a>.</div>
 <?php else: ?>
     <div class="message error">Gagal mengambil data berita/kegiatan: <?php echo $conn->error; ?></div>
 <?php endif; ?>

 <?php
    if (isset($result_berita) && $result_berita) $result_berita->free();
    if (isset($conn)) $conn->close();
    // Include footer admin
    include $pathPrefix . 'includes/admin_footer.php';
    ?>
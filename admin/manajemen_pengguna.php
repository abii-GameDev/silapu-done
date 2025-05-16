<?php
session_start();
$pathPrefix = '../'; // Path dari admin/ ke root
$adminPathPrefix = ''; // Path di dalam folder admin itu sendiri
$pageTitle = "Manajemen Pengguna"; // Judul spesifik untuk halaman ini

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

// Ambil semua data pengguna dari database
$sql_users = "SELECT id, username, email, role, created_at FROM users ORDER BY created_at DESC";
$result_users = $conn->query($sql_users);

// Include header admin
include $pathPrefix . 'includes/admin_header.php';
?>

<h2 class="section-title"> <?php // Class dari admin_style.css ?>
    <?php echo $pageTitle; ?>
</h2>

<?php if (isset($_SESSION['admin_user_message'])): // Jika ada pesan feedback ?>
    <div class="message <?php echo htmlspecialchars($_SESSION['admin_user_message']['type']); ?>">
        <?php echo htmlspecialchars($_SESSION['admin_user_message']['text']); ?>
    </div>
    <?php unset($_SESSION['admin_user_message']); ?>
<?php endif; ?>


<?php if ($result_users && $result_users->num_rows > 0): ?>
    <div class="table-responsive">
        <table class="data-table"> <?php // Terapkan class .data-table ?>
            <thead>
                <tr> <?php // Style inline bisa dihapus jika sudah di .data-table thead th ?>
                    <th>ID</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Tanggal Daftar</th>
                    <th style="text-align: center;">Aksi</th> <?php // text-align bisa di CSS untuk .data-table .actions th ?>
                </tr>
            </thead>
            <tbody>
                <?php while($user = $result_users->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($user['id']); ?></td>
                        <td><?php echo htmlspecialchars($user['username']); ?></td>
                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                        <td>
                            <span class="status-badge <?php 
                                $role_class = 'status-menunggu'; // Default
                                if (strtolower($user['role']) == 'admin') $role_class = 'status-disetujui'; // Contoh mapping
                                elseif (strtolower($user['role']) == 'user') $role_class = 'status-diproses'; // Contoh mapping
                                echo $role_class;
                            ?>">
                                <?php echo htmlspecialchars(ucfirst($user['role'])); ?>
                            </span>
                        </td>
                        <td><?php echo date('d M Y, H:i', strtotime($user['created_at'])); ?></td>
                        <td class="actions" style="text-align: center;"> <?php // Terapkan class .actions dan text-align ?>
                            <a href="<?php /* echo $adminPathPrefix; */ ?>edit_pengguna.php?id=<?php echo $user['id']; ?>" class="btn-admin btn-admin-warning btn-sm">Edit</a>
                            <a href="<?php /* echo $adminPathPrefix; */ ?>hapus_pengguna.php?id=<?php echo $user['id']; ?>" onclick="return confirm('Anda yakin ingin menghapus pengguna ini? Aksi ini mungkin tidak dapat diurungkan.');" class="btn-admin btn-admin-danger btn-sm">Hapus</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
<?php elseif ($result_users): ?>
    <div class="message info">Belum ada pengguna yang terdaftar.</div>
<?php else: ?>
    <div class="message error">Gagal mengambil data pengguna: <?php echo $conn->error; ?></div>
<?php endif; ?>

<?php
// ... (PHP untuk $conn->close() dan include footer tetap sama) ...
if(isset($result_users) && $result_users) $result_users->free();
if(isset($conn)) $conn->close();
include $pathPrefix . 'includes/admin_footer.php';
?>
<?php
session_start();
$pathPrefix = '../'; // Path dari admin/ ke root
$adminPathPrefix = ''; // Path di dalam folder admin itu sendiri
$pageTitle = "Edit Pengguna"; // Judul spesifik untuk halaman ini

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

if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['admin_user_message'] = ['type' => 'error', 'text' => 'ID pengguna tidak valid.'];
    header("Location: " . $adminPathPrefix . "manajemen_pengguna.php");
    exit;
}

$user_id = intval($_GET['id']);

// Proses update data jika form disubmit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $role = trim($_POST['role']);

    // Validasi sederhana
    if (empty($username) || empty($email) || empty($role)) {
        $message = ['type' => 'error', 'text' => 'Semua field harus diisi.'];
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = ['type' => 'error', 'text' => 'Format email tidak valid.'];
    } else {
        // Update data pengguna
        $stmt = $conn->prepare("UPDATE users SET username = ?, email = ?, role = ? WHERE id = ?");
        $stmt->bind_param("sssi", $username, $email, $role, $user_id);
        if ($stmt->execute()) {
            $message = ['type' => 'success', 'text' => 'Data pengguna berhasil diperbarui.'];
        } else {
            $message = ['type' => 'error', 'text' => 'Gagal memperbarui data pengguna: ' . $conn->error];
        }
        $stmt->close();
    }
}

// Ambil data pengguna untuk ditampilkan di form
$stmt = $conn->prepare("SELECT username, email, role FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    $_SESSION['admin_user_message'] = ['type' => 'error', 'text' => 'Pengguna tidak ditemukan.'];
    header("Location: " . $adminPathPrefix . "manajemen_pengguna.php");
    exit;
}
$user = $result->fetch_assoc();
$stmt->close();

include $pathPrefix . 'includes/admin_header.php';
?>

<h2 class="section-title"><?php echo $pageTitle; ?></h2>

<?php if (isset($message)): ?>
    <div class="message <?php echo htmlspecialchars($message['type']); ?>">
        <?php echo htmlspecialchars($message['text']); ?>
    </div>
<?php endif; ?>

<div class="form-container" style="max-width: 500px; margin: 20px auto; padding: 20px; border: 1px solid #ddd; border-radius: 8px; background-color: #f9f9f9;">
<form method="post" action="">
    <div class="form-group" style="margin-bottom: 15px;">
        <label for="username" style="display: block; font-weight: bold; margin-bottom: 5px;">Username:</label>
        <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
    </div>
    <div class="form-group" style="margin-bottom: 15px;">
        <label for="email" style="display: block; font-weight: bold; margin-bottom: 5px;">Email:</label>
        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
    </div>
    <div class="form-group" style="margin-bottom: 20px;">
        <label for="role" style="display: block; font-weight: bold; margin-bottom: 5px;">Role:</label>
        <select id="role" name="role" required style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
            <option value="admin" <?php echo ($user['role'] === 'admin') ? 'selected' : ''; ?>>Admin</option>
            <option value="user" <?php echo ($user['role'] === 'user') ? 'selected' : ''; ?>>User</option>
        </select>
    </div>
    <button type="submit" class="btn-admin btn-admin-primary" style="margin-right: 10px;">Simpan Perubahan</button>
    <a href="<?php echo $adminPathPrefix; ?>manajemen_pengguna.php" class="btn-admin btn-admin-secondary">Batal</a>
</form>
</div>

<?php
include $pathPrefix . 'includes/admin_footer.php';
if(isset($conn)) $conn->close();
?>

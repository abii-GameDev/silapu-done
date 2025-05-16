 <?php
 session_start();
 require '../config/db.php'; // Sesuaikan path jika config/db.php ada di root atau level lain

 // Pastikan hanya admin yang bisa akses
 if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
     $_SESSION['login_messages'] = [['type' => 'error', 'text' => 'Akses ditolak. Anda tidak memiliki izin.']];
     header("Location: ../auth/login.php"); // Arahkan ke login jika bukan admin
     exit;
 }

 if (isset($_GET['id']) && isset($_GET['status'])) {
     $anggota_id = intval($_GET['id']);
     $new_status = $_GET['status']; // Ambil status dari GET

     // Validasi status yang diizinkan
     $allowed_statuses = ['Aktif', 'Ditolak', 'Menunggu Konfirmasi', 'Nonaktif']; // Tambahkan status lain jika perlu
     if (!in_array($new_status, $allowed_statuses)) {
         $_SESSION['admin_message'] = ['type' => 'error', 'text' => 'Status tidak valid.'];
         header("Location: manajemen_anggota.php");
         exit;
     }

     // Update status di database
     $stmt = $conn->prepare("UPDATE data_anggota SET status_keanggotaan = ? WHERE id = ?");
     if (!$stmt) {
         $_SESSION['admin_message'] = ['type' => 'error', 'text' => 'Database error (prepare): ' . $conn->error];
     } else {
         $stmt->bind_param("si", $new_status, $anggota_id);
         if ($stmt->execute()) {
             $_SESSION['admin_message'] = ['type' => 'success', 'text' => 'Status keanggotaan berhasil diperbarui menjadi ' . htmlspecialchars($new_status) . '.'];
         } else {
             $_SESSION['admin_message'] = ['type' => 'error', 'text' => 'Gagal memperbarui status: ' . $stmt->error];
         }
         $stmt->close();
     }
     $conn->close();
 } else {
     $_SESSION['admin_message'] = ['type' => 'error', 'text' => 'Parameter tidak lengkap.'];
 }

 header("Location: manajemen_anggota.php"); // Redirect kembali ke halaman manajemen anggota
 exit;
 ?>

 <?php
 session_start();
 require '../config/db.php';

 // Pastikan hanya admin yang bisa akses
 if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
     $_SESSION['login_messages'] = [['type' => 'error', 'text' => 'Akses ditolak. Anda tidak memiliki izin.']];
     header("Location: ../auth/login.php");
     exit;
 }

 if (isset($_GET['id']) && isset($_GET['status'])) {
     $usaha_id = intval($_GET['id']);
     $new_status = $_GET['status'];

     // Validasi status yang diizinkan
     $allowed_statuses = ['Disetujui', 'Ditolak', 'Menunggu Persetujuan', 'Ditangguhkan']; // Tambahkan status lain jika perlu
     if (!in_array($new_status, $allowed_statuses)) {
         $_SESSION['admin_usaha_message'] = ['type' => 'error', 'text' => 'Status tidak valid.'];
         header("Location: manajemen_usaha.php");
         exit;
     }

     // Update status di database
     // Jika status 'Ditolak', idealnya admin bisa menambahkan catatan melalui halaman detail nanti.
     // Untuk sekarang, kita hanya update status.
     $stmt = $conn->prepare("UPDATE usaha_mahasiswa SET status_pengajuan = ? WHERE id = ?");
     if (!$stmt) {
         $_SESSION['admin_usaha_message'] = ['type' => 'error', 'text' => 'Database error (prepare): ' . $conn->error];
     } else {
         $stmt->bind_param("si", $new_status, $usaha_id);
         if ($stmt->execute()) {
             $_SESSION['admin_usaha_message'] = ['type' => 'success', 'text' => 'Status pengajuan usaha berhasil diperbarui menjadi ' . htmlspecialchars($new_status) . '.'];
             // Di sini bisa ditambahkan logika notifikasi ke mahasiswa jika diperlukan
         } else {
             $_SESSION['admin_usaha_message'] = ['type' => 'error', 'text' => 'Gagal memperbarui status: ' . $stmt->error];
         }
         $stmt->close();
     }
     $conn->close();
 } else {
     $_SESSION['admin_usaha_message'] = ['type' => 'error', 'text' => 'Parameter tidak lengkap.'];
 }

 header("Location: manajemen_usaha.php"); // Redirect kembali ke halaman manajemen usaha
 exit;
 ?>

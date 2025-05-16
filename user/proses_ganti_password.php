 <?php
 session_start();
 require '../config/db.php';

 if (!isset($_SESSION['user_id'])) {
     header("Location: ../auth/login.php");
     exit;
 }
 $user_id = $_SESSION['user_id'];

 if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_ganti_password'])) {
     $password_lama = $_POST['password_lama'];
     $password_baru = $_POST['password_baru'];
     $konfirmasi_password_baru = $_POST['konfirmasi_password_baru'];

     // Validasi dasar
     if (empty($password_lama) || empty($password_baru) || empty($konfirmasi_password_baru)) {
         $_SESSION['profil_message'] = ['type' => 'error', 'text' => 'Semua field password wajib diisi.'];
         header("Location: profil_saya.php");
         exit;
     }
     if (strlen($password_baru) < 6) {
         $_SESSION['profil_message'] = ['type' => 'error', 'text' => 'Password baru minimal 6 karakter.'];
         header("Location: profil_saya.php");
         exit;
     }
     if ($password_baru !== $konfirmasi_password_baru) {
         $_SESSION['profil_message'] = ['type' => 'error', 'text' => 'Password baru dan konfirmasi password tidak cocok.'];
         header("Location: profil_saya.php");
         exit;
     }

     // Ambil password hash saat ini dari database
     $stmt_get_pass = $conn->prepare("SELECT password FROM users WHERE id = ?");
     if (!$stmt_get_pass) { /* Handle error */ $_SESSION['profil_message'] = ['type'=>'error','text'=>'DB Error (get pass)']; header("Location: profil_saya.php"); exit; }
     $stmt_get_pass->bind_param("i", $user_id);
     $stmt_get_pass->execute();
     $result_pass = $stmt_get_pass->get_result();
     if ($result_pass->num_rows !== 1) {
         $_SESSION['profil_message'] = ['type' => 'error', 'text' => 'Pengguna tidak ditemukan.'];
         header("Location: profil_saya.php"); exit;
     }
     $current_user_data = $result_pass->fetch_assoc();
     $current_password_hash = $current_user_data['password'];
     $stmt_get_pass->close();

     // Verifikasi password lama
     if (password_verify($password_lama, $current_password_hash)) {
         // Password lama benar, hash password baru
         $password_baru_hashed = password_hash($password_baru, PASSWORD_DEFAULT);

         // Update password baru di database
         $stmt_update_pass = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
         if (!$stmt_update_pass) { /* Handle error */ $_SESSION['profil_message'] = ['type'=>'error','text'=>'DB Error (update pass)']; header("Location: profil_saya.php"); exit;}
         $stmt_update_pass->bind_param("si", $password_baru_hashed, $user_id);
         if ($stmt_update_pass->execute()) {
             $_SESSION['profil_message'] = ['type' => 'success', 'text' => 'Password berhasil diubah.'];
             // Opsional: Logout pengguna setelah ganti password agar login ulang
             // session_destroy();
             // header("Location: ../auth/login.php?message=Password berhasil diubah, silakan login kembali.");
             // exit;
         } else {
             $_SESSION['profil_message'] = ['type' => 'error', 'text' => 'Gagal mengubah password: ' . $stmt_update_pass->error];
         }
         $stmt_update_pass->close();
     } else {
         $_SESSION['profil_message'] = ['type' => 'error', 'text' => 'Password lama yang Anda masukkan salah.'];
     }
     $conn->close();
     header("Location: profil_saya.php");
     exit;

 } else {
     header("Location: profil_saya.php");
     exit;
 }
 ?>

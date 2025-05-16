 <?php
 session_start();
 require '../config/db.php'; // Sesuaikan path jika config/db.php ada di root

 // Pastikan pengguna sudah login
 if (!isset($_SESSION['user_id'])) {
     $_SESSION['login_messages'] = [['type' => 'error', 'text' => 'Anda harus login untuk melakukan aksi ini.']];
     header("Location: ../auth/login.php");
     exit;
 }

 $user_id = $_SESSION['user_id']; // Ambil user_id dari session

 if ($_SERVER["REQUEST_METHOD"] == "POST") {
     // Ambil data dari form
     $nama_usaha = trim($_POST['nama_usaha'] ?? '');
     $kategori_usaha = trim($_POST['kategori_usaha'] ?? '');
     $deskripsi_usaha = trim($_POST['deskripsi_usaha'] ?? '');
     $alamat_usaha = trim($_POST['alamat_usaha'] ?? null); // Opsional
     $kontak_usaha = trim($_POST['kontak_usaha'] ?? null); // Opsional
     
     $foto_path_db = null; // Path foto yang akan disimpan ke DB

     // Validasi dasar
     if (empty($nama_usaha) || empty($kategori_usaha) || empty($deskripsi_usaha)) {
         $_SESSION['usaha_message'] = ['type' => 'error', 'text' => 'Nama usaha, kategori, dan deskripsi wajib diisi.'];
         header("Location: ajukan_usaha.php");
         exit;
     }

     // --- Proses Upload File (Jika ada foto yang diupload) ---
     if (isset($_FILES['foto_usaha']) && $_FILES['foto_usaha']['error'] == UPLOAD_ERR_OK) {
         $upload_dir = '../uploads/usaha_mahasiswa/'; // Buat folder ini di root project
         if (!is_dir($upload_dir)) {
             mkdir($upload_dir, 0755, true); // Buat direktori jika belum ada
         }

         $file_tmp_name = $_FILES['foto_usaha']['tmp_name'];
         $file_name = basename($_FILES['foto_usaha']['name']);
         $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
         $allowed_ext = ['jpg', 'jpeg', 'png', 'gif'];

         if (in_array($file_ext, $allowed_ext)) {
             if ($_FILES['foto_usaha']['size'] <= 2000000) { // Max 2MB
                 // Buat nama file unik untuk menghindari konflik
                 $new_file_name = uniqid('usaha_', true) . '.' . $file_ext;
                 $destination = $upload_dir . $new_file_name;

                 if (move_uploaded_file($file_tmp_name, $destination)) {
                     $foto_path_db = 'uploads/usaha_mahasiswa/' . $new_file_name; // Path relatif dari root project
                 } else {
                     $_SESSION['usaha_message'] = ['type' => 'error', 'text' => 'Gagal memindahkan file yang diupload.'];
                     header("Location: ajukan_usaha.php");
                     exit;
                 }
             } else {
                 $_SESSION['usaha_message'] = ['type' => 'error', 'text' => 'Ukuran file terlalu besar (maks 2MB).'];
                 header("Location: ajukan_usaha.php");
                 exit;
             }
         } else {
             $_SESSION['usaha_message'] = ['type' => 'error', 'text' => 'Tipe file tidak diizinkan (hanya JPG, PNG, GIF).'];
             header("Location: ajukan_usaha.php");
             exit;
         }
     }
     // --- Akhir Proses Upload File ---

     // Simpan data ke database
     $stmt = $conn->prepare("INSERT INTO usaha_mahasiswa 
                             (user_id, nama_usaha, kategori_usaha, deskripsi_usaha, alamat_usaha, kontak_usaha, foto_produk_atau_logo) 
                             VALUES (?, ?, ?, ?, ?, ?, ?)");
     if (!$stmt) {
         $_SESSION['usaha_message'] = ['type' => 'error', 'text' => 'Database error (prepare): ' . $conn->error];
     } else {
         // Parameter: i (integer untuk user_id), s (string untuk lainnya)
         $stmt->bind_param("issssss", 
             $user_id, 
             $nama_usaha, 
             $kategori_usaha, 
             $deskripsi_usaha, 
             $alamat_usaha, 
             $kontak_usaha,
             $foto_path_db // Ini bisa null jika tidak ada foto
         );

         if ($stmt->execute()) {
             $_SESSION['usaha_message'] = ['type' => 'success', 'text' => 'Pengajuan usaha Anda berhasil dikirim dan sedang menunggu persetujuan.'];
             // Redirect ke dashboard user atau halaman "Usaha Saya"
             header("Location: dashboard.php"); // Atau ke usaha_saya.php
             exit;
         } else {
             $_SESSION['usaha_message'] = ['type' => 'error', 'text' => 'Gagal menyimpan data usaha: ' . $stmt->error];
         }
         $stmt->close();
     }
     $conn->close();
     
     // Jika ada error sebelum redirect sukses, redirect kembali ke form
     header("Location: ajukan_usaha.php");
     exit;

 } else {
     // Jika bukan POST request, redirect
     $_SESSION['usaha_message'] = ['type' => 'error', 'text' => 'Metode request tidak valid.'];
     header("Location: ajukan_usaha.php");
     exit;
 }
 ?>

 <?php
 session_start();
 require '../config/db.php';

 // Pastikan pengguna sudah login
 if (!isset($_SESSION['user_id'])) {
     $_SESSION['login_messages'] = [['type' => 'error', 'text' => 'Anda harus login untuk melakukan aksi ini.']];
     header("Location: ../auth/login.php");
     exit;
 }

 $user_id = $_SESSION['user_id'];

 if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_edit_usaha'], $_POST['usaha_id'])) {
     $usaha_id = intval($_POST['usaha_id']);

     // Ambil data dari form
     $nama_usaha = trim($_POST['nama_usaha'] ?? '');
     $kategori_usaha = trim($_POST['kategori_usaha'] ?? '');
     $deskripsi_usaha = trim($_POST['deskripsi_usaha'] ?? '');
     $alamat_usaha = trim($_POST['alamat_usaha'] ?? null);
     $kontak_usaha = trim($_POST['kontak_usaha'] ?? null);
     $hapus_foto_lama = isset($_POST['hapus_foto_lama']) ? 1 : 0;

     $foto_path_db_update = null; // Akan diisi jika ada foto baru atau foto lama dihapus

     // Validasi dasar
     if (empty($nama_usaha) || empty($kategori_usaha) || empty($deskripsi_usaha)) {
         $_SESSION['usaha_edit_message'] = ['type' => 'error', 'text' => 'Nama usaha, kategori, dan deskripsi wajib diisi.'];
         header("Location: edit_usaha_saya.php?id=" . $usaha_id);
         exit;
     }

     // --- Cek kepemilikan usaha dan status ---
     $stmt_check = $conn->prepare("SELECT foto_produk_atau_logo, status_pengajuan FROM usaha_mahasiswa WHERE id = ? AND user_id = ?");
     if (!$stmt_check) {
         $_SESSION['usaha_edit_message'] = ['type' => 'error', 'text' => 'Database error (check).'];
         header("Location: edit_usaha_saya.php?id=" . $usaha_id);
         exit;
     }
     $stmt_check->bind_param("ii", $usaha_id, $user_id);
     $stmt_check->execute();
     $result_check = $stmt_check->get_result();
     if ($result_check->num_rows !== 1) {
         $_SESSION['usaha_edit_message'] = ['type' => 'error', 'text' => 'Usaha tidak ditemukan atau Anda tidak berhak mengeditnya.'];
         header("Location: usaha_saya.php");
         exit;
     }
     $current_usaha_data = $result_check->fetch_assoc();
     $foto_lama = $current_usaha_data['foto_produk_atau_logo'];
     $status_saat_ini = $current_usaha_data['status_pengajuan'];
     $stmt_check->close();

     $editable_statuses = ['Menunggu Persetujuan', 'Ditolak'];
     if (!in_array($status_saat_ini, $editable_statuses)) {
         $_SESSION['usaha_edit_message'] = ['type' => 'error', 'text' => 'Usaha dengan status "' . htmlspecialchars($status_saat_ini) . '" tidak dapat diedit saat ini.'];
         header("Location: detail_usaha_saya.php?id=" . $usaha_id);
         exit;
     }
     // --- Akhir Cek kepemilikan dan status ---


     // --- Proses Upload File Baru (Jika ada) ---
     $new_foto_uploaded = false;
     if (isset($_FILES['foto_usaha']) && $_FILES['foto_usaha']['error'] == UPLOAD_ERR_OK) {
         $upload_dir = '../uploads/usaha_mahasiswa/';
         // (Pastikan direktori sudah ada dan writable)

         $file_tmp_name = $_FILES['foto_usaha']['tmp_name'];
         $file_name = basename($_FILES['foto_usaha']['name']);
         $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
         $allowed_ext = ['jpg', 'jpeg', 'png', 'gif'];

         if (in_array($file_ext, $allowed_ext)) {
             if ($_FILES['foto_usaha']['size'] <= 2000000) { // Max 2MB
                 $new_file_name = uniqid('usaha_', true) . '.' . $file_ext;
                 $destination = $upload_dir . $new_file_name;

                 if (move_uploaded_file($file_tmp_name, $destination)) {
                     $foto_path_db_update = 'uploads/usaha_mahasiswa/' . $new_file_name;
                     $new_foto_uploaded = true;
                     // Hapus foto lama jika foto baru berhasil diupload
                     if ($foto_lama && file_exists('../' . $foto_lama)) {
                         unlink('../' . $foto_lama);
                     }
                 } else {
                     $_SESSION['usaha_edit_message'] = ['type' => 'error', 'text' => 'Gagal memindahkan file baru.'];
                     header("Location: edit_usaha_saya.php?id=" . $usaha_id);
                     exit;
                 }
             } else {
                 $_SESSION['usaha_edit_message'] = ['type' => 'error', 'text' => 'Ukuran file baru terlalu besar (maks 2MB).'];
                 header("Location: edit_usaha_saya.php?id=" . $usaha_id);
                 exit;
             }
         } else {
             $_SESSION['usaha_edit_message'] = ['type' => 'error', 'text' => 'Tipe file baru tidak diizinkan.'];
             header("Location: edit_usaha_saya.php?id=" . $usaha_id);
             exit;
         }
     } elseif ($hapus_foto_lama && !$new_foto_uploaded && $foto_lama) {
         // Jika user mencentang hapus foto lama dan TIDAK mengupload baru
         if (file_exists('../' . $foto_lama)) {
             unlink('../' . $foto_lama);
         }
         $foto_path_db_update = null; // Set jadi null di DB
     } elseif (!$new_foto_uploaded && !$hapus_foto_lama) {
         // Jika tidak ada upload baru dan tidak mencentang hapus, gunakan foto lama
         $foto_path_db_update = $foto_lama;
     }
     // --- Akhir Proses Upload File ---

     // Setelah diedit, status pengajuan bisa direset ke 'Menunggu Persetujuan' lagi
     $status_pengajuan_baru = 'Menunggu Persetujuan';

     // Update data di database
     $sql_update = "UPDATE usaha_mahasiswa SET 
                     nama_usaha = ?, 
                     kategori_usaha = ?, 
                     deskripsi_usaha = ?, 
                     alamat_usaha = ?, 
                     kontak_usaha = ?, 
                     foto_produk_atau_logo = ?,
                     status_pengajuan = ?,
                     catatan_admin = NULL -- Reset catatan admin setelah diedit
                   WHERE id = ? AND user_id = ?";
     
     $stmt_update = $conn->prepare($sql_update);
     if (!$stmt_update) {
         $_SESSION['usaha_edit_message'] = ['type' => 'error', 'text' => 'Database error (prepare update): ' . $conn->error];
     } else {
         $stmt_update->bind_param("ssssssssi", 
             $nama_usaha, 
             $kategori_usaha, 
             $deskripsi_usaha, 
             $alamat_usaha, 
             $kontak_usaha,
             $foto_path_db_update, // Ini bisa null atau path baru atau path lama
             $status_pengajuan_baru,
             $usaha_id,
             $user_id
         );

         if ($stmt_update->execute()) {
             $_SESSION['usaha_message'] = ['type' => 'success', 'text' => 'Pengajuan usaha berhasil diperbarui dan akan ditinjau ulang.'];
             header("Location: detail_usaha_saya.php?id=" . $usaha_id);
             exit;
         } else {
             $_SESSION['usaha_edit_message'] = ['type' => 'error', 'text' => 'Gagal memperbarui data usaha: ' . $stmt_update->error];
         }
         $stmt_update->close();
     }
     $conn->close();
     
     // Jika ada error sebelum redirect sukses, redirect kembali ke form edit
     header("Location: edit_usaha_saya.php?id=" . $usaha_id);
     exit;

 } else {
     $_SESSION['usaha_message'] = ['type' => 'error', 'text' => 'Aksi tidak valid.'];
     header("Location: usaha_saya.php");
     exit;
 }
 ?>

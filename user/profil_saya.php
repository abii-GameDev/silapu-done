 <?php
 session_start();
 $pathPrefix = '../'; // Path dari user/ ke root
 $pageTitle = "Profil Saya";

 // Cek jika pengguna belum login
 if (!isset($_SESSION['user_id'])) {
     $_SESSION['login_messages'] = [['type' => 'error', 'text' => 'Anda harus login untuk mengakses halaman ini.']];
     header("Location: " . $pathPrefix . "auth/login.php");
     exit;
 }

 $user_id = $_SESSION['user_id'];

 // Include koneksi database
 require $pathPrefix . 'config/db.php';

 // Ambil data pengguna saat ini
 $user_data = null;
 $stmt_user = $conn->prepare("SELECT username, email, created_at FROM users WHERE id = ?");
 if ($stmt_user) {
     $stmt_user->bind_param("i", $user_id);
     $stmt_user->execute();
     $result_user = $stmt_user->get_result();
     if ($result_user->num_rows === 1) {
         $user_data = $result_user->fetch_assoc();
     }
     $stmt_user->close();
 } else {
     // Handle error prepare statement
     die("Error mengambil data pengguna: " . $conn->error); // Sebaiknya log, bukan die()
 }

 // Include header
 include $pathPrefix . 'includes/header.php';
 ?>

 <section class="profil-saya-section" style="padding: 40px 20px; background-color: var(--light-gray);">
     <div class="container" style="max-width: 900px;">
         <h2 class="section-title" style="color: var(--dark-green); text-shadow: none; margin-bottom: 25px; text-align:left;">
             <?php echo $pageTitle; ?>
         </h2>
         
         <a href="<?php echo $pathPrefix; ?>user/dashboard.php" style="display: inline-block; margin-bottom: 20px; text-decoration: none; background-color: var(--dark); color: white; padding: 8px 15px; border-radius: 5px;">« Kembali ke Dashboard</a>

         <?php
         // Tampilkan pesan dari proses update (jika ada)
         if (isset($_SESSION['profil_message'])) {
             $message = $_SESSION['profil_message'];
             echo '<div class="message ' . htmlspecialchars($message['type']) . '" style="margin-bottom: 20px;">' . htmlspecialchars($message['text']) . '</div>';
             unset($_SESSION['profil_message']);
         }
         ?>

         <?php if ($user_data): ?>
             <div style="background-color: var(--white); padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); margin-bottom:30px;">
                 <h3 style="margin-top:0; margin-bottom:20px; color:var(--dark); border-bottom:1px solid #eee; padding-bottom:10px;">Informasi Akun</h3>
                 <p><strong>Username:</strong> <?php echo htmlspecialchars($user_data['username']); ?></p>
                 <p><strong>Email:</strong> <?php echo htmlspecialchars($user_data['email']); ?></p>
                 <p><strong>Tanggal Bergabung:</strong> <?php echo date('d F Y', strtotime($user_data['created_at'])); ?></p>
                 <!-- Tombol untuk edit username bisa ditambahkan di sini jika diinginkan -->
             </div>

             <div style="background-color: var(--white); padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
                 <h3 style="margin-top:0; margin-bottom:20px; color:var(--dark); border-bottom:1px solid #eee; padding-bottom:10px;">Ubah Password</h3>
                 <form action="<?php echo $pathPrefix; ?>user/proses_ganti_password.php" method="POST">
                     <div class="form-group" style="margin-bottom: 15px;">
                         <label for="password_lama" style="display: block; margin-bottom: 5px; font-weight: 600;">Password Lama:</label>
                         <input type="password" name="password_lama" id="password_lama" required class="form-control" style="width:100%; padding:10px; border:1px solid #ddd; border-radius:4px;">
                     </div>
                     <div class="form-group" style="margin-bottom: 15px;">
                         <label for="password_baru" style="display: block; margin-bottom: 5px; font-weight: 600;">Password Baru:</label>
                         <input type="password" name="password_baru" id="password_baru" required class="form-control" style="width:100%; padding:10px; border:1px solid #ddd; border-radius:4px;">
                         <small>Minimal 6 karakter.</small>
                     </div>
                     <div class="form-group" style="margin-bottom: 20px;">
                         <label for="konfirmasi_password_baru" style="display: block; margin-bottom: 5px; font-weight: 600;">Konfirmasi Password Baru:</label>
                         <input type="password" name="konfirmasi_password_baru" id="konfirmasi_password_baru" required class="form-control" style="width:100%; padding:10px; border:1px solid #ddd; border-radius:4px;">
                     </div>
                     <button type="submit" name="submit_ganti_password" class="btn" style="background-color: var(--dark-green); color: white; padding: 10px 20px; border:none; border-radius:4px; cursor:pointer;">Ubah Password</button>
                 </form>
             </div>
             
             <!-- Tambahan: Form untuk Edit Username (Opsional) -->
             <!--
             <div style="background-color: var(--white); padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); margin-top:30px;">
                 <h3 style="margin-top:0; margin-bottom:20px; color:var(--dark); border-bottom:1px solid #eee; padding-bottom:10px;">Ubah Username</h3>
                 <form action="<?php echo $pathPrefix; ?>user/proses_edit_profil.php" method="POST">
                     <div class="form-group" style="margin-bottom: 15px;">
                         <label for="username_baru" style="display: block; margin-bottom: 5px; font-weight: 600;">Username Baru:</label>
                         <input type="text" name="username_baru" id="username_baru" value="<?php echo htmlspecialchars($user_data['username']); ?>" required class="form-control" style="width:100%; padding:10px; border:1px solid #ddd; border-radius:4px;">
                     </div>
                     <button type="submit" name="submit_edit_username" class="btn" style="background-color: var(--dark-green); color: white; padding: 10px 20px; border:none; border-radius:4px; cursor:pointer;">Simpan Username</button>
                 </form>
             </div>
             -->

         <?php else: ?>
             <p class="message error">Gagal memuat data profil Anda.</p>
         <?php endif; ?>
     </div>
 </section>

 <?php
 if(isset($conn)) $conn->close();
 // Include footer
 include $pathPrefix . 'includes/footer.php';
 ?>

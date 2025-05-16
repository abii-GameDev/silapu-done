<?php
session_start();
$pathPrefix = ''; // Karena file ini ada di root project
?>
<?php include 'includes/header.php'; ?>

<style>

</style>

<section class="registration" style="padding-top: 40px; padding-bottom: 40px;"> <!-- Beri sedikit padding atas/bawah jika perlu -->
    <div class="container">
        <div class="section-title">
            <h2>Pendaftaran Anggota Koperasi</h2>
            <p>Bergabunglah dengan Koperasi Mahasiswa UIN Raden Intan Lampung</p>
        </div>

        <div class="form-container">
            <?php
            if (isset($_SESSION['pendaftaran_anggota_message'])) {
                $message = $_SESSION['pendaftaran_anggota_message'];
                echo '<div class="message ' . htmlspecialchars($message['type']) . '">' . htmlspecialchars($message['text']) . '</div>';
                unset($_SESSION['pendaftaran_anggota_message']);
            }
            ?>

            <form id="registrationForm" method="POST" action="proses_pendaftaran_anggota.php">
                <div class="form-group">
                    <label for="nama">Nama Lengkap</label>
                    <input type="text" class="form-control" id="nama" name="nama_lengkap" required>
                </div>

                <div class="form-group">
                    <label for="nim">NIM</label>
                    <input type="text" class="form-control" id="nim" name="nim" required>
                </div>

                <div class="form-group">
                    <label for="semester">Semester</label>
                    <select class="form-control" id="semester" name="semester" required>
                        <option value="">Pilih Semester</option>
                        <option value="1">Semester 1</option>
                        <option value="2">Semester 2</option>
                        <option value="3">Semester 3</option>
                        <option value="4">Semester 4</option>
                        <option value="5">Semester 5</option>
                        <option value="6">Semester 6</option>
                        <option value="7">Semester 7</option>
                        <option value="8">Semester 8</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="prodi">Program Studi</label>
                    <input type="text" class="form-control" id="prodi" name="program_studi" required>
                </div>

                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" class="form-control" id="email" name="email" required>
                </div>

                <div class="form-group">
                    <label for="hp">Nomor HP/WhatsApp</label>
                    <input type="tel" class="form-control" id="hp" name="nomor_hp" required>
                </div>

                <div class="form-group">
                    <label for="alasan">Alasan Bergabung</label>
                    <textarea class="form-control" id="alasan" name="alasan_bergabung" rows="4" required></textarea>
                </div>

                <button type="submit" class="btn-submit">Daftar Sekarang</button>
            </form>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
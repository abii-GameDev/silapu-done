<?php
session_start();
require 'config/db.php'; // Pastikan path ini benar ke file koneksi database Anda

// Inisialisasi array untuk pesan feedback
$message = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Ambil data dari form dan sanitasi dasar
    $nama_lengkap = trim($_POST['nama_lengkap'] ?? '');
    $nim = trim($_POST['nim'] ?? '');
    $semester = trim($_POST['semester'] ?? '');
    $program_studi = trim($_POST['program_studi'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $nomor_hp = trim($_POST['nomor_hp'] ?? '');
    $alasan_bergabung = trim($_POST['alasan_bergabung'] ?? '');

    // Mendapatkan user_id jika pengguna sedang login
    $user_id_for_anggota = null;
    if (isset($_SESSION['user_id'])) {
        $user_id_for_anggota = $_SESSION['user_id'];
    }

    // Validasi sederhana
    if (empty($nama_lengkap) || empty($nim) || empty($semester) || empty($program_studi) || empty($email)) {
        $message = ['type' => 'error', 'text' => 'Nama Lengkap, NIM, Semester, Program Studi, dan Email wajib diisi.'];
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = ['type' => 'error', 'text' => 'Format email tidak valid.'];
    } else {
        // Cek apakah NIM sudah terdaftar
        $stmt_check_nim = $conn->prepare("SELECT id FROM data_anggota WHERE nim = ?");
        if (!$stmt_check_nim) {
            $message = ['type' => 'error', 'text' => 'Database error (prepare nim check): ' . $conn->error];
        } else {
            $stmt_check_nim->bind_param("s", $nim);
            $stmt_check_nim->execute();
            $stmt_check_nim->store_result();

            if ($stmt_check_nim->num_rows > 0) {
                $message = ['type' => 'error', 'text' => 'NIM sudah terdaftar.'];
            }
            $stmt_check_nim->close();
        }

        // Cek apakah Email sudah terdaftar
        if (empty($message)) {
            $stmt_check_email = $conn->prepare("SELECT id FROM data_anggota WHERE email = ?");
            if (!$stmt_check_email) {
                $message = ['type' => 'error', 'text' => 'Database error (prepare email check): ' . $conn->error];
            } else {
                $stmt_check_email->bind_param("s", $email);
                $stmt_check_email->execute();
                $stmt_check_email->store_result();

                if ($stmt_check_email->num_rows > 0) {
                    $message = ['type' => 'error', 'text' => 'Email sudah terdaftar sebagai anggota.'];
                }
                $stmt_check_email->close();
            }
        }

        // Jika tidak ada error dari validasi dan pengecekan duplikasi
        if (empty($message)) {
            // Query INSERT dengan kolom user_id
            $sql = "INSERT INTO data_anggota (user_id, nama_lengkap, nim, semester, program_studi, email, nomor_hp, alasan_bergabung) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt_insert = $conn->prepare($sql);

            if (!$stmt_insert) {
                $message = ['type' => 'error', 'text' => 'Database error (prepare insert): ' . $conn->error];
            } else {
                $stmt_insert->bind_param("isssssss", 
                    $user_id_for_anggota,
                    $nama_lengkap, 
                    $nim, 
                    $semester, 
                    $program_studi, 
                    $email, 
                    $nomor_hp, 
                    $alasan_bergabung
                );

                if ($stmt_insert->execute()) {
                    $message = ['type' => 'success', 'text' => 'Pendaftaran anggota berhasil! Data Anda sedang diproses.'];
                } else {
                    $message = ['type' => 'error', 'text' => 'Gagal melakukan pendaftaran anggota: ' . $stmt_insert->error];
                }
                $stmt_insert->close();
            }
        }
    }
    $conn->close();
} else {
    $message = ['type' => 'error', 'text' => 'Metode request tidak valid.'];
}

// Simpan pesan ke session
$_SESSION['pendaftaran_anggota_message'] = $message;

// Redirect kembali ke halaman pendaftaran
header("Location: pendaftaran_anggota.php");
exit;
?>

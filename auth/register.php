<?php
session_start(); // Mulai sesi untuk pesan feedback
require '../config/db.php'; // Pastikan path ini benar

$messages = []; // Array untuk menampung pesan

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Validasi dasar (bisa ditambahkan lebih banyak)
    if (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
        $messages[] = ['type' => 'error', 'text' => 'Semua field harus diisi!'];
    } elseif ($password !== $confirm_password) {
        $messages[] = ['type' => 'error', 'text' => 'Password dan konfirmasi password tidak sama!'];
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $messages[] = ['type' => 'error', 'text' => 'Format email tidak valid!'];
    } else {
        // Check if email already exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        if (!$stmt) { // Cek jika prepare gagal
            $messages[] = ['type' => 'error', 'text' => 'Database error (prepare select): ' . $conn->error];
        } else {
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $stmt->store_result();
            if ($stmt->num_rows > 0) {
                $messages[] = ['type' => 'error', 'text' => 'Email sudah terdaftar!'];
            }
            $stmt->close();
        }

        // Check if username already exists (opsional, tapi baik)
        // Anda perlu menambahkan pengecekan ini jika username juga harus unik
        /*
        $stmt_user = $conn->prepare("SELECT id FROM users WHERE username = ?");
        if (!$stmt_user) {
            $messages[] = ['type' => 'error', 'text' => 'Database error (prepare select username): ' . $conn->error];
        } else {
            $stmt_user->bind_param("s", $username);
            $stmt_user->execute();
            $stmt_user->store_result();
            if ($stmt_user->num_rows > 0) {
                $messages[] = ['type' => 'error', 'text' => 'Username sudah terdaftar!'];
            }
            $stmt_user->close();
        }
        */

        // Jika tidak ada error sejauh ini, lanjutkan insert
        if (empty($messages)) {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            $stmt_insert = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
            if (!$stmt_insert) { // Cek jika prepare gagal
                $messages[] = ['type' => 'error', 'text' => 'Database error (prepare insert): ' . $conn->error];
            } else {
                $stmt_insert->bind_param("sss", $username, $email, $hashed_password);
                if ($stmt_insert->execute()) {
                    $_SESSION['register_success'] = "Pendaftaran berhasil! Silakan login.";
                    header("Location: login.php"); // Redirect ke login.php di direktori yang sama
                    exit;
                } else {
                    $messages[] = ['type' => 'error', 'text' => 'Terjadi kesalahan saat pendaftaran: ' . $stmt_insert->error];
                }
                $stmt_insert->close();
            }
        }
    }
    $conn->close();
} else {
    // Jika bukan POST request, redirect ke halaman login atau tampilkan error
    // Untuk keamanan, lebih baik tidak menampilkan pesan "Invalid request method." secara langsung
    header("Location: login.php");
    exit;
}

// Jika ada error, simpan pesan error di session dan redirect kembali ke form register (tab signup di login.php)
if (!empty($messages)) {
    $_SESSION['register_messages'] = $messages;
    header("Location: login.php#signup-form"); // Redirect kembali ke login dengan hash agar tab signup aktif
    exit;
}

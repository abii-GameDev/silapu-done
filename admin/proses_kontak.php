<?php
session_start();
require 'config/db.php';

function is_ajax_request() {
    return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
}

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Enable error reporting for debugging
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);

    // Get form data and sanitize
    $name = isset($_POST['name']) ? trim($_POST['name']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $subject = isset($_POST['subject']) ? trim($_POST['subject']) : '';
    $message = isset($_POST['message']) ? trim($_POST['message']) : '';

    // Basic validation
    if (empty($name) || empty($email) || empty($subject) || empty($message)) {
        if (is_ajax_request()) {
            header('Content-Type: application/json');
            echo json_encode(['status' => 'error', 'message' => 'Semua field harus diisi.']);
            exit;
        } else {
            $_SESSION['contact_message'] = ['type' => 'error', 'text' => 'Semua field harus diisi.'];
            header('Location: beranda.php');
            exit;
        }
    }

    // Prepare and bind to insert message into database
    $stmt = $conn->prepare("INSERT INTO kontak_masuk (name, email, subject, message, created_at) VALUES (?, ?, ?, ?, NOW())");
    if ($stmt) {
        $stmt->bind_param("ssss", $name, $email, $subject, $message);
        if ($stmt->execute()) {
            if (is_ajax_request()) {
                header('Content-Type: application/json');
                echo json_encode(['status' => 'success', 'message' => 'Pesan berhasil dikirim. Terima kasih!']);
                exit;
            } else {
                $_SESSION['contact_message'] = ['type' => 'success', 'text' => 'Pesan berhasil dikirim. Terima kasih!'];
                header('Location: beranda.php');
                exit;
            }
        } else {
            if (is_ajax_request()) {
                header('Content-Type: application/json');
                echo json_encode(['status' => 'error', 'message' => 'Gagal mengirim pesan: ' . $stmt->error]);
                exit;
            } else {
                $_SESSION['contact_message'] = ['type' => 'error', 'text' => 'Gagal mengirim pesan: ' . $stmt->error];
                header('Location: beranda.php');
                exit;
            }
        }
    } else {
        if (is_ajax_request()) {
            header('Content-Type: application/json');
            echo json_encode(['status' => 'error', 'message' => 'Gagal menyiapkan query: ' . $conn->error]);
            exit;
        } else {
            $_SESSION['contact_message'] = ['type' => 'error', 'text' => 'Gagal menyiapkan query: ' . $conn->error];
            header('Location: beranda.php');
            exit;
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    // Show messages page without admin restriction
    $result = $conn->query("SELECT id, name, email, subject, message, created_at FROM kontak_masuk ORDER BY created_at DESC");
    ?>
    <!DOCTYPE html>
    <html lang="id">
    <head>
        <meta charset="UTF-8">
        <title>Pesan Kontak Masuk</title>
        <link rel="stylesheet" href="assets/css/admin_style.css">
    </head>
    <body class="admin-body">
        <div class="main-content-area">
            <main>
                <h1>Pesan Kontak Masuk</h1>
                <?php if ($result && $result->num_rows > 0): ?>
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Nama</th>
                                <th>Email</th>
                                <th>Subjek</th>
                                <th>Pesan</th>
                                <th>Tanggal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['name']); ?></td>
                                    <td><?php echo htmlspecialchars($row['email']); ?></td>
                                    <td><?php echo htmlspecialchars($row['subject']); ?></td>
                                    <td><?php echo nl2br(htmlspecialchars($row['message'])); ?></td>
                                    <td><?php echo date('d-m-Y H:i', strtotime($row['created_at'])); ?></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p>Tidak ada pesan masuk.</p>
                <?php endif; ?>
            </main>
        </div>
    </body>
    </html>
    <?php
    exit;
}

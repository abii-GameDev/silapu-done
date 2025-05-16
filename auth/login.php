<?php
session_start();
require '../config/db.php';

$pathPrefix = '../';
$login_messages = [];

if (isset($_SESSION['user_id']) && isset($_SESSION['username'])) {
    if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
        header("Location: " . $pathPrefix . "admin/dashboard.php");
    } else {
        header("Location: " . $pathPrefix . "user/dashboard.php");
    }
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['emailOrUsername']) && isset($_POST['password'])) {
    $emailOrUsername = trim($_POST['emailOrUsername']);
    $password_input = $_POST['password'];

    if (empty($emailOrUsername) || empty($password_input)) {
        $login_messages[] = ['type' => 'error', 'text' => 'Email/Username dan Password wajib diisi.'];
    } else {
        $sql = "SELECT id, username, email, password, role FROM users WHERE email = ? OR username = ?";
        $stmt = $conn->prepare($sql);

        if (!$stmt) {
            $login_messages[] = ['type' => 'error', 'text' => 'Database error (prepare): ' . $conn->error];
        } else {
            $stmt->bind_param("ss", $emailOrUsername, $emailOrUsername);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 1) {
                $user = $result->fetch_assoc();

                if (password_verify($password_input, $user['password'])) {
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['email'] = $user['email'];
                    $_SESSION['role'] = $user['role'];

                    if ($user['role'] === 'admin') {
                        header("Location: " . $pathPrefix . "admin/dashboard.php");
                        exit;
                    } else {
                        header("Location: " . $pathPrefix . "user/dashboard.php");
                        exit;
                    }
                } else {
                    $login_messages[] = ['type' => 'error', 'text' => 'Email/Username atau Password salah.'];
                }
            } else {
                $login_messages[] = ['type' => 'error', 'text' => 'Email/Username atau Password salah.'];
            }
            $stmt->close();
        }
    }
    $conn->close();
}
?>
<?php include $pathPrefix . 'includes/header.php'; ?>

<style>
    :root {
        --primary: #4CAF50;
        --primary-dark: #388E3C;
        --primary-light: #A5D6A7;
        --accent: #FF9800;
        --text-primary: #212121;
        --text-secondary: #757575;
        --white: #FFFFFF;
        --error: #F44336;
        --success: #4CAF50;
        --warning: #FFC107;
        --info: #2196F3;
        --light-gray: #f5f5f5;
        --gray: #e0e0e0;
        --dark-gray: #9e9e9e;
        --shadow-light: rgba(0, 0, 0, 0.08);
        --shadow-medium: rgba(0, 0, 0, 0.12);
    }

    /* Global adjustments */
    body {
        background-color: #f9f9f9;
        font-family: 'Poppins', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        color: var(--text-primary);
    }

    .container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 20px;
    }

    /* Auth Container */
    .auth-section {
        display: flex;
        align-items: center;
        justify-content: center;
        min-height: calc(100vh - 100px);
        padding: 40px 0;
        background: linear-gradient(135deg, rgba(165, 214, 167, 0.2) 0%, rgba(200, 230, 201, 0.3) 100%);
        position: relative;
        overflow: hidden;
    }

    .auth-section::before {
        content: '';
        position: absolute;
        top: -50px;
        left: -50px;
        width: 200px;
        height: 200px;
        background-color: var(--primary-light);
        border-radius: 50%;
        opacity: 0.3;
        animation: float 15s infinite ease-in-out;
    }

    .auth-section::after {
        content: '';
        position: absolute;
        bottom: -70px;
        right: -70px;
        width: 300px;
        height: 300px;
        background-color: var(--primary-light);
        border-radius: 50%;
        opacity: 0.2;
        animation: float 20s infinite ease-in-out reverse;
    }

    @keyframes float {
        0% {
            transform: translate(0, 0) rotate(0deg);
        }

        25% {
            transform: translate(10px, 15px) rotate(5deg);
        }

        50% {
            transform: translate(5px, -10px) rotate(10deg);
        }

        75% {
            transform: translate(-15px, 5px) rotate(3deg);
        }

        100% {
            transform: translate(0, 0) rotate(0deg);
        }
    }

    .auth-container {
        max-width: 480px;
        width: 100%;
        margin: 0 auto;
        background-color: var(--white);
        padding: 40px;
        border-radius: 16px;
        box-shadow: 0 10px 30px var(--shadow-light);
        position: relative;
        z-index: 10;
        transition: all 0.3s ease;
        overflow: hidden;
    }

    .auth-container:hover {
        box-shadow: 0 15px 35px var(--shadow-medium);
        transform: translateY(-5px);
    }

    .auth-logo {
        text-align: center;
        margin-bottom: 30px;
    }

    .auth-logo img {
        height: 60px;
        width: auto;
    }

    .auth-title {
        text-align: center;
        margin-bottom: 30px;
    }

    .auth-title h2 {
        color: var(--primary-dark);
        font-size: 2rem;
        margin-bottom: 10px;
        font-weight: 700;
    }

    .auth-title p {
        color: var(--text-secondary);
        font-size: 1rem;
        line-height: 1.6;
    }

    .auth-tabs {
        display: flex;
        margin-bottom: 30px;
        border-bottom: 2px solid var(--light-gray);
    }

    .auth-tab {
        flex: 1;
        padding: 14px 20px;
        text-align: center;
        cursor: pointer;
        font-weight: 600;
        font-size: 1.1rem;
        color: var(--text-secondary);
        position: relative;
        transition: all 0.3s ease;
    }

    .auth-tab:hover {
        color: var(--primary);
    }

    .auth-tab.active {
        color: var(--primary);
    }

    .auth-tab.active::after {
        content: '';
        position: absolute;
        bottom: -2px;
        left: 0;
        width: 100%;
        height: 3px;
        background-color: var(--primary);
        animation: slideIn 0.3s ease forwards;
    }

    @keyframes slideIn {
        from {
            width: 0;
            opacity: 0;
        }

        to {
            width: 100%;
            opacity: 1;
        }
    }

    .auth-form {
        display: none;
        animation: fadeIn 0.5s ease;
    }

    .auth-form.active {
        display: block;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(20px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .form-group {
        margin-bottom: 24px;
        position: relative;
    }

    .form-group label {
        display: block;
        margin-bottom: 8px;
        color: var(--text-primary);
        font-weight: 500;
        font-size: 0.95rem;
        transition: all 0.3s ease;
    }

    .form-control {
        width: 100%;
        padding: 14px 16px;
        border: 2px solid var(--gray);
        border-radius: 12px;
        font-size: 1rem;
        transition: all 0.3s ease;
        color: var(--text-primary);
        background-color: var(--white);
    }

    .form-control:focus {
        border-color: var(--primary);
        outline: none;
        box-shadow: 0 0 0 4px rgba(76, 175, 80, 0.2);
    }

    .form-control:hover {
        border-color: var(--dark-gray);
    }

    .btn {
        display: inline-block;
        width: 100%;
        padding: 15px 20px;
        background: linear-gradient(to right, var(--primary), var(--primary-dark));
        color: var(--white);
        text-decoration: none;
        border-radius: 12px;
        font-weight: 600;
        font-size: 1rem;
        border: none;
        cursor: pointer;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
        text-align: center;
    }

    .btn::after {
        content: '';
        position: absolute;
        top: 50%;
        left: 50%;
        width: 100%;
        height: 100%;
        background-color: rgba(255, 255, 255, 0.2);
        border-radius: 50%;
        transform: translate(-50%, -50%) scale(0);
        opacity: 0;
        transition: transform 0.5s, opacity 0.3s;
    }

    .btn:hover {
        transform: translateY(-3px);
        box-shadow: 0 7px 14px rgba(0, 0, 0, 0.1), 0 5px 5px rgba(0, 0, 0, 0.1);
    }

    .btn:hover::after {
        transform: translate(-50%, -50%) scale(2);
        opacity: 1;
    }

    .btn:active {
        transform: translateY(0);
        box-shadow: 0 3px 8px rgba(0, 0, 0, 0.1);
    }

    .form-footer {
        margin-top: 20px;
        text-align: center;
        color: var(--text-secondary);
        font-size: 0.95rem;
    }

    .form-footer a {
        color: var(--primary);
        text-decoration: none;
        font-weight: 600;
        transition: all 0.3s ease;
    }

    .form-footer a:hover {
        color: var(--primary-dark);
        text-decoration: underline;
    }

    /* Messages */
    .message {
        padding: 12px 16px;
        margin-bottom: 20px;
        border-radius: 10px;
        font-weight: 500;
        font-size: 0.95rem;
        display: flex;
        align-items: center;
        animation: messageFadeIn 0.5s ease;
    }

    @keyframes messageFadeIn {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .message.error {
        background-color: rgba(244, 67, 54, 0.1);
        color: var(--error);
        border-left: 4px solid var(--error);
    }

    .message.success {
        background-color: rgba(76, 175, 80, 0.1);
        color: var(--success);
        border-left: 4px solid var(--success);
    }

    .message.warning {
        background-color: rgba(255, 193, 7, 0.1);
        color: var(--warning);
        border-left: 4px solid var(--warning);
    }

    .message.info {
        background-color: rgba(33, 150, 243, 0.1);
        color: var(--info);
        border-left: 4px solid var(--info);
    }

    .message::before {
        font-family: "Font Awesome 5 Free";
        font-weight: 900;
        margin-right: 10px;
    }

    .message.error::before {
        content: "\f071";
    }

    .message.success::before {
        content: "\f00c";
    }

    .message.warning::before {
        content: "\f06a";
    }

    .message.info::before {
        content: "\f05a";
    }

    /* Responsive Styles */
    @media (max-width: 768px) {
        .auth-container {
            padding: 30px;
            margin: 20px;
            width: auto;
        }

        .auth-title h2 {
            font-size: 1.8rem;
        }

        .auth-tab {
            padding: 12px 15px;
            font-size: 1rem;
        }

        .btn {
            padding: 14px 18px;
        }
    }

    @media (max-width: 480px) {
        .auth-container {
            padding: 25px 20px;
            border-radius: 12px;
        }

        .auth-title h2 {
            font-size: 1.6rem;
        }

        .form-control {
            padding: 12px 14px;
        }
    }
</style>

<!-- Font Awesome CDN Link -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
<!-- Google Fonts -->
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

<section class="auth-section">
    <div class="container">
        <div class="auth-container">
            <div class="auth-title">
                <h2>Selamat Datang</h2>
                <p>Masuk ke akun Anda atau daftar sebagai pengguna baru</p>
            </div>

            <div class="auth-tabs">
                <div class="auth-tab <?php echo (!isset($_SESSION['register_messages']) && empty($login_messages) && (!isset($_GET['form']) || $_GET['form'] === 'login')) ? 'active' : ''; ?>" onclick="switchTab('login', event)">
                    <i class="fas fa-sign-in-alt"></i> Login
                </div>
                <div class="auth-tab <?php echo (isset($_SESSION['register_messages']) || (isset($_GET['form']) && $_GET['form'] === 'signup')) ? 'active' : ''; ?>" onclick="switchTab('signup', event)">
                    <i class="fas fa-user-plus"></i> Daftar
                </div>
            </div>

            <!-- Login Form -->
            <form id="login-form" class="auth-form <?php echo (!isset($_SESSION['register_messages']) && empty($login_messages) && (!isset($_GET['form']) || $_GET['form'] === 'login')) ? 'active' : ''; ?>" method="POST" action="login.php">
                <?php
                if (!empty($login_messages)) {
                    foreach ($login_messages as $message) {
                        echo '<div class="message ' . htmlspecialchars($message['type']) . '">' . htmlspecialchars($message['text']) . '</div>';
                    }
                }
                if (isset($_SESSION['register_success'])) {
                    echo '<div class="message success">' . htmlspecialchars($_SESSION['register_success']) . '</div>';
                    unset($_SESSION['register_success']);
                }
                if (isset($_SESSION['login_messages']) && is_array($_SESSION['login_messages'])) {
                    foreach ($_SESSION['login_messages'] as $msg) {
                        if (is_array($msg) && isset($msg['type']) && isset($msg['text'])) {
                            echo '<div class="message ' . htmlspecialchars($msg['type']) . '">' . htmlspecialchars($msg['text']) . '</div>';
                        }
                    }
                    unset($_SESSION['login_messages']);
                }
                ?>
                <div class="form-group">
                    <label for="login-email"><i class="fas fa-user"></i> Email atau Username</label>
                    <input type="text" class="form-control" id="login-email" name="emailOrUsername" required placeholder="Masukkan email atau username Anda" value="<?php echo isset($_POST['emailOrUsername']) ? htmlspecialchars($_POST['emailOrUsername']) : ''; ?>">
                </div>
                <div class="form-group">
                    <label for="login-password"><i class="fas fa-lock"></i> Password</label>
                    <input type="password" class="form-control" id="login-password" name="password" required placeholder="Masukkan password Anda">
                </div>
                <button type="submit" class="btn">
                    <i class="fas fa-sign-in-alt"></i> Login
                </button>
                <div class="form-footer">
                    Lupa password? <a href="#">Reset disini</a>
                </div>
            </form>

            <!-- Signup Form -->
            <form id="signup-form" class="auth-form <?php echo (isset($_SESSION['register_messages']) || (isset($_GET['form']) && $_GET['form'] === 'signup')) ? 'active' : ''; ?>" method="POST" action="register.php">
                <?php
                if (isset($_SESSION['register_messages'])) {
                    foreach ($_SESSION['register_messages'] as $message) {
                        echo '<div class="message ' . htmlspecialchars($message['type']) . '">' . htmlspecialchars($message['text']) . '</div>';
                    }
                    unset($_SESSION['register_messages']);
                }
                ?>
                <div class="form-group">
                    <label for="signup-name"><i class="fas fa-user"></i> Nama Lengkap</label>
                    <input type="text" class="form-control" id="signup-name" name="username" required placeholder="Masukkan nama lengkap Anda" value="<?php echo isset($_SESSION['form_data']['username']) ? htmlspecialchars($_SESSION['form_data']['username']) : ''; ?>">
                </div>
                <div class="form-group">
                    <label for="signup-email"><i class="fas fa-envelope"></i> Email</label>
                    <input type="email" class="form-control" id="signup-email" name="email" required placeholder="Masukkan email aktif Anda" value="<?php echo isset($_SESSION['form_data']['email']) ? htmlspecialchars($_SESSION['form_data']['email']) : ''; ?>">
                </div>
                <div class="form-group">
                    <label for="signup-password"><i class="fas fa-lock"></i> Password</label>
                    <input type="password" class="form-control" id="signup-password" name="password" required placeholder="Buat password Anda (min. 8 karakter)">
                </div>
                <div class="form-group">
                    <label for="signup-confirm"><i class="fas fa-check-circle"></i> Konfirmasi Password</label>
                    <input type="password" class="form-control" id="signup-confirm" name="confirm_password" required placeholder="Konfirmasi password Anda">
                </div>
                <button type="submit" class="btn">
                    <i class="fas fa-user-plus"></i> Daftar
                </button>
                <div class="form-footer">
                    Sudah punya akun? <a href="#" onclick="switchTab('login', event); window.history.pushState({}, '', 'login.php?form=login');">Login disini</a>
                </div>
                <?php if (isset($_SESSION['form_data'])) unset($_SESSION['form_data']); ?>
            </form>
        </div>
    </div>
</section>

<script>
    function switchTab(tabId, event) {
        // Mencegah default behavior link jika ada
        if (event) {
            event.preventDefault();
        }

        // Menghapus kelas active dari semua tab dan form
        document.querySelectorAll('.auth-tab').forEach(tab => {
            tab.classList.remove('active');
        });
        document.querySelectorAll('.auth-form').forEach(form => {
            form.classList.remove('active');
        });

        // Menambahkan kelas active pada tab yang diklik
        if (event) {
            event.currentTarget.classList.add('active');
        } else {
            document.querySelector(`.auth-tab:nth-child(${tabId === 'login' ? 1 : 2})`).classList.add('active');
        }

        // Menambahkan kelas active pada form yang sesuai
        document.getElementById(`${tabId}-form`).classList.add('active');

        // Update URL jika perlu
        const formParam = tabId === 'login' ? 'login' : 'signup';
        if (window.history && window.history.pushState) {
            window.history.pushState({}, '', `login.php?form=${formParam}`);
        }
    }
</script>

<?php include $pathPrefix . 'includes/footer.php'; ?>
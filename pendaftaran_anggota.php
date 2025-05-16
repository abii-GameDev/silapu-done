<?php
session_start();
// Path prefix untuk includes
$pathPrefix = './'; // Menggunakan relatif path yang benar
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pendaftaran Anggota Koperasi - UIN Raden Intan Lampung</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
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

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--light-gray);
            color: var(--text-primary);
            line-height: 1.6;
        }

        /* Registration Section */
        .registration {
            padding: 70px 0;
            background-color: var(--light-gray);
        }

        .container {
            max-width: 1140px;
            margin: 0 auto;
            padding: 0 15px;
        }

        .section-title {
            text-align: center;
            margin-bottom: 40px;
        }

        .section-title h2 {
            color: var(--primary-dark);
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 15px;
            position: relative;
            display: inline-block;
            padding-bottom: 15px;
        }

        .section-title h2:after {
            content: '';
            position: absolute;
            width: 60px;
            height: 3px;
            background-color: var(--accent);
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
        }

        .section-title p {
            color: var(--text-secondary);
            font-size: 18px;
            max-width: 600px;
            margin: 0 auto;
        }

        /* Card styling */
        .form-container {
            background-color: var(--white);
            border-radius: 10px;
            box-shadow: 0 10px 30px var(--shadow-light);
            padding: 30px;
            max-width: 800px;
            margin: 0 auto;
        }

        /* Form layout */
        .form-row {
            display: flex;
            flex-wrap: wrap;
            margin: -10px;
        }

        .form-col {
            flex: 1 0 calc(50% - 20px);
            margin: 10px;
        }

        @media (max-width: 768px) {
            .form-col {
                flex: 1 0 100%;
            }
        }

        /* Form elements */
        .form-group {
            margin-bottom: 25px;
        }

        .form-group label {
            display: block;
            font-weight: 500;
            margin-bottom: 8px;
            color: var(--text-primary);
            font-size: 16px;
        }

        .form-control {
            width: 100%;
            height: 50px;
            padding: 0 15px;
            font-size: 16px;
            border: 1px solid var(--gray);
            border-radius: 8px;
            transition: all 0.3s ease;
            background-color: var(--white);
            color: var(--text-primary);
        }

        .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(76, 175, 80, 0.2);
            outline: none;
        }

        select.form-control {
            -webkit-appearance: none;
            -moz-appearance: none;
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%23757575' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 15px center;
            padding-right: 40px;
        }

        textarea.form-control {
            height: auto;
            padding: 15px;
            resize: vertical;
            min-height: 120px;
        }

        /* Button styling */
        .btn-submit {
            display: inline-block;
            background-color: var(--primary);
            color: var(--white);
            border: none;
            border-radius: 8px;
            padding: 15px 30px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            width: 100%;
            text-align: center;
            text-transform: uppercase;
            letter-spacing: 1px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .btn-submit:hover {
            background-color: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 6px 8px rgba(0, 0, 0, 0.15);
        }

        .btn-submit:active {
            transform: translateY(0);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        /* Form header */
        .form-header {
            border-bottom: 1px solid var(--gray);
            padding-bottom: 20px;
            margin-bottom: 30px;
        }

        .form-header h3 {
            color: var(--text-primary);
            font-size: 24px;
            margin-bottom: 10px;
            font-weight: 600;
        }

        .form-header p {
            color: var(--text-secondary);
            margin: 0;
            font-size: 16px;
        }

        /* Required field indicator */
        .required:after {
            content: '*';
            color: var(--error);
            margin-left: 4px;
        }

        .required-note {
            text-align: right;
            font-size: 14px;
            color: var(--text-secondary);
            margin-bottom: 20px;
        }

        /* Message styling */
        .message {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 25px;
            font-weight: 500;
            display: flex;
            align-items: center;
            font-size: 16px;
        }

        .message:before {
            font-family: "Font Awesome 6 Free";
            font-weight: 900;
            margin-right: 10px;
            font-size: 18px;
        }

        .message.success {
            background-color: rgba(76, 175, 80, 0.1);
            color: var(--success);
            border-left: 4px solid var(--success);
        }

        .message.success:before {
            content: "\f058";
            /* check-circle */
        }

        .message.error {
            background-color: rgba(244, 67, 54, 0.1);
            color: var(--error);
            border-left: 4px solid var(--error);
        }

        .message.error:before {
            content: "\f057";
            /* times-circle */
        }

        .message.warning {
            background-color: rgba(255, 193, 7, 0.1);
            color: var(--warning);
            border-left: 4px solid var(--warning);
        }

        .message.warning:before {
            content: "\f071";
            /* exclamation-triangle */
        }

        .message.info {
            background-color: rgba(33, 150, 243, 0.1);
            color: var(--info);
            border-left: 4px solid var(--info);
        }

        .message.info:before {
            content: "\f05a";
            /* info-circle */
        }

        /* Popup success message */
        .popup-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1000;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
        }

        .popup-overlay.active {
            opacity: 1;
            visibility: visible;
        }

        .popup-content {
            background-color: var(--white);
            padding: 30px;
            border-radius: 10px;
            text-align: center;
            max-width: 500px;
            width: 90%;
            transform: translateY(-20px);
            transition: all 0.3s ease;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        }

        .popup-overlay.active .popup-content {
            transform: translateY(0);
        }

        .popup-icon {
            width: 80px;
            height: 80px;
            margin: 0 auto 20px;
            background-color: #e8f5e9;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .popup-icon i {
            font-size: 40px;
            color: var(--success);
        }

        .popup-title {
            font-size: 24px;
            color: var(--text-primary);
            margin-bottom: 10px;
            font-weight: 600;
        }

        .popup-message {
            color: var(--text-secondary);
            margin-bottom: 25px;
            font-size: 16px;
            line-height: 1.6;
        }

        .popup-btn {
            display: inline-block;
            background-color: var(--primary);
            color: var(--white);
            border: none;
            border-radius: 8px;
            padding: 12px 30px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-transform: uppercase;
        }

        .popup-btn:hover {
            background-color: var(--primary-dark);
        }

        /* Placeholder styling */
        ::placeholder {
            color: var(--dark-gray);
            opacity: 0.7;
        }

        /* Focus visual indicator */
        .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(76, 175, 80, 0.2);
        }

        /* Form icon */
        .form-icon {
            text-align: center;
            margin-bottom: 20px;
        }

        .form-icon i {
            font-size: 48px;
            color: var(--primary);
            background-color: var(--primary-light);
            width: 80px;
            height: 80px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
    </style>
</head>

<body>
    <?php include $pathPrefix . 'includes/header.php'; ?>

    <section class="registration">
        <div class="container">
            <div class="section-title">
                <h2>Pendaftaran Anggota Koperasi</h2>
                <p>Bergabunglah dengan Koperasi Mahasiswa UIN Raden Intan Lampung dan nikmati berbagai keuntungannya</p>
            </div>

            <div class="form-container">
                <div class="form-icon">
                    <i class="fas fa-user-plus"></i>
                </div>

                <!-- Progress indicator dihapus -->

                <div class="form-header">
                    <h3>Informasi Pendaftar</h3>
                    <p>Silakan isi data diri Anda dengan lengkap dan benar untuk proses pendaftaran</p>
                </div>

                <div class="required-note">
                    <span class="required"></span> Wajib diisi
                </div>

                <?php
                if (isset($_SESSION['pendaftaran_anggota_message'])) {
                    $message = $_SESSION['pendaftaran_anggota_message'];
                    echo '<div class="message ' . htmlspecialchars($message['type']) . '">' . htmlspecialchars($message['text']) . '</div>';
                    unset($_SESSION['pendaftaran_anggota_message']);
                }
                ?>

                <form id="registrationForm" method="POST" action="proses_pendaftaran_anggota.php">
                    <div class="form-row">
                        <div class="form-col">
                            <div class="form-group">
                                <label for="nama" class="required">Nama Lengkap</label>
                                <input type="text" class="form-control" id="nama" name="nama_lengkap" placeholder="Masukkan nama lengkap Anda" required>
                            </div>
                        </div>
                        <div class="form-col">
                            <div class="form-group">
                                <label for="nim" class="required">NIM</label>
                                <input type="text" class="form-control" id="nim" name="nim" placeholder="Masukkan NIM Anda" required>
                            </div>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-col">
                            <div class="form-group">
                                <label for="semester" class="required">Semester</label>
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
                        </div>
                        <div class="form-col">
                            <div class="form-group">
                                <label for="prodi" class="required">Program Studi</label>
                                <input type="text" class="form-control" id="prodi" name="program_studi" placeholder="Masukkan program studi Anda" required>
                            </div>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-col">
                            <div class="form-group">
                                <label for="email" class="required">Email</label>
                                <input type="email" class="form-control" id="email" name="email" placeholder="Masukkan email aktif Anda" required>
                            </div>
                        </div>
                        <div class="form-col">
                            <div class="form-group">
                                <label for="hp" class="required">Nomor HP/WhatsApp</label>
                                <input type="tel" class="form-control" id="hp" name="nomor_hp" placeholder="Contoh: 08123456789" required>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="alasan" class="required">Alasan Bergabung</label>
                        <textarea class="form-control" id="alasan" name="alasan_bergabung" rows="4" placeholder="Ceritakan mengapa Anda ingin bergabung dengan Koperasi Mahasiswa" required></textarea>
                    </div>

                    <button type="submit" class="btn-submit">
                        <i class="fas fa-paper-plane"></i> Daftar Sekarang
                    </button>
                </form>
            </div>
        </div>
    </section>

    <!-- Success Popup -->
    <div class="popup-overlay" id="successPopup">
        <div class="popup-content">
            <div class="popup-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <h3 class="popup-title">Pendaftaran Berhasil!</h3>
            <p class="popup-message">
                Selamat! Anda telah berhasil terdaftar sebagai anggota Koperasi Mahasiswa UIN Raden Intan Lampung.
                Tim kami akan segera memproses pendaftaran Anda dan menghubungi melalui email atau WhatsApp yang telah Anda daftarkan.
            </p>
            <button class="popup-btn" id="closePopup">Tutup</button>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Di bagian JavaScript, hapus baris yang mencegah pengiriman form di production -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('registrationForm');
            const successPopup = document.getElementById('successPopup');
            const closePopupBtn = document.getElementById('closePopup');

            // Check if there's a success parameter in URL
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.get('status') === 'success') {
                showSuccessPopup();
            }

            if (form) {
                form.addEventListener('submit', function(event) {
                    // HAPUS ATAU KOMENTAR BARIS INI DI PRODUCTION
                    // event.preventDefault();
                    // showSuccessPopup();

                    // Validation code
                    const nim = document.getElementById('nim').value;
                    const hp = document.getElementById('hp').value;
                    let isValid = true;

                    // Reset any existing error messages
                    document.querySelectorAll('.error-message').forEach(el => el.remove());

                    // NIM validation - simple example (adjust as needed)
                    if (!/^\d{8,12}$/.test(nim)) {
                        isValid = false;
                        showError('nim', 'NIM harus berupa 8-12 digit angka');
                    }

                    // Phone validation
                    if (!/^(0|\+62)\d{9,12}$/.test(hp)) {
                        isValid = false;
                        showError('hp', 'Nomor HP tidak valid. Gunakan format yang benar (contoh: 08123456789)');
                    }

                    if (!isValid) {
                        event.preventDefault();
                    }
                });

                // Function to show error message
                function showError(fieldId, message) {
                    const field = document.getElementById(fieldId);
                    const errorDiv = document.createElement('div');
                    errorDiv.className = 'error-message';
                    errorDiv.style.color = 'var(--error)';
                    errorDiv.style.fontSize = '14px';
                    errorDiv.style.marginTop = '5px';
                    errorDiv.innerHTML = `<i class="fas fa-exclamation-circle"></i> ${message}`;
                    field.parentNode.appendChild(errorDiv);
                    field.style.borderColor = 'var(--error)';

                    // Remove error when field is focused
                    field.addEventListener('focus', function() {
                        if (errorDiv.parentNode) {
                            errorDiv.parentNode.removeChild(errorDiv);
                        }
                        field.style.borderColor = '';
                    });
                }
            }

            // Success popup functions
            function showSuccessPopup() {
                successPopup.classList.add('active');
                document.body.style.overflow = 'hidden';
            }

            if (closePopupBtn) {
                closePopupBtn.addEventListener('click', function() {
                    successPopup.classList.remove('active');
                    document.body.style.overflow = '';

                    // Optional: Reset form after successful submission
                    if (form) {
                        form.reset();
                    }
                });
            }

            // Close popup if user clicks outside of it
            successPopup.addEventListener('click', function(e) {
                if (e.target === successPopup) {
                    successPopup.classList.remove('active');
                    document.body.style.overflow = '';
                }
            });
        });
    </script>

    <?php include $pathPrefix . 'includes/footer.php'; ?>
</body>

</html>
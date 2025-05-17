<?php
session_start();
$pathPrefix = ''; // Path dari root ke root (no prefix)
$pageTitle = "Konsultasi Usaha"; // Judul spesifik untuk halaman ini

// Cek jika pengguna belum login, redirect ke halaman login
if (!isset($_SESSION['user_id'])) {
    header("Location: " . $pathPrefix . "auth/login.php");
    exit;
}

// Include koneksi database
require $pathPrefix . 'config/db.php';

// Include header user
include $pathPrefix . 'includes/header.php';
?>

<h2 class="section-title" style="text-align: center; position: relative; margin: 20px auto 30px auto; padding-bottom: 10px; width: fit-content;">
    <?php echo $pageTitle; ?>
    <span style="position: absolute; bottom: 0; left: 50%; transform: translateX(-50%); width: 60px; height: 4px; background-color: #f1c40f; border-radius: 2px;"></span>
</h2>

<style>
.chat-container {
    max-width: 700px;
    margin: 20px auto;
    border: 1px solid #ddd;
    border-radius: 10px;
    padding: 15px;
    background-color: #f9f9f9;
    height: 500px;
    display: flex;
    flex-direction: column;
}

.chat-messages {
    flex-grow: 1;
    overflow-y: auto;
    padding: 10px;
    border-bottom: 1px solid #ccc;
}

.message {
    max-width: 70%;
    margin-bottom: 15px;
    padding: 10px 15px;
    border-radius: 20px;
    clear: both;
    word-wrap: break-word;
}

.message.user {
    background-color: #2ecc71;
    color: white;
    align-self: flex-end;
    border-bottom-right-radius: 0;
}

.message.admin {
    background-color: #ecf0f1;
    color: #333;
    align-self: flex-start;
    border-bottom-left-radius: 0;
}

.chat-input {
    display: flex;
    margin-top: 10px;
}

.chat-input input[type="text"] {
    flex-grow: 1;
    padding: 10px 15px;
    border-radius: 20px;
    border: 1px solid #ccc;
    font-size: 1rem;
    outline: none;
}

.chat-input button {
    background-color: #2ecc71;
    border: none;
    color: white;
    padding: 10px 20px;
    margin-left: 10px;
    border-radius: 20px;
    cursor: pointer;
    font-weight: 600;
    transition: background-color 0.3s ease;
}

.chat-input button:hover {
    background-color: #27ae60;
}

.options-container {
    margin-top: 10px;
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
}

.option-button {
    background-color: #3498db;
    color: white;
    border: none;
    padding: 8px 15px;
    border-radius: 20px;
    cursor: pointer;
    font-weight: 600;
    transition: background-color 0.3s ease;
}

.option-button:hover {
    background-color: #2980b9;
}
</style>

<div class="chat-container">
    <div id="chatMessages" class="chat-messages">
        <div class="message admin">Halo! Ada yang bisa kami bantu terkait koperasi mahasiswa?</div>
    </div>
    <div class="options-container" id="optionsContainer">
        <button class="option-button" onclick="selectOption('Bagaimana cara menjadi anggota?')">Bagaimana cara menjadi anggota?</button>
        <button class="option-button" onclick="selectOption('Apa saja layanan yang tersedia?')">Apa saja layanan yang tersedia?</button>
        <button class="option-button" onclick="selectOption('Bagaimana prosedur pengajuan usaha?')">Bagaimana prosedur pengajuan usaha?</button>
    </div>
    <form id="chatForm" class="chat-input" onsubmit="return sendMessage();">
        <input type="text" id="chatInput" placeholder="Tulis pertanyaan Anda..." autocomplete="off" required />
        <button type="submit">Kirim</button>
    </form>
</div>

<script>
const chatMessages = document.getElementById('chatMessages');
const chatInput = document.getElementById('chatInput');
const optionsContainer = document.getElementById('optionsContainer');

function appendMessage(text, sender) {
    const messageDiv = document.createElement('div');
    messageDiv.classList.add('message', sender);
    messageDiv.textContent = text;
    chatMessages.appendChild(messageDiv);
    chatMessages.scrollTop = chatMessages.scrollHeight;
}

function selectOption(text) {
    appendMessage(text, 'user');
    optionsContainer.style.display = 'none';
    setTimeout(() => {
        const reply = getReply(text);
        appendMessage(reply, 'admin');
        optionsContainer.style.display = 'flex';
    }, 1000);
}

function sendMessage() {
    const text = chatInput.value.trim();
    if (text === '') return false;
    appendMessage(text, 'user');
    chatInput.value = '';
    optionsContainer.style.display = 'none';
    setTimeout(() => {
        const reply = getReply(text);
        appendMessage(reply, 'admin');
        optionsContainer.style.display = 'flex';
    }, 1000);
    return false;
}

function getReply(userText) {
    const lowerText = userText.toLowerCase();
    if (lowerText.includes('anggota')) {
        return 'Untuk menjadi anggota, Anda dapat mendaftar melalui formulir pendaftaran anggota di situs kami.';
    } else if (lowerText.includes('layanan')) {
        return 'Kami menyediakan layanan pengajuan usaha, konsultasi, dan marketplace untuk anggota koperasi.';
    } else if (lowerText.includes('prosedur') || lowerText.includes('pengajuan')) {
        return 'Prosedur pengajuan usaha meliputi pengisian formulir, verifikasi, dan persetujuan dari pengurus koperasi.';
    } else {
        return 'Terima kasih atas pertanyaannya. Kami akan segera menghubungi Anda untuk informasi lebih lanjut.';
    }
}
</script>

<?php
// Include footer user
include $pathPrefix . 'includes/footer.php';

if(isset($conn)) $conn->close();
?>

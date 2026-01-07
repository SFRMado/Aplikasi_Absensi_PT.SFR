<?php
session_start();

// ✅ Sudah login → langsung dashboard
if (!empty($_SESSION['pegawai_logged']) && $_SESSION['pegawai_logged'] === true) {
    header('Location: dashboard.php');
    exit;
}


// ================== SECURITY HEADERS ==================
header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: no-referrer');
header(
    "Content-Security-Policy: default-src 'self'; ".
    "script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net; ".
    "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; ".
    "font-src https://fonts.gstatic.com; ".
    "img-src 'self' blob: data:; ".
    "media-src 'self' blob:; ".
    "connect-src 'self';"
);

// ================== CSRF & RATE LIMIT ==================
$_SESSION['csrf_token']     ??= bin2hex(random_bytes(32));
$_SESSION['login_attempts'] ??= 0;

// ================== DEPENDENCIES ==================
require_once __DIR__.'/config/koneksi.php';
require_once __DIR__.'/config/proses-login.php';
require_once __DIR__.'/config/auth.php';
// ================== HANDLE LOGIN ==================
$loginError = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $result = prosesLoginPegawai($_POST);

    if ($result === true) {
        // ✅ LOGIN SUKSES
        $_SESSION['pegawai_logged'] = true;

        header('Location: dashboard.php');
        exit;
    }

    // ❌ LOGIN GAGAL
    $loginError = $result;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Login Admin | PT. SFR</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Google Font -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">

<style>
/* ===== RESET & BASE ===== */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: 'Poppins', sans-serif;
}

body {
    min-height: 100vh;
    overflow: hidden;
    background: radial-gradient(circle at top, #0f2027, #000000);
    color: #fff;
}

canvas {
    position: fixed;
    inset: 0;
    z-index: 1;
}

.container {
    position: relative;
    z-index: 2;
    min-height: 100vh;
    display: flex;
    justify-content: center;
    align-items: center;
    padding: 20px;
}

/* ===== LOGIN BOX ===== */
.login-box {
    width: 100%;
    max-width: 420px;
    background: rgba(255,255,255,0.1);
    backdrop-filter: blur(15px);
    border-radius: 22px;
    padding: 45px 40px;
    box-shadow: 0 25px 60px rgba(0,0,0,0.6);
    animation: fadeZoom 1.1s ease;
    transition: all 0.5s ease;
}

.login-box.hidden {
  opacity: 0;
  pointer-events: none;
  transform: scale(0.95);
  filter: blur(3px);
  display: none
}


/* ===== TEXT ===== */
.login-box h1 {
    text-align: center;
    font-size: 2rem;
    margin-bottom: 10px;
    background: linear-gradient(135deg, #ff512f, #dd2476);
    background-clip: text;
    -webkit-background-clip: text;
    color: transparent;
    letter-spacing: 1px;
}

.login-box p {
    text-align: center;
    font-size: 0.95rem;
    opacity: 0.85;
    margin-bottom: 35px;
}

/* ===== FORM ===== */
.form-group {
    margin-bottom: 22px;
}

.form-group label {
    display: block;
    font-size: 0.85rem;
    margin-bottom: 6px;
    opacity: 0.85;
}

.form-group input {
    width: 100%;
    padding: 14px 16px;
    border-radius: 14px;
    border: none;
    outline: none;
    background: rgba(255,255,255,0.15);
    color: #fff;
    font-size: 0.95rem;
    transition: 0.3s;
}

.form-group input:focus {
    background: rgba(255,255,255,0.25);
    box-shadow: 0 0 8px rgba(255,77,77,0.6);
}

.form-group input::placeholder {
    color: rgba(255,255,255,0.6);
}

/* ===== BUTTON ===== */
.btn-login {
    width: 100%;
    margin-top: 10px;
    padding: 14px;
    border-radius: 16px;
    border: none;
    font-size: 1rem;
    font-weight: 600;
    cursor: pointer;
    color: #fff;
    background: linear-gradient(135deg, #ff512f, #dd2476);
    transition: 0.4s;
}

.btn-login:hover {
    transform: translateY(-3px);
    box-shadow: 0 12px 30px rgba(221,36,118,0.5);
}

/* ===== BACK LINK ===== */
.back-link {
    margin-top: 25px;
    text-align: center;
    font-size: 0.85rem;
}

.back-link a {
    color: #ff9bd5;
    text-decoration: none;
}

/* ===== ANIMATIONS ===== */
@keyframes fadeZoom {
    from { opacity: 0; transform: scale(0.85); }
    to { opacity: 1; transform: scale(1); }
}

/* ===== FLOATING SUCCESS (POSISI TENGAH LAYAR) ===== */
.floating-success {
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%) scale(0.7);
    background: linear-gradient(135deg,#00ffcc,#00c6ff);
    color:#003344;
    padding:14px 26px;
    border-radius:18px;
    font-weight:600;
    font-size:1rem;
    box-shadow:0 20px 45px rgba(0,255,200,0.45);
    z-index:10000;
    opacity:0;
    pointer-events:none;
    transition: all 0.8s ease;
}

.floating-success.show {
    opacity:1;
    transform: translate(-50%, -50%) scale(1);
}

.floating-success.hide {
    opacity:0;
    transform: translate(-50%, -50%) scale(0.5);
}


/* ===== WATCH DOGS VERIFY OVERLAY ===== */
#reVerify {
  position: fixed;
  inset: 0;
  background:
    repeating-linear-gradient(
      0deg,
      rgba(0,255,255,0.06),
      rgba(0,255,255,0.06) 1px,
      transparent 1px,
      transparent 3px
    ),
    radial-gradient(circle at center, #001a28, #000);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 10050;
  opacity: 0;
  pointer-events: none;
  transition: opacity .5s ease;
}

#reVerify.show {
  opacity: 1;
  pointer-events: all;
}

/* PANEL UTAMA */
.verify-panel {
  width: 420px;
  text-align: left;
  color: #00e5ff;
  font-family: 'Poppins', sans-serif;
}

/* TITLE */
.verify-title {
  font-size: 1.6rem;
  letter-spacing: 4px;
  font-weight: 600;
  margin-bottom: 10px;
  text-shadow: 0 0 12px rgba(0,229,255,.6);
}

@keyframes glitchBlue {
  0% { transform: translate(0); }
  20% { transform: translate(-1px,1px); }
  40% { transform: translate(1px,-1px); }
  60% { transform: translate(-2px,1px); }
  80% { transform: translate(2px,-2px); }
}


/* ===== QR MODAL LAYOUT ===== */
.qr-modal {
  display: none;
  position: fixed;
  inset: 0;
  background: rgba(0,0,0,0.9);
  z-index: 9999;
  align-items: center;
  justify-content: center;
}

.qr-wrapper {
    display: flex;
    gap: 20px;
    max-width: 1100px;
    width: 100%;
}

/* ===== VIDEO BOX ===== */
.qr-video-box {
    flex: 2;
    background: #000;
    border-radius: 20px;
    padding: 15px;
    box-shadow: 0 0 40px rgba(0,255,255,0.15);
}

/* FRAME MENJAGA RASIO KAMERA */
.qr-video-frame {
  width: 100%;
  max-width: 360px;
  aspect-ratio: 3 / 4; /* portrait camera */
  background: #000;
  border-radius: 22px;
  overflow: hidden;
  box-shadow:
    0 0 0 3px rgba(0,255,255,0.25),
    0 0 35px rgba(0,255,255,0.35);
  position: relative;
}

/* VIDEO */
.qr-video-frame video {
    width: 100%;
    height: 420px;
    object-fit: cover;
    border-radius: 15px;
    background: #000;
}


/* ===== SIDE PANELS ===== */
.qr-side-panels {
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 15px;
}

/* PANEL CARD */
.qr-panel {
    background: rgba(20,20,20,0.85);
    backdrop-filter: blur(10px);
    border-radius: 15px;
    padding: 15px;
    box-shadow: 0 0 25px rgba(0,0,0,0.6);
}

.qr-panel h4 {
    margin: 0 0 8px;
    color: #00e5ff;
    font-size: 14px;
}

.qr-panel p,
.qr-panel li {
    font-size: 13px;
    color: #ccc;
}

.qr-panel ul {
    padding-left: 18px;
    margin: 5px 0 0;
}

/* CLOSE BUTTON */
.qr-close-btn {
  margin-top: auto;
  padding: 12px;
  border-radius: 14px;
  border: none;
  cursor: pointer;
  font-weight: 600;
  background: linear-gradient(135deg,#ff512f,#dd2476);
  color: #fff;
}

/* ANIMATION */
@keyframes slideInRight {
  from { opacity: 0; transform: translateX(30px); }
  to { opacity: 1; transform: translateX(0); }
}

/* RESPONSIVE */
@media (max-width: 768px) {
  .qr-wrapper {
    flex-direction: column;
  }
  .qr-side-panels {
    width: 100%;
  }
}
.scan-border {
  position: absolute;
  inset: 18px;
  border-radius: 16px;
  border: 2px dashed rgba(0,255,255,0.7);
  animation: pulseScan 2s infinite;
  pointer-events: none;
}

@keyframes pulseScan {
  0% { opacity: 0.4; }
  50% { opacity: 1; }
  100% { opacity: 0.4; }
}
</style>
</head>
<body>
<canvas id="bg"></canvas>

<!-- FLOATING SUCCESS -->
<div id="floatingSuccess" class="floating-success">✅ Login berhasil! Mengalihkan ke dashboard...</div>

<div id="reVerify">
  <div class="verify-box">
    <h2 id="verifyTitle">SCANNING CREDENTIALS</h2>

    <p id="verifyDesc">
      Encrypting credentials · Validating token · Syncing access
    </p>
  </div>
</div>





<div class="container">
    <div class="login-box" id="loginBox">
        <h1>Login</h1>

        <?php if (!empty($loginError)): ?>
            <div style="margin-bottom:15px;padding:10px;border-radius:10px;background:rgba(255,0,0,0.15);text-align:center;font-size:0.85rem;">
                ❌ <?php echo htmlspecialchars($loginError); ?>
            </div>
        <?php endif; ?>

        <form method="post" action="" autocomplete="off" id="loginForm">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">

            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" inputmode="text" minlength="4" maxlength="50" autocomplete="off" placeholder="Masukkan username" required>
            </div>

            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" minlength="6" maxlength="100" autocomplete="new-password" placeholder="Masukkan password" required>
            </div>

            <button class="btn-login" id="btnLogin">Login</button>
            <button type="button" class="btn-login" id="btnQR" style="margin-top:12px;background:linear-gradient(135deg,#00c6ff,#0072ff)">Login via Scan QR Code</button>
            <p style="margin-top:12px;font-size:0.8rem;opacity:0.7">
                Percobaan login: <?php echo $_SESSION['login_attempts']; ?>/5
            </p>
        </form>

        <div class="back-link">
            <a href="../opsi-login.php">← Kembali ke pilihan login</a>
        </div>

        <!-- QR Login Modal -->
<div id="qrModal" class="qr-modal">
  <div class="qr-wrapper">

    <!-- VIDEO CAMERA -->
  <div class="qr-video-box">
  <div class="qr-video-frame">
    <div class="scan-border"></div>
    <video id="qrVideo" autoplay muted></video>
  </div>
  </div>


    <!-- RIGHT FLOATING PANELS -->
    <div class="qr-side-panels">

      <!-- PANEL LOKASI -->
      <div class="qr-panel">
        <h4>📍 Lokasi Anda saat ini</h4>
        <p id="qrLocation">
          Mengambil lokasi...
        </p>
      </div>

      <!-- PANEL INSTRUKSI -->
      <div class="qr-panel">
        <h4>📖 Instruksi Scan</h4>
        <ul>
          <li>Pastikan QR terlihat jelas</li>
          <li>Arahkan QR ke tengah kamera</li>
          <li>Jangan terlalu dekat / jauh</li>
          <li>Gunakan pencahayaan cukup</li>
        </ul>
      </div>

      <button class="qr-close-btn" onclick="closeQR()">Tutup</button>
    </div>
  </div>
</div>
<canvas id="qrCanvas" hidden></canvas>
<script src="https://cdn.jsdelivr.net/npm/jsqr/dist/jsQR.js"></script>
<script>
/* =====================================================
   BACKGROUND HEX ANIMATION
===================================================== */
const canvas = document.getElementById('bg');
const ctx = canvas.getContext('2d');

function resize() {
  canvas.width = window.innerWidth;
  canvas.height = window.innerHeight;
}
resize();
window.addEventListener('resize', resize);

const MAX_HEX = 120;
const SPAWN_INTERVAL = 180;
const HEX_SIZE_MIN = 6;
const HEX_SIZE_MAX = 14;

class Hexagon {
  constructor() { this.reset(); }
  reset() {
    this.size = rand(HEX_SIZE_MIN, HEX_SIZE_MAX);
    this.x = Math.random() * canvas.width;
    this.y = -this.size * 2;
    this.speed = rand(0.3, 0.8);
    this.alpha = 0;
    this.life = 0;
    this.maxLife = rand(300, 500);
    this.glow = rand(8, 16);
  }
  update() {
    this.y += this.speed;
    this.life++;
    if (this.alpha < 1 && this.life < 80) this.alpha += 0.015;
    if (this.life > this.maxLife - 120) this.alpha -= 0.01;
    if (this.alpha <= 0 || this.y > canvas.height + 50) this.reset();
  }
  draw() {
    ctx.save();
    ctx.globalAlpha = this.alpha;
    ctx.strokeStyle = '#00f6ff';
    ctx.lineWidth = 1;
    ctx.shadowColor = '#00f6ff';
    ctx.shadowBlur = this.glow;
    drawHexagon(this.x, this.y, this.size);
    ctx.restore();
  }
}

function rand(min, max) { return Math.random() * (max - min) + min; }

function drawHexagon(x, y, size) {
  ctx.beginPath();
  for (let i = 0; i <= 6; i++) {
    const a = (Math.PI * 2 / 6) * i + Math.PI / 6;
    const px = x + size * Math.cos(a);
    const py = y + size * Math.sin(a);
    i === 0 ? ctx.moveTo(px, py) : ctx.lineTo(px, py);
  }
  ctx.closePath();
  ctx.stroke();
}

const hexagons = [];
let lastSpawn = 0;
function animate(time) {
  ctx.clearRect(0, 0, canvas.width, canvas.height);
  if (hexagons.length < MAX_HEX && time - lastSpawn > SPAWN_INTERVAL) {
    hexagons.push(new Hexagon());
    lastSpawn = time;
  }
  hexagons.forEach(h => { h.update(); h.draw(); });
  requestAnimationFrame(animate);
}
requestAnimationFrame(animate);

/* =====================================================
   QR LOGIN SYSTEM (FIXED & STABLE)
===================================================== */
const btnQR   = document.getElementById('btnQR');
const qrModal = document.getElementById('qrModal');
const video   = document.getElementById('qrVideo');

let stream = null;
let scanning = false;
let verifyingQR = false;

const qrCanvas = document.createElement('canvas');
const qrCtx = qrCanvas.getContext('2d', { willReadFrequently: true });

btnQR.addEventListener('click', async () => {
  if (verifyingQR) return;

  qrModal.style.display = 'flex';
  scanning = true;

  stream = await navigator.mediaDevices.getUserMedia({
    video: { facingMode: 'environment', width: 640, height: 480 }
  });

  video.srcObject = stream;
  video.setAttribute('playsinline', true);
  await video.play();

  getUserLocation();
  requestAnimationFrame(scanQR);
});

function scanQR() {
  if (!scanning || verifyingQR) return;

  if (video.readyState === video.HAVE_ENOUGH_DATA) {
    qrCanvas.width = video.videoWidth;
    qrCanvas.height = video.videoHeight;
    qrCtx.drawImage(video, 0, 0);

    const img = qrCtx.getImageData(0, 0, qrCanvas.width, qrCanvas.height);
    const code = jsQR(img.data, img.width, img.height);

    if (code && code.data) {
      scanning = false;
      stopQR();
      handleQRResult(code.data);
      return;
    }
  }
  requestAnimationFrame(scanQR);
}

function handleQRResult(qrData) {
  console.log('QR TERDETEKSI:', qrData);

  fetch('config/proses-qr-login.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({
      qr: qrData 
    })
  })
  .then(res => res.json())
  .then(data => {
    if (data.success) {
      window.location.replace(data.redirect);
    } else {
      alert(data.message || 'QR tidak valid');
      closeQR();
    }
  })
  .catch(err => {
    console.error(err);
    alert('Gagal memverifikasi QR');
    closeQR();
  });
}

function stopQR() {
  scanning = false;
  if (stream) stream.getTracks().forEach(t => t.stop());
}

function closeQR() {
  stopQR();
  qrModal.style.display = 'none';
}

/* =====================================================
   FORM LOGIN (LOCKED WHEN QR ACTIVE)
===================================================== */
const form = document.getElementById('loginForm');
const reVerify = document.getElementById('reVerify');
const loginBox = document.getElementById('loginBox');
const floatingSuccess = document.getElementById('floatingSuccess');

const verifyTitle = document.getElementById('verifyTitle');
const verifyDesc  = document.getElementById('verifyDesc');

let isSubmitting = false;

form.addEventListener('submit', e => {
  if (isSubmitting || verifyingQR) return;

  e.preventDefault();
  isSubmitting = true;

  loginBox.classList.add('hidden');
  reVerify.classList.add('show');

  verifyTitle.textContent = 'SCANNING CREDENTIALS';
  verifyDesc.textContent  = 'Encrypting credentials · Validating token';

  setTimeout(() => {
    verifyTitle.textContent = 'ESTABLISHING SECURE SESSION';
    verifyDesc.textContent = 'Authorizing access · Syncing session';
  }, 1500);

  setTimeout(() => {
    verifyTitle.textContent = 'ACCESS GRANTED';
    verifyDesc.textContent = 'Login verified successfully';
  }, 3200);

  setTimeout(() => {
    reVerify.classList.remove('show');
    floatingSuccess.classList.add('show');
    setTimeout(() => form.submit(), 1500);
  }, 4200);
});

/* =====================================================
   GEOLOCATION
===================================================== */
function getUserLocation() {
  const locEl = document.getElementById('qrLocation');
  if (!navigator.geolocation) {
    locEl.textContent = 'Geolocation tidak didukung';
    return;
  }
  navigator.geolocation.getCurrentPosition(
    pos => {
      locEl.innerHTML = `Latitude : ${pos.coords.latitude.toFixed(6)}<br>
                         Longitude: ${pos.coords.longitude.toFixed(6)}`;
    },
    () => locEl.textContent = 'Gagal mengambil lokasi',
    { enableHighAccuracy: true, timeout: 8000 }
  );
}
</script>
</body>
</html>
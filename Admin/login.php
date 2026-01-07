<?php
session_set_cookie_params([
    'lifetime' => 0, // session cookie hilang saat browser ditutup
    'path' => '/',
    'httponly' => true,
    'samesite' => 'Lax'
]);
session_start();

// ===== SECURITY HEADERS =====
header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: no-referrer');
header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; font-src https://fonts.gstatic.com; img-src 'self' blob: data:; media-src 'self' blob:; connect-src 'self';");


// ===== CSRF TOKEN =====
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// ===== SIMPLE RATE LIMIT =====
if (!isset($_SESSION['login_attempts'])) {
    $_SESSION['login_attempts'] = 0;
}

// ===== LOAD DEPENDENCIES =====
require_once 'config/koneksi.php';
require_once 'config/proses-login.php';
require_once 'config/auth.php'; // proteksi halaman admin (requireAdminLogin, forceLogout)

// ===== HANDLE LOGIN ADMIN =====
function handleLoginAdmin() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST'
        && isset($_POST['username'], $_POST['password'])
    ) {
        $user = prosesLoginAdmin($_POST); // harus return array user

        if (is_array($user)) {
            session_regenerate_id(true); // 🔐 anti session fixation
            $_SESSION['admin_logged']   = true;
            $_SESSION['admin_id']       = $user['id_user'];
            $_SESSION['username']       = $user['username'];
            $_SESSION['nama_lengkap']   = $user['nama_lengkap'];
            $_SESSION['last_activity']  = time();
            $_SESSION['login_success'] = true;
           return null; // sukses login
        } else {
            // ===== LOGIN GAGAL =====
            return $user; // string error
        }


        return $user; // string error
    }

    return null;
}

$loginError = handleLoginAdmin();
?>
<?php
if (!empty($_SESSION['login_success'])) {
    unset($_SESSION['login_success']);
    header('Location: dashboard.php');
    exit;
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


/* ===== RE VERIFY OVERLAY ala RESIDENT EVIL ===== */
#reVerify {
    position: fixed;
    inset: 0;
    background: rgba(20,0,0,0.65);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 10050;
    pointer-events: none;
    opacity: 0;
    transform: scale(1.05);
    transition: opacity 0.6s ease, transform 0.6s ease;
    flex-direction: column;
    color: #ff0000;
    font-family: 'Poppins', sans-serif;
    letter-spacing: 2px;
    text-align: center;
}

#reVerify.show {
    opacity: 1;
    pointer-events: all;
    transform: scale(1);
}

#reVerify h2 {
    font-size: 2rem;
    font-weight: 700;
    margin-bottom: 20px;
    text-shadow:
        0 0 8px #ff0000,
        0 0 16px #ff4d4d,
        0 0 24px #ff0000;
    animation: blinkRed 1s infinite alternate, glitchText 0.2s infinite;
}

#reVerify .loader {
    width: 120px;
    height: 20px;
    background: #330000;
    border-radius: 10px;
    overflow: hidden;
    margin-bottom: 15px;
    box-shadow: 0 0 15px #ff0000 inset;
}

#reVerify .loader span {
    display: block;
    height: 100%;
    width: 0%;
    background: linear-gradient(90deg, #ff0000, #ff4d4d);
    border-radius: 10px;
    animation: loading 4s linear forwards;
}

/* ===== Animations ===== */
@keyframes loading {
    0% { width: 0; }
    100% { width: 100%; }
}

@keyframes blinkRed {
    0% { opacity: 1; }
    50% { opacity: 0.3; }
    100% { opacity: 1; }
}

/* ===== Text Glitch ala Resident Evil ===== */
@keyframes glitchText {
    0% { transform: translate(0); }
    20% { transform: translate(-1px, 1px); }
    40% { transform: translate(1px, -1px); }
    60% { transform: translate(-2px, 1px); }
    80% { transform: translate(2px, -2px); }
    100% { transform: translate(0); }
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
/* =========================================================
   RESPONSIVE ENHANCEMENT — MOBILE / ANDROID FIRST
   ========================================================= */

/* ===== GLOBAL MOBILE SAFETY ===== */
html, body {
  width: 100%;
  height: 100%;
  overflow-x: hidden;
  -webkit-text-size-adjust: 100%;
  touch-action: manipulation;
}

/* ===== CONTAINER ===== */
@media (max-width: 576px) {
  .container {
    padding: 15px;
    align-items: flex-start;
    padding-top: 40px;
  }
}

/* ===== LOGIN BOX RESPONSIVE ===== */
@media (max-width: 576px) {
  .login-box {
    max-width: 100%;
    padding: 32px 22px;
    border-radius: 18px;
  }

  .login-box h1 {
    font-size: 1.6rem;
  }

  .login-box p {
    font-size: 0.85rem;
    margin-bottom: 28px;
  }
}

/* ===== FORM INPUT MOBILE ===== */
@media (max-width: 576px) {
  .form-group label {
    font-size: 0.8rem;
  }

  .form-group input {
    padding: 13px 14px;
    font-size: 0.9rem;
    border-radius: 12px;
  }
}

/* ===== BUTTON MOBILE TOUCH FRIENDLY ===== */
@media (max-width: 576px) {
  .btn-login {
    padding: 14px;
    font-size: 0.95rem;
    border-radius: 14px;
  }
}

/* ===== BACK LINK ===== */
@media (max-width: 576px) {
  .back-link {
    font-size: 0.8rem;
  }
}

/* ===== FLOATING SUCCESS MOBILE ===== */
@media (max-width: 576px) {
  .floating-success {
    font-size: 0.9rem;
    padding: 12px 20px;
    border-radius: 14px;
  }
}

/* =========================================================
   RE-VERIFY OVERLAY MOBILE
   ========================================================= */
@media (max-width: 576px) {
  #reVerify h2 {
    font-size: 1.4rem;
    letter-spacing: 1px;
  }

  #reVerify .loader {
    width: 90px;
    height: 16px;
  }
}

/* =========================================================
   QR MODAL — MOBILE / ANDROID
   ========================================================= */
@media (max-width: 768px) {
  .qr-modal {
    padding: 15px;
  }

  .qr-wrapper {
    flex-direction: column;
    gap: 15px;
    max-width: 100%;
  }

  .qr-video-box {
    padding: 12px;
    border-radius: 16px;
  }

  .qr-video-frame {
    max-width: 100%;
    aspect-ratio: 3 / 4;
    border-radius: 18px;
  }

  .qr-video-frame video {
    height: 100%;
    border-radius: 16px;
  }
}

/* ===== SIDE PANELS MOBILE ===== */
@media (max-width: 768px) {
  .qr-side-panels {
    flex-direction: column;
    gap: 12px;
  }

  .qr-panel {
    padding: 14px;
    border-radius: 14px;
  }

  .qr-panel h4 {
    font-size: 13px;
  }

  .qr-panel p,
  .qr-panel li {
    font-size: 12.5px;
  }
}

/* ===== CLOSE BUTTON MOBILE ===== */
@media (max-width: 768px) {
  .qr-close-btn {
    padding: 13px;
    font-size: 0.9rem;
    border-radius: 14px;
  }
}

/* =========================================================
   EXTRA SMALL DEVICES (≤ 360px)
   ========================================================= */
@media (max-width: 360px) {
  .login-box {
    padding: 28px 18px;
  }

  .login-box h1 {
    font-size: 1.45rem;
  }

  .btn-login {
    font-size: 0.9rem;
  }

  .floating-success {
    font-size: 0.85rem;
  }
}

/* =========================================================
   TABLET OPTIMIZATION
   ========================================================= */
@media (min-width: 769px) and (max-width: 1024px) {
  .login-box {
    max-width: 380px;
  }

  .qr-wrapper {
    max-width: 900px;
  }
}

/* =========================================================
   LARGE DESKTOP STABILITY
   ========================================================= */
@media (min-width: 1400px) {
  .login-box {
    max-width: 440px;
  }

  .qr-wrapper {
    max-width: 1200px;
  }
}

</style>
</head>
<body>
<canvas id="bg"></canvas>

<!-- FLOATING SUCCESS -->
<div id="floatingSuccess" class="floating-success">✅ Login berhasil! Mengalihkan ke dashboard...</div>

<!-- RE-VERIFY OVERLAY ala Resident Evil -->
<div id="reVerify">
    <h2>MEM-VERIFIKASI LOGIN</h2>
    <div class="loader"><span></span></div>
    <p style="opacity:0.8;font-size:0.85rem;letter-spacing:1px;">Harap tunggu, sistem sedang mem-verifikasi kredensial login...</p>
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
// ===== CANVAS PARTICLES BACKGROUND =====
const canvas = document.getElementById('bg');
const ctx = canvas.getContext('2d');

function resize() {
  canvas.width = window.innerWidth;
  canvas.height = window.innerHeight;
}
resize();
window.addEventListener('resize', resize);

const particles = [];
const colors = ['#ff512f', '#dd2476', '#ff9bd5'];

class Particle {
  constructor() { this.reset(); }
  reset() {
    this.x = Math.random() * canvas.width;
    this.y = Math.random() * canvas.height;
    this.radius = Math.random() * 2 + 1;
    this.speedY = Math.random() * 1.5 + 0.5;
    this.alpha = Math.random();
    this.color = colors[Math.floor(Math.random() * colors.length)];
  }
  update() {
    this.y -= this.speedY;
    if (this.y < 0) this.reset();
  }
  draw() {
    ctx.beginPath();
    ctx.globalAlpha = this.alpha;
    ctx.fillStyle = this.color;
    ctx.arc(this.x, this.y, this.radius, 0, Math.PI * 2);
    ctx.fill();
    ctx.globalAlpha = 1;
  }
}

for (let i = 0; i < 120; i++) particles.push(new Particle());
(function animate(){
  ctx.clearRect(0, 0, canvas.width, canvas.height);
  particles.forEach(p => { p.update(); p.draw(); });
  requestAnimationFrame(animate);
})();

// ==================================================
// ================== QR LOGIN ======================
// ==================================================
const btnQR   = document.getElementById('btnQR');
const qrModal = document.getElementById('qrModal');
const video   = document.getElementById('qrVideo');

let stream;
let scanning = false;

const qrCanvas = document.createElement('canvas');
const qrCtx = qrCanvas.getContext('2d', {
  willReadFrequently: true
});
btnQR.addEventListener('click', async () => {
  qrModal.style.display = 'flex';

  stream = await navigator.mediaDevices.getUserMedia({
    video: {
      facingMode: 'environment',
      width: { ideal: 640 },
      height: { ideal: 480 }
    }
  });

  video.srcObject = stream;
  video.setAttribute('playsinline', true);
  await video.play();
  getUserLocation();
  scanning = true;
  requestAnimationFrame(scanQR);
});

function getUserLocation() {
  const locEl = document.getElementById('qrLocation');

  if (!navigator.geolocation) {
    locEl.textContent = 'Geolocation tidak didukung';
    return;
  }

  navigator.geolocation.getCurrentPosition(
    pos => {
      const lat = pos.coords.latitude.toFixed(6);
      const lng = pos.coords.longitude.toFixed(6);
      locEl.innerHTML = `
        Latitude : ${lat}<br>
        Longitude: ${lng}
      `;
    },
    () => {
      locEl.textContent = 'Gagal mengambil lokasi';
    },
    { enableHighAccuracy: true, timeout: 8000 }
  );
}

function scanQR() {
  if (!scanning) return;

  if (video.readyState === video.HAVE_ENOUGH_DATA) {
    qrCanvas.width  = video.videoWidth;
    qrCanvas.height = video.videoHeight;

    qrCtx.drawImage(video, 0, 0, qrCanvas.width, qrCanvas.height);

    const imageData = qrCtx.getImageData(
      0, 0,
      qrCanvas.width,
      qrCanvas.height
    );

    const code = jsQR(
      imageData.data,
      imageData.width,
      imageData.height
    );

    if (code) {
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

const form = document.getElementById('loginForm');
const reVerify = document.getElementById('reVerify');
const loginBox = document.getElementById('loginBox');
const floatingSuccess = document.getElementById('floatingSuccess');

let isSubmitting = false; // ⛔ cegah double submit

form.addEventListener('submit', function (e) {
  if (isSubmitting) return;
  isSubmitting = true;

  e.preventDefault();

  // 1. Sembunyikan form login
  loginBox.classList.add('hidden');

  // 2. Tampilkan overlay verifikasi
  reVerify.classList.add('show');

  // 3. Reset & jalankan loader
  const loaderSpan = reVerify.querySelector('.loader span');
  loaderSpan.style.animation = 'none';
  void loaderSpan.offsetWidth;
  loaderSpan.style.animation = 'loading 4s linear forwards';

  // 4. Setelah animasi verifikasi selesai
  setTimeout(() => {
    reVerify.classList.remove('show');

    // 5. Tampilkan floating success
    floatingSuccess.classList.add('show');

    // 6. Tahan agar user MELIHAT pesan sukses
    setTimeout(() => {
      form.submit(); // ➜ PHP redirect ke dashboard.php
    }, 1500);

  }, 4000);
});
// ===== AUTO LOGOUT =====
function autoLogout() {
  navigator.sendBeacon('config/logout.php');
}
window.addEventListener('beforeunload', autoLogout);
window.addEventListener('pagehide', autoLogout);
</script>

</body>
</html>
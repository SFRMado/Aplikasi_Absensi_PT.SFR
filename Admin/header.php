<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

require_once 'config/koneksi.php';

/* ================== VALIDASI LOGIN ================== */
if (!isset($_SESSION['admin_logged'], $_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

/* ================== DATA ADMIN ================== */
$namaAdmin = $_SESSION['nama_lengkap'] ?? 'Admin';
$fotoUser  = 'assets/img/default-avatar.png';

/* ================== AMBIL FOTO DARI TABEL USERS ================== */
try {
    if (isset($pdo) && $pdo instanceof PDO) {
        $stmt = $pdo->prepare("
            SELECT foto 
            FROM users 
            WHERE id_user = :id
            LIMIT 1
        ");
        $stmt->execute([
            'id' => $_SESSION['admin_id']
        ]);

        $foto = $stmt->fetchColumn();

        if (!empty($foto) && file_exists($foto)) {
            $fotoUser = $foto;
        }
    }
} catch (Throwable $e) {
    // fail-safe: pakai default avatar
}
?>

<style>
/* ================== HAMBURGER ================== */
.hamburger {
    position: fixed;
    top: 18px;
    left: 18px;
    z-index: 1300;
    width: 36px;
    height: 28px;
    cursor: pointer;
}

.hamburger span {
    display: block;
    height: 4px;
    width: 100%;
    background: #7e7d7d70;
    margin: 6px 0;
    border-radius: 2px;
    transition: all .3s ease;
}

/* HAMBURGER → X */
.hamburger.active span:nth-child(1) {
    transform: translateY(10px) rotate(45deg);
}
.hamburger.active span:nth-child(2) {
    opacity: 0;
}
.hamburger.active span:nth-child(3) {
    transform: translateY(-10px) rotate(-45deg);
}

/* ================== SIDEBAR (DEFAULT HIDDEN) ================== */
.sidebar {
    position: fixed;
    top: 0;
    left: 0;

    width: 220px;
    height: 100vh;

    background: #1e272e;
    color: #fff;
    padding: 20px;

    transform: translateX(-120%);
    opacity: 0;
    visibility: hidden;
    pointer-events: none;

    transition: transform .35s ease, opacity .25s ease;
    z-index: 1200;
}

.sidebar.active {
    transform: translateX(0);
    opacity: 1;
    visibility: visible;
    pointer-events: auto;
}

/* ================== SIDEBAR MENU ================== */
.sidebar-title {
    text-align: center;
    margin-bottom: 28px;
    font-size: 18px;
}

.sidebar-menu a {
    display: block;
    padding: 12px 10px;
    margin-bottom: 6px;

    color: #fff;
    text-decoration: none;
    border-radius: 6px;

    transition: background .2s ease, transform .15s ease;
}

.sidebar-menu a:hover {
    background: #485460;
    transform: translateX(4px);
}

.sidebar-menu a.active {
    background: #0984e3;
}

/* ================== HEADER FLOAT ================== */
.admin-header {
    position: fixed;
    top: 16px;
    right: 24px;
    z-index: 1300;

    display: flex;
    align-items: center;
    gap: 14px;

    background: #fff;
    padding: 10px 16px;
    border-radius: 10px;
    box-shadow: 0 6px 18px rgba(0,0,0,.12);

    opacity: 0;
    transform: translateY(-15px);
    animation: headerFade .7s ease forwards;
}

@keyframes headerFade {
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* ================== HEADER CONTENT ================== */
.welcome-text {
    font-size: 14px;
    font-weight: 600;
    white-space: nowrap;
}

.admin-avatar {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    border: 2px solid #0984e3;
    object-fit: cover;
}

.btn-logout {
    background: #d63031;
    color: #fff;
    padding: 6px 12px;
    border-radius: 6px;
    text-decoration: none;
    font-size: 12px;
    transition: background .2s ease;
}

.btn-logout:hover {
    background: #b02324;
}

/* ================== OVERLAY ================== */
.sidebar-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,.35);
    opacity: 0;
    visibility: hidden;
    transition: .3s;
    z-index: 1100;
}

.sidebar-overlay.active {
    opacity: 1;
    visibility: visible;
}
/* =======================================================
   GLOBAL MOBILE SAFETY
   ======================================================= */
html, body {
  width: 100%;
  overflow-x: hidden;
  touch-action: manipulation;
}

/* =======================================================
   HAMBURGER RESPONSIVE
   ======================================================= */
@media (max-width: 576px) {
  .hamburger {
    top: 14px;
    left: 14px;
    width: 34px;
    height: 26px;
  }

  .hamburger span {
    height: 3px;
    margin: 5px 0;
  }

  .hamburger.active span:nth-child(1) {
    transform: translateY(9px) rotate(45deg);
  }

  .hamburger.active span:nth-child(3) {
    transform: translateY(-9px) rotate(-45deg);
  }
}

/* =======================================================
   SIDEBAR RESPONSIVE
   ======================================================= */
@media (max-width: 576px) {
  .sidebar {
    width: 78vw;
    max-width: 280px;
    padding: 18px;
  }

  .sidebar-title {
    font-size: 16px;
    margin-bottom: 22px;
  }

  .sidebar-menu a {
    padding: 14px 12px;
    font-size: 14px;
  }
}

/* EXTRA SMALL DEVICES */
@media (max-width: 360px) {
  .sidebar {
    width: 85vw;
  }

  .sidebar-menu a {
    font-size: 13px;
    padding: 13px 10px;
  }
}

/* =======================================================
   TABLET OPTIMIZATION
   ======================================================= */
@media (min-width: 577px) and (max-width: 1024px) {
  .sidebar {
    width: 240px;
  }
}

/* =======================================================
   HEADER FLOAT RESPONSIVE
   ======================================================= */
@media (max-width: 768px) {
  .admin-header {
    top: 12px;
    right: 12px;
    padding: 8px 12px;
    gap: 10px;
    border-radius: 8px;
  }

  .welcome-text {
    font-size: 13px;
    max-width: 120px;
    overflow: hidden;
    text-overflow: ellipsis;
  }

  .admin-avatar {
    width: 32px;
    height: 32px;
  }

  .btn-logout {
    padding: 6px 10px;
    font-size: 11.5px;
  }
}

/* =======================================================
   VERY SMALL SCREENS
   ======================================================= */
@media (max-width: 360px) {
  .welcome-text {
    display: none;
  }
}

/* =======================================================
   DESKTOP STABILITY
   ======================================================= */
@media (min-width: 1200px) {
  .sidebar {
    width: 220px;
  }

  .admin-header {
    top: 18px;
    right: 24px;
  }
}

/* =======================================================
   OVERLAY MOBILE OPTIMIZATION
   ======================================================= */
@media (max-width: 768px) {
  .sidebar-overlay {
    backdrop-filter: blur(2px);
  }
}

</style>

<!-- ================== HAMBURGER ================== -->
<div class="hamburger" id="hamburgerBtn">
    <span></span>
    <span></span>
    <span></span>
</div>


<!-- ================== SIDEBAR (ONLY ON CLICK) ================== -->
<?php include 'sidebar.php'; ?>
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<!-- ================== HEADER ================== -->
<header class="admin-header">
    <span class="welcome-text">
        👋 Welcome, <?= htmlspecialchars($namaAdmin, ENT_QUOTES, 'UTF-8') ?>
    </span>

    <img src="<?= htmlspecialchars($fotoUser, ENT_QUOTES, 'UTF-8') ?>" class="admin-avatar">

    <a href="logout.php" class="btn-logout">Logout</a>
</header>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const hamburger = document.getElementById('hamburgerBtn');
    const sidebar   = document.querySelector('.sidebar');
    const overlay   = document.getElementById('sidebarOverlay');

    if (!hamburger || !sidebar || !overlay) return;

    // Toggle sidebar
    hamburger.addEventListener('click', () => {
        hamburger.classList.toggle('active');
        sidebar.classList.toggle('active');
        overlay.classList.toggle('active');
    });

    // Klik overlay = tutup sidebar
    overlay.addEventListener('click', () => {
        hamburger.classList.remove('active');
        sidebar.classList.remove('active');
        overlay.classList.remove('active');
    });
});
</script>
<!-- LEAFLET -->
<link
  rel="stylesheet"
  href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
/>
<script
  src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js">
</script>

<style>
/* MAP CONTAINER */
#map {
    width: 100%;
    height: 380px;
    margin-top: 30px;
    border-radius: 16px;
    border: 1px solid #e5e7eb;
    box-shadow: 0 10px 22px rgba(0,0,0,.08);
}
</style>


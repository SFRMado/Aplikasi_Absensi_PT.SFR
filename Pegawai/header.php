<?php
// ================== SESSION ==================
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// ================== VALIDASI LOGIN PEGAWAI ==================
if (
    empty($_SESSION['pegawai_logged']) ||
    $_SESSION['pegawai_logged'] !== true ||
    empty($_SESSION['pegawai_id'])
) {
    header('Location: login.php?auth=required');
    exit;
}

// ================== DB ==================
require_once 'config/koneksi.php';

// ================== DATA PEGAWAI ==================
$namaPegawai = $_SESSION['nama_lengkap'] ?? 'Pegawai';
$fotoUser    = 'assets/img/default-avatar.png';

// ================== FOTO USER ==================
try {
    if (isset($pdo) && $pdo instanceof PDO) {
        $stmt = $pdo->prepare("
            SELECT foto
            FROM users
            WHERE id_user = :id
            LIMIT 1
        ");
        $stmt->execute([
            'id' => $_SESSION['pegawai_id']
        ]);

        $foto = $stmt->fetchColumn();
        if (!empty($foto) && file_exists($foto)) {
            $fotoUser = $foto;
        }
    }
} catch (Throwable $e) {
    // fallback ke avatar default
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
.hamburger.active span:nth-child(1) {
    transform: translateY(10px) rotate(45deg);
}
.hamburger.active span:nth-child(2) {
    opacity: 0;
}
.hamburger.active span:nth-child(3) {
    transform: translateY(-10px) rotate(-45deg);
}

/* ================== SIDEBAR ================== */
.sidebar {
    position: fixed;
    inset: 0 auto 0 0;
    width: 220px;
    background: #1e272e;
    color: #fff;
    padding: 20px;
    transform: translateX(-120%);
    opacity: 0;
    visibility: hidden;
    transition: .35s;
    z-index: 1200;
}
.sidebar.active {
    transform: translateX(0);
    opacity: 1;
    visibility: visible;
}

.sidebar-menu a {
    display: block;
    padding: 12px 10px;
    color: #fff;
    text-decoration: none;
    border-radius: 6px;
}
.sidebar-menu a:hover {
    background: #485460;
}

/* ================== HEADER ================== */
.pegawai-header {
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
}

.pegawai-avatar {
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
}
</style>


<!-- HAMBURGER -->
<div class="hamburger" id="hamburgerBtn">
    <span></span>
    <span></span>
    <span></span>
</div>

<!-- SIDEBAR -->
<?php include 'sidebar.php'; ?>
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<!-- HEADER -->
<header class="pegawai-header">
    <span>👋 Welcome, <?= htmlspecialchars($namaPegawai) ?></span>
    <img src="<?= htmlspecialchars($fotoUser) ?>" class="pegawai-avatar">
    <a href="logout.php" class="btn-logout">Logout</a>
</header>


<script>
document.addEventListener('DOMContentLoaded', () => {
    const hamburger = document.getElementById('hamburgerBtn');
    const sidebar   = document.querySelector('.sidebar');
    const overlay   = document.getElementById('sidebarOverlay');

    if (!hamburger || !sidebar || !overlay) return;

    hamburger.addEventListener('click', () => {
        hamburger.classList.toggle('active');
        sidebar.classList.toggle('active');
        overlay.classList.toggle('active');
    });

    overlay.addEventListener('click', () => {
        hamburger.classList.remove('active');
        sidebar.classList.remove('active');
        overlay.classList.remove('active');
    });
});
</script>



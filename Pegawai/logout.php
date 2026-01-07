<?php
declare(strict_types=1);

// ================== SESSION ==================
session_start();

// ================== DB ==================
require_once __DIR__ . '/config/koneksi.php';

// ================== SIMPAN DATA SEBELUM LOGOUT ==================
$idUser   = $_SESSION['pegawai_id'] ?? null;
$username = $_SESSION['username'] ?? 'unknown';
$role     = $_SESSION['role'] ?? 'pegawai';

// ================== LOG AKTIVITAS LOGOUT ==================
if ($idUser) {
    try {
        $stmt = $pdo->prepare("
            INSERT INTO log_aktivitas
            (id_user, username, role, aktivitas, ip_address, user_agent, status)
            VALUES
            (:id, :username, :role, 'Logout Pegawai', :ip, :agent, 'berhasil')
        ");

        $stmt->execute([
            'id'       => $idUser,
            'username' => $username,
            'role'     => $role,
            'ip'       => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'agent'    => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
        ]);
    } catch (Throwable $e) {
        // Logging gagal tidak boleh menghentikan logout
    }
}

// ================== DESTROY SESSION ==================
$_SESSION = [];

if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params['path'],
        $params['domain'],
        $params['secure'],
        $params['httponly']
    );
}

session_destroy();
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Logout</title>
<meta name="viewport" content="width=device-width, initial-scale=1">

<style>
body {
    margin: 0;
    height: 100vh;
    display: flex;
    justify-content: center;
    align-items: center;
    background: linear-gradient(135deg, #0984e3, #00cec9);
    font-family: 'Segoe UI', Arial, sans-serif;
    color: #fff;
}

.logout-box {
    text-align: center;
    background: rgba(255,255,255,.15);
    padding: 40px 50px;
    border-radius: 18px;
    backdrop-filter: blur(12px);
    box-shadow: 0 20px 40px rgba(0,0,0,.25);
    animation: fadeIn .8s ease forwards;
}

h2 {
    margin-bottom: 12px;
}

p {
    font-size: 14px;
    opacity: .9;
}

/* ===== SPINNER ===== */
.spinner {
    width: 55px;
    height: 55px;
    border: 5px solid rgba(255,255,255,.25);
    border-top: 5px solid #fff;
    border-radius: 50%;
    margin: 20px auto;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: scale(.9);
    }
    to {
        opacity: 1;
        transform: scale(1);
    }
}
</style>

<script>
setTimeout(() => {
    window.location.href = 'login-pegawai.php';
}, 2000);
</script>

</head>
<body>

<div class="logout-box">
    <div class="spinner"></div>
    <h2>Logout Berhasil</h2>
    <p>Anda akan diarahkan ke halaman login...</p>
</div>

</body>
</html>

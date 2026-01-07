<?php
/**
 * logout.php
 * Logout Admin + Log Aktivitas + Absensi Keluar
 */

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

require_once 'config/koneksi.php';

$status   = 'error';
$message  = 'Logout gagal.';
$detail   = '';

try {
    if (empty($_SESSION['admin_logged']) || empty($_SESSION['admin_id'])) {
        throw new Exception('Session login tidak ditemukan atau sudah berakhir.');
    }

    $idUser   = $_SESSION['admin_id'];
    $username = $_SESSION['admin_username'] ?? '-';

    /* ================== UPDATE ABSENSI (JAM KELUAR) ================== */
    $cekAbsen = $pdo->prepare("
        SELECT id_absensi
        FROM absensi
        WHERE id_pegawai = :id
          AND tanggal = CURDATE()
        LIMIT 1
    ");
    $cekAbsen->execute(['id' => $idUser]);
    $absen = $cekAbsen->fetch(PDO::FETCH_ASSOC);

    if ($absen) {
        $update = $pdo->prepare("
            UPDATE absensi
            SET jam_keluar = CURTIME(),
                updated_at = NOW()
            WHERE id_absensi = :id_absen
              AND jam_keluar IS NULL
        ");
        $update->execute(['id_absen' => $absen['id_absensi']]);
    }

    /* ================== LOG AKTIVITAS ================== */
    $log = $pdo->prepare("
        INSERT INTO log_aktivitas
        (id_user, username, role, aktivitas, ip_address, user_agent, status)
        VALUES
        (:id_user, :username, 'admin', 'Logout Admin', :ip, :agent, 'berhasil')
    ");
    $log->execute([
        'id_user' => $idUser,
        'username'=> $username,
        'ip'      => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
        'agent'   => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
    ]);

    /* ================== HANCURKAN SESSION ================== */
    session_unset();
    session_destroy();

    $status  = 'success';
    $message = 'Logout berhasil.';
    $detail  = 'Session ditutup dan absensi dicatat dengan aman.';

} catch (Throwable $e) {

    error_log('[LOGOUT ERROR] ' . $e->getMessage());

    $status  = 'error';
    $message = 'Logout gagal.';
    $detail  = $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Logout</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<style>
/* ================== BASE ================== */
body {
    margin: 0;
    height: 100vh;
    background: radial-gradient(circle at top, #1e272e, #000);
    display: flex;
    justify-content: center;
    align-items: center;
    font-family: 'Segoe UI', sans-serif;
    color: #fff;
    overflow: hidden;
}

/* ================== PROCESS BOX ================== */
.logout-box {
    text-align: center;
    animation: fadeIn 0.6s ease;
}

.spinner {
    width: 64px;
    height: 64px;
    border: 6px solid rgba(255,255,255,.2);
    border-top: 6px solid #00cec9;
    border-radius: 50%;
    margin: 0 auto 18px;
    animation: spin 1s linear infinite;
}

.status-text {
    font-size: 18px;
    letter-spacing: .5px;
    opacity: .9;
}

/* ================== FLOATING MESSAGE ================== */
.floating {
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%) scale(.8);
    padding: 26px 32px;
    border-radius: 14px;
    text-align: center;
    min-width: 300px;
    box-shadow: 0 20px 60px rgba(0,0,0,.45);
    opacity: 0;
    transition: all .6s cubic-bezier(.22,1.28,.32,1);
    z-index: 10;
}

.floating.success {
    background: linear-gradient(135deg, #00b894, #00cec9);
}

.floating.error {
    background: linear-gradient(135deg, #d63031, #ff7675);
}

.floating.show {
    opacity: 1;
    transform: translate(-50%, -50%) scale(1);
}

.floating h3 {
    margin: 0 0 8px;
    font-size: 22px;
}

.floating p {
    margin: 0;
    font-size: 14px;
    opacity: .95;
}

/* ================== ANIM ================== */
@keyframes spin {
    to { transform: rotate(360deg); }
}
@keyframes fadeIn {
    from { opacity: 0; transform: scale(.95); }
    to   { opacity: 1; transform: scale(1); }
}
</style>
</head>

<body>

<div class="logout-box" id="processBox">
    <div class="spinner"></div>
    <div class="status-text">Memproses Logout...</div>
</div>

<div class="floating <?= $status ?>" id="floatMsg">
    <h3><?= htmlspecialchars($message) ?></h3>
    <p><?= htmlspecialchars($detail) ?></p>
</div>

<script>
setTimeout(() => {
    document.getElementById('processBox').style.display = 'none';
    const msg = document.getElementById('floatMsg');
    msg.classList.add('show');
}, 1600);

// Redirect otomatis
setTimeout(() => {
    window.location.href = 'login.php';
}, <?= $status === 'success' ? 3600 : 5200 ?>);
</script>

</body>
</html>

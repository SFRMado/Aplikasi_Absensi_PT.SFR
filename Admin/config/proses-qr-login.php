<?php
/**
 * proses-qr-login.php
 * Login via QR Code (JSON API) - FIXED VERSION
 */

header('Content-Type: application/json');

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

require_once __DIR__ . '/koneksi.php';

/* ================== AMBIL RAW JSON ================== */
$raw = file_get_contents('php://input');
$data = json_decode($raw, true);

if (!is_array($data) || empty($data['qr'])) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Data QR tidak ditemukan'
    ]);
    exit;
}

/* ================== DECODE QR ================== */
$qr = json_decode($data['qr'], true);

if (!is_array($qr) || empty($qr['id_user'])) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Format QR tidak valid'
    ]);
    exit;
}

$idUser = (int) $qr['id_user'];

/* ================== CEK USER ================== */
$stmt = $pdo->prepare("
    SELECT id_user, username, nama_lengkap, status, role
    FROM users
    WHERE id_user = ?
    LIMIT 1
");
$stmt->execute([$idUser]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'User tidak ditemukan'
    ]);
    exit;
}

if ($user['status'] !== 'aktif') {
    http_response_code(403);
    echo json_encode([
        'success' => false,
        'message' => 'User tidak aktif'
    ]);
    exit;
}

/* ================== SET SESSION ================== */
session_regenerate_id(true);

$_SESSION['admin_logged'] = true;
$_SESSION['admin_id']     = $user['id_user'];
$_SESSION['username']     = $user['username'];
$_SESSION['nama_lengkap'] = $user['nama_lengkap'];
$_SESSION['role']         = $user['role'];
$_SESSION['login_via']    = 'qr';
$_SESSION['last_activity']= time();

/* ================== LOG AKTIVITAS ================== */
try {
    $log = $pdo->prepare("
        INSERT INTO log_aktivitas
        (id_user, username, role, aktivitas, ip_address, user_agent, status)
        VALUES
        (:id, :username, :role, 'Login via QR Code', :ip, :agent, 'berhasil')
    ");

    $log->execute([
        'id'       => $user['id_user'],
        'username' => $user['username'],
        'role'     => $user['role'],
        'ip'       => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
        'agent'    => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
    ]);
} catch (Exception $e) {
    // logging gagal TIDAK BOLEH menghentikan login
}

/* ================== CATAT ABSENSI ================== */
try {
    $cek = $pdo->prepare("
        SELECT id_absensi
        FROM absensi
        WHERE id_pegawai = :id
        AND tanggal = CURDATE()
        LIMIT 1
    ");
    $cek->execute(['id' => $user['id_user']]);

    if (!$cek->fetch()) {
        $insert = $pdo->prepare("
            INSERT INTO absensi
            (id_pegawai, tanggal, jam_absen, status, device_info, created_at)
            VALUES
            (:id, CURDATE(), CURTIME(), 'hadir', :device, NOW())
        ");

        $insert->execute([
            'id'     => $user['id_user'],
            'device' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
        ]);
    }
} catch (Exception $e) {
    // absensi gagal TIDAK BOLEH menghentikan login
}

/* ================== RESPONSE ================== */
echo json_encode([
    'success'  => true,
    'redirect' => 'dashboard.php'
]);
exit;

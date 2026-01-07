<?php
/**
 * proses-qr-login-pegawai.php
 * Login Pegawai via QR Code (JSON API) - STABLE VERSION
 */

header('Content-Type: application/json; charset=utf-8');

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

require_once 'koneksi.php';

/* ================== AMBIL RAW JSON ================== */
$raw  = file_get_contents('php://input');
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

$idPegawai = (int) $qr['id_user'];

/* ================== CEK USER PEGAWAI ================== */
$stmt = $pdo->prepare("
    SELECT 
        id_user,
        username,
        nama_lengkap,
        role,
        status
    FROM users
    WHERE id_user = ?
      AND role = 'pegawai'
    LIMIT 1
");
$stmt->execute([$idPegawai]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'Pegawai tidak ditemukan'
    ]);
    exit;
}

if ($user['status'] !== 'aktif') {
    http_response_code(403);
    echo json_encode([
        'success' => false,
        'message' => 'Akun pegawai tidak aktif'
    ]);
    exit;
}

/* ================== SET SESSION ================== */
        session_regenerate_id(true);

        $_SESSION['pegawai_logged'] = true;
        $_SESSION['pegawai_id']     = $user['id_user'];
        $_SESSION['username']       = $user['username'];
        $_SESSION['nama_lengkap']   = $user['nama_lengkap'];
        $_SESSION['role']           = 'pegawai';
        $_SESSION['last_activity']  = time();






/* ================== LOG AKTIVITAS ================== */
try {
    $log = $pdo->prepare("
        INSERT INTO log_aktivitas
        (id_user, username, role, aktivitas, ip_address, user_agent, status)
        VALUES
        (:id, :username, 'pegawai', 'Login Pegawai via QR', :ip, :agent, 'berhasil')
    ");

    $log->execute([
        'id'       => $user['id_user'],
        'username' => $user['username'],
        'ip'       => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
        'agent'    => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
    ]);
} catch (Throwable $e) {
    // Logging gagal TIDAK BOLEH menghentikan login
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
} catch (Throwable $e) {
    // Absensi gagal TIDAK BOLEH menghentikan login
}

/* ================== RESPONSE ================== */
echo json_encode([
    'success'  => true,
    'redirect' => 'dashboard.php'
]);
exit;

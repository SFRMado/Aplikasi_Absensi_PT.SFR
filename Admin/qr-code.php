<?php
/**
 * generate-qr.php
 * Generate & simpan QR Code user
 */

require_once __DIR__ . '/config/koneksi.php';
require_once __DIR__ . '/assets/libs/phpqrcode/qrlib.php';

/* ================== VALIDASI PARAM ================== */
$idUser = (int) ($_GET['id_user'] ?? 0);

if ($idUser <= 0) {
    die('ID tidak valid!');
}

/* ================== AMBIL DATA USER ================== */
$stmt = $pdo->prepare("
    SELECT id_user, nama_lengkap, qr_code
    FROM users
    WHERE id_user = :id
    LIMIT 1
");
$stmt->execute(['id' => $idUser]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    die('User tidak ditemukan!');
}

/* ================== CEK QR SUDAH ADA ================== */
if (!empty($user['qr_code'])) {
    header('Location: users.php?qr=exists');
    exit;
}

/* ================== DATA QR ================== */
/* Bisa JSON / token / id saja */
$dataQR = json_encode([
    'id_user' => $user['id_user'],
    'nama'    => $user['nama_lengkap'],
    'role'    => 'USER',
    'type'    => 'ABSENSI',
    'issued'  => date('Y-m-d H:i:s')
]);

/* ================== FOLDER QR ================== */
$qrDir = 'assets/img/qr/';
if (!is_dir($qrDir)) {
    mkdir($qrDir, 0755, true);
}

/* ================== NAMA FILE ================== */
$fileName = 'qr_user_' . $user['id_user'] . '.png';
$filePath = $qrDir . $fileName;

/* ================== GENERATE QR ================== */
QRcode::png(
    $dataQR,
    $filePath,
    QR_ECLEVEL_H, // Error correction tinggi
    8             // Size
);

/* ================== SIMPAN KE DATABASE ================== */
$update = $pdo->prepare("
    UPDATE users
    SET qr_code = :qr
    WHERE id_user = :id
");
$update->execute([
    'qr' => 'qr/' . $fileName,
    'id' => $user['id_user']
]);

/* ================== REDIRECT ================== */
header('Location: users.php?qr=success');
exit;

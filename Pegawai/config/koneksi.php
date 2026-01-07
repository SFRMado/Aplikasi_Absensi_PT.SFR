<?php
/**
 * koneksi.php
 * Koneksi Database Aplikasi Absensi QR-Code PT. SFR
 * Menggunakan PDO (AMAN & REKOMENDASI)
 */

// ===== KONFIGURASI DATABASE =====
$host     = 'localhost';
$dbname   = 'db_aplikasi_absensi_pt_sfr';
$username = 'root';      // ganti sesuai server
$password = '';          // ganti sesuai server
$charset  = 'utf8mb4';

// ===== PDO OPTIONS (SECURITY HARDENED) =====
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // error jelas & aman
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // hasil array asosiatif
    PDO::ATTR_EMULATE_PREPARES   => false,                   // prepared statement asli
    PDO::ATTR_PERSISTENT         => false                    // hindari session DB menggantung
];

// ===== DSN =====
$dsn = "mysql:host=$host;dbname=$dbname;charset=$charset";

try {
    $pdo = new PDO($dsn, $username, $password, $options);
} catch (PDOException $e) {
    // Jangan tampilkan detail error ke user (keamanan)
    http_response_code(500);
    error_log('Koneksi DB Error: ' . $e->getMessage());
    die('Koneksi database gagal. Silakan hubungi administrator.');
}

// ===== SET TIMEZONE =====
date_default_timezone_set('Asia/Jakarta');

// ===== OPTIONAL: TEST KONEKSI (HAPUS DI PRODUKSI) =====
// echo 'Koneksi database berhasil';

?>
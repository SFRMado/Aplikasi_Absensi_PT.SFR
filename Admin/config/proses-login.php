<?php
/**
 * proses-login.php
 * Berisi function prosesLoginAdmin()
 * Dipanggil oleh login-admin.php
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function prosesLoginAdmin(array $data)
{
    global $pdo;

    // ===== CSRF VALIDATION =====
    if (
        !isset($data['csrf_token']) ||
        !isset($_SESSION['csrf_token']) ||
        !hash_equals($_SESSION['csrf_token'], $data['csrf_token'])
    ) {
        return 'Token keamanan tidak valid.';
    }

    // ===== RATE LIMIT =====
    if (isset($_SESSION['login_attempts']) && $_SESSION['login_attempts'] >= 5) {
        return 'Terlalu banyak percobaan login. Silakan coba lagi nanti.';
    }

    $username = trim($data['username'] ?? '');
    $password = $data['password'] ?? '';

    if ($username === '' || $password === '') {
        return 'Username dan password wajib diisi.';
    }

    try {
        $stmt = $pdo->prepare("
            SELECT id_user, username, password, nama_lengkap 
            FROM users 
            WHERE username = :username 
              AND role = 'admin' 
              AND status = 'aktif'
            LIMIT 1
        ");
        $stmt->execute(['username' => $username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user || !password_verify($password, $user['password'])) {
            $_SESSION['login_attempts'] = ($_SESSION['login_attempts'] ?? 0) + 1;
            return 'Username atau password salah.';
        }

        // ===== LOGIN BERHASIL =====
        session_regenerate_id(true);

        $_SESSION['admin_logged']   = true;
        $_SESSION['admin_id']       = $user['id_user'];
        $_SESSION['admin_username'] = $user['username'];
        $_SESSION['admin_name']     = $user['nama_lengkap'];
        $_SESSION['login_attempts'] = 0;
        $_SESSION['login_success']  = true;

        // ==================================================
        // 1️⃣ LOG AKTIVITAS
        // ==================================================
        $log = $pdo->prepare("
            INSERT INTO log_aktivitas 
            (id_user, username, role, aktivitas, ip_address, user_agent, status)
            VALUES 
            (:id_user, :username, 'admin', 'Login Admin', :ip, :agent, 'berhasil')
        ");

        $log->execute([
            'id_user' => $user['id_user'],
            'username'=> $user['username'],
            'ip'      => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'agent'   => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
        ]);

        // ==================================================
        // 2️⃣ CATAT ABSENSI LOGIN ADMIN
        // ==================================================

       $cekAbsen = $pdo->prepare("
        SELECT id_absensi
        FROM absensi
        WHERE id_pegawai = :id
        AND tanggal = CURDATE()
        LIMIT 1
        ");
        $cekAbsen->execute([
        'id' => $user['id_user']
        ]);


        if (!$cekAbsen->fetch()) {

            $insertAbsen = $pdo->prepare("
                INSERT INTO absensi
                (id_pegawai, tanggal, jam_absen, status, device_info, created_at)
                VALUES
                (:id, CURDATE(), CURTIME(), 'hadir', :device, NOW())
            ");

            $insertAbsen->execute([
                'id'     => $user['id_user'],
                'device' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
            ]);
        }


        // ⛔ Jangan redirect di sini
        return null;

    } catch (Throwable $e) {

        error_log('[LOGIN ADMIN ERROR] ' . $e->getMessage());

        // Jangan override login sukses
        if (!empty($_SESSION['login_success'])) {
            return null;
        }

        return 'Terjadi kesalahan sistem. Silakan coba lagi.';
    }
}

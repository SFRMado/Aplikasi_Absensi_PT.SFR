<?php
/**
 * proses-login-pegawai.php
 * Core logic login Pegawai
 * ❌ TANPA redirect
 * ❌ TANPA echo / HTML
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function prosesLoginPegawai(array $data)
{
    global $pdo;

    /* ===== CSRF (opsional untuk QR) ===== */
    if (isset($data['csrf_token'])) {
        if (
            empty($_SESSION['csrf_token']) ||
            !hash_equals($_SESSION['csrf_token'], $data['csrf_token'])
        ) {
            return 'Token keamanan tidak valid.';
        }
    }

    /* ===== RATE LIMIT ===== */
    if (!empty($_SESSION['login_attempts']) && $_SESSION['login_attempts'] >= 5) {
        return 'Terlalu banyak percobaan login.';
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
              AND role = 'pegawai'
              AND status = 'aktif'
            LIMIT 1
        ");
        $stmt->execute(['username' => $username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user || !password_verify($password, $user['password'])) {
            $_SESSION['login_attempts'] = ($_SESSION['login_attempts'] ?? 0) + 1;
            return 'Username atau password salah.';
        }

        /* ===== LOGIN BERHASIL ===== */
            session_regenerate_id(true);

            $_SESSION['pegawai_logged']   = true;
            $_SESSION['pegawai_id']       = $user['id_user'];
            $_SESSION['pegawai_username'] = $user['username'];
            $_SESSION['pegawai_nama']     = $user['nama_lengkap'];
            $_SESSION['role']             = 'pegawai';
            $_SESSION['login_attempts']   = 0;
            $_SESSION['last_activity']    = time();

            return true;




        $_SESSION['login_attempts'] = 0;

        /* ===== LOG AKTIVITAS ===== */
        $log = $pdo->prepare("
            INSERT INTO log_aktivitas
            (id_user, username, role, aktivitas, ip_address, user_agent, status)
            VALUES
            (:id, :username, 'pegawai', 'Login Pegawai', :ip, :agent, 'berhasil')
        ");
        $log->execute([
            'id'       => $user['id_user'],
            'username' => $user['username'],
            'ip'       => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'agent'    => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
        ]);

        /* ===== ABSENSI ===== */
        $cek = $pdo->prepare("
            SELECT id_absensi FROM absensi
            WHERE id_pegawai = :id AND tanggal = CURDATE()
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

        /* ✅ PENTING */
        return true;

    } catch (Throwable $e) {
        error_log('[LOGIN PEGAWAI ERROR] ' . $e->getMessage());
        return 'Terjadi kesalahan sistem.';
    }
}

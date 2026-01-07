<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function requirePegawaiLogin(): void
{
    // ❌ Belum login
    if (
        empty($_SESSION['pegawai_logged']) ||
        $_SESSION['pegawai_logged'] !== true ||
        empty($_SESSION['pegawai_id'])
    ) {
        forcePegawaiLogout('auth=required');
    }

    // ⏱️ Idle timeout 10 menit
    if (
        isset($_SESSION['last_activity']) &&
        (time() - $_SESSION['last_activity']) > 600
    ) {
        forcePegawaiLogout('auth=expired');
    }

    // ✅ Update aktivitas
    $_SESSION['last_activity'] = time();
}

function forcePegawaiLogout(string $query = ''): void
{
    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
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

    $redirect = 'login.php';
    if ($query) {
        $redirect .= '?' . $query;
    }

    header('Location: ' . $redirect);
    exit;
}

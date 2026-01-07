<?php
/**
 * auth-admin.php
 * Proteksi autentikasi Admin (FINAL & STABIL)
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * ===============================
 * WAJIB LOGIN ADMIN
 * Dipakai di SEMUA halaman admin
 * ===============================
 */
function requireAdminLogin(): void
{
    // ⛔ Belum login / session rusak
    if (
        empty($_SESSION['admin_logged']) ||
        $_SESSION['admin_logged'] !== true ||
        empty($_SESSION['admin_id'])
    ) {
        forceLogout('auth=required');
    }

    // ⏱️ IDLE TIMEOUT (10 menit = 600 detik)
    $idleLimit = 600;

    if (
        isset($_SESSION['last_activity']) &&
        (time() - $_SESSION['last_activity']) > $idleLimit
    ) {
        forceLogout('auth=expired');
    }

    // ✅ Update aktivitas setiap request VALID
    $_SESSION['last_activity'] = time();
}

/**
 * ===============================
 * LOGOUT PAKSA (AMAN)
 * ===============================
 */
function forceLogout(string $query = ''): void
{
    // Bersihkan session sepenuhnya
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

    $redirect = '../index.php';
    if ($query) {
        $redirect .= '?' . $query;
    }

    header('Location: ' . $redirect);
    exit;
}

<?php

require_once dirname(__DIR__) . '/includes/config.php';

// Hủy toàn bộ session
$_SESSION = [];
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}
session_destroy();

// Redirect về trang login
header('Location: ' . ADMIN_URL . '/login.php');
exit;

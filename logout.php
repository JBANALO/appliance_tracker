<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/security.php';

initSecureSession();

// Log logout event
if (isset($_SESSION['admin_email'])) {
    logSecurityEvent('LOGOUT', ['email' => $_SESSION['admin_email']]);
}

$_SESSION = array();
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time()-3600, '/');
}

session_destroy();
header("Location: login.php?logout=success");
exit;
?>
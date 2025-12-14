<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/security.php';

initSecureSession();

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

require_once "claim.php";

$id = $_GET['id'] ?? null;

if ($id) {
    $claim = new Claim();
    $claim->deleteClaim($id);
}

header("Location: viewclaim.php");
exit;
?>
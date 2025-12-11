<?php
session_start();

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

require_once "Claim.php";

$id = $_GET['id'] ?? null;

if ($id) {
    $claim = new Claim();
    $claim->deleteClaim($id);
}

header("Location: viewclaim.php");
exit;
?>
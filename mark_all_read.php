<?php
session_start();

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

require_once "Notification.php";

$notificationObj = new Notification();
$notificationObj->markAllAsRead();

header("Location: admin_dashboard.php");
exit;
?>
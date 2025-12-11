<?php
session_start();

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

require_once "Notification.php";

$notificationObj = new Notification();


$notificationObj->addNotification(
    'claim',
    'New Warranty Claim Filed',
    'Samsung Refrigerator claim filed by John Doe',
    'viewclaim.php?id=1'
);

$notificationObj->addNotification(
    'warranty',
    'Warranty Expiring Soon',
    'LG Washing Machine warranty expires in 5 days',
    'viewappliance.php?id=2'
);

$notificationObj->addNotification(
    'info',
    'System Update',
    'New features have been added to the system',
    'admin_dashboard.php'
);

echo "<i class=\"fas fa-check-circle\"></i> Test notifications created successfully!<br>";
echo "<a href='admin_dashboard.php'>Go to Dashboard</a>";
?>
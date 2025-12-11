<?php
session_start();

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

require_once "database.php";
require_once "Notification.php";

$notificationObj = new Notification();
$all_notifications = $notificationObj->getAllNotifications(50);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>All Notifications</title>
    <link rel="stylesheet" href="styles.css"
</head>
<body>
    <div class="container">
        <div class="page-header">
            <h1>🔔 All Notifications</h1>
            <a href="admin_dashboard.php" class="btn-back">← Back to Dashboard</a>
        </div>

        <?php
        $unread_count = $notificationObj->getUnreadCount();
        if ($unread_count > 0):
        ?>
        <a href="mark_all_read.php">
            <button class="mark-all-btn">Mark All as Read (<?= $unread_count ?>)</button>
        </a>
        <?php endif; ?>

        <?php if (count($all_notifications) > 0): ?>
        <ul class="notification-list">
            <?php foreach($all_notifications as $notif): ?>
            <li class="notification-item <?= $notif['is_read'] == 0 ? 'unread' : '' ?>" 
                onclick="window.location.href='<?= $notif['link'] ?? '#' ?>'">
                <div>
                    <span class="notification-type type-<?= strtolower($notif['type']) ?>">
                        <?= htmlspecialchars($notif['type']) ?>
                    </span>
                    <span class="notification-time">
                        <?= date('F d, Y h:i A', strtotime($notif['created_at'])) ?>
                    </span>
                </div>
                <div class="notification-title"><?= htmlspecialchars($notif['title']) ?></div>
                <div class="notification-message"><?= htmlspecialchars($notif['message']) ?></div>
            </li>
            <?php endforeach; ?>
        </ul>
        <?php else: ?>
        <div class="no-notifications">
            <h3> No Notifications</h3>
            <p>You're all caught up!</p>
        </div>
        <?php endif; ?>
    </div>
</body>
</html>
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
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background: #f5f5f5;
            padding: 20px;
        }

        .container {
            max-width: 900px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        .page-header {
            background: #667eea;
            color: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .page-header h1 {
            margin: 0;
            color: white;
        }

        .btn-back {
            background: white;
            color: #667eea;
            padding: 10px 20px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: bold;
        }

        .notification-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .notification-item {
            padding: 20px;
            border-bottom: 1px solid #e0e0e0;
            cursor: pointer;
        }

        .notification-item:hover {
            background: #f8f9fa;
        }

        .notification-item.unread {
            background: #e7f3ff;
            border-left: 4px solid #007bff;
        }

        .notification-title {
            font-weight: bold;
            color: #333;
            margin-bottom: 8px;
            font-size: 16px;
        }

        .notification-message {
            color: #666;
            font-size: 14px;
            margin-bottom: 8px;
        }

        .notification-time {
            color: #999;
            font-size: 12px;
        }

        .notification-type {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: bold;
            margin-right: 10px;
        }

        .type-claim { background: #fff3cd; color: #856404; }
        .type-warranty { background: #f8d7da; color: #721c24; }
        .type-info { background: #d1ecf1; color: #0c5460; }
        .type-success { background: #d4edda; color: #155724; }

        .no-notifications {
            padding: 50px;
            text-align: center;
            color: #999;
        }

        .mark-all-btn {
            background: #28a745;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: bold;
            margin-bottom: 20px;
        }

        .mark-all-btn:hover {
            background: #218838;
        }
    </style>
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
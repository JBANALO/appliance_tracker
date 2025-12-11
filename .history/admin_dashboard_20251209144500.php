<?php
session_start();

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

require_once "database.php";
require_once "Appliance.php";
require_once "Claim.php";
require_once "Notification.php";

$db = new Database();
$conn = $db->connect();

$query = $conn->query("SELECT COUNT(*) as total FROM appliance");
$total_appliances = $query->fetch()['total'];

$query = $conn->query("SELECT COUNT(*) as total FROM appliance WHERE warranty_end_date >= CURDATE()");
$active_warranties = $query->fetch()['total'];

$query = $conn->query("SELECT COUNT(*) as total FROM appliance WHERE warranty_end_date < CURDATE()");
$expired_warranties = $query->fetch()['total'];

$query = $conn->query("SELECT COUNT(*) as total FROM appliance WHERE warranty_end_date >= CURDATE() AND warranty_end_date <= DATE_ADD(CURDATE(), INTERVAL 30 DAY)");
$expiring_soon = $query->fetch()['total'];

$query = $conn->query("SELECT COUNT(*) as total FROM claim");
$total_claims = $query->fetch()['total'];

$query = $conn->query("SELECT COUNT(*) as total FROM claim WHERE claim_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)");
$recent_claims = $query->fetch()['total'];

$active_percentage = $total_appliances > 0 ? round(($active_warranties / $total_appliances) * 100) : 0;
$expired_percentage = $total_appliances > 0 ? round(($expired_warranties / $total_appliances) * 100) : 0;

// Get notifications
$notificationObj = new Notification();
$unread_count = $notificationObj->getUnreadCount();
$recent_notifications = $notificationObj->getUnreadNotifications(5);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="admin_dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .dashboard-header {
            background: #667eea;
            color: white;
            padding: 30px;
            border-radius: 15px;
            margin-bottom: 30px;
            text-align: center;
            position: relative;
        }

        .dashboard-header h1 {
            margin: 0;
            color: white;
        }

        /* Notification Bell Styles */
        .notification-bell {
            position: absolute;
            top: 30px;
            right: 30px;
            cursor: pointer;
        }

        .bell-icon {
            font-size: 28px;
            color: white;
            position: relative;
        }

        .notification-badge {
            position: absolute;
            top: -8px;
            right: -8px;
            background: #dc3545;
            color: white;
            border-radius: 50%;
            padding: 4px 8px;
            font-size: 12px;
            font-weight: bold;
            min-width: 20px;
            text-align: center;
        }

        .notification-dropdown {
            display: none;
            position: absolute;
            top: 50px;
            right: 0;
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.2);
            width: 350px;
            max-height: 500px;
            overflow-y: auto;
            z-index: 1000;
        }

        .notification-dropdown.show {
            display: block;
        }

        .notification-header {
            padding: 15px;
            border-bottom: 1px solid #e0e0e0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .notification-header h3 {
            margin: 0;
            color: #333;
            font-size: 16px;
        }

        .mark-all-read {
            color: #667eea;
            font-size: 12px;
            cursor: pointer;
            text-decoration: none;
        }

        .mark-all-read:hover {
            text-decoration: underline;
        }

        .notification-item {
            padding: 15px;
            border-bottom: 1px solid #f0f0f0;
            cursor: pointer;
            transition: background 0.2s;
        }

        .notification-item:hover {
            background: #f8f9fa;
        }

        .notification-item.unread {
            background: #e7f3ff;
        }

        .notification-title {
            font-weight: bold;
            color: #333;
            margin-bottom: 5px;
            font-size: 14px;
        }

        .notification-message {
            color: #666;
            font-size: 13px;
            margin-bottom: 5px;
        }

        .notification-time {
            color: #999;
            font-size: 11px;
        }

        .no-notifications {
            padding: 30px;
            text-align: center;
            color: #999;
        }

        .view-all-link {
            display: block;
            padding: 12px;
            text-align: center;
            background: #f8f9fa;
            color: #667eea;
            text-decoration: none;
            font-weight: bold;
            border-radius: 0 0 8px 8px;
        }

        .view-all-link:hover {
            background: #e9ecef;
        }

        .kpi-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .kpi-card {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            text-align: center;
            transition: transform 0.3s;
        }

        .kpi-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 20px rgba(0,0,0,0.15);
        }

        .kpi-number {
            font-size: 42px;
            font-weight: bold;
            margin: 10px 0;
        }

        .kpi-label {
            color: #666;
            font-size: 14px;
            text-transform: uppercase;
        }

        .kpi-card.active .kpi-number { color: #28a745; }
        .kpi-card.expired .kpi-number { color: #dc3545; }
        .kpi-card.warning .kpi-number { color: #ffc107; }
        .kpi-card.info .kpi-number { color: #17a2b8; }
        .kpi-card.primary .kpi-number { color: #007bff; }

        .progress-bar {
            width: 100%;
            height: 8px;
            background: #e0e0e0;
            border-radius: 4px;
            margin-top: 10px;
            overflow: hidden;
        }

        .progress-fill {
            height: 100%;
            border-radius: 4px;
            transition: width 0.5s;
        }

        .progress-fill.active { background: #28a745; }
        .progress-fill.expired { background: #dc3545; }

        .quick-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 15px;
            margin-bottom: 30px;
        }

        .action-button {
            background: #007bff;
            color: white;
            padding: 15px 20px;
            border-radius: 8px;
            text-decoration: none;
            text-align: center;
            font-weight: bold;
            transition: 0.3s;
            display: block;
        }

        .action-button:hover {
            background: #0056b3;
            transform: scale(1.02);
        }

        .action-button.report { background: #17a2b8; }
        .action-button.report:hover { background: #138496; }

        .action-button.print { background: #28a745; }
        .action-button.print:hover { background: #218838; }

        .logout-button {
            background: #dc3545;
        }

        .logout-button:hover {
            background: #a71d2a;
        }

        @media print {
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <div class="container" style="max-width: 1200px;">
        <div class="dashboard-header">
            <h1>Admin Dashboard</h1>
            <p>Warranty Tracker Management System</p>
            
            <!-- Notification Bell -->
            <div class="notification-bell" onclick="toggleNotifications()">
                <span class="bell-icon"><i class="fas fa-bell"></i></span>
                <?php if ($unread_count > 0): ?>
                <span class="notification-badge"><?= $unread_count ?></span>
                <?php endif; ?>
                
                <div class="notification-dropdown" id="notificationDropdown">
                    <div class="notification-header">
                        <h3>Notifications</h3>
                        <?php if ($unread_count > 0): ?>
                        <a href="mark_all_read.php" class="mark-all-read">Mark all as read</a>
                        <?php endif; ?>
                    </div>
                    
                    <?php if (count($recent_notifications) > 0): ?>
                        <?php foreach($recent_notifications as $notif): ?>
                        <div class="notification-item <?= $notif['is_read'] == 0 ? 'unread' : '' ?>" 
                             onclick="window.location.href='<?= $notif['link'] ?? '#' ?>'">
                            <div class="notification-title"><?= htmlspecialchars($notif['title']) ?></div>
                            <div class="notification-message"><?= htmlspecialchars($notif['message']) ?></div>
                            <div class="notification-time"><?= date('M d, Y h:i A', strtotime($notif['created_at'])) ?></div>
                        </div>
                        <?php endforeach; ?>
                        <a href="view_all_notifications.php" class="view-all-link">View All Notifications</a>
                    <?php else: ?>
                        <div class="no-notifications">No new notifications</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="kpi-container">
            <div class="kpi-card info">
                <div class="kpi-number"><?= $total_appliances ?></div>
                <div class="kpi-label">Total Appliances</div>
            </div>

            <div class="kpi-card active">
                <div class="kpi-number"><?= $active_warranties ?></div>
                <div class="kpi-label">Active Warranties</div>
                <div class="progress-bar">
                    <div class="progress-fill active" style="width: <?= $active_percentage ?>%"></div>
                </div>
                <small><?= $active_percentage ?>% of total</small>
            </div>

            <div class="kpi-card expired">
                <div class="kpi-number"><?= $expired_warranties ?></div>
                <div class="kpi-label">Expired Warranties</div>
                <div class="progress-bar">
                    <div class="progress-fill expired" style="width: <?= $expired_percentage ?>%"></div>
                </div>
                <small><?= $expired_percentage ?>% of total</small>
            </div>

            <div class="kpi-card warning">
                <div class="kpi-number"><?= $expiring_soon ?></div>
                <div class="kpi-label">Expiring Soon (30 days)</div>
            </div>

            <div class="kpi-card primary">
                <div class="kpi-number"><?= $total_claims ?></div>
                <div class="kpi-label">Total Claims</div>
            </div>

            <div class="kpi-card warning">
                <div class="kpi-number"><?= $recent_claims ?></div>
                <div class="kpi-label">Recent Claims (30 days)</div>
            </div>
        </div>

        <div class="quick-actions no-print">
            <a href="viewappliance.php" class="action-button">View Appliances</a>
            <a href="viewowner.php" class="action-button">View Owners</a>
            <a href="viewclaim.php" class="action-button">View Claims</a>
            
            <a href="reports.php" class="action-button report">Reports</a>
            <a href="print_warranty.php" class="action-button print" target="_blank">Print Certificate</a>
            <a href="print_dashboard_summary.php" class="action-button print" target="_blank">Print Summary</a>
            
            <a href="logout.php" class="action-button logout-button">Logout</a>
        </div>
    </div>

    <script>
        function toggleNotifications() {
            const dropdown = document.getElementById('notificationDropdown');
            dropdown.classList.toggle('show');
        }

        window.onclick = function(event) {
            if (!event.target.matches('.bell-icon') && !event.target.matches('.notification-badge')) {
                const dropdown = document.getElementById('notificationDropdown');
                if (dropdown.classList.contains('show')) {
                    dropdown.classList.remove('show');
                }
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            const cards = document.querySelectorAll('.kpi-card');
            cards.forEach((card, index) => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(20px)';
                setTimeout(() => {
                    card.style.transition = 'all 0.5s';
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, index * 100);
            });
        });
    </script>
</body>
</html>
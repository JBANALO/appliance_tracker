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
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"
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
            cards.forEach((card, ) => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(20px)';
                setTimeout(() => {
                    card.style.transition = 'all 0.5s';
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                },  * 100);
            });
        });
    </script>
</body>
</html>
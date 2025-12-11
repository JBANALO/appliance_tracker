<?php
session_start();

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

require_once "database.php";

$db = new Database();
$conn = $db->connect();


$report_type = $_GET['type'] ?? 'all';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Reports & Lists</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="container" style="max-width: 1200px;">
        <div class="header">
            <a href="admin_dashboard.php" class="back-btn">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
            <h1 style="font-size: 32px; color: #667eea; margin: 20px 0 10px 0;">
                <i class="fas fa-chart-bar"></i> Reports & Analytics
            </h1>
            <p style="color: #666; margin: 0;">Comprehensive warranty tracking reports and statistics</p>
        </div>

        <!-- Report Type Selector -->
        <div class="report-tabs no-print" style="display: flex; flex-wrap: wrap; gap: 10px; margin: 20px 0; padding: 15px; background: white; border-radius: 10px; border: 1px solid #e0e0e0;">
            <a href="?type=all" class="tab-button" style="padding: 10px 15px; border-radius: 8px; text-decoration: none; font-weight: 500; <?= $report_type == 'all' ? 'background: #667eea; color: white;' : 'background: #f5f5f5; color: #333;' ?>">All Appliances</a>
            <a href="?type=active" class="tab-button" style="padding: 10px 15px; border-radius: 8px; text-decoration: none; font-weight: 500; <?= $report_type == 'active' ? 'background: #667eea; color: white;' : 'background: #f5f5f5; color: #333;' ?>">Active Warranties</a>
            <a href="?type=expired" class="tab-button" style="padding: 10px 15px; border-radius: 8px; text-decoration: none; font-weight: 500; <?= $report_type == 'expired' ? 'background: #667eea; color: white;' : 'background: #f5f5f5; color: #333;' ?>">Expired Warranties</a>
            <a href="?type=expiring" class="tab-button" style="padding: 10px 15px; border-radius: 8px; text-decoration: none; font-weight: 500; <?= $report_type == 'expiring' ? 'background: #667eea; color: white;' : 'background: #f5f5f5; color: #333;' ?>">Expiring Soon</a>
            <a href="?type=claims" class="tab-button" style="padding: 10px 15px; border-radius: 8px; text-decoration: none; font-weight: 500; <?= $report_type == 'claims' ? 'background: #667eea; color: white;' : 'background: #f5f5f5; color: #333;' ?>">Claims Report</a>
            <a href="?type=owners" class="tab-button" style="padding: 10px 15px; border-radius: 8px; text-decoration: none; font-weight: 500; <?= $report_type == 'owners' ? 'background: #667eea; color: white;' : 'background: #f5f5f5; color: #333;' ?>">Owners Report</a>
        </div>

        <div class="action-buttons no-print" style="display: flex; gap: 10px; margin-bottom: 20px;">
            <button onclick="window.print()" class="btn btn-primary">
                <i class="fas fa-print"></i> Print Report
            </button>
            <a href="admin_dashboard.php" class="btn btn-secondary">
                <i class="fas fa-times"></i> Close
            </a>
        </div>

        <?php
        // Generate report based on type
        switch($report_type) {
            case 'all':
                $sql = "SELECT a.*, o.owner_name, o.email, o.phone,
                        CASE 
                            WHEN a.warranty_end_date < CURDATE() THEN 'Expired'
                            WHEN a.warranty_end_date <= DATE_ADD(CURDATE(), INTERVAL 30 DAY) THEN 'Expiring Soon'
                            ELSE 'Active'
                        END as status
                        FROM appliance a
                        LEFT JOIN owner o ON a.owner_id = o.id
                        ORDER BY a.warranty_end_date ASC";
                $title = "All Appliances Report";
                break;

            case 'active':
                $sql = "SELECT a.*, o.owner_name, o.email, o.phone,
                        'Active' as status
                        FROM appliance a
                        LEFT JOIN owner o ON a.owner_id = o.id
                        WHERE a.warranty_end_date >= CURDATE()
                        ORDER BY a.warranty_end_date ASC";
                $title = "Active Warranties Report";
                break;

            case 'expired':
                $sql = "SELECT a.*, o.owner_name, o.email, o.phone,
                        'Expired' as status
                        FROM appliance a
                        LEFT JOIN owner o ON a.owner_id = o.id
                        WHERE a.warranty_end_date < CURDATE()
                        ORDER BY a.warranty_end_date DESC";
                $title = "Expired Warranties Report";
                break;

            case 'expiring':
                $sql = "SELECT a.*, o.owner_name, o.email, o.phone,
                        'Expiring Soon' as status,
                        DATEDIFF(a.warranty_end_date, CURDATE()) as days_left
                        FROM appliance a
                        LEFT JOIN owner o ON a.owner_id = o.id
                        WHERE a.warranty_end_date >= CURDATE() 
                        AND a.warranty_end_date <= DATE_ADD(CURDATE(), INTERVAL 30 DAY)
                        ORDER BY a.warranty_end_date ASC";
                $title = "Warranties Expiring Soon (Next 30 Days)";
                break;

            case 'claims':
                $sql = "SELECT c.*, a.appliance_name, a.model_number, a.serial_number, 
                        o.owner_name, o.email, o.phone
                        FROM claim c
                        LEFT JOIN appliance a ON c.appliance_id = a.id
                        LEFT JOIN owner o ON a.owner_id = o.id
                        ORDER BY c.claim_date DESC";
                $title = "Claims Report";
                break;

            case 'owners':
                $sql = "SELECT o.*, COUNT(a.id) as total_appliances,
                        SUM(CASE WHEN a.warranty_end_date >= CURDATE() THEN 1 ELSE 0 END) as active_warranties,
                        SUM(CASE WHEN a.warranty_end_date < CURDATE() THEN 1 ELSE 0 END) as expired_warranties
                        FROM owner o
                        LEFT JOIN appliance a ON o.id = a.owner_id
                        GROUP BY o.id
                        ORDER BY o.owner_name ASC";
                $title = "Owners Report";
                break;

            default:
                $sql = "SELECT * FROM appliance";
                $title = "Report";
        }

        $query = $conn->query($sql);
        $results = $query->fetchAll(PDO::FETCH_ASSOC);
        $total_records = count($results);
        ?>

        <h2><?= $title ?></h2>
        
        <div class="summary-box">
            <div class="summary-item">
                <strong><?= $total_records ?></strong><br>
                Total Records
            </div>
            <div class="summary-item">
                Report Generated: <strong><?= date('F d, Y H:i:s') ?></strong>
            </div>
        </div>

        <?php if ($report_type == 'claims'): ?>
            <!-- Claims Report Table -->
            <table>
                <thead>
                    <tr>
                        <th>Claim ID</th>
                        <th>Appliance</th>
                        <th>Owner</th>
                        <th>Claim Description</th>
                        <th>Status</th>
                        <th>Claim Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($results as $row): ?>
                    <tr>
                        <td>#<?= $row['id'] ?></td>
                        <td>
                            <?= htmlspecialchars($row['appliance_name']) ?><br>
                            <small>S/N: <?= htmlspecialchars($row['serial_number']) ?></small>
                        </td>
                        <td>
                            <?= htmlspecialchars($row['owner_name']) ?><br>
                            <small><?= htmlspecialchars($row['email']) ?></small>
                        </td>
                        <td><?= htmlspecialchars($row['claim_description']) ?></td>
                        <td>
                            <span style="padding: 5px 10px; background: #ffc107; border-radius: 15px; font-size: 12px; font-weight: bold;">
                                <?= htmlspecialchars($row['claim_status']) ?>
                            </span>
                        </td>
                        <td><?= date('M d, Y', strtotime($row['claim_date'])) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

        <?php elseif ($report_type == 'owners'): ?>
            <!-- Owners Report Table -->
            <table>
                <thead>
                    <tr>
                        <th>Owner ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Total Appliances</th>
                        <th>Active Warranties</th>
                        <th>Expired Warranties</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($results as $row): ?>
                    <tr>
                        <td>#<?= $row['id'] ?></td>
                        <td><?= htmlspecialchars($row['owner_name']) ?></td>
                        <td><?= htmlspecialchars($row['email']) ?></td>
                        <td><?= htmlspecialchars($row['phone']) ?></td>
                        <td><?= $row['total_appliances'] ?></td>
                        <td><?= $row['active_warranties'] ?></td>
                        <td><?= $row['expired_warranties'] ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

        <?php else: ?>
            <!-- Appliances Report Table -->
            <table>
                <thead>
                    <tr>
                        <th>Appliance</th>
                        <th>Owner</th>
                        <th>Model/Serial</th>
                        <th>Purchase Date</th>
                        <th>Warranty Period</th>
                        <th>Warranty End</th>
                        <th>Status</th>
                        <?php if($report_type == 'expiring'): ?>
                        <th>Days Left</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($results as $row): ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($row['appliance_name']) ?></strong></td>
                        <td>
                            <?= htmlspecialchars($row['owner_name'] ?? 'N/A') ?><br>
                            <small><?= htmlspecialchars($row['email'] ?? '') ?></small>
                        </td>
                        <td>
                            Model: <?= htmlspecialchars($row['model_number']) ?><br>
                            <small>S/N: <?= htmlspecialchars($row['serial_number']) ?></small>
                        </td>
                        <td><?= date('M d, Y', strtotime($row['purchase_date'])) ?></td>
                        <td><?= $row['warranty_period'] ?> year(s)</td>
                        <td><?= date('M d, Y', strtotime($row['warranty_end_date'])) ?></td>
                        <td>
                            <span class="status-badge status-<?= strtolower(str_replace(' ', '-', $row['status'])) ?>">
                                <?= $row['status'] ?>
                            </span>
                        </td>
                        <?php if($report_type == 'expiring'): ?>
                        <td><strong><?= $row['days_left'] ?></strong> days</td>
                        <?php endif; ?>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>

        <?php if($total_records == 0): ?>
            <p style="text-align: center; padding: 40px; color: #999;">No records found for this report.</p>
        <?php endif; ?>
    </div>
</body>
</html>
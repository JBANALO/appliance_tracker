<?php
session_start();

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

require_once "database.php";

$db = new Database();
$conn = $db->connect();

// Get report type from URL
$report_type = $_GET['type'] ?? 'all';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Reports & Lists</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background: #f5f5f5;
            padding: 20px;
        }

        .report-container {
            max-width: 1400px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        .report-header {
            background: #667eea;
            color: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
        }

        .report-tabs {
            display: flex;
            gap: 10px;
            margin-bottom: 30px;
            flex-wrap: wrap;
        }

        .tab-button {
            padding: 12px 24px;
            background: #e0e0e0;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: bold;
            text-decoration: none;
            color: #333;
        }

        .tab-button:hover {
            background: #d0d0d0;
        }

        .tab-button.active {
            background: #007bff;
            color: white;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th {
            background: #007bff;
            color: white;
            padding: 12px;
            text-align: left;
            font-weight: bold;
        }

        td {
            padding: 12px;
            border-bottom: 1px solid #e0e0e0;
        }

        tr:hover {
            background: #f8f9fa;
        }

        .status-badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
        }

        .status-active {
            background: #d4edda;
            color: #155724;
        }

        .status-expired {
            background: #f8d7da;
            color: #721c24;
        }

        .status-expiring {
            background: #fff3cd;
            color: #856404;
        }

        .action-buttons {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }

        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: bold;
            text-decoration: none;
            display: inline-block;
        }

        .btn-print {
            background: #28a745;
            color: white;
        }

        .btn-print:hover {
            background: #218838;
        }

        .btn-back {
            background: #6c757d;
            color: white;
        }

        .btn-back:hover {
            background: #5a6268;
        }

        .summary-box {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .summary-item {
            display: inline-block;
            margin-right: 30px;
            font-size: 16px;
        }

        .summary-item strong {
            color: #007bff;
            font-size: 24px;
        }

        @media print {
            .no-print { display: none; }
            body { background: white; }
            .report-container { box-shadow: none; }
        }
    </style>
</head>
<body>
    <div class="report-container">
        <div class="report-header">
            <h1>📊 Reports & Lists</h1>
            <p>Comprehensive warranty tracking reports</p>
        </div>

        <!-- Report Type Selector -->
        <div class="report-tabs no-print">
            <a href="?type=all" class="tab-button <?= $report_type == 'all' ? 'active' : '' ?>">All Appliances</a>
            <a href="?type=active" class="tab-button <?= $report_type == 'active' ? 'active' : '' ?>">Active Warranties</a>
            <a href="?type=expired" class="tab-button <?= $report_type == 'expired' ? 'active' : '' ?>">Expired Warranties</a>
            <a href="?type=expiring" class="tab-button <?= $report_type == 'expiring' ? 'active' : '' ?>">Expiring Soon</a>
            <a href="?type=claims" class="tab-button <?= $report_type == 'claims' ? 'active' : '' ?>">Claims Report</a>
            <a href="?type=owners" class="tab-button <?= $report_type == 'owners' ? 'active' : '' ?>">Owners Report</a>
        </div>

        <div class="action-buttons no-print">
            <button onclick="window.print()" class="btn btn-print">🖨️ Print Report</button>
            <a href="admin_dashboard.php" class="btn btn-back">← Back to Dashboard</a>
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
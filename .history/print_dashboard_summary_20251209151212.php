<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}
require_once "database.php";
$db = new Database();
$conn = $db->connect();

// Get all statistics
$total_appliances = $conn->query("SELECT COUNT(*) as total FROM appliance")->fetch()['total'];
$active_warranties = $conn->query("SELECT COUNT(*) as total FROM appliance WHERE warranty_end_date >= CURDATE()")->fetch()['total'];
$expired_warranties = $conn->query("SELECT COUNT(*) as total FROM appliance WHERE warranty_end_date < CURDATE()")->fetch()['total'];
$expiring_soon = $conn->query("SELECT COUNT(*) as total FROM appliance WHERE warranty_end_date >= CURDATE() AND warranty_end_date <= DATE_ADD(CURDATE(), INTERVAL 30 DAY)")->fetch()['total'];
$total_claims = $conn->query("SELECT COUNT(*) as total FROM claim")->fetch()['total'];
$total_owners = $conn->query("SELECT COUNT(*) as total FROM owner")->fetch()['total'];

// Get expiring warranties list
$expiring_list = $conn->query("SELECT a.*, o.owner_name, 
                               DATEDIFF(a.warranty_end_date, CURDATE()) as days_left
                               FROM appliance a
                               LEFT JOIN owner o ON a.owner_id = o.id
                               WHERE a.warranty_end_date >= CURDATE() 
                               AND a.warranty_end_date <= DATE_ADD(CURDATE(), INTERVAL 30 DAY)
                               ORDER BY a.warranty_end_date ASC")->fetchAll(PDO::FETCH_ASSOC);

// Get recently expired warranties
$recent_expired = $conn->query("SELECT a.*, o.owner_name
                                FROM appliance a
                                LEFT JOIN owner o ON a.owner_id = o.id
                                WHERE a.warranty_end_date < CURDATE()
                                AND a.warranty_end_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
                                ORDER BY a.warranty_end_date DESC
                                LIMIT 10")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Summary Report</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', 'Trebuchet MS', sans-serif;
            background: #f5f7fa;
            padding: 20px;
            color: #333;
            line-height: 1.6;
        }

        .button-bar {
            max-width: 1200px;
            margin: 0 auto 30px;
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
        }

        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            font-size: 15px;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-weight: 600;
            transition: all 0.2s ease;
        }

        .btn-print {
            background: #28a745;
            color: white;
        }

        .btn-print:hover {
            background: #218838;
        }

        .btn-back {
            background: #667eea;
            color: white;
        }

        .btn-back:hover {
            background: #5568d3;
        }

        .report-container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 30px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .report-header {
            background: linear-gradient(135deg, #667eea 0%, #5568d3 100%);
            color: white;
            padding: 50px 40px;
            text-align: center;
        }

        .report-header h1 {
            font-size: 36px;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 15px;
        }

        .report-header h1 i {
            font-size: 40px;
        }

        .report-header h2 {
            font-size: 24px;
            font-weight: 300;
            opacity: 0.95;
        }

        .report-content {
            padding: 40px;
        }

        .report-meta {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
            padding: 20px;
            background: #f0f7ff;
            border-radius: 8px;
            border-left: 4px solid #667eea;
        }

        .meta-item {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .meta-item i {
            font-size: 20px;
            color: #667eea;
        }

        .meta-item .label {
            font-size: 12px;
            color: #666;
            text-transform: uppercase;
            font-weight: 600;
        }

        .meta-item .value {
            font-size: 15px;
            color: #333;
            font-weight: 600;
        }

        .section {
            margin-bottom: 50px;
        }

        .section-header {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 3px solid #667eea;
        }

        .section-header i {
            font-size: 24px;
            color: #667eea;
        }

        .section-title {
            font-size: 22px;
            color: #333;
            font-weight: 700;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            padding: 25px;
            text-align: center;
            transition: all 0.3s ease;
        }

        .stat-card:hover {
            border-color: #667eea;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.2);
        }

        .stat-card.primary {
            border-color: #667eea;
            background: #f0f7ff;
        }

        .stat-card.success {
            border-color: #28a745;
            background: #f0f9f5;
        }

        .stat-card.danger {
            border-color: #dc3545;
            background: #fdf5f6;
        }

        .stat-card.warning {
            border-color: #ffc107;
            background: #fffef0;
        }

        .stat-label {
            font-size: 12px;
            color: #666;
            text-transform: uppercase;
            font-weight: 600;
            letter-spacing: 0.5px;
            margin-bottom: 10px;
        }

        .stat-number {
            font-size: 42px;
            font-weight: 700;
            color: #667eea;
            margin-bottom: 5px;
        }

        .stat-card.success .stat-number {
            color: #28a745;
        }

        .stat-card.danger .stat-number {
            color: #dc3545;
        }

        .stat-card.warning .stat-number {
            color: #ffc107;
        }

        .stat-subtitle {
            font-size: 12px;
            color: #999;
        }

        .alert-box {
            padding: 20px 25px;
            margin: 20px 0;
            border-radius: 8px;
            border-left: 4px solid;
            display: flex;
            gap: 15px;
            align-items: flex-start;
        }

        .alert-box i {
            font-size: 18px;
            flex-shrink: 0;
            margin-top: 3px;
        }

        .alert-warning {
            background: #fffef0;
            border-color: #ffc107;
            color: #856404;
        }

        .alert-warning i {
            color: #ffc107;
        }

        .alert-danger {
            background: #fdf5f6;
            border-color: #dc3545;
            color: #721c24;
        }

        .alert-danger i {
            color: #dc3545;
        }

        .alert-success {
            background: #f0f9f5;
            border-color: #28a745;
            color: #155724;
        }

        .alert-success i {
            color: #28a745;
        }

        .table-section {
            margin-top: 25px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        thead {
            background: #f8f9fa;
            border-bottom: 2px solid #667eea;
        }

        th {
            padding: 15px;
            text-align: left;
            font-weight: 700;
            color: #333;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        td {
            padding: 15px;
            border-bottom: 1px solid #e0e0e0;
            font-size: 14px;
        }

        tbody tr:hover {
            background: #f0f7ff;
        }

        tbody tr:last-child td {
            border-bottom: none;
        }

        .summary-section {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 30px;
            margin-top: 20px;
        }

        .summary-section h3 {
            color: #333;
            font-size: 16px;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .summary-section h3 i {
            color: #667eea;
        }

        .summary-list {
            list-style: none;
            margin: 15px 0;
        }

        .summary-list li {
            padding: 10px 0;
            padding-left: 30px;
            position: relative;
            color: #555;
        }

        .summary-list li:before {
            content: "▸";
            position: absolute;
            left: 0;
            color: #667eea;
            font-weight: bold;
        }

        .report-footer {
            border-top: 2px solid #e0e0e0;
            margin-top: 50px;
            padding-top: 30px;
            text-align: center;
            color: #999;
            font-size: 13px;
        }

        .report-footer p {
            margin: 8px 0;
        }

        @media print {
            .button-bar {
                display: none !important;
            }

            body {
                background: white;
                padding: 0;
            }

            .report-container {
                box-shadow: none;
                max-width: 100%;
            }

            .report-header {
                page-break-after: avoid;
            }

            .section {
                page-break-inside: avoid;
            }

            table {
                page-break-inside: avoid;
            }
        }

        @page {
            size: A4;
            margin: 1.5cm;
        }

        @media (max-width: 768px) {
            .report-header {
                padding: 30px 20px;
            }

            .report-header h1 {
                font-size: 24px;
            }

            .report-content {
                padding: 20px;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }

            .report-meta {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="button-bar">
        <button onclick="window.print()" class="btn btn-print">
            <i class="fas fa-print"></i>
            Print Report
        </button>
        <a href="admin_dashboard.php" class="btn btn-back">
            <i class="fas fa-arrow-left"></i>
            Back to Dashboard
        </a>
    </div>

    <div class="report-container">
        <div class="report-header">
            <h1>
                <i class="fas fa-chart-bar"></i>
                Warranty Tracker
            </h1>
            <h2>Dashboard Summary Report</h2>
        </div>

        <div class="report-content">
            <!-- Report Metadata -->
            <div class="report-meta">
                <div class="meta-item">
                    <i class="fas fa-calendar-alt"></i>
                    <div>
                        <div class="label">Report Generated</div>
                        <div class="value"><?= date('F d, Y') ?> at <?= date('h:i A') ?></div>
                    </div>
                </div>
                <div class="meta-item">
                    <i class="fas fa-clock"></i>
                    <div>
                        <div class="label">Report Period</div>
                        <div class="value">All Time</div>
                    </div>
                </div>
                <div class="meta-item">
                    <i class="fas fa-database"></i>
                    <div>
                        <div class="label">Data Snapshot</div>
                        <div class="value">Current</div>
                    </div>
                </div>
            </div>

            <!-- Overall Statistics Section -->
            <div class="section">
                <div class="section-header">
                    <i class="fas fa-th-large"></i>
                    <div class="section-title">Overall Statistics</div>
                </div>

                <div class="stats-grid">
                    <div class="stat-card primary">
                        <div class="stat-label">Total Appliances</div>
                        <div class="stat-number"><?= $total_appliances ?></div>
                        <div class="stat-subtitle">Being tracked</div>
                    </div>

                    <div class="stat-card success">
                        <div class="stat-label">Active Warranties</div>
                        <div class="stat-number"><?= $active_warranties ?></div>
                        <div class="stat-subtitle">Currently valid</div>
                    </div>

                    <div class="stat-card danger">
                        <div class="stat-label">Expired Warranties</div>
                        <div class="stat-number"><?= $expired_warranties ?></div>
                        <div class="stat-subtitle">No longer valid</div>
                    </div>

                    <div class="stat-card warning">
                        <div class="stat-label">Expiring Soon</div>
                        <div class="stat-number"><?= $expiring_soon ?></div>
                        <div class="stat-subtitle">Next 30 days</div>
                    </div>

                    <div class="stat-card primary">
                        <div class="stat-label">Total Claims</div>
                        <div class="stat-number"><?= $total_claims ?></div>
                        <div class="stat-subtitle">Filed to date</div>
                    </div>

                    <div class="stat-card primary">
                        <div class="stat-label">Registered Owners</div>
                        <div class="stat-number"><?= $total_owners ?></div>
                        <div class="stat-subtitle">In system</div>
                    </div>
                </div>

                <!-- Status Alerts -->
                <?php
                $active_percentage = $total_appliances > 0 ? round(($active_warranties / $total_appliances) * 100) : 0;
                $expired_percentage = $total_appliances > 0 ? round(($expired_warranties / $total_appliances) * 100) : 0;
                ?>

                <?php if ($active_percentage >= 70): ?>
                <div class="alert-box alert-success">
                    <i class="fas fa-check-circle"></i>
                    <div>
                        <strong>Good System Health:</strong> <?= $active_percentage ?>% of warranties are currently active. Warranty coverage is well-maintained.
                    </div>
                </div>
                <?php elseif ($expired_percentage >= 50): ?>
                <div class="alert-box alert-danger">
                    <i class="fas fa-exclamation-circle"></i>
                    <div>
                        <strong>Alert:</strong> <?= $expired_percentage ?>% of warranties have expired. Immediate action required to contact owners for renewals.
                    </div>
                </div>
                <?php endif; ?>

                <?php if ($expiring_soon > 0): ?>
                <div class="alert-box alert-warning">
                    <i class="fas fa-clock"></i>
                    <div>
                        <strong>Action Required:</strong> <?= $expiring_soon ?> warranty(ies) will expire in the next 30 days. Contact owners proactively.
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <!-- Warranties Expiring Soon -->
            <?php if (count($expiring_list) > 0): ?>
            <div class="section">
                <div class="section-header">
                    <i class="fas fa-hourglass-end"></i>
                    <div class="section-title">Warranties Expiring Soon (Next 30 Days)</div>
                </div>

                <div class="table-section">
                    <table>
                        <thead>
                            <tr>
                                <th>Appliance</th>
                                <th>Owner</th>
                                <th>Model Number</th>
                                <th>Expiration Date</th>
                                <th>Days Left</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($expiring_list as $item): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($item['appliance_name']) ?></strong></td>
                                <td><?= htmlspecialchars($item['owner_name'] ?? 'N/A') ?></td>
                                <td><?= htmlspecialchars($item['model_number']) ?></td>
                                <td><?= date('M d, Y', strtotime($item['warranty_end_date'])) ?></td>
                                <td><strong style="color: #ffc107;"><?= $item['days_left'] ?></strong> days</td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endif; ?>

            <!-- Recently Expired Warranties -->
            <?php if (count($recent_expired) > 0): ?>
            <div class="section">
                <div class="section-header">
                    <i class="fas fa-exclamation-triangle"></i>
                    <div class="section-title">Recently Expired Warranties (Last 30 Days)</div>
                </div>

                <div class="table-section">
                    <table>
                        <thead>
                            <tr>
                                <th>Appliance</th>
                                <th>Owner</th>
                                <th>Model Number</th>
                                <th>Expired On</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($recent_expired as $item): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($item['appliance_name']) ?></strong></td>
                                <td><?= htmlspecialchars($item['owner_name'] ?? 'N/A') ?></td>
                                <td><?= htmlspecialchars($item['model_number']) ?></td>
                                <td><?= date('M d, Y', strtotime($item['warranty_end_date'])) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endif; ?>

            <!-- Summary & Recommendations -->
            <div class="section">
                <div class="section-header">
                    <i class="fas fa-file-alt"></i>
                    <div class="section-title">Summary & Recommendations</div>
                </div>

                <div class="summary-section">
                    <h3><i class="fas fa-heartbeat"></i> System Health Overview</h3>
                    <ul class="summary-list">
                        <li>Total appliances being tracked: <strong><?= $total_appliances ?></strong></li>
                        <li>Warranty compliance rate: <strong><?= $active_percentage ?>%</strong></li>
                        <li>Claims processed to date: <strong><?= $total_claims ?></strong></li>
                        <li>Owner network size: <strong><?= $total_owners ?></strong></li>
                    </ul>
                </div>

                <div class="summary-section" style="margin-top: 20px;">
                    <h3><i class="fas fa-tasks"></i> Recommended Action Items</h3>
                    <ul class="summary-list">
                        <?php if ($expiring_soon > 0): ?>
                        <li>Contact <?= $expiring_soon ?> owner(s) about expiring warranties within 30 days</li>
                        <?php endif; ?>

                        <?php if ($expired_warranties > 0): ?>
                        <li>Review <?= $expired_warranties ?> expired warranties for potential renewal opportunities</li>
                        <?php endif; ?>

                        <?php if ($total_claims > 0): ?>
                        <li>Process and follow up on any pending warranty claims</li>
                        <?php endif; ?>

                        <li>Schedule regular system maintenance and data backups</li>
                        <li>Review warranty policies and renewal schedules quarterly</li>
                    </ul>
                </div>
            </div>

            <!-- Report Footer -->
            <div class="report-footer">
                <p><strong>Warranty Tracker Management System</strong></p>
                <p>This report is confidential and intended for authorized personnel only.</p>
                <p>© <?= date('Y') ?> Warranty Tracker. All rights reserved.</p>
                <p>For questions or support, please contact the system administrator.</p>
            </div>
        </div>
    </div>
</body>
</html>
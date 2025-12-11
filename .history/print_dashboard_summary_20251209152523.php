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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"
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
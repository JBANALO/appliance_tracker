<?php
session_start();

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

require_once "database.php";

$db = new Database();
$conn = $db->connect();


$appliance_id = $_GET['id'] ?? null;
$appliance_data = null;

if ($appliance_id) {
    $sql = "SELECT a.*, o.owner_name, o.email, o.phone, o.address,
            CASE 
                WHEN a.warranty_end_date < CURDATE() THEN 'Expired'
                ELSE 'Active'
            END as status
            FROM appliance a
            LEFT JOIN owner o ON a.owner_id = o.id
            WHERE a.id = :id";
    $query = $conn->prepare($sql);
    $query->bindParam(':id', $appliance_id);
    $query->execute();
    $appliance_data = $query->fetch(PDO::FETCH_ASSOC);
}


$all_appliances = $conn->query("SELECT a.id, a.appliance_name, a.serial_number, o.owner_name 
                                 FROM appliance a 
                                 LEFT JOIN owner o ON a.owner_id = o.id 
                                 ORDER BY a.appliance_name")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Printable Warranty Certificate</title>
</head>
<body>
  
    <div class="no-print">
        <div class="selector-header">
            <h2><i class="fas fa-print"></i> Print Warranty Certificate</h2>
            <p>Select an appliance to generate a printable warranty certificate</p>
        </div>

        <form method="GET">
            <div class="form-group">
                <label for="appliance_select">Select Appliance:</label>
                <select name="id" id="appliance_select" required>
                    <option value="">-- Choose an Appliance --</option>
                    <?php foreach($all_appliances as $app): ?>
                        <option value="<?= $app['id'] ?>" <?= $appliance_id == $app['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($app['appliance_name']) ?> - 
                            S/N: <?= htmlspecialchars($app['serial_number']) ?> 
                            (Owner: <?= htmlspecialchars($app['owner_name'] ?? 'N/A') ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit">Load Certificate</button>
        </form>

        <?php if ($appliance_data): ?>
        <div style="margin-top: 20px; display: flex; gap: 10px;">
            <button onclick="window.print()" class="btn-print">🖨️ Print Certificate</button>
            <button onclick="window.location.href='admin_dashboard.php'" class="btn-back">← Back to Dashboard</button>
        </div>
        <?php endif; ?>
    </div>

    <?php if ($appliance_data): ?>
    <div class="certificate">
        <div class="certificate-header">
            <div class="certificate-title">WARRANTY CERTIFICATE</div>
            <div class="certificate-subtitle">Official Warranty Documentation</div>
        </div>

        <div class="certificate-body">
            <p style="text-align: center; margin-bottom: 30px; font-size: 16px;">
                This certifies that the following appliance is covered under warranty:
            </p>

            <div class="info-row">
                <div class="info-item">
                    <div class="info-label">Appliance Name</div>
                    <div class="info-value"><?= htmlspecialchars($appliance_data['appliance_name']) ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Model Number</div>
                    <div class="info-value"><?= htmlspecialchars($appliance_data['model_number']) ?></div>
                </div>
            </div>

            <div class="info-row">
                <div class="info-item">
                    <div class="info-label">Serial Number</div>
                    <div class="info-value"><?= htmlspecialchars($appliance_data['serial_number']) ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Certificate ID</div>
                    <div class="info-value">WRT-<?= str_pad($appliance_data['id'], 6, '0', STR_PAD_LEFT) ?></div>
                </div>
            </div>

            <div class="info-row">
                <div class="info-item">
                    <div class="info-label">Owner Name</div>
                    <div class="info-value"><?= htmlspecialchars($appliance_data['owner_name'] ?? 'N/A') ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Contact Email</div>
                    <div class="info-value"><?= htmlspecialchars($appliance_data['email'] ?? 'N/A') ?></div>
                </div>
            </div>

            <div class="info-row">
                <div class="info-item">
                    <div class="info-label">Phone Number</div>
                    <div class="info-value"><?= htmlspecialchars($appliance_data['phone'] ?? 'N/A') ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Purchase Date</div>
                    <div class="info-value"><?= date('F d, Y', strtotime($appliance_data['purchase_date'])) ?></div>
                </div>
            </div>

            <div class="info-row">
                <div class="info-item">
                    <div class="info-label">Warranty Period</div>
                    <div class="info-value"><?= htmlspecialchars($appliance_data['warranty_period']) ?> Year(s)</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Warranty Expiration</div>
                    <div class="info-value"><?= date('F d, Y', strtotime($appliance_data['warranty_end_date'])) ?></div>
                </div>
            </div>

            <?php
            $today = date('Y-m-d');
            $warranty_end = $appliance_data['warranty_end_date'];
            $is_expired = $today > $warranty_end;
            $days_left = ceil((strtotime($warranty_end) - strtotime($today)) / (60 * 60 * 24));
            ?>

            <div class="warranty-status <?= $is_expired ? 'expired' : 'active' ?>">
                <?php if ($is_expired): ?>
                     WARRANTY EXPIRED
                <?php else: ?>
                    ✓ WARRANTY ACTIVE (<?= $days_left ?> days remaining)
                <?php endif; ?>
            </div>

            
            </div>
        </div>

        <div class="certificate-footer">
            <p><strong>Certificate Generated:</strong> <?= date('F d, Y H:i:s') ?></p>
            <p style="margin-top: 10px;">
                This is an official warranty certificate. Keep this document for your records.<br>
                For warranty claims or inquiries, please contact our support team.
            </p>
            <p style="margin-top: 10px; font-style: italic;">
                Warranty Tracker System - Appliance Warranty Management
            </p>
        </div>
    </div>
    <?php else: ?>
    <div class="certificate" style="text-align: center; padding: 100px 40px;">
        <h2 style="color: #999;">Please select an appliance to generate certificate</h2>
    </div>
    <?php endif; ?>
</body>
</html>
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print Warranty Certificate</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @media print {
            .no-print { display: none !important; }
            body { background: white; }
        }
        
        .no-print {
            max-width: 900px;
            margin: 30px auto;
            padding: 0 20px;
        }
        
        .page-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            border-radius: 12px;
            margin-bottom: 30px;
            text-align: center;
        }
        
        .page-header h1 {
            margin: 0 0 10px 0;
            font-size: 28px;
            font-weight: 600;
        }
        
        .page-header p {
            margin: 0;
            opacity: 0.95;
            font-size: 15px;
        }
        
        .selection-card {
            background: white;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        
        .selection-card h2 {
            margin: 0 0 20px 0;
            font-size: 20px;
            color: #1f2937;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .selection-card h2 i {
            color: #667eea;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #374151;
        }
        
        .form-group select {
            width: 100%;
            padding: 12px;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.3s;
        }
        
        .form-group select:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        
        .button-group {
            display: grid;
            grid-template-columns: 1fr;
            gap: 12px;
            margin-top: 20px;
        }
        
        .button-group button,
        .button-group a {
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            font-size: 15px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s;
            text-align: center;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        
        .btn-load {
            background: #667eea;
            color: white;
        }
        
        .btn-load:hover {
            background: #5568d3;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
        }
        
        .btn-print {
            background: #10b981;
            color: white;
        }
        
        .btn-print:hover {
            background: #059669;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
        }
        
        .btn-back {
            background: #6b7280;
            color: white;
        }
        
        .btn-back:hover {
            background: #4b5563;
            transform: translateY(-2px);
        }
        
        .certificate {
            max-width: 800px;
            margin: 40px auto;
            background: white;
            border: 3px solid #667eea;
            border-radius: 12px;
            padding: 40px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }
        
        .certificate-header {
            text-align: center;
            border-bottom: 3px solid #667eea;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        
        .certificate-title {
            font-size: 36px;
            font-weight: 700;
            color: #667eea;
            margin-bottom: 10px;
            letter-spacing: 2px;
        }
        
        .certificate-subtitle {
            font-size: 18px;
            color: #6b7280;
        }
        
        .info-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }
        
        .info-item {
            background: #f9fafb;
            padding: 15px;
            border-radius: 8px;
            border-left: 4px solid #667eea;
        }
        
        .info-label {
            font-size: 12px;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 5px;
            font-weight: 600;
        }
        
        .info-value {
            font-size: 16px;
            color: #1f2937;
            font-weight: 500;
        }
        
        .warranty-status {
            text-align: center;
            padding: 20px;
            border-radius: 8px;
            font-size: 20px;
            font-weight: 700;
            margin-top: 30px;
        }
        
        .warranty-status.active {
            background: #d1fae5;
            color: #059669;
        }
        
        .warranty-status.expired {
            background: #fee2e2;
            color: #dc2626;
        }
        
        .certificate-footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 2px solid #e5e7eb;
            text-align: center;
            font-size: 14px;
            color: #6b7280;
        }
        
        .placeholder-message {
            text-align: center;
            padding: 100px 40px;
            color: #9ca3af;
        }
        
        .placeholder-message i {
            font-size: 64px;
            margin-bottom: 20px;
            color: #d1d5db;
        }
        
        .placeholder-message h2 {
            margin: 0;
            color: #6b7280;
        }
    </style>
</head>
<body>
    <div class="no-print">
        <div class="page-header">
            <h1><i class="fas fa-certificate"></i> Print Warranty Certificate</h1>
            <p>Select an appliance to generate a printable warranty certificate</p>
        </div>

        <div class="selection-card">
            <h2><i class="fas fa-search"></i> Select Appliance</h2>
            <form method="GET">
                <div class="form-group">
                    <label for="appliance_select"><i class="fas fa-box"></i> Choose Appliance</label>
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
                <div class="button-group">
                    <button type="submit" class="btn-load"><i class="fas fa-file-certificate"></i> Load Certificate</button>
                </div>
            </form>
        </div>

        <?php if ($appliance_data): ?>
        <div class="button-group">
            <button onclick="window.print()" class="btn-print"><i class="fas fa-print"></i> Print Certificate</button>
            <a href="admin_dashboard.php" class="btn-back"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
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
                    <i class="fas fa-times-circle"></i> WARRANTY EXPIRED
                <?php else: ?>
                    <i class="fas fa-check-circle"></i> WARRANTY ACTIVE (<?= $days_left ?> days remaining)
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
    <div class="certificate">
        <div class="placeholder-message">
            <i class="fas fa-box-open"></i>
            <h2>Please select an appliance to generate certificate</h2>
        </div>
    </div>
    <?php endif; ?>
</body>
</html>
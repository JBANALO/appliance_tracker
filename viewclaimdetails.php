<?php
session_start();

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

require_once "claim.php";
require_once "EmailNotification.php";

$id = $_GET['id'] ?? null;

if (!$id) {
    header("Location: viewclaim.php");
    exit;
}

$claimObj = new Claim();
$claim = $claimObj->getClaimById($id);

if (!$claim) {
    header("Location: viewclaim.php");
    exit;
}

$claimStatus = $claim['claim_status'];
$dropdownStatuses = ['Pending', 'Approved', 'Rejected'];

if ($claimStatus === 'Approved') {
    $dropdownStatuses = ['Approved'];
} elseif ($claimStatus === 'Rejected') {
    $dropdownStatuses = ['Rejected'];
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $new_status = trim($_POST['status'] ?? '');
    $admin_notes = trim($_POST['admin_notes'] ?? '');

    $allowed_statuses = $dropdownStatuses;

    if (in_array($new_status, $allowed_statuses, true)) {
        if ($claimObj->updateClaimStatus($id, $new_status, $admin_notes)) {
            // Send email non-blocking
            try {
                @$emailNotification = new EmailNotification();
                @$emailNotification->sendClaimStatusUpdateEmail(
                    $claim['email'],
                    $claim['owner_name'],
                    $claim['appliance_name'],
                    $id,
                    $new_status,
                    $admin_notes
                );
            } catch (Exception $e) {
                // Silently fail - don't block update
            }

            header("Location: viewclaim.php?status_updated=1");
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Claim Details</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f7fa;
            padding: 20px;
        }

        .container {
            max-width: 1000px;
            margin: 0 auto;
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            padding: 40px;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 40px;
            padding-bottom: 20px;
            border-bottom: 2px solid #f0f0f0;
        }

        .header h1 {
            color: #333;
            font-size: 32px;
            margin: 0;
        }

        .back-btn {
            background: #667eea;
            color: white;
            padding: 10px 20px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .back-btn:hover {
            background: #5568d3;
        }

        .section {
            margin-bottom: 40px;
        }

        .section h2 {
            color: #333;
            font-size: 20px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            padding-bottom: 10px;
            border-bottom: 2px solid #667eea;
        }

        .info-row {
            display: grid;
            grid-template-columns: 250px 1fr;
            gap: 20px;
            padding: 15px 0;
            border-bottom: 1px solid #f0f0f0;
            align-items: start;
        }

        .info-row:last-child {
            border-bottom: none;
        }

        .info-label {
            font-weight: 600;
            color: #555;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .info-value {
            color: #333;
            font-size: 16px;
        }

        .status-badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 13px;
        }

        .status-pending {
            background: #fff3cd;
            color: #856404;
        }

        .status-approved {
            background: #d4edda;
            color: #155724;
        }

        .status-rejected {
            background: #f8d7da;
            color: #721c24;
        }

        .issue-box {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            border-left: 4px solid #667eea;
            margin-top: 10px;
            line-height: 1.6;
            color: #333;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        select, textarea {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 15px;
            font-family: inherit;
        }

        select:focus, textarea:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        textarea {
            min-height: 120px;
            resize: vertical;
        }

        button {
            background: #667eea;
            color: white;
            padding: 14px 32px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        button:hover {
            background: #5568d3;
        }

        .appliance-section {
            padding: 0;
            margin-bottom: 0;
        }

        .empty-state {
            text-align: center;
            padding: 30px;
            color: #999;
            background: #f8f9fa;
            border-radius: 8px;
            margin-top: 10px;
        }

        @media (max-width: 768px) {
            .container {
                padding: 20px;
            }

            .header {
                flex-direction: column;
                gap: 15px;
                align-items: flex-start;
            }

            .info-row {
                grid-template-columns: 1fr;
                gap: 8px;
            }

            .info-label {
                font-size: 12px;
            }

            .info-value {
                font-size: 15px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Claim Details</h1>
            <a href="viewclaim.php" class="back-btn">
                <i class="fas fa-arrow-left"></i>
                Back to Claims
            </a>
        </div>

        
        <div class="section">
            <h2>
                <i class="fas fa-laptop"></i>
                Appliance Information
            </h2>
            <div class="appliance-section">
                <div class="info-row">
                    <div class="info-label">Appliance Name:</div>
                    <div class="info-value"><?= htmlspecialchars($claim['appliance_name']) ?></div>
                </div>
                <div class="info-row">
                    <div class="info-label">Model Number:</div>
                    <div class="info-value"><?= htmlspecialchars($claim['model_number']) ?></div>
                </div>
                <div class="info-row">
                    <div class="info-label">Serial Number:</div>
                    <div class="info-value"><?= htmlspecialchars($claim['serial_number']) ?></div>
                </div>
                <div class="info-row">
                    <div class="info-label">Warranty End Date:</div>
                    <div class="info-value"><?= htmlspecialchars($claim['warranty_end_date']) ?></div>
                </div>
            </div>
        </div>

        
        <div class="section">
            <h2>
                <i class="fas fa-user"></i>
                Owner Information
            </h2>
            <div class="info-row">
                <div class="info-label">Owner Name:</div>
                <div class="info-value"><?= htmlspecialchars($claim['owner_name']) ?></div>
            </div>
            <div class="info-row">
                <div class="info-label">Email:</div>
                <div class="info-value"><?= htmlspecialchars($claim['email']) ?></div>
            </div>
            <div class="info-row">
                <div class="info-label">Phone:</div>
                <div class="info-value"><?= htmlspecialchars($claim['phone']) ?></div>
            </div>
        </div>

       
        <div class="section">
            <h2>
                <i class="fas fa-file-contract"></i>
                Claim Information
            </h2>
            <div class="info-row">
                <div class="info-label">Claim Date:</div>
                <div class="info-value"><?= htmlspecialchars($claim['claim_date']) ?></div>
            </div>
            <div class="info-row">
                <div class="info-label">Claim Status:</div>
                <div class="info-value">
                    <span class="status-badge status-<?= strtolower(str_replace(' ', '-', $claim['claim_status'])) ?>">
                        <?= htmlspecialchars($claim['claim_status']) ?>
                    </span>
                </div>
            </div>
            <div class="info-row">
                <div class="info-label">Warranty Status:</div>
                <div class="info-value">
                    <span class="status-badge status-<?= strtolower(str_replace(' ', '-', $claim['status'])) ?>">
                        <?= htmlspecialchars($claim['status']) ?>
                    </span>
                </div>
            </div>
            <div class="info-row">
                <div class="info-label">Issue Description:</div>
                <div class="info-value">
                    <div class="issue-box">
                        <?= nl2br(htmlspecialchars($claim['issue_description'])) ?>
                    </div>
                </div>
            </div>
            <?php if (!empty($claim['admin_notes'])): ?>
            <div class="info-row">
                <div class="info-label">Admin Notes:</div>
                <div class="info-value">
                    <div class="issue-box">
                        <?= nl2br(htmlspecialchars($claim['admin_notes'])) ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Update Claim Status Section -->
        <div class="section">
            <h2>
                <i class="fas fa-edit"></i>
                Update Claim Status
            </h2>
            <form method="POST" action="">
                <div class="form-group">
                    <label for="status">
                        <i class="fas fa-check-circle"></i>
                        Status
                    </label>
                    <select name="status" id="status" required>
                        <?php foreach ($dropdownStatuses as $statusOption): ?>
                            <option value="<?= $statusOption ?>" <?= $claim['claim_status'] == $statusOption ? 'selected' : '' ?>><?= $statusOption ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="admin_notes">
                        <i class="fas fa-sticky-note"></i>
                        Admin Notes
                    </label>
                    <textarea name="admin_notes" id="admin_notes" placeholder="Add any notes about this claim..."><?= htmlspecialchars($claim['admin_notes']) ?></textarea>
                </div>

                <button type="submit">
                    <i class="fas fa-save"></i>
                    Update Claim
                </button>
            </form>
        </div>
    </div>
</body>
</html>
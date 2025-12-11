<?php
require_once "database.php";
require_once "Appliance.php";
require_once "Owner.php";
require_once "Claim.php";

$applianceObj = new Appliance();
$ownerObj = new Owner();
$claimObj = new Claim();

if (isset($_GET["id"])) {
    $appliance_id = trim(htmlspecialchars($_GET["id"]));
    $appliance = $applianceObj->getApplianceById($appliance_id);
    
    if (!$appliance) {
        echo "Appliance not found.";
        exit;
    }
    
    $owner = $ownerObj->getOwnerById($appliance["owner_id"]);
    $claims = $claimObj->getClaimsByAppliance($appliance_id);
} else {
    echo "Invalid request.";
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Appliance Details</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="table_style.css">
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
            max-width: 1200px;
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

        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }

        .info-item {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            border: 1px solid #e0e0e0;
        }

        .info-label {
            font-size: 12px;
            color: #999;
            text-transform: uppercase;
            font-weight: 600;
            margin-bottom: 5px;
            letter-spacing: 0.5px;
        }

        .info-value {
            font-size: 16px;
            color: #333;
            font-weight: 500;
            word-break: break-word;
        }

        .status-badge {
            display: inline-block;
            padding: 8px 16px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 13px;
        }

        .status-badge.active {
            background: #d4edda;
            color: #155724;
        }

        .status-badge.expired {
            background: #f8d7da;
            color: #721c24;
        }

        .action-buttons {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            margin-top: 20px;
        }

        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
        }

        .btn-edit {
            background: #28a745;
            color: white;
        }

        .btn-edit:hover {
            background: #218838;
        }

        .btn-delete {
            background: #dc3545;
            color: white;
        }

        .btn-delete:hover {
            background: #a71d2a;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }

        th {
            background: #667eea;
            color: white;
            padding: 15px;
            text-align: left;
            font-weight: 600;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        td {
            padding: 15px;
            border-bottom: 1px solid #f0f0f0;
            color: #333;
        }

        tr:hover {
            background: #f8f9fa;
        }

        .claim-status {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 12px;
        }

        .claim-status.pending {
            background: #fff3cd;
            color: #856404;
        }

        .claim-status.approved {
            background: #d4edda;
            color: #155724;
        }

        .claim-status.rejected {
            background: #f8d7da;
            color: #721c24;
        }

        .view-link {
            color: #007bff;
            text-decoration: none;
            font-weight: 600;
        }

        .view-link:hover {
            text-decoration: underline;
        }

        .no-data {
            text-align: center;
            padding: 40px;
            color: #999;
            background: #f8f9fa;
            border-radius: 8px;
        }

        .owner-card {
            background: #f8f9fa;
            border-left: 4px solid #667eea;
            padding: 20px;
            border-radius: 8px;
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

            .info-grid {
                grid-template-columns: 1fr;
            }

            .action-buttons {
                flex-direction: column;
            }

            .btn {
                width: 100%;
                justify-content: center;
            }

            th, td {
                padding: 12px;
                font-size: 14px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Appliance Details</h1>
            <a href="viewappliance.php" class="back-btn">
                <i class="fas fa-arrow-left"></i>
                Back to List
            </a>
        </div>

        <!-- Appliance Information Section -->
        <div class="section">
            <h2>
                <i class="fas fa-laptop"></i>
                Appliance Information
            </h2>
            <div class="info-grid">
                <div class="info-item">
                    <div class="info-label">Appliance Name</div>
                    <div class="info-value"><?= htmlspecialchars($appliance["appliance_name"]) ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Model Number</div>
                    <div class="info-value"><?= htmlspecialchars($appliance["model_number"]) ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Serial Number</div>
                    <div class="info-value"><?= htmlspecialchars($appliance["serial_number"]) ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Purchase Date</div>
                    <div class="info-value"><?= htmlspecialchars($appliance["purchase_date"]) ?></div>
                </div>
            </div>
        </div>

        <!-- Warranty Information Section -->
        <div class="section">
            <h2>
                <i class="fas fa-shield-alt"></i>
                Warranty Information
            </h2>
            <div class="info-grid">
                <div class="info-item">
                    <div class="info-label">Warranty Period</div>
                    <div class="info-value"><?= htmlspecialchars($appliance["warranty_period"]) ?> year(s)</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Warranty End Date</div>
                    <div class="info-value"><?= htmlspecialchars($appliance["warranty_end_date"]) ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Status</div>
                    <div class="info-value">
                        <span class="status-badge <?= strtolower($appliance["status"]) ?>">
                            <?= htmlspecialchars($appliance["status"]) ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Owner Information Section -->
        <div class="section">
            <h2>
                <i class="fas fa-user"></i>
                Owner Information
            </h2>
            <?php if ($owner): ?>
            <div class="owner-card">
                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-label">Owner Name</div>
                        <div class="info-value"><?= htmlspecialchars($owner["owner_name"]) ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Email Address</div>
                        <div class="info-value"><?= htmlspecialchars($owner["email"]) ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Phone Number</div>
                        <div class="info-value"><?= htmlspecialchars($owner["phone"]) ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Address</div>
                        <div class="info-value"><?= htmlspecialchars($owner["address"]) ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">City</div>
                        <div class="info-value"><?= htmlspecialchars($owner["city"]) ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">State/Province</div>
                        <div class="info-value"><?= htmlspecialchars($owner["state"]) ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Zip Code</div>
                        <div class="info-value"><?= htmlspecialchars($owner["zip_code"]) ?></div>
                    </div>
                </div>
            </div>
            <?php else: ?>
            <div class="no-data">
                <i class="fas fa-user-slash" style="font-size: 48px; margin-bottom: 15px; opacity: 0.5;"></i>
                <p>Owner information not found.</p>
            </div>
            <?php endif; ?>
        </div>

        <!-- Warranty Claims Section -->
        <div class="section">
            <h2>
                <i class="fas fa-file-contract"></i>
                Warranty Claims
            </h2>
            <?php if ($claims && count($claims) > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Claim Date</th>
                        <th>Status</th>
                        <th>Description</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($claims as $claim): ?>
                    <tr>
                        <td><?= htmlspecialchars($claim["claim_date"]) ?></td>
                        <td>
                            <span class="claim-status <?= strtolower($claim["claim_status"]) ?>">
                                <?= htmlspecialchars($claim["claim_status"]) ?>
                            </span>
                        </td>
                        <td><?= htmlspecialchars(substr($claim["claim_description"], 0, 50)) ?>...</td>
                        <td>
                            <a href="viewclaimdetails.php?id=<?= $claim['id'] ?>" class="view-link">
                                <i class="fas fa-eye"></i> View Details
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <?php else: ?>
            <div class="no-data">
                <i class="fas fa-inbox" style="font-size: 48px; margin-bottom: 15px; opacity: 0.5;"></i>
                <p>No warranty claims filed for this appliance.</p>
            </div>
            <?php endif; ?>
        </div>

        <!-- Action Buttons -->
        <div class="action-buttons">
            <a href="editappliance.php?id=<?= $appliance['id'] ?>" class="btn btn-edit">
                <i class="fas fa-edit"></i>
                Edit Appliance
            </a>
            <a href="deleteappliance.php?id=<?= $appliance['id'] ?>" class="btn btn-delete" onclick="return confirm('Are you sure you want to delete this appliance?');">
                <i class="fas fa-trash"></i>
                Delete Appliance
            </a>
        </div>
    </div>
</body>
</html>
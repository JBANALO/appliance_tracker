<?php
require_once "database.php";
require_once "Owner.php";

$ownerObj = new Owner();

if (isset($_GET["id"])) {
    $owner_id = trim(htmlspecialchars($_GET["id"]));
    $owner = $ownerObj->getOwnerById($owner_id);
    
    if (!$owner) {
        echo "Owner not found.";
        exit;
    }
} else {
    echo "Invalid request.";
    exit;
}

$appliances = $ownerObj->getOwnerAppliances($owner_id);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Owner Details</title>
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

        .owner-info-section {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 30px;
            margin-bottom: 40px;
            border-left: 4px solid #667eea;
        }

        .owner-info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .info-item {
            background: white;
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

        .action-buttons {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
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

        .appliances-section {
            margin-top: 40px;
        }

        .appliances-section h2 {
            color: #333;
            font-size: 24px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
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

        .status {
            padding: 6px 12px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 12px;
            display: inline-block;
        }

        .status.active {
            background: #d4edda;
            color: #155724;
        }

        .status.expired {
            background: #f8d7da;
            color: #721c24;
        }

        .action-links {
            display: flex;
            gap: 8px;
        }

        .action-links a {
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
            text-decoration: none;
            border: 1px solid #e0e0e0;
        }

        .action-links .view-btn {
            color: #007bff;
            border-color: #007bff;
        }

        .action-links .view-btn:hover {
            background: #007bff;
            color: white;
        }

        .action-links .edit-btn {
            color: #28a745;
            border-color: #28a745;
        }

        .action-links .edit-btn:hover {
            background: #28a745;
            color: white;
        }

        .no-appliances {
            text-align: center;
            padding: 40px;
            color: #999;
            background: #f8f9fa;
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

            .owner-info-grid {
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
            <h1>Owner Details</h1>
            <a href="viewowner.php" class="back-btn">
                <i class="fas fa-arrow-left"></i>
                Back to List
            </a>
        </div>

        <div class="owner-info-section">
            <div class="owner-info-grid">
                <div class="info-item">
                    <div class="info-label">Full Name</div>
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

            <div class="action-buttons">
                <a href="editowner.php?id=<?= $owner['id'] ?>" class="btn btn-edit">
                    <i class="fas fa-edit"></i>
                    Edit Owner
                </a>
                <a href="deleteowner.php?id=<?= $owner['id'] ?>" class="btn btn-delete" onclick="return confirm('Are you sure you want to delete this owner?');">
                    <i class="fas fa-trash"></i>
                    Delete Owner
                </a>
            </div>
        </div>

        <div class="appliances-section">
            <h2>
                <i class="fas fa-list"></i>
                Associated Appliances
            </h2>
            
            <?php if ($appliances && count($appliances) > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>Appliance Name</th>
                        <th>Model Number</th>
                        <th>Serial Number</th>
                        <th>Warranty End Date</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                $no = 1;
                foreach ($appliances as $appliance) {
                    $statusClass = strtolower($appliance["status"]);
                ?>
                    <tr>
                        <td><?= $no++ ?></td>
                        <td><?= htmlspecialchars($appliance["appliance_name"]) ?></td>
                        <td><?= htmlspecialchars($appliance["model_number"]) ?></td>
                        <td><?= htmlspecialchars($appliance["serial_number"]) ?></td>
                        <td><?= htmlspecialchars($appliance["warranty_end_date"]) ?></td>
                        <td><span class="status <?= $statusClass ?>"><?= htmlspecialchars($appliance["status"]) ?></span></td>
                        <td>
                            <div class="action-links">
                                <a href="viewdetails.php?id=<?= $appliance['id'] ?>" class="view-btn">
                                    <i class="fas fa-eye"></i> View
                                </a>
                                <a href="editappliance.php?id=<?= $appliance['id'] ?>" class="edit-btn">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                            </div>
                        </td>
                    </tr>
                <?php
                }
                ?>
                </tbody>
            </table>
            <?php else: ?>
            <div class="no-appliances">
                <i class="fas fa-inbox" style="font-size: 48px; margin-bottom: 15px; opacity: 0.5;"></i>
                <p>No appliances found for this owner.</p>
            </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
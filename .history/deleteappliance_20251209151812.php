<?php
require_once "database.php";
require_once "Appliance.php";

$applianceObj = new Appliance();

if($_SERVER["REQUEST_METHOD"] == "GET") {
    if(isset($_GET["id"])) {
        $aid = trim(htmlspecialchars($_GET["id"]));
        $appliance = $applianceObj->fetchAppliance($aid);
        
        if(!$appliance) {
            echo "<a href='viewappliance.php'>View Appliance</a>";
            exit("Appliance not found");
        }
    } else {
        echo "<a href='viewappliance.php'>View Appliance</a>";
        exit("Appliance not found");
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $aid = trim(htmlspecialchars($_POST["id"] ?? ""));
    
    if ($applianceObj->deleteAppliance($aid)) {
        header("Location: viewappliance.php");
        exit;
    } else {
        echo "Failed to delete appliance.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete Appliance</title>
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
            max-width: 800px;
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

        .warning-box {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
            display: flex;
            gap: 15px;
            align-items: flex-start;
        }

        .warning-box i {
            color: #856404;
            font-size: 20px;
            margin-top: 2px;
        }

        .warning-box p {
            color: #856404;
            margin: 0;
            font-size: 15px;
            line-height: 1.5;
        }

        .info-section {
            background: #f9f9f9;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            padding: 25px;
            margin-bottom: 30px;
        }

        .info-section h3 {
            color: #333;
            font-size: 16px;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .info-section h3 i {
            color: #667eea;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
        }

        .info-item {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

        .info-label {
            font-size: 12px;
            color: #666;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .info-value {
            font-size: 15px;
            color: #333;
            font-weight: 500;
        }

        .button-group {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
        }

        .btn-danger {
            background: #dc3545;
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

        .btn-danger:hover {
            background: #c82333;
        }

        .btn-secondary {
            background: #6c757d;
            color: white;
            padding: 14px 32px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-secondary:hover {
            background: #5a6268;
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

            .header h1 {
                font-size: 24px;
            }

            .info-grid {
                grid-template-columns: 1fr;
            }

            .button-group {
                flex-direction: column;
            }

            .btn-danger, .btn-secondary {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Delete Appliance</h1>
            <a href="viewappliance.php" class="back-btn">
                <i class="fas fa-arrow-left"></i>
                Back to List
            </a>
        </div>

        <div class="warning-box">
            <i class="fas fa-exclamation-triangle"></i>
            <p><strong>Warning:</strong> This action cannot be undone. All data related to this appliance will be permanently deleted from the system.</p>
        </div>

        <div class="info-section">
            <h3>
                <i class="fas fa-laptop"></i>
                Appliance Details
            </h3>
            <div class="info-grid">
                <div class="info-item">
                    <span class="info-label">Appliance Name</span>
                    <span class="info-value"><?= htmlspecialchars($appliance["appliance_name"] ?? '') ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Model Number</span>
                    <span class="info-value"><?= htmlspecialchars($appliance["model_number"] ?? '') ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Serial Number</span>
                    <span class="info-value"><?= htmlspecialchars($appliance["serial_number"] ?? '') ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Purchase Date</span>
                    <span class="info-value"><?= htmlspecialchars($appliance["purchase_date"] ?? '') ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Warranty Period</span>
                    <span class="info-value"><?= htmlspecialchars($appliance["warranty_period"] ?? '') ?> Years</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Warranty End Date</span>
                    <span class="info-value"><?= htmlspecialchars($appliance["warranty_end_date"] ?? '') ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Status</span>
                    <span class="info-value"><?= htmlspecialchars($appliance["status"] ?? '') ?></span>
                </div>
            </div>
        </div>

        <form action="" method="post" onsubmit="return confirm('Are you absolutely sure you want to delete this appliance? This action cannot be undone.');">
            <input type="hidden" name="id" value="<?= htmlspecialchars($appliance["id"] ?? '') ?>">
            
            <div class="button-group">
                <button type="submit" class="btn-danger">
                    <i class="fas fa-trash-alt"></i>
                    Delete Appliance
                </button>
                <a href="viewappliance.php" class="btn-secondary">
                    <i class="fas fa-times"></i>
                    Cancel
                </a>
            </div>
        </form>
    </div>
</body>
</html>
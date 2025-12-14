<?php
require_once "database.php";
require_once "appliance.php";
require_once "owner.php";

$applianceObj = new Appliance();
$ownerObj = new Owner();

$search_result = null;
$error_message = "";
$search_type = "";
$search_value = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $search_type = trim(htmlspecialchars($_POST["search_type"] ?? ""));
    $search_value = trim(htmlspecialchars($_POST["search_value"] ?? ""));

    if (empty($search_value)) {
        $error_message = "Please enter a search value";
    } else {
        $db = new Database();
        if ($search_type == "serial") {
            $sql = "SELECT a.*, o.owner_name, o.email, o.phone,
                    CASE 
                        WHEN a.warranty_end_date < CURDATE() THEN 'Expired'
                        ELSE 'Active'
                    END as calculated_status
                    FROM appliance a 
                    LEFT JOIN owner o ON a.owner_id = o.id 
                    WHERE LOWER(REPLACE(TRIM(a.serial_number), ' ', '')) = LOWER(REPLACE(TRIM(:value), ' ', ''))";
        } elseif ($search_type == "owner_email") {
            $sql = "SELECT a.*, o.owner_name, o.email, o.phone,
                    CASE 
                        WHEN a.warranty_end_date < CURDATE() THEN 'Expired'
                        ELSE 'Active'
                    END as calculated_status
                    FROM appliance a 
                    LEFT JOIN owner o ON a.owner_id = o.id 
                    WHERE LOWER(REPLACE(TRIM(o.email), ' ', '')) LIKE LOWER(REPLACE(TRIM(:value), ' ', ''))";
        } elseif ($search_type == "model") {
            $sql = "SELECT a.*, o.owner_name, o.email, o.phone,
                    CASE 
                        WHEN a.warranty_end_date < CURDATE() THEN 'Expired'
                        ELSE 'Active'
                    END as calculated_status
                    FROM appliance a 
                    LEFT JOIN owner o ON a.owner_id = o.id 
                    WHERE LOWER(REPLACE(TRIM(a.model_number), ' ', '')) LIKE LOWER(REPLACE(TRIM(:value), ' ', ''))";
        } else {
            $error_message = "Invalid search type";
        }

        if (!$error_message) {
            try {
                $query = $db->connect()->prepare($sql);
                $query->bindParam(":value", $search_value);
                
                if ($query->execute()) {
                    $results = $query->fetchAll(PDO::FETCH_ASSOC);
                    
                    if ($results && count($results) > 0) {
                        foreach ($results as &$row) {
                            $row['status'] = $row['calculated_status'];
                        }
                        $search_result = $results;
                    } else {
                        $error_message = "No warranty found. Please check your information and try again.";
                    }
                } else {
                    $error_message = "Error searching database";
                }
            } catch (PDOException $e) {
                $error_message = "Database error: " . $e->getMessage();
            }
        }
    }
}

// Check for success message from claim submission
$success_message = isset($_GET['claim_success']) ? "Claim submitted successfully! You will receive a confirmation email shortly." : "";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Warranty Tracker</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body style="background: #f5f7fa;">
    <div class="container">
        <div class="header" style="text-align: center; border: none; margin-bottom: 30px;">
            <h1 style="font-size: 36px; color: #667eea; margin-bottom: 10px;">
                <i class="fas fa-shield-alt"></i> Warranty Tracker
            </h1>
            <p class="subtitle">Track your appliance warranty status easily</p>
        </div>

        <?php if ($success_message): ?>
            <div class="alert-success" style="margin-bottom: 20px; padding: 15px; background: #d4edda; border: 1px solid #c3e6cb; border-radius: 8px; color: #155724;">
                <strong><i class="fas fa-check-circle"></i> Success!</strong> <?= htmlspecialchars($success_message) ?>
            </div>
        <?php endif; ?>

        <div class="card" style="margin-bottom: 30px;">
            <div class="card-header" style="display: flex; align-items: center; gap: 10px;">
                <i class="fas fa-search" style="color: #667eea;"></i>
                Search Your Warranty
            </div>
            
            <div class="alert-info" style="margin-bottom: 20px;">
                <strong><i class="fas fa-info-circle"></i> How to search:</strong>
                <ul style="margin-left: 30px; margin-top: 10px;">
                    <li>By Serial Number - Enter your appliance's serial number</li>
                    <li>By Email - Enter the email address registered with your warranty</li>
                    <li>By Model Number - Enter your appliance's model number</li>
                </ul>
            </div>

            <form action="" method="post">
                <div class="form-grid" style="grid-template-columns: 1fr 1fr auto; gap: 15px; align-items: flex-end;">
                    <div class="form-group">
                        <label for="search_type"><i class="fas fa-filter"></i> Search Type</label>
                        <select name="search_type" id="search_type" class="form-control" required>
                            <option value="">-- Select --</option>
                            <option value="serial" <?= $search_type == "serial" ? "selected" : "" ?>>Serial Number</option>
                            <option value="owner_email" <?= $search_type == "owner_email" ? "selected" : "" ?>>Email Address</option>
                            <option value="model" <?= $search_type == "model" ? "selected" : "" ?>>Model Number</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="search_value"><i class="fas fa-edit"></i> Enter Information</label>
                        <input type="text" name="search_value" id="search_value" class="form-control" value="<?= htmlspecialchars($search_value) ?>" placeholder="Enter your search information" required>
                    </div>

                    <button type="submit" class="btn btn-primary" style="margin-bottom: 0;">
                        <i class="fas fa-search"></i> Search
                    </button>
                </div>
            </form>
        </div>

        <?php if ($error_message): ?>
            <div class="alert-danger" style="margin-bottom: 20px;">
                <strong><i class="fas fa-exclamation-circle"></i> Error:</strong> <?= htmlspecialchars($error_message) ?>
            </div>
        <?php endif; ?>

        <?php if ($search_result): ?>
            <div style="margin-bottom: 20px;">
                <h2 style="color: #333; display: flex; align-items: center; gap: 10px;">
                    <i class="fas fa-check-circle" style="color: #28a745;"></i>
                    Warranty Information Found
                </h2>
            </div>
            
            <?php foreach ($search_result as $appliance): ?>
                <?php
                    $today = date('Y-m-d');
                    $warranty_end = $appliance["warranty_end_date"];
                    $is_expired = $today > $warranty_end;
                    $days_left = (strtotime($warranty_end) - strtotime($today)) / (60 * 60 * 24);
                    
                    $current_status = $appliance["status"];
                    $statusClass = strtolower($current_status) === 'expired' ? 'badge-danger' : 'badge-success';
                ?>
                <div class="card" style="margin-bottom: 20px;">
                    <div class="card-header" style="display: flex; align-items: center; justify-content: space-between; border-bottom: 2px solid #f0f0f0;">
                        <h3 style="margin: 0; display: flex; align-items: center; gap: 10px;">
                            <i class="fas fa-laptop"></i> <?= htmlspecialchars($appliance["appliance_name"]) ?>
                        </h3>
                        <span class="badge <?= $statusClass ?>">
                            <i class="fas fa-<?= $is_expired ? 'times-circle' : 'check-circle' ?>"></i>
                            <?= htmlspecialchars($current_status) ?>
                        </span>
                    </div>

                    <div style="padding: 20px;">
                       
                        <?php if ($appliance["owner_name"]): ?>
                        <div style="margin-bottom: 25px;">
                            <h4 style="color: #667eea; display: flex; align-items: center; gap: 8px; margin-bottom: 15px;">
                                <i class="fas fa-user"></i> Owner Information
                            </h4>
                            <div class="info-grid" style="grid-template-columns: repeat(3, 1fr);">
                                <div class="info-item">
                                    <span class="info-label"><i class="fas fa-user-circle"></i> Name</span>
                                    <span class="info-value"><?= htmlspecialchars($appliance["owner_name"]) ?></span>
                                </div>
                                <div class="info-item">
                                    <span class="info-label"><i class="fas fa-envelope"></i> Email</span>
                                    <span class="info-value"><?= htmlspecialchars($appliance["email"]) ?></span>
                                </div>
                                <div class="info-item">
                                    <span class="info-label"><i class="fas fa-phone"></i> Phone</span>
                                    <span class="info-value"><?= htmlspecialchars($appliance["phone"]) ?></span>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>

                      
                        <div style="margin-bottom: 25px;">
                            <h4 style="color: #667eea; display: flex; align-items: center; gap: 8px; margin-bottom: 15px;">
                                <i class="fas fa-cog"></i> Appliance Details
                            </h4>
                            <div class="info-grid" style="grid-template-columns: repeat(2, 1fr);">
                                <div class="info-item">
                                    <span class="info-label"><i class="fas fa-barcode"></i> Model Number</span>
                                    <span class="info-value"><?= htmlspecialchars($appliance["model_number"]) ?></span>
                                </div>
                                <div class="info-item">
                                    <span class="info-label"><i class="fas fa-key"></i> Serial Number</span>
                                    <span class="info-value"><?= htmlspecialchars($appliance["serial_number"]) ?></span>
                                </div>
                                <div class="info-item">
                                    <span class="info-label"><i class="fas fa-calendar"></i> Purchase Date</span>
                                    <span class="info-value"><?= htmlspecialchars($appliance["purchase_date"]) ?></span>
                                </div>
                                <div class="info-item">
                                    <span class="info-label"><i class="fas fa-hourglass"></i> Warranty Period</span>
                                    <span class="info-value"><?= htmlspecialchars($appliance["warranty_period"]) ?> year(s)</span>
                                </div>
                            </div>
                        </div>

                      
                        <div style="margin-bottom: 20px;">
                            <h4 style="color: #667eea; display: flex; align-items: center; gap: 8px; margin-bottom: 15px;">
                                <i class="fas fa-shield-alt"></i> Warranty Status
                            </h4>
                            <div class="info-grid" style="grid-template-columns: repeat(2, 1fr);">
                                <div class="info-item">
                                    <span class="info-label"><i class="fas fa-calendar-alt"></i> End Date</span>
                                    <span class="info-value"><?= htmlspecialchars($appliance["warranty_end_date"]) ?></span>
                                </div>
                                <div class="info-item">
                                    <span class="info-label"><i class="fas fa-hourglass-end"></i> Days Remaining</span>
                                    <span class="info-value">
                                        <?php if ($is_expired): ?>
                                            <span style="color: #dc3545; font-weight: bold;">Expired</span>
                                        <?php else: ?>
                                            <span style="color: #28a745; font-weight: bold;"><?= ceil($days_left) ?> days</span>
                                        <?php endif; ?>
                                    </span>
                                </div>
                            </div>
                        </div>

                        
                        <div style="margin-top: 25px; border-top: 1px solid #f0f0f0; padding-top: 20px;">
                            <?php if ($current_status == "Active" && !$is_expired): ?>
                                <a href="customer_file_claim.php?appliance_id=<?= $appliance['id'] ?>&serial=<?= urlencode($appliance['serial_number']) ?>" class="btn btn-primary">
                                    <i class="fas fa-file-contract"></i> File Warranty Claim
                                </a>
                            <?php else: ?>
                                <button disabled class="btn btn-secondary" style="cursor: not-allowed; opacity: 0.6;">
                                    <i class="fas fa-ban"></i> Warranty Expired - Cannot File Claim
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</body>
</html>
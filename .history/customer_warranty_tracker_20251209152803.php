<?php
require_once "database.php";
require_once "Appliance.php";
require_once "Owner.php";

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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Warranty Tracker</title>
</head>
<body>
    <div class="container">
        <h1>Warranty Tracker</h1>
        <p>Track your appliance warranty status easily</p>

        <div class="search-box">
            <h2>Search Your Warranty</h2>
            
            <div class="info-box">
                <strong>How to search:</strong>
                <ul>
                    <li>By Serial Number - Enter your appliance's serial number</li>
                    <li>By Email - Enter the email address registered with your warranty</li>
                    <li>By Model Number - Enter your appliance's model number</li>
                </ul>
            </div>

            <form action="" method="post">
                <label for="search_type">Search Type *</label>
                <select name="search_type" id="search_type" required>
                    <option value="">-- Select --</option>
                    <option value="serial" <?= $search_type == "serial" ? "selected" : "" ?>>Serial Number</option>
                    <option value="owner_email" <?= $search_type == "owner_email" ? "selected" : "" ?>>Email Address</option>
                    <option value="model" <?= $search_type == "model" ? "selected" : "" ?>>Model Number</option>
                </select>

                <label for="search_value">Enter Information *</label>
                <input type="text" name="search_value" id="search_value" value="<?= htmlspecialchars($search_value) ?>" placeholder="Enter your search information" required>

                <button type="submit">Search Warranty</button>
            </form>
        </div>

        <?php if ($error_message): ?>
            <div class="error">
                <strong>Error:</strong> <?= htmlspecialchars($error_message) ?>
            </div>
        <?php endif; ?>

        <?php if ($search_result): ?>
            <h2>Warranty Information Found</h2>
            
            <?php foreach ($search_result as $appliance): ?>
                <?php
                    $today = date('Y-m-d');
                    $warranty_end = $appliance["warranty_end_date"];
                    $is_expired = $today > $warranty_end;
                    $days_left = (strtotime($warranty_end) - strtotime($today)) / (60 * 60 * 24);
                    
                    $current_status = $appliance["status"]; 
                ?>
                <div class="result-box">
                    <h3><?= htmlspecialchars($appliance["appliance_name"]) ?></h3>
                    
                    <table>
                        <tr>
                            <th>Detail</th>
                            <th>Information</th>
                        </tr>
                        <?php if ($appliance["owner_name"]): ?>
                        <tr>
                            <td><strong>Owner Name</strong></td>
                            <td><?= htmlspecialchars($appliance["owner_name"]) ?></td>
                        </tr>
                        <tr>
                            <td><strong>Email</strong></td>
                            <td><?= htmlspecialchars($appliance["email"]) ?></td>
                        </tr>
                        <tr>
                            <td><strong>Phone</strong></td>
                            <td><?= htmlspecialchars($appliance["phone"]) ?></td>
                        </tr>
                        <?php else: ?>
                        <tr>
                            <td colspan="2"><em>Owner information not available</em></td>
                        </tr>
                        <?php endif; ?>
                        <tr>
                            <td><strong>Model Number</strong></td>
                            <td><?= htmlspecialchars($appliance["model_number"]) ?></td>
                        </tr>
                        <tr>
                            <td><strong>Serial Number</strong></td>
                            <td><?= htmlspecialchars($appliance["serial_number"]) ?></td>
                        </tr>
                        <tr>
                            <td><strong>Purchase Date</strong></td>
                            <td><?= htmlspecialchars($appliance["purchase_date"]) ?></td>
                        </tr>
                        <tr>
                            <td><strong>Warranty Period</strong></td>
                            <td><?= htmlspecialchars($appliance["warranty_period"]) ?> year(s)</td>
                        </tr>
                        <tr>
                            <td><strong>Warranty End Date</strong></td>
                            <td><?= htmlspecialchars($appliance["warranty_end_date"]) ?></td>
                        </tr>
                        <tr>
                            <td><strong>Warranty Status</strong></td>
                            <td>
                                <span class="status <?= strtolower($current_status) ?>">
                                    <?= htmlspecialchars($current_status) ?>
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Days Left</strong></td>
                            <td>
                                <?php if ($is_expired): ?>
                                    <span class="status expired">Expired</span>
                                <?php else: ?>
                                    <strong><?= ceil($days_left) ?></strong> days remaining
                                <?php endif; ?>
                            </td>
                        </tr>
                    </table>

                    <div class="claim-button">
                        <?php if ($current_status == "Active" && !$is_expired): ?>
                            <a href="customer_file_claim.php?appliance_id=<?= $appliance['id'] ?>&serial=<?= urlencode($appliance['serial_number']) ?>" class="btn-file-claim">
                                File Warranty Claim
                            </a>
                        <?php else: ?>
                            <button disabled class="btn-disabled">
                                Warranty Expired - Cannot File Claim
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</body>
</html>
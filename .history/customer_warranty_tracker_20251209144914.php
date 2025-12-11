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
            max-width: 900px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
        }

        h1 {
            color: #333;
            margin-bottom: 10px;
            text-align: center;
        }

        .container > p {
            text-align: center;
            color: #666;
            margin-bottom: 30px;
        }

        .search-box {
            background: #f8f9fa;
            padding: 25px;
            border-radius: 12px;
            margin-bottom: 30px;
        }

        .search-box h2 {
            color: #333;
            margin-bottom: 15px;
        }

        .info-box {
            background: #e7f3ff;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid #007bff;
        }

        .info-box ul {
            margin: 10px 0 0 20px;
        }

        .info-box li {
            margin: 5px 0;
            color: #333;
        }

        label {
            display: block;
            color: #333;
            font-weight: 600;
            margin-bottom: 8px;
            margin-top: 15px;
        }

        select,
        input[type="text"] {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 15px;
        }

        select:focus,
        input[type="text"]:focus {
            outline: none;
            border-color: #007bff;
        }

        button[type="submit"] {
            width: 100%;
            background: #007bff;
            color: white;
            padding: 14px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            margin-top: 20px;
        }

        button[type="submit"]:hover {
            background: #0056b3;
        }

        .error {
            background: #f8d7da;
            color: #721c24;
            padding: 15px;
            border-radius: 8px;
            margin: 20px 0;
            border: 1px solid #f5c6cb;
        }

        h2 {
            color: #333;
            margin: 30px 0 20px 0;
        }

        .result-box {
            background: #f8f9fa;
            padding: 25px;
            border-radius: 12px;
            margin-bottom: 20px;
        }

        .result-box h3 {
            color: #667eea;
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 8px;
            overflow: hidden;
        }

        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #e0e0e0;
        }

        th {
            background: #667eea;
            color: white;
            font-weight: 600;
        }

        tr:last-child td {
            border-bottom: none;
        }

        .status {
            padding: 6px 12px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 13px;
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

        .claim-button {
            margin-top: 20px;
            text-align: center;
        }

        .btn-file-claim {
        .btn-file-claim {
            display: inline-block;
            background: #28a745;
            color: white;
            padding: 12px 30px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;

        .btn-file-claim:hover {
            background: #218838;
        }

        .btn-disabled {
            background: #6c757d;
            color: white;
            padding: 12px 30px;
            border-radius: 8px;
            border: none;
            font-weight: 600;
            cursor: not-allowed;
            opacity: 0.6;
        }

        @media (max-width: 768px) {
            .container {
                padding: 20px;
            }

            table {
                font-size: 14px;
            }

            th, td {
                padding: 8px;
            }
        }
    </style>
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
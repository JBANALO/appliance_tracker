<?php
// Database Import Script for Railway
// WARNING: Delete this file after successful import for security!

require_once __DIR__ . '/config.php';

// Set execution time limit
set_time_limit(300); // 5 minutes

// Your SQL file content
$sql = <<<'SQL'
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

CREATE TABLE `admin` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `reset_token` varchar(255) DEFAULT NULL,
  `reset_expires` datetime DEFAULT NULL,
  `verification_code` varchar(6) DEFAULT NULL,
  `is_verified` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `admin` (`id`, `username`, `password`, `name`, `email`, `created_at`, `reset_token`, `reset_expires`, `verification_code`, `is_verified`) VALUES
(1, 'jossie', '$2y$10$hc3FP3OoUhG52KAR.96atOWjDwwx29BUy28ls.lSfits8WISmgEFK', 'Josie banalo', 'josiebanalo977@gmail.com', '2025-10-27 10:59:37', '455355', '2025-12-12 17:47:47', NULL, 1),
(2, 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrator', 'admin@warranty.com', '2025-10-27 15:05:13', NULL, NULL, NULL, 1),
(3, 'heidilynn', '$2y$10$NDO77SJXkd01xDYoOUEjnuFC9t5yMG5ONPOeqNczUXWicl7ofzFVK', 'heidi lynn rubia', 'heidilynnrubia09@gmail.com', '2025-11-19 17:09:41', '641532', '2025-11-19 18:24:57', NULL, 1),
(4, 'heidi1', '$2y$10$7EZ1JGC7/a9KdOSiFQ163OLJOEudW4aYR2Q9qVfW/ihU.F1X54f2q', 'heidi lynn rubia', 'heidilynnrubia09@gmail.com', '2025-12-09 08:20:58', NULL, NULL, NULL, 1),
(5, 'heidi2', '$2y$10$QRXeldGXQxCd3nmNQ/.LIuwscXhKBv7zuuUs2SPD966InScxEm6cy', 'heidi lynn rubia', 'heidilynnrubia23@gmail.com', '2025-12-09 08:22:14', NULL, NULL, NULL, 1);

CREATE TABLE `appliance` (
  `id` int(11) NOT NULL,
  `appliance_name` varchar(100) NOT NULL,
  `brand` varchar(100) DEFAULT NULL,
  `model` varchar(100) DEFAULT NULL,
  `serial_number` varchar(100) DEFAULT NULL,
  `purchase_price` decimal(10,2) DEFAULT NULL,
  `purchase_date` date DEFAULT NULL,
  `condition_status` varchar(50) DEFAULT 'Good',
  `model_number` varchar(100) DEFAULT NULL,
  `warranty_period` int(11) DEFAULT NULL,
  `warranty_end_date` date DEFAULT NULL,
  `status` varchar(50) DEFAULT NULL,
  `owner_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `appliance` (`id`, `appliance_name`, `brand`, `model`, `serial_number`, `purchase_price`, `purchase_date`, `condition_status`, `model_number`, `warranty_period`, `warranty_end_date`, `status`, `owner_id`) VALUES
(1, 'Samsung Refrigerator', NULL, NULL, 'SN2025REF12345', NULL, '2023-10-13', 'Good', 'RT46K6238S8/TC', 3, '2026-10-13', 'Active', 5),
(2, 'LG Washing Machine', NULL, NULL, 'LGWASH2025X89', NULL, '2023-02-10', 'Good', 'F2721HTWV', 3, '2026-02-10', 'Active', 7),
(3, 'Acer Laptop', NULL, NULL, 'ACER123456', NULL, '2023-10-28', 'Good', 'Aspire 3 A315-59', 4, '2027-10-28', 'Active', 9),
(4, 'Dell Monitor', NULL, NULL, 'DELL987654', NULL, '2023-05-15', 'Good', 'S2722DC', 2, '2027-05-15', 'Active', 11);

CREATE TABLE `claim` (
  `id` int(11) NOT NULL,
  `appliance_id` int(11) NOT NULL,
  `claim_date` date NOT NULL,
  `claim_description` text NOT NULL,
  `claim_status` varchar(50) NOT NULL DEFAULT 'Pending',
  `resolution_notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `claim` (`id`, `appliance_id`, `claim_date`, `claim_description`, `claim_status`, `resolution_notes`, `created_at`, `updated_at`) VALUES
(2, 5, '2025-11-10', 'lag, black screen', 'Rejected', '', '2025-11-10 14:37:54', '2025-11-10 14:57:34'),
(3, 5, '2025-11-10', 'lag, abnormalities, black screen virus', 'Pending', '', '2025-11-10 14:56:56', '2025-11-10 14:56:56'),
(5, 5, '2025-11-16', 'lagging black cam not working', 'Approved', '', '2025-11-16 03:52:06', '2025-11-16 04:25:22'),
(7, 5, '2025-11-16', 'haahjhsjkjdhhd', 'Rejected', '', '2025-11-16 04:24:43', '2025-11-19 17:02:52'),
(8, 5, '2025-11-16', 'josiehfhhhhf', 'Pending', '', '2025-11-16 04:27:53', '2025-11-16 04:27:53'),
(9, 5, '2025-11-16', 'jhasgdhgshdgd', 'Approved', '', '2025-11-16 04:33:35', '2025-11-16 04:46:47'),
(11, 5, '2025-11-06', 'nothing happened lanhg', 'Approved', 'Monday', '2025-11-16 04:38:53', '2025-12-09 17:17:54'),
(12, 5, '2025-11-16', 'sounds system black screen lagging', 'Approved', '', '2025-11-16 04:44:38', '2025-11-16 04:47:43'),
(15, 1, '2025-10-12', 'black screen', 'Approved', 'GO by Monday', '2025-12-09 16:48:59', '2025-12-09 16:54:45'),
(16, 1, '2025-10-12', 'black screen', 'Approved', '', '2025-12-09 16:49:03', '2025-12-09 16:50:51'),
(17, 1, '2025-12-11', 'i have issue in keyboard', 'Rejected', '', '2025-12-12 16:09:41', '2025-12-12 16:54:45'),
(18, 1, '2025-12-12', 'jjdbbfffbfbdbfbfsdhhhdh', 'Approved', '', '2025-12-12 16:38:36', '2025-12-12 16:40:05');

CREATE TABLE `notification` (
  `id` int(11) NOT NULL,
  `type` varchar(50) NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `link` varchar(255) DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `notification` (`id`, `type`, `title`, `message`, `link`, `is_read`, `created_at`) VALUES
(1, 'claim', 'New Warranty Claim Filed', 'A new claim has been filed for acer laptop by alexandra', 'viewclaim.php', 1, '2025-11-16 04:37:35'),
(2, 'claim', 'New Warranty Claim Filed', 'A new claim has been filed for laptop by JOSIE BANALO', 'viewclaim.php', 1, '2025-11-16 04:44:38'),
(3, 'claim', 'New Warranty Claim Filed', 'A new claim has been filed for Samsung Refrigerator by JOSIE BANALO', 'viewclaim.php', 1, '2025-12-09 06:41:29'),
(4, 'claim', 'New Warranty Claim Filed', 'A new claim has been filed for Samsung Refrigerator by JOSIE BANALO', 'viewclaim.php', 1, '2025-12-09 08:19:00'),
(5, 'claim', 'New Warranty Claim Filed', 'A new claim has been filed for Samsung Refrigerator by JOSIE BANALO', 'viewclaim.php', 1, '2025-12-09 16:48:59'),
(6, 'claim', 'New Warranty Claim Filed', 'A new claim has been filed for Samsung Refrigerator by JOSIE BANALO', 'viewclaim.php', 1, '2025-12-09 16:49:03'),
(7, 'claim', 'New Warranty Claim Filed', 'A new claim has been filed for Samsung Refrigerator by JOSIE BANALO', 'viewclaim.php', 0, '2025-12-12 16:09:41'),
(8, 'claim', 'New Warranty Claim Filed', 'A new claim has been filed for Samsung Refrigerator by JOSIE BANALO', 'viewclaim.php', 0, '2025-12-12 16:38:36');

CREATE TABLE `owner` (
  `id` int(11) NOT NULL,
  `owner_name` varchar(100) NOT NULL,
  `contact_number` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `city` varchar(50) DEFAULT NULL,
  `state` varchar(50) DEFAULT NULL,
  `zip_code` varchar(10) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `owner` (`id`, `owner_name`, `contact_number`, `email`, `address`, `phone`, `city`, `state`, `zip_code`, `created_at`) VALUES
(5, 'JOSIE BANALO', NULL, 'josiebanalo977@gmail.com', 'Guiwan', '09052460749', 'Zamboanga city', 'Zamboanga del Sur', '7000', '2025-12-09 06:38:38'),
(6, 'Riya Santos', NULL, 'riyasantos@gmail.com', 'PUTIK', '09377725478', 'Zamboanga city', 'Zamboanga del Sur', '7000', '2025-12-09 06:38:38'),
(7, 'MARIA ORTEGA', NULL, 'mariaortega@gmail.com', 'TUMAGA', '09075467852', 'Zamboanga city', 'Zamboanga del Sur', '7000', '2025-12-09 06:38:38'),
(9, 'alexandra', NULL, 'alexandra@gmail.com', 'STA. MARIA', '09863528262', 'Zamboanga city', 'Zamboanga del Sur', '7000', '2025-12-09 06:38:38'),
(10, 'John Doe', NULL, 'john@example.com', '123 Main St', '09123456789', 'Sample City', 'Sample State', '1234', '2025-12-09 06:38:38'),
(11, 'Heidi Lynn Rubia', NULL, 'heidilynnrubia09@gmail.com', 'Tumaga Perez Drive', '09656923753', 'Zamboanga City', 'Zamboanga Del Sur', '7000', '2025-12-09 16:59:52');

ALTER TABLE `admin`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

ALTER TABLE `appliance`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `claim`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_claim_status` (`claim_status`),
  ADD KEY `idx_claim_appliance` (`appliance_id`);

ALTER TABLE `notification`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `owner`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `admin`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

ALTER TABLE `appliance`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

ALTER TABLE `claim`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

ALTER TABLE `notification`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

ALTER TABLE `owner`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

COMMIT;
SQL;

try {
    // Connect to database
    $dsn = "mysql:host=" . DB_HOST . ";port=" . (defined('DB_PORT') ? DB_PORT : '3306') . ";dbname=" . DB_NAME . ";charset=utf8mb4";
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
    
    echo "<!DOCTYPE html><html><head><title>Database Import</title>";
    echo "<style>body{font-family:Arial,sans-serif;max-width:800px;margin:50px auto;padding:20px;}";
    echo "h2{color:#2563eb;}h3{color:#16a34a;}.error{color:#dc2626;}.warning{color:#ea580c;}";
    echo ".success{color:#16a34a;background:#f0fdf4;padding:10px;border-radius:5px;margin:20px 0;}";
    echo ".info{background:#eff6ff;padding:15px;border-radius:5px;margin:20px 0;border-left:4px solid #2563eb;}";
    echo "a{display:inline-block;margin-top:20px;padding:10px 20px;background:#2563eb;color:white;text-decoration:none;border-radius:5px;}";
    echo "a:hover{background:#1d4ed8;}</style></head><body>";
    
    echo "<h2>🚀 Database Import Tool</h2>";
    echo "<p>Starting import to Railway MySQL...</p>";
    
    // Split SQL into individual statements
    $statements = array_filter(
        array_map('trim', explode(';', $sql)),
        function($stmt) {
            return !empty($stmt) && !preg_match('/^--/', $stmt);
        }
    );
    
    $successCount = 0;
    $errorCount = 0;
    $errors = [];
    
    // Execute each statement
    foreach ($statements as $statement) {
        if (empty($statement)) continue;
        
        try {
            $pdo->exec($statement);
            $successCount++;
        } catch (PDOException $e) {
            $errorCount++;
            $errors[] = $e->getMessage();
        }
    }
    
    echo "<div class='success'>";
    echo "<h3>✅ Import Complete!</h3>";
    echo "<p><strong>Successful statements:</strong> $successCount</p>";
    echo "<p><strong>Errors/Warnings:</strong> $errorCount</p>";
    echo "</div>";
    
    if ($errorCount > 0 && $errorCount < 5) {
        echo "<div class='warning'><p><strong>Some warnings (usually safe to ignore):</strong></p><ul>";
        foreach (array_slice($errors, 0, 5) as $error) {
            echo "<li>" . htmlspecialchars($error) . "</li>";
        }
        echo "</ul></div>";
    }
    
    echo "<div class='info'>";
    echo "<p><strong>⚠️ IMPORTANT SECURITY NOTICE:</strong></p>";
    echo "<p>Delete this file immediately by running:</p>";
    echo "<code style='background:#1e293b;color:#fff;padding:10px;display:block;margin:10px 0;border-radius:5px;'>";
    echo "git rm import_database.php<br>git commit -m \"Remove import script\"<br>git push";
    echo "</code>";
    echo "</div>";
    
    echo "<a href='login.php'>🔐 Go to Login Page</a>";
    echo "</body></html>";
    
} catch (PDOException $e) {
    echo "<!DOCTYPE html><html><head><title>Import Error</title>";
    echo "<style>body{font-family:Arial;max-width:800px;margin:50px auto;padding:20px;}";
    echo ".error{color:#dc2626;background:#fef2f2;padding:20px;border-radius:5px;border-left:4px solid #dc2626;}</style></head><body>";
    echo "<div class='error'>";
    echo "<h3>❌ Database Connection Failed</h3>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
    echo "</div></body></html>";
}
?>
<?php
// Temporary debug file to find missing files and errors
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Debug Dashboard</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 1200px;
            margin: 20px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .debug-section {
            background: white;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
            border-bottom: 2px solid #007bff;
            padding-bottom: 10px;
        }
        h2 {
            color: #555;
            margin-top: 20px;
        }
        .success {
            color: #28a745;
            font-weight: bold;
        }
        .error {
            color: #dc3545;
            font-weight: bold;
        }
        .warning {
            color: #ffc107;
            font-weight: bold;
        }
        pre {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 4px;
            overflow-x: auto;
            border-left: 4px solid #007bff;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th, td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background: #007bff;
            color: white;
        }
        .file-list {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 10px;
            margin-top: 10px;
        }
        .file-item {
            padding: 8px;
            background: #e9ecef;
            border-radius: 4px;
            font-family: monospace;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <h1>🔍 Debug Information for Admin Dashboard</h1>

    <!-- PHP Info Section -->
    <div class="debug-section">
        <h2>📋 System Information</h2>
        <table>
            <tr>
                <th>Property</th>
                <th>Value</th>
            </tr>
            <tr>
                <td>PHP Version</td>
                <td><?php echo phpversion(); ?></td>
            </tr>
            <tr>
                <td>Current Directory</td>
                <td><?php echo getcwd(); ?></td>
            </tr>
            <tr>
                <td>Script Filename</td>
                <td><?php echo __FILE__; ?></td>
            </tr>
            <tr>
                <td>Document Root</td>
                <td><?php echo $_SERVER['DOCUMENT_ROOT'] ?? 'Not set'; ?></td>
            </tr>
        </table>
    </div>

    <!-- Check Required Files -->
    <div class="debug-section">
        <h2>📁 Required Files Check</h2>
        <?php
        $required_files = [
            'config.php',
            'security.php',
            'database.php',
            'Appliance.php',
            'Claim.php',
            'Notification.php',
            'admin_dashboard.php',
            'login.php',
            'styles.css'
        ];

        echo '<table>';
        echo '<tr><th>File</th><th>Status</th><th>Full Path</th></tr>';
        
        foreach ($required_files as $file) {
            $exists = file_exists($file);
            $full_path = $exists ? realpath($file) : 'Not found';
            $status_class = $exists ? 'success' : 'error';
            $status_icon = $exists ? '✅' : '❌';
            
            echo "<tr>";
            echo "<td><code>$file</code></td>";
            echo "<td class='$status_class'>$status_icon " . ($exists ? 'EXISTS' : 'MISSING') . "</td>";
            echo "<td><small>$full_path</small></td>";
            echo "</tr>";
        }
        
        echo '</table>';
        ?>
    </div>

    <!-- Directory Contents -->
    <div class="debug-section">
        <h2>📂 Files in Current Directory</h2>
        <?php
        $files = scandir('.');
        $files = array_diff($files, ['.', '..']);
        
        echo '<div class="file-list">';
        foreach ($files as $file) {
            $is_dir = is_dir($file);
            $icon = $is_dir ? '📁' : '📄';
            echo "<div class='file-item'>$icon $file</div>";
        }
        echo '</div>';
        ?>
    </div>

    <!-- Database Connection Test -->
    <div class="debug-section">
        <h2>🗄️ Database Connection Test</h2>
        <?php
        echo '<pre>';
        echo "DB_HOST: " . ($_ENV['DB_HOST'] ?? getenv('DB_HOST') ?? 'Not set') . "\n";
        echo "DB_PORT: " . ($_ENV['DB_PORT'] ?? getenv('DB_PORT') ?? 'Not set') . "\n";
        echo "DB_NAME: " . ($_ENV['DB_NAME'] ?? getenv('DB_NAME') ?? 'Not set') . "\n";
        echo "DB_USER: " . ($_ENV['DB_USER'] ?? getenv('DB_USER') ?? 'Not set') . "\n";
        echo "DB_PASS: " . (($_ENV['DB_PASS'] ?? getenv('DB_PASS')) ? '***HIDDEN***' : 'Not set') . "\n";
        echo '</pre>';

        // Try to connect to database
        try {
            $host = $_ENV['DB_HOST'] ?? getenv('DB_HOST');
            $port = $_ENV['DB_PORT'] ?? getenv('DB_PORT');
            $dbname = $_ENV['DB_NAME'] ?? getenv('DB_NAME');
            $user = $_ENV['DB_USER'] ?? getenv('DB_USER');
            $pass = $_ENV['DB_PASS'] ?? getenv('DB_PASS');

            if ($host && $dbname && $user) {
                $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";
                $pdo = new PDO($dsn, $user, $pass, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
                ]);
                echo '<p class="success">✅ Database connection successful!</p>';
                
                // Check tables
                $stmt = $pdo->query("SHOW TABLES");
                $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
                echo '<h3>📊 Database Tables:</h3>';
                echo '<pre>';
                print_r($tables);
                echo '</pre>';
            } else {
                echo '<p class="error">❌ Database credentials not configured</p>';
            }
        } catch (Exception $e) {
            echo '<p class="error">❌ Database connection failed:</p>';
            echo '<pre>' . htmlspecialchars($e->getMessage()) . '</pre>';
        }
        ?>
    </div>

    <!-- Try Loading admin_dashboard.php -->
    <div class="debug-section">
        <h2>🚀 Attempting to Load admin_dashboard.php</h2>
        <?php
        if (file_exists('admin_dashboard.php')) {
            echo '<p class="warning">⚠️ Attempting to include admin_dashboard.php (this may show errors)...</p>';
            echo '<pre>';
            
            // Capture output and errors
            ob_start();
            try {
                // Set a session to prevent redirect
                session_start();
                $_SESSION['admin_logged_in'] = true;
                $_SESSION['admin_id'] = 1;
                
                include('admin_dashboard.php');
                $output = ob_get_clean();
                echo "Output captured successfully!\n";
                echo "Length: " . strlen($output) . " characters\n";
            } catch (Exception $e) {
                ob_end_clean();
                echo '<span class="error">❌ ERROR CAUGHT:</span>' . "\n";
                echo htmlspecialchars($e->getMessage()) . "\n\n";
                echo "Stack trace:\n";
                echo htmlspecialchars($e->getTraceAsString());
            } catch (Error $e) {
                ob_end_clean();
                echo '<span class="error">❌ FATAL ERROR CAUGHT:</span>' . "\n";
                echo htmlspecialchars($e->getMessage()) . "\n\n";
                echo "Stack trace:\n";
                echo htmlspecialchars($e->getTraceAsString());
            }
            
            echo '</pre>';
        } else {
            echo '<p class="error">❌ admin_dashboard.php file not found!</p>';
        }
        ?>
    </div>

    <div class="debug-section">
        <h2>💡 Next Steps</h2>
        <ol>
            <li>Check which required files are <span class="error">MISSING</span> above</li>
            <li>Add those missing files to your project</li>
            <li>Commit and push:
                <pre>git add .
git commit -m "Add missing files"
git push</pre>
            </li>
            <li><strong>Delete this debug file after fixing:</strong>
                <pre>git rm debug_dashboard.php
git commit -m "Remove debug script"
git push</pre>
            </li>
        </ol>
    </div>
</body>
</html>
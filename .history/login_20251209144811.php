<?php
session_start();

if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header("Location: admin_dashboard.php");
    exit;
}
require_once "database.php";
$error_message = "";
$success_message = "";
$username = "";


if (isset($_GET['logout']) && $_GET['logout'] === 'success') {
    $success_message = "You have been logged out successfully";
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim(htmlspecialchars($_POST["username"] ?? ""));
    $password = trim($_POST["password"] ?? "");
    if (empty($username)) {
        $error_message = "Username is required";
    } elseif (empty($password)) {
        $error_message = "Password is required";
    } else {
        $db = new Database();
        $sql = "SELECT * FROM admin WHERE username = :username";
        $query = $db->connect()->prepare($sql);
        $query->bindParam(":username", $username);
        
        if ($query->execute()) {
            $admin = $query->fetch();
            
            if ($admin && password_verify($password, $admin['password'])) {
                
                $_SESSION['admin_logged_in'] = true;
                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['admin_username'] = $admin['username'];
                $_SESSION['admin_name'] = $admin['name'];
                
                header("Location: admin_dashboard.php");
                exit;
            } else {
                $error_message = "Invalid username or password";
            }
        } else {
            $error_message = "Database error. Please try again.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f7fa;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .login-container {
            background: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 400px;
            text-align: center;
        }

        h1 {
            color: #333;
            margin-bottom: 10px;
            font-size: 28px;
        }

        .subtitle {
            color: #666;
            margin-bottom: 30px;
            font-size: 14px;
        }

        .success {
            background: #d4edda;
            color: #155724;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            border: 1px solid #c3e6cb;
        }

        .error {
            background: #f8d7da;
            color: #721c24;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            border: 1px solid #f5c6cb;
        }

        form {
            text-align: left;
        }

        label {
            display: block;
            color: #333;
            font-weight: 600;
            margin-bottom: 8px;
            font-size: 14px;
        }

        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 15px;
            margin-bottom: 20px;
        }

        input[type="text"]:focus,
        input[type="password"]:focus {
            outline: none;
            border-color: #667eea;
        }

        button[type="submit"] {
            width: 100%;
            background: #667eea;
            color: white;
            padding: 14px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s;
        }

        button[type="submit"]:hover {
            background: #5568d3;
        }

        .links {
            margin-top: 20px;
            font-size: 14px;
            color: #666;
        }

        .links a {
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
        }

        .links a:hover {
            text-decoration: underline;
        }

        .divider {
            margin: 15px 0;
        }

        @media (max-width: 480px) {
            .login-container {
                padding: 30px 20px;
            }

            h1 {
                font-size: 24px;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h1>Admin Login</h1>
        <p class="subtitle">Warranty Tracker System</p>
        <p>Forgot your password? <a href="forgot_password.php">Reset it here</a></p>
        
        <?php if ($success_message): ?>
            <div class="success"><?= htmlspecialchars($success_message) ?></div>
        <?php endif; ?>
        
        <?php if ($error_message): ?>
            <div class="error"><?= htmlspecialchars($error_message) ?></div>
        <?php endif; ?>
        
        <form action="" method="post">
            <label for="username">Username</label>
            <input type="text" name="username" id="username" value="<?= htmlspecialchars($username) ?>" required autofocus>
            
            <label for="password">Password</label>
            <input type="password" name="password" id="password" required>
            
            <button type="submit">Login</button>
        </form>
        
        <div class="links">
            <p>Don't have an account? <a href="register.php">Register here</a></p>
            <p class="divider"><a href="customer_warranty_tracker.php">Back to Customer Portal</a></p>
        </div>
    </div>
</body>
</html>
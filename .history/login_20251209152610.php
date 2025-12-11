<?php
session_start();

if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header("Location: admin_dashboard.php");
    exit;
}
require_once "database.php";
$error_message = "";
$success_message = "";
$email = "";


if (isset($_GET['logout']) && $_GET['logout'] === 'success') {
    $success_message = "You have been logged out successfully";
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim(htmlspecialchars($_POST["email"] ?? ""));
    $password = trim($_POST["password"] ?? "");
    if (empty($email)) {
        $error_message = "Email is required";
    } elseif (empty($password)) {
        $error_message = "Password is required";
    } else {
        $db = new Database();
        $sql = "SELECT * FROM admin WHERE email = :email";
        $query = $db->connect()->prepare($sql);
        $query->bindParam(":email", $email);
        
        if ($query->execute()) {
            $admin = $query->fetch();
            
            if ($admin && password_verify($password, $admin['password'])) {
                
                $_SESSION['admin_logged_in'] = true;
                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['admin_email'] = $admin['email'];
                $_SESSION['admin_name'] = $admin['name'];
                
                header("Location: admin_dashboard.php");
                exit;
            } else {
                $error_message = "Invalid email or password";
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
    <link rel="stylesheet" href="styles.css"
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
            <label for="email">Email Address</label>
            <input type="email" name="email" id="email" value="<?= htmlspecialchars($email) ?>" required autofocus>
            
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
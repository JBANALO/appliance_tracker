<?php
session_start();

if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header("Location: admin_dashboard.php");
    exit;
}

require_once "Admin.php";

$success_message = "";
$form = ["username" => "", "name" => "", "email" => "", "password" => "", "confirm_password" => ""];
$errors = ["username" => "", "name" => "", "email" => "", "password" => "", "confirm_password" => ""];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $form["username"] = trim(htmlspecialchars($_POST["username"] ?? ""));
    $form["name"] = trim(htmlspecialchars($_POST["name"] ?? ""));
    $form["email"] = trim(htmlspecialchars($_POST["email"] ?? ""));
    $form["password"] = trim($_POST["password"] ?? "");
    $form["confirm_password"] = trim($_POST["confirm_password"] ?? "");

    if (empty($form["username"])) {
        $errors["username"] = "Username is required";
    } elseif (strlen($form["username"]) < 4) {
        $errors["username"] = "Username must be at least 4 characters";
    }
    if (empty($form["name"])) $errors["name"] = "Full name is required";
    if (empty($form["email"])) {
        $errors["email"] = "Email is required";
    } elseif (!filter_var($form["email"], FILTER_VALIDATE_EMAIL)) {
        $errors["email"] = "Invalid email format";
    }
    if (empty($form["password"])) {
        $errors["password"] = "Password is required";
    } elseif (strlen($form["password"]) < 6) {
        $errors["password"] = "Password must be at least 6 characters";
    }
    if (empty($form["confirm_password"])) {
        $errors["confirm_password"] = "Please confirm your password";
    } elseif ($form["password"] !== $form["confirm_password"]) {
        $errors["confirm_password"] = "Passwords do not match";
    }

    if (!array_filter($errors)) {
        $adminObj = new Admin();
        
        if ($adminObj->usernameExists($form["username"])) {
            $errors["username"] = "Username already exists";
        } else {
            $adminObj->username = $form["username"];
            $adminObj->password = $form["password"];
            $adminObj->name = $form["name"];
            $adminObj->email = $form["email"];
            
            if ($adminObj->register()) {
                $success_message = "Registration successful! You can now login.";
                $form = ["username" => "", "name" => "", "email" => "", "password" => "", "confirm_password" => ""];
            } else {
                $errors["username"] = "Registration failed. Please try again.";
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
    <title>Admin Registration</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="center-container">
        <div class="auth-box">
            <h1>Admin Registration</h1>
            <p class="subtitle">Create your admin account</p>
            
            <?php if ($success_message): ?>
                <div class="alert-success">
                    <p><?= $success_message ?></p>
                </div>
                <p class="link-text"><a href="login.php">Click here to login</a></p>
            <?php endif; ?>
            
            <?php if (!$success_message): ?>
            <form action="" method="post">
                <div class="form-group">
                    <label for="username">Username <span class="required">*</span></label>
                    <input type="text" name="username" id="username" value="<?= htmlspecialchars($form["username"]) ?>" required>
                    <?php if (!empty($errors["username"])): ?>
                        <span class="error"><?= $errors["username"] ?></span>
                    <?php endif; ?>
                </div>
                
                <div class="form-group">
                    <label for="name">Full Name <span class="required">*</span></label>
                    <input type="text" name="name" id="name" value="<?= htmlspecialchars($form["name"]) ?>" required>
                    <?php if (!empty($errors["name"])): ?>
                        <span class="error"><?= $errors["name"] ?></span>
                    <?php endif; ?>
                </div>
                
                <div class="form-group">
                    <label for="email">Email <span class="required">*</span></label>
                    <input type="email" name="email" id="email" value="<?= htmlspecialchars($form["email"]) ?>" required>
                    <?php if (!empty($errors["email"])): ?>
                        <span class="error"><?= $errors["email"] ?></span>
                    <?php endif; ?>
                </div>
                
                <div class="form-group">
                    <label for="password">Password <span class="required">*</span></label>
                    <input type="password" name="password" id="password" required>
                    <?php if (!empty($errors["password"])): ?>
                        <span class="error"><?= $errors["password"] ?></span>
                    <?php endif; ?>
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">Confirm Password <span class="required">*</span></label>
                    <input type="password" name="confirm_password" id="confirm_password" required>
                    <?php if (!empty($errors["confirm_password"])): ?>
                        <span class="error"><?= $errors["confirm_password"] ?></span>
                    <?php endif; ?>
                </div>
                
                <button type="submit">Register</button>
            </form>
            
            <div class="divider"></div>
            
            <p class="link-text">Already have an account? <a href="login.php">Login here</a></p>
            <?php endif; ?>
            
            <div class="divider"></div>
            <p class="link-text"><a href="customer_warranty_tracker.php">Back to Customer Portal</a></p>
        </div>
    </div>
</body>
</html>
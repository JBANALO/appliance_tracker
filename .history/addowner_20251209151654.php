<?php
require_once "database.php";
require_once "Owner.php";

$owner = [
    "owner_name" => "", "email" => "", "phone" => "",
    "address" => "", "city" => "", "state" => "", "zip_code" => ""
];

$errors = [
    "owner_name" => "", "email" => "", "phone" => "",
    "address" => "", "city" => "", "state" => "", "zip_code" => ""
];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $owner["owner_name"] = trim(htmlspecialchars($_POST["owner_name"] ?? ""));
    $owner["email"] = trim(htmlspecialchars($_POST["email"] ?? ""));
    $owner["phone"] = trim(htmlspecialchars($_POST["phone"] ?? ""));
    $owner["address"] = trim(htmlspecialchars($_POST["address"] ?? ""));
    $owner["city"] = trim(htmlspecialchars($_POST["city"] ?? ""));
    $owner["state"] = trim(htmlspecialchars($_POST["state"] ?? ""));
    $owner["zip_code"] = trim(htmlspecialchars($_POST["zip_code"] ?? ""));

    if (empty($owner["owner_name"])) {
        $errors["owner_name"] = "Owner name is required";
    }
    if (empty($owner["email"])) {
        $errors["email"] = "Email is required";
    } elseif (!filter_var($owner["email"], FILTER_VALIDATE_EMAIL)) {
        $errors["email"] = "Invalid email format";
    }
    if (empty($owner["phone"])) {
        $errors["phone"] = "Phone number is required";
    }
    if (empty($owner["address"])) {
        $errors["address"] = "Address is required";
    }
    if (empty($owner["city"])) {
        $errors["city"] = "City is required";
    }
    if (empty($owner["state"])) {
        $errors["state"] = "State is required";
    }
    if (empty($owner["zip_code"])) {
        $errors["zip_code"] = "Zip code is required";
    }

    if (!array_filter($errors)) {
        $ownerObj = new Owner();
        $ownerObj->owner_name = $owner["owner_name"];
        $ownerObj->email = $owner["email"];
        $ownerObj->phone = $owner["phone"];
        $ownerObj->address = $owner["address"];
        $ownerObj->city = $owner["city"];
        $ownerObj->state = $owner["state"];
        $ownerObj->zip_code = $owner["zip_code"];

        if ($ownerObj->addOwner()) {
            header("Location: viewowner.php");
            exit;
        } else {
            echo "Failed to add owner.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Owner</title>
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

        form {
            display: flex;
            flex-direction: column;
            gap: 24px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        label {
            display: block;
            color: #333;
            font-weight: 600;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .required {
            color: #dc3545;
        }

        input[type="text"],
        input[type="email"],
        input[type="tel"] {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 15px;
            font-family: inherit;
            background: white;
        }

        input[type="text"]:focus,
        input[type="email"]:focus,
        input[type="tel"]:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .error {
            color: #dc3545;
            font-size: 13px;
            font-weight: 600;
            margin: 0;
        }

        .info-box {
            background: #f0f7ff;
            border-left: 4px solid #667eea;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            color: #333;
            font-size: 14px;
        }

        .form-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 24px;
        }

        .form-row .form-group {
            grid-column: span 1;
        }

        .button-group {
            display: flex;
            gap: 12px;
            margin-top: 20px;
            flex-wrap: wrap;
        }

        button[type="submit"] {
            background: #28a745;
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

        button[type="submit"]:hover {
            background: #218838;
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

            .form-row {
                grid-template-columns: 1fr;
            }

            .button-group {
                flex-direction: column;
            }

            button, .btn-secondary {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Add Owner</h1>
            <a href="viewowner.php" class="back-btn">
                <i class="fas fa-arrow-left"></i>
                Back to List
            </a>
        </div>

        <div class="info-box">
            <i class="fas fa-info-circle"></i>
            Fields marked with <span class="required">*</span> are required
        </div>

        <form action="" method="post">
            <div class="form-row">
                <div class="form-group">
                    <label for="owner_name">
                        Owner Name <span class="required">*</span>
                    </label>
                    <input type="text" name="owner_name" id="owner_name" value="<?= $owner["owner_name"] ?>" required>
                    <?php if ($errors["owner_name"]): ?>
                        <p class="error"><i class="fas fa-exclamation-circle"></i> <?= $errors["owner_name"] ?></p>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="email">
                        Email <span class="required">*</span>
                    </label>
                    <input type="email" name="email" id="email" value="<?= $owner["email"] ?>" required>
                    <?php if ($errors["email"]): ?>
                        <p class="error"><i class="fas fa-exclamation-circle"></i> <?= $errors["email"] ?></p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="phone">
                        Phone Number <span class="required">*</span>
                    </label>
                    <input type="tel" name="phone" id="phone" value="<?= $owner["phone"] ?>" required>
                    <?php if ($errors["phone"]): ?>
                        <p class="error"><i class="fas fa-exclamation-circle"></i> <?= $errors["phone"] ?></p>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="address">
                        Address <span class="required">*</span>
                    </label>
                    <input type="text" name="address" id="address" value="<?= $owner["address"] ?>" required>
                    <?php if ($errors["address"]): ?>
                        <p class="error"><i class="fas fa-exclamation-circle"></i> <?= $errors["address"] ?></p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="city">
                        City <span class="required">*</span>
                    </label>
                    <input type="text" name="city" id="city" value="<?= $owner["city"] ?>" required>
                    <?php if ($errors["city"]): ?>
                        <p class="error"><i class="fas fa-exclamation-circle"></i> <?= $errors["city"] ?></p>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="state">
                        State <span class="required">*</span>
                    </label>
                    <input type="text" name="state" id="state" value="<?= $owner["state"] ?>" required>
                    <?php if ($errors["state"]): ?>
                        <p class="error"><i class="fas fa-exclamation-circle"></i> <?= $errors["state"] ?></p>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="zip_code">
                        Zip Code <span class="required">*</span>
                    </label>
                    <input type="text" name="zip_code" id="zip_code" value="<?= $owner["zip_code"] ?>" required>
                    <?php if ($errors["zip_code"]): ?>
                        <p class="error"><i class="fas fa-exclamation-circle"></i> <?= $errors["zip_code"] ?></p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="button-group">
                <button type="submit">
                    <i class="fas fa-user-plus"></i>
                    Add Owner
                </button>
                <a href="viewowner.php" class="btn-secondary">
                    <i class="fas fa-times"></i>
                    Cancel
                </a>
            </div>
        </form>
    </div>
</body>
</html>
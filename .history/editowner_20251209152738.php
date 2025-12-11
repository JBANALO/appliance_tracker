<?php
require_once "database.php";
require_once "Owner.php";

$owner = [
    "id" => "", "owner_name" => "", "email" => "", "phone" => "",
    "address" => "", "city" => "", "state" => "", "zip_code" => ""
];

$errors = [
    "owner_name" => "", "email" => "", "phone" => "",
    "address" => "", "city" => "", "state" => "", "zip_code" => ""
];

$ownerObj = new Owner();

if (isset($_GET["id"])) {
    $owner_id = trim(htmlspecialchars($_GET["id"]));
    $ownerData = $ownerObj->getOwnerById($owner_id);
    
    if ($ownerData) {
        $owner["id"] = $ownerData["id"];
        $owner["owner_name"] = $ownerData["owner_name"];
        $owner["email"] = $ownerData["email"];
        $owner["phone"] = $ownerData["phone"];
        $owner["address"] = $ownerData["address"];
        $owner["city"] = $ownerData["city"];
        $owner["state"] = $ownerData["state"];
        $owner["zip_code"] = $ownerData["zip_code"];
    } else {
        echo "Owner not found.";
        exit;
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $owner["id"] = trim(htmlspecialchars($_POST["id"] ?? ""));
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
        $ownerObj->owner_name = $owner["owner_name"];
        $ownerObj->email = $owner["email"];
        $ownerObj->phone = $owner["phone"];
        $ownerObj->address = $owner["address"];
        $ownerObj->city = $owner["city"];
        $ownerObj->state = $owner["state"];
        $ownerObj->zip_code = $owner["zip_code"];

        if ($ownerObj->updateOwner($owner["id"])) {
            header("Location: viewowner.php");
            exit;
        } else {
            echo "Failed to update owner.";
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Edit Owner</title>
</head>
<body>
    <h1>Edit Owner</h1>
    <a href="viewowner.php">Back to List</a>

    <form action="" method="post">
        <label>Fields with <span>*</span> are required</label>

        <input type="hidden" name="id" value="<?= $owner["id"] ?>">

        <label for="owner_name">Owner Name <span>*</span></label>
        <input type="text" name="owner_name" id="owner_name" value="<?= $owner["owner_name"] ?>">
        <p class="error"><?= $errors["owner_name"] ?></p>

        <label for="email">Email <span>*</span></label>
        <input type="text" name="email" id="email" value="<?= $owner["email"] ?>">
        <p class="error"><?= $errors["email"] ?></p>

        <label for="phone">Phone Number <span>*</span></label>
        <input type="text" name="phone" id="phone" value="<?= $owner["phone"] ?>">
        <p class="error"><?= $errors["phone"] ?></p>

        <label for="address">Address <span>*</span></label>
        <input type="text" name="address" id="address" value="<?= $owner["address"] ?>">
        <p class="error"><?= $errors["address"] ?></p>

        <label for="city">City <span>*</span></label>
        <input type="text" name="city" id="city" value="<?= $owner["city"] ?>">
        <p class="error"><?= $errors["city"] ?></p>

        <label for="state">State <span>*</span></label>
        <input type="text" name="state" id="state" value="<?= $owner["state"] ?>">
        <p class="error"><?= $errors["state"] ?></p>

        <label for="zip_code">Zip Code <span>*</span></label>
        <input type="text" name="zip_code" id="zip_code" value="<?= $owner["zip_code"] ?>">
        <p class="error"><?= $errors["zip_code"] ?></p>

        <button type="submit">Update Owner</button>
    </form>
</body>
</html>
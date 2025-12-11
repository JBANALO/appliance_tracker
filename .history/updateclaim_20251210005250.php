<?php
session_start();

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

require_once "Claim.php";
require_once "EmailNotification.php";

$id = $_GET['id'] ?? null;
$status = $_GET['status'] ?? null;

if ($id && $status && in_array($status, ['Approved', 'Rejected'])) {
    $claim = new Claim();
    
    // Get claim details before updating
    $claimDetails = $claim->getClaimById($id);
    
    // Update the claim status
    if ($claim->updateClaimStatus($id, $status)) {
        // Send email notification
        $emailNotification = new EmailNotification();
        $emailNotification->sendClaimStatusUpdateEmail(
            $claimDetails['email'],
            $claimDetails['owner_name'],
            $claimDetails['appliance_name'],
            $id,
            $status,
            $claimDetails['admin_notes'] ?? ''
        );
    }
}

header("Location: viewclaim.php");
exit;
?>
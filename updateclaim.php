<?php
ob_start();
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/security.php';

initSecureSession();

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

require_once "claim.php";
    require_once "EmailNotification.php";

$id = $_GET['id'] ?? null;
$status = $_GET['status'] ?? null;

if ($id && $status && in_array($status, ['Approved', 'Rejected'])) {
    $claim = new Claim();
    
   
    $claimDetails = $claim->getClaimById($id);
    

    if ($claim->updateClaimStatus($id, $status)) {
      
        // Redirect immediately
        header("Location: viewclaim.php");
        flush();
        
        // Send email in background (after redirect)
        $emailNotification = new EmailNotification();
        $emailNotification->sendClaimStatusUpdateEmail(
            $claimDetails['email'],
            $claimDetails['owner_name'],
            $claimDetails['appliance_name'],
            $id,
            $status,
            $claimDetails['admin_notes'] ?? ''
        );
        exit;
    }
}

header("Location: viewclaim.php");
exit;
?>
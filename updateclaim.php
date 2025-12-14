<?php
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
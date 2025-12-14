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
      
        // Disconnect client to truly disconnect and navigate immediately
        header("Connection: close");
        header("Content-Length: 0");
        header("Location: viewclaim.php");
        ob_end_clean();
        flush();
        
        // Send email AFTER client disconnects (truly async)
        try {
            @$emailNotification = new EmailNotification();
            @$emailNotification->sendClaimStatusUpdateEmail(
                $claimDetails['email'],
                $claimDetails['owner_name'],
                $claimDetails['appliance_name'],
                $id,
                $status,
                $claimDetails['admin_notes'] ?? ''
            );
        } catch (Exception $e) {
            // Silently fail
        }
        exit;
    }
}

header("Location: viewclaim.php");
exit;
?>
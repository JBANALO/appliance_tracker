<?php
require_once 'Database.php';

class Claim {
    public $id = "";
    public $appliance_id = "";
    public $claim_date = "";
    public $claim_description = "";
    public $claim_status = "";
    public $resolution_notes = "";

    protected $db;

    public function __construct() {
        $this->db = new Database();
    }

    public function addClaim() {
        $sql = "INSERT INTO claim (appliance_id, claim_date, claim_description, claim_status, resolution_notes)
                VALUES (:appliance_id, :claim_date, :claim_description, :claim_status, :resolution_notes)";
        
        $query = $this->db->connect()->prepare($sql);
        $query->bindParam(":appliance_id", $this->appliance_id);
        $query->bindParam(":claim_date", $this->claim_date);
        $query->bindParam(":claim_description", $this->claim_description);
        $query->bindParam(":claim_status", $this->claim_status);
        $query->bindParam(":resolution_notes", $this->resolution_notes);
    
        return $query->execute();
    }

    public function viewClaim($search = "", $status = "") {
        $sql = "SELECT c.*, a.appliance_name, a.serial_number, o.owner_name, o.email 
                FROM claim c 
                JOIN appliance a ON c.appliance_id = a.id 
                JOIN owner o ON a.owner_id = o.id 
                WHERE a.appliance_name LIKE CONCAT('%', :search, '%') 
                AND c.claim_status LIKE CONCAT('%', :status, '%') 
                ORDER BY c.claim_date DESC";
        
        $query = $this->db->connect()->prepare($sql);
        $query->bindParam(":search", $search);
        $query->bindParam(":status", $status);
        if ($query->execute()) {
            return $query->fetchAll();
        } else {
            return null;
        }
    }

    public function getClaimById($id) {
        $sql = "SELECT c.*, 
                a.appliance_name, 
                a.model_number,
                a.serial_number, 
                a.warranty_end_date,
                CASE 
                    WHEN a.warranty_end_date < CURDATE() THEN 'Expired'
                    ELSE 'Active'
                END as status,
                o.owner_name, 
                o.email, 
                o.phone,
                c.claim_description as issue_description,
                c.resolution_notes as admin_notes
                FROM claim c 
                JOIN appliance a ON c.appliance_id = a.id 
                JOIN owner o ON a.owner_id = o.id 
                WHERE c.id = :id";
        $query = $this->db->connect()->prepare($sql);
        $query->bindParam(":id", $id);
        if ($query->execute()) {
            return $query->fetch();
        } else {
            return null;
        }
    }

    public function getClaimsByAppliance($appliance_id) {
        $sql = "SELECT * FROM claim WHERE appliance_id = :appliance_id ORDER BY claim_date DESC";
        $query = $this->db->connect()->prepare($sql);
        $query->bindParam(":appliance_id", $appliance_id);
        if ($query->execute()) {
            return $query->fetchAll();
        } else {
            return null;
        }
    }

    public function updateClaim($id) {
        $sql = "UPDATE claim SET claim_date=:claim_date, claim_description=:claim_description, claim_status=:claim_status, resolution_notes=:resolution_notes WHERE id=:id";
        
        $query = $this->db->connect()->prepare($sql);
        $query->bindParam(":claim_date", $this->claim_date);
        $query->bindParam(":claim_description", $this->claim_description);
        $query->bindParam(":claim_status", $this->claim_status);
        $query->bindParam(":resolution_notes", $this->resolution_notes);
        $query->bindParam(":id", $id);
    
        return $query->execute();
    }

    public function updateClaimStatus($id, $status, $admin_notes = '') {
        if (!empty($admin_notes)) {
            $sql = "UPDATE claim SET claim_status = :status, resolution_notes = :admin_notes WHERE id = :id";
            $query = $this->db->connect()->prepare($sql);
            $query->bindParam(":status", $status);
            $query->bindParam(":admin_notes", $admin_notes);
            $query->bindParam(":id", $id);
        } else {
            $sql = "UPDATE claim SET claim_status = :status WHERE id = :id";
            $query = $this->db->connect()->prepare($sql);
            $query->bindParam(":status", $status);
            $query->bindParam(":id", $id);
        }

        return $query->execute();
    }

    public function deleteClaim($id) {
        $sql = "DELETE FROM claim WHERE id = :id";
        $query = $this->db->connect()->prepare($sql);
        $query->bindParam(":id", $id);
        return $query->execute();
    }

    public function getClaimStats() {
        $sql = "SELECT 
                COUNT(*) as total_claims,
                SUM(CASE WHEN claim_status = 'Pending' THEN 1 ELSE 0 END) as pending_claims,
                SUM(CASE WHEN claim_status = 'Approved' THEN 1 ELSE 0 END) as approved_claims,
                SUM(CASE WHEN claim_status = 'Rejected' THEN 1 ELSE 0 END) as rejected_claims,
                SUM(CASE WHEN claim_status = 'Completed' THEN 1 ELSE 0 END) as completed_claims
                FROM claim";
        
        $query = $this->db->connect()->prepare($sql);
        if ($query->execute()) {
            return $query->fetch();
        } else {
            return null;
        }
    }
}
?>
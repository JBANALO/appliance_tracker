<?php
require_once 'Database.php';

class Appliance {
    public $id = "";
    public $appliance_name = "";
    public $model_number = "";
    public $serial_number = "";
    public $purchase_date = "";
    public $warranty_period = "";
    public $warranty_end_date = "";
    public $status = "";
    public $owner_id = "";

    protected $db;

    public function __construct() {
        $this->db = new Database();
    }

    public function addAppliance() {
        $sql = "INSERT INTO appliance (appliance_name, model_number, serial_number, purchase_date, warranty_period, warranty_end_date, status, owner_id)
                VALUES (:appliance_name, :model_number, :serial_number, :purchase_date, :warranty_period, :warranty_end_date, :status, :owner_id)";
        
        $query = $this->db->connect()->prepare($sql);
        $query->bindParam(":appliance_name", $this->appliance_name);
        $query->bindParam(":model_number", $this->model_number);
        $query->bindParam(":serial_number", $this->serial_number);
        $query->bindParam(":purchase_date", $this->purchase_date);
        $query->bindParam(":warranty_period", $this->warranty_period);
        $query->bindParam(":warranty_end_date", $this->warranty_end_date);
        $query->bindParam(":status", $this->status);
        $query->bindParam(":owner_id", $this->owner_id);
    
        return $query->execute();
    }


    public function viewAppliance($search = "", $status = "") {
        $sql = "SELECT *, 
                CASE 
                    WHEN warranty_end_date < CURDATE() THEN 'Expired'
                    ELSE 'Active'
                END as calculated_status
                FROM appliance 
                WHERE appliance_name LIKE CONCAT('%', :search, '%')";
        
        // If status filter is provided, add it to WHERE clause
        if (!empty($status)) {
            $sql .= " HAVING calculated_status = :status";
        }
        
        $sql .= " ORDER BY appliance_name ASC";
        
        $query = $this->db->connect()->prepare($sql);
        $query->bindParam(":search", $search);
        
        if (!empty($status)) {
            $query->bindParam(":status", $status);
        }
        
        if ($query->execute()) {
            $results = $query->fetchAll();
            // Replace the 'status' field with calculated status
            foreach ($results as &$row) {
                $row['status'] = $row['calculated_status'];
            }
            return $results;
        } else {
            return null;
        }
    }

    public function isApplianceExist($appliance_name) {
        $sql = "SELECT COUNT(*) as total FROM appliance WHERE appliance_name = :appliance_name";
        $query = $this->db->connect()->prepare($sql);
        $query->bindParam(":appliance_name", $appliance_name);
        $record = null;
        if ($query->execute()) {
            $record = $query->fetch();
        }
        if ($record["total"] > 0) {
            return true;
        } else {
            return false;
        }
    }

    // UPDATED: Returns appliance with calculated status
    public function getApplianceById($id) {
        $sql = "SELECT *, 
                CASE 
                    WHEN warranty_end_date < CURDATE() THEN 'Expired'
                    ELSE 'Active'
                END as calculated_status
                FROM appliance WHERE id = :id";
        $query = $this->db->connect()->prepare($sql);
        $query->bindParam(":id", $id);
        if ($query->execute()) {
            $result = $query->fetch();
            if ($result) {
                $result['status'] = $result['calculated_status'];
            }
            return $result;
        } else {
            return null;
        }
    }

    // UPDATED: Returns appliance with calculated status
    public function fetchAppliance($id) {
        $sql = "SELECT *, 
                CASE 
                    WHEN warranty_end_date < CURDATE() THEN 'Expired'
                    ELSE 'Active'
                END as calculated_status
                FROM appliance WHERE id = :id";
        $query = $this->db->connect()->prepare($sql);
        $query->bindParam(":id", $id);
        if ($query->execute()) {
            $result = $query->fetch();
            if ($result) {
                $result['status'] = $result['calculated_status'];
            }
            return $result;
        } else {
            return null;
        }
    }

    public function updateAppliance($id) {
        $sql = "UPDATE appliance SET appliance_name=:appliance_name, model_number=:model_number, serial_number=:serial_number, purchase_date=:purchase_date, warranty_period=:warranty_period, warranty_end_date=:warranty_end_date, status=:status, owner_id=:owner_id WHERE id=:id";
        
        $query = $this->db->connect()->prepare($sql);
        $query->bindParam(":appliance_name", $this->appliance_name);
        $query->bindParam(":model_number", $this->model_number);
        $query->bindParam(":serial_number", $this->serial_number);
        $query->bindParam(":purchase_date", $this->purchase_date);
        $query->bindParam(":warranty_period", $this->warranty_period);
        $query->bindParam(":warranty_end_date", $this->warranty_end_date);
        $query->bindParam(":status", $this->status);
        $query->bindParam(":owner_id", $this->owner_id);
        $query->bindParam(":id", $id);
    
        return $query->execute();
    }

    public function deleteAppliance($id) {
        $sql = "DELETE FROM appliance WHERE id = :id";
        $query = $this->db->connect()->prepare($sql);
        $query->bindParam(":id", $id);
        return $query->execute();
    }

  
    public function checkWarrantyStatus($warranty_end_date) {
        $today = date('Y-m-d');
        if ($today > $warranty_end_date) {
            return "Expired";
        } else {
            return "Active";
        }
    }
}
?>
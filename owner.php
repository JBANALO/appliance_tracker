<?php
require_once 'database.php';

class Owner {
    public $id = "";
    public $owner_name = "";
    public $email = "";
    public $phone = "";
    public $address = "";
    public $city = "";
    public $state = "";
    public $zip_code = "";

    protected $db;

    public function __construct() {
        $this->db = new Database();
    }

    public function addOwner() {
        $sql = "INSERT INTO owner (owner_name, email, phone, address, city, state, zip_code)
                VALUES (:owner_name, :email, :phone, :address, :city, :state, :zip_code)";
        
        $query = $this->db->connect()->prepare($sql);
        $query->bindParam(":owner_name", $this->owner_name);
        $query->bindParam(":email", $this->email);
        $query->bindParam(":phone", $this->phone);
        $query->bindParam(":address", $this->address);
        $query->bindParam(":city", $this->city);
        $query->bindParam(":state", $this->state);
        $query->bindParam(":zip_code", $this->zip_code);
    
        return $query->execute();
    }

    public function viewOwner($search = "") {
        $sql = "SELECT * FROM owner WHERE owner_name LIKE CONCAT('%', ?, '%') OR email LIKE CONCAT('%', ?, '%') ORDER BY owner_name ASC";
        
        $query = $this->db->connect()->prepare($sql);
        if ($query->execute([$search, $search])) {
            return $query->fetchAll();
        } else {
            return null;
        }
    }

    public function isOwnerExist($owner_name) {
        $sql = "SELECT COUNT(*) as total FROM owner WHERE owner_name = :owner_name";
        $query = $this->db->connect()->prepare($sql);
        $query->bindParam(":owner_name", $owner_name);
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

    public function getOwnerById($id) {
        $sql = "SELECT * FROM owner WHERE id = :id";
        $query = $this->db->connect()->prepare($sql);
        $query->bindParam(":id", $id);
        if ($query->execute()) {
            return $query->fetch();
        } else {
            return null;
        }
    }

    public function getOwnerAppliances($owner_id) {
        $sql = "SELECT * FROM appliance WHERE owner_id = :owner_id ORDER BY appliance_name ASC";
        $query = $this->db->connect()->prepare($sql);
        $query->bindParam(":owner_id", $owner_id);
        if ($query->execute()) {
            return $query->fetchAll();
        } else {
            return null;
        }
    }

    public function updateOwner($id) {
        $sql = "UPDATE owner SET owner_name=:owner_name, email=:email, phone=:phone, address=:address, city=:city, state=:state, zip_code=:zip_code WHERE id=:id";
        
        $query = $this->db->connect()->prepare($sql);
        $query->bindParam(":owner_name", $this->owner_name);
        $query->bindParam(":email", $this->email);
        $query->bindParam(":phone", $this->phone);
        $query->bindParam(":address", $this->address);
        $query->bindParam(":city", $this->city);
        $query->bindParam(":state", $this->state);
        $query->bindParam(":zip_code", $this->zip_code);
        $query->bindParam(":id", $id);
    
        return $query->execute();
    }

    public function deleteOwner($id) {
        $sql = "DELETE FROM owner WHERE id = :id";
        $query = $this->db->connect()->prepare($sql);
        $query->bindParam(":id", $id);
        return $query->execute();
    }
}
?>

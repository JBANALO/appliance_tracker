<?php
require_once 'database.php';

class Admin {
    public $id = "";
    public $username = "";
    public $password = "";
    public $name = "";
    public $email = "";
    public $verification_code = "";
    public $is_verified = 0;

    protected $db;

    public function __construct() {
        $this->db = new Database();
    }

    public function register() {
        $hashed_password = password_hash($this->password, PASSWORD_DEFAULT);
        
        // Try with verification columns first (new schema)
        $sql = "INSERT INTO admin (username, password, name, email, verification_code, is_verified) 
                VALUES (:username, :password, :name, :email, :verification_code, :is_verified)";
        
        $query = $this->db->connect()->prepare($sql);
        $query->bindParam(":username", $this->username);
        $query->bindParam(":password", $hashed_password);
        $query->bindParam(":name", $this->name);
        $query->bindParam(":email", $this->email);
        $query->bindParam(":verification_code", $this->verification_code);
        $query->bindParam(":is_verified", $this->is_verified);
    
        if ($query->execute()) {
            return true;
        }
        
        // If that fails (old schema), try without verification columns
        $sql_fallback = "INSERT INTO admin (username, password, name, email) 
                         VALUES (:username, :password, :name, :email)";
        
        $query_fallback = $this->db->connect()->prepare($sql_fallback);
        $query_fallback->bindParam(":username", $this->username);
        $query_fallback->bindParam(":password", $hashed_password);
        $query_fallback->bindParam(":name", $this->name);
        $query_fallback->bindParam(":email", $this->email);
    
        return $query_fallback->execute();
    }

    public function verifyEmail($email, $code) {
        $sql = "UPDATE admin SET is_verified = 1, verification_code = NULL WHERE email = :email AND verification_code = :code AND is_verified = 0";
        $query = $this->db->connect()->prepare($sql);
        $query->bindParam(":email", $email);
        $query->bindParam(":code", $code);
        
        return $query->execute() && $query->rowCount() > 0;
    }

    public function login($username, $password) {
        $sql = "SELECT * FROM admin WHERE username = :username";
        $query = $this->db->connect()->prepare($sql);
        $query->bindParam(":username", $username);
        
        if ($query->execute()) {
            $admin = $query->fetch();
            
            if ($admin && password_verify($password, $admin['password'])) {
                return $admin;
            }
        }
        return false;
    }

    public function usernameExists($username) {
        $sql = "SELECT COUNT(*) as total FROM admin WHERE username = :username";
        $query = $this->db->connect()->prepare($sql);
        $query->bindParam(":username", $username);
        
        if ($query->execute()) {
            $record = $query->fetch();
            return $record["total"] > 0;
        }
        return false;
    }
}
?>
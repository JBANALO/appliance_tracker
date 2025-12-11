<?php
require_once __DIR__ . '/config.php';

class Database {
    private $host;
    private $dbname;
    private $username;
    private $password;

    public function __construct() {
        $this->host = DB_HOST;
        $this->dbname = DB_NAME;
        $this->username = DB_USER;
        $this->password = DB_PASS;
    }

    public function connect() {
        try {
            $pdo = new PDO("mysql:host={$this->host};dbname={$this->dbname};charset=utf8mb4", 
                          $this->username, 
                          $this->password,
                          [
                              PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                              PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                              PDO::ATTR_EMULATE_PREPARES => false
                          ]);
            return $pdo;
        } catch (PDOException $e) {
            // Log error without exposing details
            error_log("Database connection failed: " . $e->getMessage());
            
            if (APP_DEBUG) {
                die("Database connection failed: " . $e->getMessage());
            } else {
                die("A database error occurred. Please contact support.");
            }
        }
    }
}
?>
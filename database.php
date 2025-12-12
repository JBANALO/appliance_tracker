<?php
require_once __DIR__ . '/config.php';

class Database {
    private $host;
    private $dbname;
    private $username;
    private $password;
    private $port;

    public function __construct() {
        $this->host = DB_HOST;
        $this->dbname = DB_NAME;
        $this->username = DB_USER;
        $this->password = DB_PASS;
        $this->port = defined('DB_PORT') ? DB_PORT : '3306';
    }

    public function connect() {
        try {
        
            $dsn = "mysql:host={$this->host};port={$this->port};dbname={$this->dbname};charset=utf8mb4";
            
            $pdo = new PDO($dsn, 
                          $this->username, 
                          $this->password,
                          [
                              PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                              PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                              PDO::ATTR_EMULATE_PREPARES => false
                          ]);
            return $pdo;
        } catch (PDOException $e) {
           
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
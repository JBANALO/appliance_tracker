<?php
require_once 'Database.php';

class Notification {
    protected $db;

    public function __construct() {
        $this->db = new Database();
    }

    public function addNotification($type, $title, $message, $link = null) {
        $sql = "INSERT INTO notification (type, title, message, link, is_read, created_at) 
                VALUES (:type, :title, :message, :link, 0, NOW())";
        
        $query = $this->db->connect()->prepare($sql);
        $query->bindParam(':type', $type);
        $query->bindParam(':title', $title);
        $query->bindParam(':message', $message);
        $query->bindParam(':link', $link);
        
        return $query->execute();
    }

 
    public function getUnreadCount() {
        $sql = "SELECT COUNT(*) as count FROM notification WHERE is_read = 0";
        $query = $this->db->connect()->query($sql);
        $result = $query->fetch();
        return $result['count'];
    }

   
    public function getAllNotifications($limit = 10) {
        $sql = "SELECT * FROM notification ORDER BY created_at DESC LIMIT :limit";
        $query = $this->db->connect()->prepare($sql);
        $query->bindParam(':limit', $limit, PDO::PARAM_INT);
        $query->execute();
        return $query->fetchAll();
    }

    public function getUnreadNotifications($limit = 10) {
        $sql = "SELECT * FROM notification WHERE is_read = 0 ORDER BY created_at DESC LIMIT :limit";
        $query = $this->db->connect()->prepare($sql);
        $query->bindParam(':limit', $limit, PDO::PARAM_INT);
        $query->execute();
        return $query->fetchAll();
    }

    public function markAsRead($id) {
        $sql = "UPDATE notification SET is_read = 1 WHERE id = :id";
        $query = $this->db->connect()->prepare($sql);
        $query->bindParam(':id', $id);
        return $query->execute();
    }

    public function markAllAsRead() {
        $sql = "UPDATE notification SET is_read = 1 WHERE is_read = 0";
        $query = $this->db->connect()->query($sql);
        return $query->execute();
    }

   
    public function deleteOldNotifications() {
        $sql = "DELETE FROM notification WHERE created_at < DATE_SUB(NOW(), INTERVAL 30 DAY)";
        $query = $this->db->connect()->query($sql);
        return $query->execute();
    }
}
?>
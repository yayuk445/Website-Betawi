<?php
require_once __DIR__ . '/Database.php';

class ChatHistory {
    private $db;

    public function __construct() {
        $this->db = new Database();
        $this->db->createTables();
    }

    public function saveMessage($sessionId, $role, $message) {
        $userId = $_SESSION['user_id'] ?? null;

        if (!$this->db->isConnected()) {
            if (!isset($_SESSION['chat_history'])) {
                $_SESSION['chat_history'] = [];
            }
            $_SESSION['chat_history'][] = [
                'role' => $role,
                'message' => $message,
                'timestamp' => time(),
                'user_id' => $userId
            ];
            return true;
        }

        $sql = "INSERT INTO chat_history (session_id, user_id, role, message) VALUES (?, ?, ?, ?)";
        $stmt = $this->db->getConnection()->prepare($sql);
        return $stmt->execute([$sessionId, $userId, $role, $message]);
    }

    public function getHistory($sessionId, $limit = 10) {
        $userId = $_SESSION['user_id'] ?? null;

        if (!$this->db->isConnected()) {
            $history = $_SESSION['chat_history'] ?? [];
            return array_slice($history, -$limit);
        }

        $limit = (int)$limit;
        if ($userId) {
            $sql = "SELECT role, message, created_at FROM chat_history WHERE user_id = ? ORDER BY created_at DESC LIMIT $limit";
            $stmt = $this->db->getConnection()->prepare($sql);
            $stmt->execute([$userId]);
        } else {
            $sql = "SELECT role, message, created_at FROM chat_history WHERE session_id = ? ORDER BY created_at DESC LIMIT $limit";
            $stmt = $this->db->getConnection()->prepare($sql);
            $stmt->execute([$sessionId]);
        }

        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return array_reverse($results);
    }

    public function migrateHistoryToUser($sessionId, $userId) {
        $sql = "UPDATE chat_history SET user_id = ? WHERE session_id = ? AND user_id IS NULL";
        $stmt = $this->db->getConnection()->prepare($sql);
        return $stmt->execute([$userId, $sessionId]);
    }

    public function clearHistory($sessionId) {
        if (!$this->db->isConnected()) {
            unset($_SESSION['chat_history']);
            return true;
        }

        $sql = "DELETE FROM chat_history WHERE session_id = ?";
        $stmt = $this->db->getConnection()->prepare($sql);
        return $stmt->execute([$sessionId]);
    }
}
?>


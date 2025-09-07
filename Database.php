<?php
class Database {
    private $connection;

    public function __construct() {
        try {
            $this->connection = new PDO(
                "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME,
                DB_USER,
                DB_PASS,
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
        } catch (PDOException $e) {
            $this->connection = null;
        }
    }

    public function getConnection() {
        return $this->connection;
    }

    public function isConnected() {
        return $this->connection !== null;
    }

    public function createTables() {
        if (!$this->isConnected()) return false;

        try {
            $this->connection->exec("
                CREATE TABLE IF NOT EXISTS chat_history (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    session_id VARCHAR(255) NOT NULL,
                    role ENUM('user', 'assistant') NOT NULL,
                    message TEXT NOT NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    INDEX(session_id),
                    INDEX(created_at)
                )
            ");

            $this->connection->exec("
                CREATE TABLE IF NOT EXISTS rate_limits (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    session_id VARCHAR(255) NOT NULL UNIQUE,
                    request_count INT DEFAULT 1,
                    reset_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    INDEX(session_id)
                )
            ");

            return true;
        } catch (PDOException $e) {
            error_log("Create tables failed: " . $e->getMessage());
            return false;
        }
    }
}
?>


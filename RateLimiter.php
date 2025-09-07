<?php
class RateLimiter {
    private $db;
    private $maxRequests = 30;
    private $timeWindow = 3600;
    
    public function __construct() {
        $this->db = new Database();
    }
    
    public function checkLimit($sessionId) {
        if (!$this->db->isConnected()) {
            $now = time();
            if (!isset($_SESSION['rate_limit'])) {
                $_SESSION['rate_limit'] = ['count' => 1, 'reset_time' => $now + $this->timeWindow];
                return true;
            }
            
            if ($now > $_SESSION['rate_limit']['reset_time']) {
                $_SESSION['rate_limit'] = ['count' => 1, 'reset_time' => $now + $this->timeWindow];
                return true;
            }
            
            if ($_SESSION['rate_limit']['count'] >= $this->maxRequests) {
                return false;
            }
            
            $_SESSION['rate_limit']['count']++;
            return true;
        }
        
        $now = date('Y-m-d H:i:s');
        $resetTime = date('Y-m-d H:i:s', strtotime('+1 hour'));
        
        $sql = "SELECT request_count, reset_time FROM rate_limits WHERE session_id = ?";
        $stmt = $this->db->getConnection()->prepare($sql);
        $stmt->execute([$sessionId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$result) {
            $sql = "INSERT INTO rate_limits (session_id, request_count, reset_time) VALUES (?, 1, ?)";
            $stmt = $this->db->getConnection()->prepare($sql);
            $stmt->execute([$sessionId, $resetTime]);
            return true;
        }
        
        if (strtotime($now) > strtotime($result['reset_time'])) {
            $sql = "UPDATE rate_limits SET request_count = 1, reset_time = ? WHERE session_id = ?";
            $stmt = $this->db->getConnection()->prepare($sql);
            $stmt->execute([$resetTime, $sessionId]);
            return true;
        }
        
        if ($result['request_count'] >= $this->maxRequests) {
            return false;
        }
        
        $sql = "UPDATE rate_limits SET request_count = request_count + 1 WHERE session_id = ?";
        $stmt = $this->db->getConnection()->prepare($sql);
        $stmt->execute([$sessionId]);
        
        return true;
    }
}
?>
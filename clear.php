<?php
session_start();
header("Access-Control-Allow-Origin: http://purwekurto.test");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// Untuk handle preflight OPTIONS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once '../config/config.php';
require_once '../includes/ChatHistory.php';

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Method not allowed', 405);
    }
    
    $sessionId = $_POST['session_id'] ?? session_id();
    
    $chatHistory = new ChatHistory();
    $chatHistory->clearHistory($sessionId);
    
    echo json_encode([
        'status' => 'success',
        'message' => 'History berhasil dihapus'
    ]);
    
} catch (Exception $e) {
    http_response_code($e->getCode() ?: 500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
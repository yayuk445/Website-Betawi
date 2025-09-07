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

require_once '../includes/Database.php';
require_once '../config/config.php';
require_once '../includes/ChatHistory.php';

try {
    $sessionId = $_GET['session_id'] ?? session_id();
    $limit = min(intval($_GET['limit'] ?? 20), 100);
    
    $chatHistory = new ChatHistory();
    $history = $chatHistory->getHistory($sessionId, $limit);
    
    echo json_encode([
        'status' => 'success',
        'history' => $history,
        'count' => count($history)
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
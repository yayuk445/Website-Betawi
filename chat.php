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
require_once '../includes/RateLimiter.php';
require_once '../includes/GeminiAPI.php';

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Method not allowed', 405);
    }

    $data = json_decode(file_get_contents("php://input"), true);
    $message = trim($data['message'] ?? '');
    $sessionId = $data['sessionId'] ?? session_id();

    if ($message === '') {
        throw new Exception('Pesan tidak boleh kosong', 400);
    }

    $rateLimiter = new RateLimiter();
    if (!$rateLimiter->checkLimit($sessionId)) {
        throw new Exception('Batas penggunaan tercapai, coba lagi nanti', 429);
    }

    $chatHistory = new ChatHistory();
    $history = $chatHistory->getHistory($sessionId, 10);

    $gemini = new GeminiAPI();
    $reply = $gemini->sendMessage($message, $history);

    $chatHistory->saveMessage($sessionId, 'user', $message);
    $chatHistory->saveMessage($sessionId, 'assistant', $reply);

    echo json_encode([
        'status' => 'success',
        'message' => $reply,
        'timestamp' => time()
    ]);

} catch (Exception $e) {
    http_response_code($e->getCode() ?: 500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}

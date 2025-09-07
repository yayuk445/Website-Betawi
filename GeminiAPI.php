<?php
class GeminiAPI {
    private $apiKey;
    private $apiUrl;
    
    public function __construct() {
        $this->apiKey = GEMINI_API_KEY;
        $this->apiUrl = GEMINI_API_URL;
    }
    
    public function sendMessage($message, $history = []) {
        $contents = [
            [
                'role' => 'user',
                'parts' => [['text' => SYSTEM_PROMPT]]
            ]
        ];
        
        foreach ($history as $msg) {
            $contents[] = [
                'role' => $msg['role'] === 'user' ? 'user' : 'model',
                'parts' => [['text' => $msg['message']]]
            ];
        }
        
        $contents[] = [
            'role' => 'user',
            'parts' => [['text' => $message]]
        ];
        
        $payload = [
            'contents' => $contents,
            'generationConfig' => [
                'temperature' => 0.7,
                'topK' => 40,
                'topP' => 0.95,
                'maxOutputTokens' => 1024,
            ],
            'safetySettings' => [
                [
                    'category' => 'HARM_CATEGORY_HARASSMENT',
                    'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'
                ],
                [
                    'category' => 'HARM_CATEGORY_HATE_SPEECH',
                    'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'
                ],
                [
                    'category' => 'HARM_CATEGORY_SEXUALLY_EXPLICIT',
                    'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'
                ],
                [
                    'category' => 'HARM_CATEGORY_DANGEROUS_CONTENT',
                    'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'
                ]
            ]
        ];
        
        $response = $this->makeRequest($payload);
        
        if (!$response) {
            throw new Exception('Gagal menghubungi Gemini API', 500);
        }
        
        if (!isset($response['candidates'][0]['content']['parts'][0]['text'])) {
            throw new Exception('Response tidak valid dari Gemini API', 500);
        }
        
        return $response['candidates'][0]['content']['parts'][0]['text'];
    }
    
    private function makeRequest($payload) {
        $ch = curl_init();
        
        curl_setopt_array($ch, [
            CURLOPT_URL => $this->apiUrl . '?key=' . $this->apiKey,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
            ],
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => false
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        if (curl_error($ch)) {
            curl_close($ch);
            throw new Exception('cURL Error: ' . curl_error($ch), 500);
        }
        
        curl_close($ch);
        
        if ($httpCode !== 200) {
            $error = json_decode($response, true);
            $errorMsg = $error['error']['message'] ?? 'Unknown API error';
            throw new Exception("API Error: $errorMsg", $httpCode);
        }
        
        return json_decode($response, true);
    }
}
?>
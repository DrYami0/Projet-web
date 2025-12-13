<?php
header('Content-Type: application/json');

require_once "../../config.php";

// Get the raw POST data and decode it
$input = json_decode(file_get_contents('php://input'), true);
$word = $input['word'] ?? '';
$type = $input['type'] ?? '';

if (!$word || !$type) {
    echo json_encode(['error' => 'Word and type are required']);
    exit;
}

// Get Google Gemini API key from config
$apiKey = GEMINI_API_KEY;

if (empty($apiKey) || $apiKey === 'YOUR_GEMINI_API_KEY_HERE') {
    echo json_encode(['error' => 'API key not configured. Please add your Google Gemini API key to config.php']);
    exit;
}

// Craft a precise prompt in French
$prompt = "Expliquez brièvement pourquoi le mot \"{$word}\" est classé comme \"{$type}\" en grammaire française. Répondez en français, soyez concis (2-3 phrases) et pédagogique.";

// Prepare the data for the Google Gemini API
$data = [
    'contents' => [
        [
            'role' => 'user',
            'parts' => [
                [
                    'text' => $prompt
                ]
            ]
        ]
    ],
    'generationConfig' => [
        'maxOutputTokens' => 150,
        'temperature' => 0.7
    ]
];

// Initialize cURL session to call Google Gemini API
$ch = curl_init('https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash-lite:generateContent?key=' . urlencode($apiKey));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json'
]);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);

// Execute the request and capture the response
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

if ($curlError) {
    echo json_encode(['error' => 'Network error: ' . $curlError]);
    exit;
}

if ($httpCode !== 200) {
    $errorData = json_decode($response, true);
    $errorMsg = $errorData['error']['message'] ?? 'API request failed';
    echo json_encode(['error' => 'Gemini API error: ' . $errorMsg]);
    exit;
}

// Decode the response from Google Gemini
$responseData = json_decode($response, true);

if (!isset($responseData['candidates'][0]['content']['parts'][0]['text'])) {
    echo json_encode(['error' => 'Invalid response from Gemini API']);
    exit;
}

// Extract the explanation text
$explanation = $responseData['candidates'][0]['content']['parts'][0]['text'];

// Send the explanation back to the frontend
echo json_encode(['explanation' => $explanation]);
?>

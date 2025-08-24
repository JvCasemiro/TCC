<?php
// Enable error reporting for debugging
ini_set('display_errors', 0);
error_reporting(0);

// Set content type to JSON
header('Content-Type: application/json');

// Function to send JSON response
function sendResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    echo json_encode($data);
    exit;
}

try {
    // Create a stop file to signal the Python script to stop
    $stopFile = __DIR__ . '/stop_detection.flag';
    file_put_contents($stopFile, '1');
    
    // Send success response
    sendResponse(['success' => true, 'message' => 'Sinal de parada enviado com sucesso']);
    
} catch (Exception $e) {
    // Handle any errors
    sendResponse([
        'success' => false,
        'error' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ], 500);
}

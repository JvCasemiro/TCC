<?php
ini_set('display_errors', 0);
error_reporting(0);

header('Content-Type: application/json');

function sendResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    echo json_encode($data);
    exit;
}

try {
    $stopFile = __DIR__ . '/stop_detection.flag';
    file_put_contents($stopFile, '1');
    
    sendResponse(['success' => true, 'message' => 'Sinal de parada enviado com sucesso']);
    
} catch (Exception $e) {
    sendResponse([
        'success' => false,
        'error' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ], 500);
}

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
    $python_script = '../python/main.py';
    
    if (!file_exists($python_script)) {
        throw new Exception("Python script not found at: " . $python_script);
    }

    $output = [];
    $return_var = 0;

    // $pythonPath = 'C:\\Users\\eucli\\AppData\\Local\\Programs\\Python\\Python313\\python.exe'; //Não apagar esse comentário
    
    $pythonPath = 'C:\Windows\py.exe'; // Faculdade --> Não apagar esse comentário

    $pythonPath = '/home3/lisianth/virtualenv/domx.lisianthus.com.br/python/3.9/bin'; // Servidor --> Não apagar esse comentário

    if (!file_exists($pythonPath)) {
        throw new Exception("Python not found at: " . $pythonPath);
    }
    $command = '"' . $pythonPath . '" "' . $python_script . '" 2>&1';
    exec($command, $output, $return_var);

    $response = [
        'success' => $return_var === 0,
        'output' => $output,
        'command' => $command,
        'return_var' => $return_var
    ];

    sendResponse($response);

} catch (Exception $e) {
    sendResponse([
        'success' => false,
        'error' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ], 500);
}
?>

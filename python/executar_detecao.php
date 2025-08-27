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
    // Get the absolute path to the Python script
    $python_script = '../python/main.py';
    
    // Check if the Python script exists
    if (!file_exists($python_script)) {
        throw new Exception("Python script not found at: " . $python_script);
    }

    // Initialize output and return variable
    $output = [];
    $return_var = 0;

    // $pythonPath = 'C:\\Users\\eucli\\AppData\\Local\\Programs\\Python\\Python313\\python.exe'; /
    // Não apagar esse comentário
    
    $pythonPath = 'C:\Windows\py.exe'; // Faculdade --> Não apagar esse comentário

    if (!file_exists($pythonPath)) {
        throw new Exception("Python not found at: " . $pythonPath);
    }
    $command = '"' . $pythonPath . '" "' . $python_script . '" 2>&1';
    exec($command, $output, $return_var);

    // Prepare the response
    $response = [
        'success' => $return_var === 0,
        'output' => $output,
        'command' => $command,
        'return_var' => $return_var
    ];

    // Send the response
    sendResponse($response);

} catch (Exception $e) {
    // Handle any errors
    sendResponse([
        'success' => false,
        'error' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ], 500);
}
?>

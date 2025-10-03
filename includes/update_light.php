<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Enable error logging
error_log('update_light.php was called');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

try {
    // Get the request body
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Invalid JSON input: ' . json_last_error_msg());
    }

    if (!isset($input['light_id']) || !isset($input['status'])) {
        throw new Exception('Missing required parameters. Received: ' . print_r($input, true));
    }

    $lightId = $input['light_id'];
    $status = strtoupper(trim($input['status']));

    if (!in_array($status, ['ON', 'OFF'])) {
        throw new Exception('Invalid status. Must be ON or OFF. Received: ' . $status);
    }

    $statusFile = __DIR__ . '/../light_status.txt';
    
    // Log the file path for debugging
    error_log("Attempting to write to file: " . realpath($statusFile));
    
    // Check if file is writable
    if (!is_writable($statusFile)) {
        // Try to create the file if it doesn't exist
        if (!file_exists($statusFile)) {
            if (file_put_contents($statusFile, 'OFF') === false) {
                throw new Exception('Failed to create light status file');
            }
            // Set permissions
            chmod($statusFile, 0666);
        } else {
            throw new Exception('Light status file is not writable. Current permissions: ' . substr(sprintf('%o', fileperms($statusFile)), -4));
        }
    }

    // Write the status to the file
    if (file_put_contents($statusFile, $status) === false) {
        throw new Exception('Failed to write to light status file');
    }

    // Verify the write was successful
    if (file_get_contents($statusFile) !== $status) {
        throw new Exception('Failed to verify light status update');
    }

    // Return success response
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'light_id' => $lightId,
        'status' => $status,
        'message' => "Light $lightId turned $status",
        'file_path' => realpath($statusFile),
        'file_permissions' => substr(sprintf('%o', fileperms($statusFile)), -4)
    ]);
    
} catch (Exception $e) {
    // Log the error
    error_log('Error in update_light.php: ' . $e->getMessage());
    
    // Return error response
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'trace' => $e->getTraceAsString()
    ]);
}
?>

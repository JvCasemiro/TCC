<?php
// update_light.php

// Enable error reporting for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Set headers for JSON response
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Get the raw POST data
$input = json_decode(file_get_contents('php://input'), true);

// Check if the request is valid
if (!isset($input['light_id']) || !isset($input['status'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing required parameters']);
    exit();
}

$lightId = $input['light_id'];
$status = $input['status'];

// Validate status
if (!in_array($status, ['ON', 'OFF'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid status. Must be ON or OFF']);
    exit();
}

// In a real application, you would update the database here
// For this example, we'll just update a file
$statusFile = __DIR__ . '/../light_status.txt';
file_put_contents($statusFile, $status);

// Return success response
http_response_code(200);
echo json_encode([
    'success' => true,
    'light_id' => $lightId,
    'status' => $status,
    'message' => "Light $lightId turned $status"
]);
?>

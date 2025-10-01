<?php
header('Content-Type: application/json');

$lightStatusFile = __DIR__ . '/light_status.txt';
$status = 'OFF'; // Default status

// Read the current status from the file
if (file_exists($lightStatusFile)) {
    $status = trim(file_get_contents($lightStatusFile));
    $status = strtoupper($status) === 'ON' ? 'ON' : 'OFF';
}

// Return the status as JSON
echo json_encode([
    'status' => $status,
    'timestamp' => date('Y-m-d H:i:s')
]);
?>

<?php
header('Content-Type: application/json');

$lightStatusFile = __DIR__ . '/light_status.txt';
$status = 'OFF';

if (file_exists($lightStatusFile)) {
    $status = trim(file_get_contents($lightStatusFile));
    $status = strtoupper($status) === 'ON' ? 'ON' : 'OFF';
}
echo json_encode([
    'status' => $status,
    'timestamp' => date('Y-m-d H:i:s')
]);
?>

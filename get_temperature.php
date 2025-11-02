<?php
header('Content-Type: application/json');

$temperature_file = 'temperature_data.json';
$default_data = [
    'temperature' => 0,
    'humidity' => 0,
    'last_update' => 'N/A',
    'status' => 'waiting'
];

if (file_exists($temperature_file)) {
    $json_data = file_get_contents($temperature_file);
    $data = json_decode($json_data, true);
    
    if ($data) {
        echo json_encode($data);
    } else {
        echo json_encode($default_data);
    }
} else {
    echo json_encode($default_data);
}
?>

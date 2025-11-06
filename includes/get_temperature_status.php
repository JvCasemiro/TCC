<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/control_temperature.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

try {
    $temperatureController = new TemperatureController($conn);
    
    $statusInfo = $temperatureController->getStatus();
    
    $stmt = $conn->query("SELECT ID_Temperatura, Nome, Comodo, Status FROM Temperaturas ORDER BY ID_Temperatura");
    $temperaturas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $response = [
        'success' => true,
        'status' => $statusInfo['status'],
        'porcentagem' => $statusInfo['porcentagem'],
        'temperaturas' => $temperaturas
    ];
    
} catch (Exception $e) {
    http_response_code(500);
    $response = [
        'success' => false,
        'message' => 'Erro ao obter status das temperaturas: ' . $e->getMessage()
    ];
    error_log('Erro em get_temperature_status.php: ' . $e->getMessage());
}

echo json_encode($response, JSON_UNESCAPED_UNICODE);
?>

<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/control_lights.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

try {
    $lightController = new LightController($conn);
    
    $statusInfo = $lightController->getStatus();
    
    $stmt = $conn->query("SELECT ID_Lampada, Nome, Comodo, Status FROM Lampadas ORDER BY ID_Lampada");
    $lampadas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $response = [
        'success' => true,
        'status' => $statusInfo['status'],
        'porcentagem' => $statusInfo['porcentagem'],
        'lampadas' => $lampadas
    ];
    
} catch (Exception $e) {
    http_response_code(500);
    $response = [
        'success' => false,
        'message' => 'Erro ao obter status das lâmpadas: ' . $e->getMessage()
    ];
    error_log('Erro em get_lights_status.php: ' . $e->getMessage());
}

echo json_encode($response, JSON_UNESCAPED_UNICODE);
?>

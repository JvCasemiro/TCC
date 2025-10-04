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
    // Cria uma instância do controlador de lâmpadas
    $lightController = new LightController($conn);
    
    // Obtém o status de todas as lâmpadas
    $statusInfo = $lightController->getStatus();
    
    // Obtém os detalhes das lâmpadas do banco de dados
    $stmt = $conn->query("SELECT ID_Lampada, Nome, Comodo, Status, Brilho FROM Lampadas ORDER BY ID_Lampada");
    $lampadas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Prepara a resposta
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

// Retorna a resposta em formato JSON
echo json_encode($response, JSON_UNESCAPED_UNICODE);
?>

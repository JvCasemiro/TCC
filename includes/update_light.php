<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Inclui o arquivo de configuração do banco de dados
require_once __DIR__ . '/../config/database.php';
// Inclui a classe de controle de lâmpadas
require_once __DIR__ . '/control_lights.php';

// Habilita o log de erros
error_log('update_light.php was called - IP: ' . $_SERVER['REMOTE_ADDR']);

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Inicializa a resposta
$response = [
    'success' => false,
    'message' => '',
    'status' => '',
    'porcentagem' => 0
];

try {
    // Verifica se o usuário está autenticado (exemplo básico)
    session_start();
    if (!isset($_SESSION['user_id'])) {
        throw new Exception('Acesso não autorizado. Faça login para continuar.');
    }

    // Obtém o corpo da requisição
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Entrada JSON inválida: ' . json_last_error_msg());
    }

    if (!isset($input['light_id'])) {
        throw new Exception('ID da lâmpada não informado');
    }

    $lightId = (int)$input['light_id'];
    $status = isset($input['status']) ? strtoupper(trim($input['status'])) : null;

    if ($status !== null && !in_array($status, ['ON', 'OFF'])) {
        throw new Exception('Status inválido. Deve ser ON ou OFF. Recebido: ' . $status);
    }

    // Verifica se a lâmpada existe
    $stmt = $conn->prepare("SELECT ID_Lampada FROM Lampadas WHERE ID_Lampada = ?");
    $stmt->execute([$lightId]);
    if ($stmt->rowCount() === 0) {
        throw new Exception('Lâmpada não encontrada');
    }

    // Cria uma instância do controlador de lâmpadas
    $lightController = new LightController($conn);
    
    // Atualiza o status da lâmpada
    if ($status !== null) {
        $lightController->updateLightStatus($lightId, $status);
    }
    
    // Obtém o status atualizado diretamente do banco de dados para garantir consistência
    $stmt = $conn->prepare("SELECT Status FROM Lampadas WHERE ID_Lampada = ?");
    $stmt->execute([$lightId]);
    $lightData = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Prepara a resposta
    $response = [
        'success' => true,
        'message' => "Lâmpada {$lightId} " . strtolower($lightData['Status']),
        'status' => $lightData['Status'],
        'porcentagem' => $lightData['Status'] === 'ON' ? 100 : 0
    ];
    
    // Log da operação bem-sucedida
    error_log("Lâmpada {$lightId} atualizada - Status: {$lightData['Status']}");

} catch (Exception $e) {
    http_response_code(400);
    $response = [
        'success' => false,
        'message' => 'Erro: ' . $e->getMessage(),
        'status' => '',
        'porcentagem' => 0
    ];
    error_log('Erro em update_light.php: ' . $e->getMessage() . ' - IP: ' . $_SERVER['REMOTE_ADDR']);
}

// Retorna a resposta em formato JSON
echo json_encode($response, JSON_UNESCAPED_UNICODE);
?>
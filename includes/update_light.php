<?php
// Garante que nenhuma saída seja enviada antes do cabeçalho
if (ob_get_level() == 0) {
    ob_start();
}

// Configurações de cabeçalho
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Habilita exibição de erros para depuração (mas não exibe na saída)
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/php_errors.log');

// Inclui o arquivo de configuração do banco de dados
require_once __DIR__ . '/../config/database.php';
// Inclui a classe de controle de lâmpadas
require_once __DIR__ . '/control_lights.php';

// Função para enviar resposta JSON e encerrar o script
function sendJsonResponse($success, $message, $data = [], $httpCode = 200) {
    http_response_code($httpCode);
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data' => $data
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

// Log da requisição
$requestData = file_get_contents('php://input');
error_log('update_light.php was called - IP: ' . $_SERVER['REMOTE_ADDR'] . ' - Data: ' . $requestData);

// Verifica se é uma requisição OPTIONS (pré-voo CORS)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    sendJsonResponse(true, 'OK');
}

// Verifica se é uma requisição POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendJsonResponse(false, 'Método não permitido', [], 405);
}

try {
    // Inicia a sessão
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // Verifica se o usuário está autenticado
    if (!isset($_SESSION['user_id'])) {
        throw new Exception('Acesso não autorizado. Faça login para continuar.');
    }

    // Obtém o corpo da requisição
    $requestData = file_get_contents('php://input');
    if ($requestData === false) {
        throw new Exception('Não foi possível ler o corpo da requisição');
    }
    
    $input = json_decode($requestData, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Entrada JSON inválida: ' . json_last_error_msg());
    }

    if (!isset($input['light_id'])) {
        throw new Exception('ID da lâmpada não informado');
    }

    $lightId = (int)$input['light_id'];
    $status = isset($input['status']) ? strtoupper($input['status']) : null;

    if ($status === null || !in_array($status, ['ON', 'OFF'])) {
        throw new Exception('Status inválido. Deve ser ON ou OFF. Recebido: ' . $status);
    }

    // Cria uma instância do controlador de lâmpadas
    $lightController = new LightController($conn);
    
    // Atualiza o status da lâmpada no banco de dados
    $stmt = $conn->prepare("UPDATE Lampadas SET Status = ? WHERE ID_Lampada = ? AND ID_Usuario = ?");
    $stmt->execute([$status, $lightId, $_SESSION['user_id']]);
    
    if ($stmt->rowCount() === 0) {
        throw new Exception('Lâmpada não encontrada ou você não tem permissão para alterá-la');
    }
    
    // Atualiza o status no arquivo de controle
    $lightController->updateLightStatus($lightId, $status);
    
    // Chama o script para controlar o Arduino
    $ch = curl_init();
    $url = 'http://' . $_SERVER['HTTP_HOST'] . '/TCC/includes/control_arduino.php';
    $postData = json_encode([
        'light_id' => $lightId,
        'status' => $status
    ]);
    
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_POST => 1,
        CURLOPT_POSTFIELDS => $postData,
        CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 5,
        CURLOPT_CONNECTTIMEOUT => 5
    ]);
    
    $result = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);
    
    if ($httpCode !== 200 || $curlError) {
        error_log("Erro ao controlar o Arduino: HTTP $httpCode - $curlError - $result");
        // Não interrompe o fluxo, apenas registra o erro
    }
    
    // Retorna a resposta de sucesso
    sendJsonResponse(true, "Lâmpada {$lightId} " . strtolower($status), [
        'status' => $status,
        'porcentagem' => $status === 'ON' ? 100 : 0
    ]);
    
} catch (Exception $e) {
    error_log('Erro em update_light.php: ' . $e->getMessage() . ' - IP: ' . $_SERVER['REMOTE_ADDR']);
    sendJsonResponse(false, $e->getMessage(), [], 400);
}
exit;
<?php
if (ob_get_level() == 0) {
    ob_start();
}
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/control_lights.php';

function sendJsonResponse($success, $message, $data = [], $httpCode = 200) {
    http_response_code($httpCode);
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data' => $data
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

$requestData = file_get_contents('php://input');
error_log('update_light.php was called - IP: ' . $_SERVER['REMOTE_ADDR'] . ' - Data: ' . $requestData);

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    sendJsonResponse(true, 'OK');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendJsonResponse(false, 'Método não permitido', [], 405);
}

try {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (!isset($_SESSION['user_id'])) {
        throw new Exception('Acesso não autorizado. Faça login para continuar.');
    }

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
    $status = isset($input['status']) ? strtoupper(trim($input['status'])) : '';
    
    if (!in_array($status, ['ON', 'OFF'])) {
        error_log("Status inválido recebido: " . json_encode($input));
        throw new Exception('Status inválido. Deve ser ON ou OFF. Recebido: ' . $status);
    }

    $lightController = new LightController($conn);
    
    $stmt = $conn->prepare("UPDATE Lampadas SET Status = ? WHERE ID_Lampada = ?");
    $stmt->execute([$status, $lightId]);
    
    if ($stmt->rowCount() === 0) {
        throw new Exception('Lâmpada não encontrada');
    }
    
    $lightController->updateLightStatus($lightId, $status);
    
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
    }
    
    sendJsonResponse(true, "Lâmpada {$lightId} " . strtolower($status), [
        'status' => $status,
        'porcentagem' => $status === 'ON' ? 100 : 0
    ]);
    
} catch (Exception $e) {
    error_log('Erro em update_light.php: ' . $e->getMessage() . ' - IP: ' . $_SERVER['REMOTE_ADDR']);
    sendJsonResponse(false, $e->getMessage(), [], 400);
}
exit;
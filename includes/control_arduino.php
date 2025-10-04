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
$pythonScript = __DIR__ . '/../python/arduino_control.py';

function sendJsonResponse($success, $message, $httpCode = 200) {
    http_response_code($httpCode);
    echo json_encode([
        'success' => $success,
        'message' => $message
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

$requestData = file_get_contents('php://input');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    sendJsonResponse(true, 'OK');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendJsonResponse(false, 'Método não permitido', 405);
}
$input = json_decode($requestData, true);
if (json_last_error() !== JSON_ERROR_NONE) {
    sendJsonResponse(false, 'JSON inválido: ' . json_last_error_msg(), 400);
}

$lightId = isset($input['light_id']) ? (int)$input['light_id'] : null;
$status = isset($input['status']) ? $input['status'] : null;

if ($lightId === null || $status === null) {
    sendJsonResponse(false, 'Parâmetros light_id e status são obrigatórios', 400);
}

require_once __DIR__ . '/../config/database.php';

try {
    $conn = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8mb4", $db_user, $db_password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $stmt = $conn->prepare("SELECT Nome, Status FROM Lampadas WHERE ID_Lampada = ?");
    $stmt->execute([$lightId]);
    $light = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$light) {
        throw new Exception('Lâmpada não encontrada no banco de dados');
    }
    
    
    if (!file_exists($pythonScript)) {
        throw new Exception("Arquivo do script Python não encontrado: " . $pythonScript);
    }
    
    $command = sprintf('python "%s" --light-name "%s" --status "%s" 2>&1', 
        $pythonScript,
        addslashes($light['Nome']),
        $status
    );
    
    $output = [];
    $return_var = 0;
    exec($command, $output, $return_var);
    
    $outputStr = implode("\n", $output);
    
    if ($return_var !== 0) {
        throw new Exception("Erro ao executar o script Python (código $return_var): $outputStr");
    }
    
    $stmt = $conn->prepare("UPDATE Lampadas SET Status = ? WHERE ID_Lampada = ?");
    $stmt->execute([$status, $lightId]);
    
    sendJsonResponse(true, 'Comando enviado com sucesso');
    
} catch (Exception $e) {
    $errorMessage = 'Erro: ' . $e->getMessage();
    error_log($errorMessage); 
    sendJsonResponse(false, $errorMessage, 500);
}

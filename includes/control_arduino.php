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
$queueFile = __DIR__ . '/../arduino_queue.json';
$daemonPidFile = __DIR__ . '/../arduino_daemon.pid';

// Importar função de garantir daemon rodando
require_once __DIR__ . '/ensure_daemon_running.php';

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
    
    // Garantir que o daemon está rodando (inicia automaticamente se necessário)
    if (!ensureDaemonRunning()) {
        throw new Exception('Não foi possível iniciar o daemon do Arduino automaticamente. Execute start_arduino_daemon.bat manualmente.');
    }
    
    // Adicionar comando à fila
    $queueData = [];
    if (file_exists($queueFile)) {
        $content = file_get_contents($queueFile);
        $queueData = json_decode($content, true) ?: [];
    }
    
    // Adicionar novo comando
    $queueData[] = [
        'light_id' => $lightId,
        'status' => $status,
        'timestamp' => time()
    ];
    
    // Salvar fila atualizada
    if (file_put_contents($queueFile, json_encode($queueData, JSON_UNESCAPED_UNICODE)) === false) {
        throw new Exception('Erro ao adicionar comando à fila');
    }
    
    // Aguardar processamento (máximo 2 segundos)
    $maxWait = 20; // 20 * 0.1s = 2 segundos
    $waited = 0;
    while ($waited < $maxWait) {
        usleep(100000); // 0.1 segundo
        $waited++;
        
        if (file_exists($queueFile)) {
            $currentQueue = json_decode(file_get_contents($queueFile), true) ?: [];
            if (empty($currentQueue)) {
                break; // Fila foi processada
            }
        }
    }
    
    $stmt = $conn->prepare("UPDATE Lampadas SET Status = ? WHERE ID_Lampada = ?");
    $stmt->execute([$status, $lightId]);
    
    sendJsonResponse(true, 'Comando enviado com sucesso');
    
} catch (Exception $e) {
    $errorMessage = 'Erro: ' . $e->getMessage();
    error_log($errorMessage); 
    sendJsonResponse(false, $errorMessage, 500);
}

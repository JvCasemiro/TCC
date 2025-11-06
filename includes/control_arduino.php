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
$temperatureId = isset($input['temperature_id']) ? (int)$input['temperature_id'] : null;
$status = isset($input['status']) ? $input['status'] : null;

if (($lightId === null && $temperatureId === null) || $status === null) {
    sendJsonResponse(false, 'Parâmetros (light_id ou temperature_id) e status são obrigatórios', 400);
}

// Determina se é comando de luz ou temperatura
$isLight = $lightId !== null;
$deviceId = $isLight ? $lightId : $temperatureId;
$deviceType = $isLight ? 'light' : 'temperature';

require_once __DIR__ . '/../config/database.php';

try {
    $conn = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8mb4", $db_user, $db_password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    if ($isLight) {
        $stmt = $conn->prepare("SELECT Nome, Status FROM Lampadas WHERE ID_Lampada = ?");
        $stmt->execute([$lightId]);
        $device = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$device) {
            throw new Exception('Lâmpada não encontrada no banco de dados');
        }
    } else {
        $stmt = $conn->prepare("SELECT Nome, Status FROM Temperaturas WHERE ID_Temperatura = ?");
        $stmt->execute([$temperatureId]);
        $device = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$device) {
            throw new Exception('Termostato não encontrado no banco de dados');
        }

        // Determina o índice (1..N) do termostato com base na ordenação por ID
        $idxStmt = $conn->query("SELECT ID_Temperatura FROM Temperaturas ORDER BY ID_Temperatura");
        $ids = $idxStmt->fetchAll(PDO::FETCH_COLUMN);
        $position = array_search($temperatureId, array_map('intval', $ids), true);
        if ($position === false) {
            throw new Exception('Falha ao calcular índice do termostato');
        }
        // Índice 1-based para o Arduino
        $tempIndex = $position + 1;
    }
    
    // Tenta iniciar o daemon; se falhar, continua e apenas registra log.
    if (!ensureDaemonRunning()) {
        error_log('Aviso: Não foi possível iniciar o daemon do Arduino automaticamente. Continuando para enfileirar o comando.');
    }
    
    $queueData = [];
    if (file_exists($queueFile)) {
        $content = file_get_contents($queueFile);
        $queueData = json_decode($content, true) ?: [];
    }
    
    if ($isLight) {
        $queueData[] = [
            'light_id' => $lightId,
            'status' => $status,
            'timestamp' => time()
        ];
    } else {
        $queueData[] = [
            'temperature_id' => $temperatureId,
            'temp_index' => isset($tempIndex) ? $tempIndex : $temperatureId,
            'status' => $status,
            'timestamp' => time(),
            'type' => 'temperature'
        ];
    }
    
    if (file_put_contents($queueFile, json_encode($queueData, JSON_UNESCAPED_UNICODE)) === false) {
        throw new Exception('Erro ao adicionar comando à fila');
    }
    
    $maxWait = 20;
    $waited = 0;
    while ($waited < $maxWait) {
        usleep(100000);
        $waited++;
        
        if (file_exists($queueFile)) {
            $currentQueue = json_decode(file_get_contents($queueFile), true) ?: [];
            if (empty($currentQueue)) {
                break;
            }
        }
    }
    
    if ($isLight) {
        $stmt = $conn->prepare("UPDATE Lampadas SET Status = ? WHERE ID_Lampada = ?");
        $stmt->execute([$status, $lightId]);
    } else {
        $stmt = $conn->prepare("UPDATE Temperaturas SET Status = ? WHERE ID_Temperatura = ?");
        $stmt->execute([$status, $temperatureId]);
    }
    
    sendJsonResponse(true, 'Comando enviado com sucesso');
    
} catch (Exception $e) {
    $errorMessage = 'Erro: ' . $e->getMessage();
    error_log($errorMessage); 
    sendJsonResponse(false, $errorMessage, 500);
}

<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
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

// Handle preflight request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    sendJsonResponse(true, 'OK');
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    sendJsonResponse(false, 'Método não permitido', 405);
}

$action = isset($_GET['action']) ? strtolower($_GET['action']) : '';

if (!in_array($action, ['on', 'off'])) {
    sendJsonResponse(false, 'Ação inválida. Use "on" ou "off"', 400);
}

// Tenta iniciar o daemon; se falhar, continua e apenas registra log.
if (!ensureDaemonRunning()) {
    error_log('Aviso: Não foi possível iniciar o daemon do Arduino automaticamente. Continuando para enfileirar o comando.');
}

// Carrega a fila existente
$queueData = [];
if (file_exists($queueFile)) {
    $content = file_get_contents($queueFile);
    $queueData = json_decode($content, true) ?: [];
}

// Adiciona o comando da irrigação da horta à fila
$queueData[] = [
    'garden' => true,
    'status' => $action === 'on' ? 'ON' : 'OFF',
    'timestamp' => time()
];

// Salva a fila de volta no arquivo
if (file_put_contents($queueFile, json_encode($queueData, JSON_PRETTY_PRINT)) === false) {
    sendJsonResponse(false, 'Erro ao salvar o comando na fila', 500);
}

// Responde com sucesso
sendJsonResponse(true, $action === 'on' ? 'Irrigação da horta sendo ligada' : 'Irrigação da horta sendo desligada');
?>

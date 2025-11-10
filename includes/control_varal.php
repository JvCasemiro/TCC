<?php
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$action = $input['action'] ?? '';

if (!in_array($action, ['OPEN', 'CLOSE'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Ação inválida']);
    exit;
}

$queueFile = __DIR__ . '/arduino_queue.json';

try {
    $queue = [];
    if (file_exists($queueFile)) {
        $content = file_get_contents($queueFile);
        $queue = json_decode($content, true) ?? [];
    }
    
    $queue[] = [
        'type' => 'varal',
        'action' => $action,
        'timestamp' => date('Y-m-d H:i:s')
    ];
    
    file_put_contents($queueFile, json_encode($queue, JSON_PRETTY_PRINT));
    
    echo json_encode([
        'success' => true,
        'message' => 'Comando enviado com sucesso',
        'action' => $action
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao processar comando: ' . $e->getMessage()
    ]);
}
?>

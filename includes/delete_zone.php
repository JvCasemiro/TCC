<?php
session_start();
require_once __DIR__ . '/../config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Usuário não autenticado']);
    exit;
}

// Check if zone ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    http_response_code(400);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'ID da zona não fornecido']);
    exit;
}

$zoneId = $_GET['id'];
$userId = $_SESSION['user_id'];

try {
    // Delete the temperature record from Temperaturas table
    $stmt = $conn->prepare("DELETE FROM Temperaturas WHERE ID_Temperatura = ? AND ID_Usuario = ?");
    $stmt->execute([$zoneId, $userId]);
    
    if ($stmt->rowCount() > 0) {
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'message' => 'Zona de temperatura removida com sucesso']);
    } else {
        http_response_code(404);
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Zona de temperatura não encontrada ou você não tem permissão para removê-la']);
    }
} catch (PDOException $e) {
    error_log('Erro ao remover zona de temperatura: ' . $e->getMessage());
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false, 
        'message' => 'Erro ao remover a zona de temperatura',
        'error' => $e->getMessage()
    ]);
}
?>

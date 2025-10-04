<?php
session_start();
require_once __DIR__ . '/../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Usuário não autenticado']);
    exit;
}

if (!isset($_POST['light_id']) || empty($_POST['light_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'ID da lâmpada não fornecido']);
    exit;
}

$lightId = $_POST['light_id'];
$userId = $_SESSION['user_id'];

try {
    $stmt = $conn->prepare("DELETE FROM Lampadas WHERE ID_Lampada = ? AND ID_Usuario = ?");
    $stmt->execute([$lightId, $userId]);
    
    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true, 'message' => 'Lâmpada removida com sucesso']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Lâmpada não encontrada ou você não tem permissão para removê-la']);
    }
} catch (PDOException $e) {
    error_log('Erro ao remover lâmpada: ' . $e->getMessage());
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Erro ao remover a lâmpada']);
}
?>

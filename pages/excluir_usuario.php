<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['Tipo_Usuario'] !== 'admin' && $_SESSION['Tipo_Usuario'] !== 'master')) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Acesso negado. Permissão insuficiente.']);
    exit;
}

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    exit;
}

$userId = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($userId <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'ID de usuário inválido']);
    exit;
}

try {
    if ($userId == $_SESSION['user_id']) {
        throw new Exception('Você não pode excluir seu próprio usuário.');
    }
    
    $stmt = $conn->prepare("SELECT ID_Usuario FROM Usuarios WHERE ID_Usuario = ? AND Ativo = TRUE");
    $stmt->execute([$userId]);
    
    if ($stmt->rowCount() === 0) {
        throw new Exception('Usuário não encontrado ou já foi excluído.');
    }
    
    $stmt = $conn->prepare("UPDATE Usuarios SET Ativo = FALSE, Data_Atualizacao = NOW() WHERE ID_Usuario = ?");
    $stmt->execute([$userId]);
    
    if ($stmt->rowCount() > 0) {
        echo json_encode([
            'success' => true,
            'message' => 'Usuário excluído com sucesso!'
        ]);
    } else {
        throw new Exception('Nenhum registro foi afetado. O usuário pode já ter sido excluído.');
    }
    
} catch (PDOException $e) {
    error_log("Erro ao excluir usuário: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao processar a exclusão do usuário.',
        'error' => $e->getMessage()
    ]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

<?php
session_start();
require_once '../config/database.php';

header('Content-Type: application/json');

// Verifica se o usuário está logado e é admin
if (!isset($_SESSION['user_id']) || $_SESSION['tipo_usuario'] !== 'admin') {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Acesso negado']);
    exit;
}

// Verifica se o método da requisição é POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    exit;
}

// Obtém e valida o ID da casa
$casaId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$casaId) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'ID da casa inválido']);
    exit;
}

try {
    // Inicia uma transação para garantir a integridade dos dados
    $conn->beginTransaction();
    
    // 1. Verifica se a casa existe
    $stmt = $conn->prepare("SELECT ID_Casa FROM Casas WHERE ID_Casa = ?");
    $stmt->execute([$casaId]);
    
    if ($stmt->rowCount() === 0) {
        $conn->rollBack();
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Casa não encontrada']);
        exit;
    }
    
    // 2. Verifica se existem usuários associados a esta casa
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM Usuarios WHERE ID_Casa = ?");
    $stmt->execute([$casaId]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($result['total'] > 0) {
        $conn->rollBack();
        http_response_code(400);
        echo json_encode([
            'success' => false, 
            'message' => 'Não é possível excluir esta casa pois existem usuários vinculados a ela.'
        ]);
        exit;
    }
    
    // 3. Exclui a casa
    $stmt = $conn->prepare("DELETE FROM Casas WHERE ID_Casa = ?");
    $stmt->execute([$casaId]);
    
    // Confirma a transação
    $conn->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'Casa excluída com sucesso'
    ]);
    
} catch (PDOException $e) {
    // Desfaz as alterações em caso de erro
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    
    error_log("Erro ao excluir casa: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao excluir casa: ' . $e->getMessage()
    ]);
}
?>

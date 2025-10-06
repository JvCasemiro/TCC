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

// Obtém e valida os dados do formulário
$casaId = filter_input(INPUT_POST, 'id_casa', FILTER_VALIDATE_INT);
$nome = filter_input(INPUT_POST, 'nome', FILTER_SANITIZE_STRING);
$endereco = filter_input(INPUT_POST, 'endereco', FILTER_SANITIZE_STRING);
$ativo = isset($_POST['ativo']) ? 1 : 0;

// Validação dos dados
if (!$casaId) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'ID da casa inválido']);
    exit;
}

if (empty($nome) || empty($endereco)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Nome e endereço são obrigatórios']);
    exit;
}

try {
    // Verifica se a casa existe
    $stmt = $conn->prepare("SELECT ID_Casa FROM Casas WHERE ID_Casa = ?");
    $stmt->execute([$casaId]);
    
    if ($stmt->rowCount() === 0) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Casa não encontrada']);
        exit;
    }
    
    // Atualiza os dados da casa
    $stmt = $conn->prepare("
        UPDATE Casas 
        SET Nome = ?, 
            Endereco = ?, 
            Ativo = ?,
            Data_Atualizacao = CURRENT_TIMESTAMP
        WHERE ID_Casa = ?
    ");
    
    $stmt->execute([$nome, $endereco, $ativo, $casaId]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Casa atualizada com sucesso'
    ]);
    
} catch (PDOException $e) {
    error_log("Erro ao atualizar casa: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao atualizar casa: ' . $e->getMessage()
    ]);
}
?>

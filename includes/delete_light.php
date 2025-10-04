<?php
// Inicia a sessão e inclui o arquivo de conexão com o banco de dados
session_start();
require_once __DIR__ . '/../config/database.php';

// Verifica se o usuário está logado
if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Usuário não autenticado']);
    exit;
}

// Verifica se o ID da lâmpada foi fornecido
if (!isset($_POST['light_id']) || empty($_POST['light_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'ID da lâmpada não fornecido']);
    exit;
}

$lightId = $_POST['light_id'];
$userId = $_SESSION['user_id'];

try {
    // Prepara a consulta para deletar a lâmpada
    $stmt = $conn->prepare("DELETE FROM Lampadas WHERE ID_Lampada = ? AND ID_Usuario = ?");
    $stmt->execute([$lightId, $userId]);
    
    // Verifica se alguma linha foi afetada
    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true, 'message' => 'Lâmpada removida com sucesso']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Lâmpada não encontrada ou você não tem permissão para removê-la']);
    }
} catch (PDOException $e) {
    // Em caso de erro, retorna uma mensagem de erro
    error_log('Erro ao remover lâmpada: ' . $e->getMessage());
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Erro ao remover a lâmpada']);
}
?>

<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['Tipo_Usuario'] !== 'admin' && $_SESSION['Tipo_Usuario'] !== 'master')) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Acesso negado. Permissão insuficiente.']);
    exit;
}

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
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
    $stmt = $conn->prepare("SELECT ID_Usuario as id, Nome_Usuario as username, Email as email, Tipo_Usuario as tipo_usuario FROM Usuarios WHERE ID_Usuario = ? AND Ativo = TRUE");
    $stmt->execute([$userId]);
    
    if ($stmt->rowCount() === 0) {
        throw new Exception('Usuário não encontrado ou inativo');
    }
    
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'user' => $user
    ]);
    
} catch (PDOException $e) {
    error_log("Erro ao buscar usuário: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao buscar dados do usuário.'
    ]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

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

// Verifica se o ID da casa foi fornecido
if (!isset($_GET['id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'ID da casa não fornecido']);
    exit;
}

$casaId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if ($casaId === false) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'ID da casa inválido']);
    exit;
}

try {
    // Busca os dados da casa
    $stmt = $conn->prepare("SELECT * FROM Casas WHERE ID_Casa = ?");
    $stmt->execute([$casaId]);
    $casa = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($casa) {
        echo json_encode([
            'success' => true,
            'casa' => $casa
        ]);
    } else {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'Casa não encontrada'
        ]);
    }
} catch (PDOException $e) {
    error_log("Erro ao buscar casa: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao buscar dados da casa'
    ]);
}
?>

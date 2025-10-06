<?php
session_start();
require_once '../config/database.php';

// Verifica se o usuário está logado e é admin
if (!isset($_SESSION['user_id']) || $_SESSION['tipo_usuario'] !== 'admin') {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Acesso negado']);
    exit;
}

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Cadastrar nova casa
    $nome = filter_input(INPUT_POST, 'nome', FILTER_SANITIZE_STRING);
    $endereco = filter_input(INPUT_POST, 'endereco', FILTER_SANITIZE_STRING);
    $ativo = isset($_POST['ativo']) ? 1 : 1; // Por padrão, a casa é ativada ao ser criada

    if (empty($nome) || empty($endereco)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Nome e endereço são obrigatórios']);
        exit;
    }

    try {
        $stmt = $conn->prepare("INSERT INTO Casas (Nome, Endereco, Ativo) VALUES (?, ?, ?)");
        $stmt->execute([$nome, $endereco, $ativo]);
        
        $casa_id = $conn->lastInsertId();
        
        // Busca os dados completos da casa recém-criada
        $stmt = $conn->prepare("SELECT * FROM Casas WHERE ID_Casa = ?");
        $stmt->execute([$casa_id]);
        $casa = $stmt->fetch(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true, 
            'message' => 'Casa cadastrada com sucesso',
            'casa' => $casa
        ]);
    } catch (PDOException $e) {
        error_log("Erro ao cadastrar casa: " . $e->getMessage());
        http_response_code(500);
        echo json_encode([
            'success' => false, 
            'message' => 'Erro ao cadastrar casa: ' . $e->getMessage()
        ]);
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Listar casas
    try {
        $stmt = $conn->query("SELECT * FROM Casas WHERE Ativo = 1 ORDER BY Nome");
        $casas = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true, 
            'casas' => $casas
        ]);
    } catch (PDOException $e) {
        error_log("Erro ao listar casas: " . $e->getMessage());
        http_response_code(500);
        echo json_encode([
            'success' => false, 
            'message' => 'Erro ao listar casas: ' . $e->getMessage()
        ]);
    }
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
}
?>

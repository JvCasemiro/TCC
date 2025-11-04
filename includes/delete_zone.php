<?php
session_start();
require_once __DIR__ . '/../config/database.php';

// Configura o cabeçalho para retornar JSON
header('Content-Type: application/json');

// Verifica se o método da requisição é DELETE
if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    exit;
}

// Verifica se o usuário está logado
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Usuário não autenticado']);
    exit;
}

// Tenta obter o ID da zona do corpo da requisição
$zoneId = null;

// Tenta obter do corpo da requisição (JSON)
$input = json_decode(file_get_contents('php://input'), true);
if (json_last_error() === JSON_ERROR_NONE && isset($input['id'])) {
    $zoneId = $input['id'];
}

// Se não encontrou no corpo, tenta obter do parâmetro de consulta
if (!$zoneId && isset($_GET['id'])) {
    $zoneId = $_GET['id'];
}

// Verifica se o ID da zona foi fornecido
if (!$zoneId) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'ID da zona não fornecido']);
    exit;
}

// Garante que o ID seja um número inteiro
$zoneId = (int)$zoneId;

$userId = $_SESSION['user_id'];

// Log para depuração
error_log("Tentando excluir zona - ID: $zoneId, Usuário: $userId");

try {
    // Verifica se a tabela existe
    $tableCheck = $conn->query("SHOW TABLES LIKE 'Temperaturas'");
    if ($tableCheck->rowCount() === 0) {
        error_log("ERRO: A tabela 'Temperaturas' não existe no banco de dados");
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Erro no servidor: Tabela não encontrada']);
        exit;
    }

    // Verifica se as colunas existem
    $columnsCheck = $conn->query("SHOW COLUMNS FROM Temperaturas LIKE 'ID_Temperatura'");
    if ($columnsCheck->rowCount() === 0) {
        error_log("ERRO: A coluna 'ID_Temperatura' não existe na tabela 'Temperaturas'");
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Erro no servidor: Estrutura de banco de dados incorreta']);
        exit;
    }

    $columnsCheck = $conn->query("SHOW COLUMNS FROM Temperaturas LIKE 'ID_Usuario'");
    if ($columnsCheck->rowCount() === 0) {
        error_log("ERRO: A coluna 'ID_Usuario' não existe na tabela 'Temperaturas'");
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Erro no servidor: Estrutura de banco de dados incorreta']);
        exit;
    }

    // Tenta excluir o registro diretamente
    $stmt = $conn->prepare("DELETE FROM Temperaturas WHERE ID_Temperatura = ? AND ID_Usuario = ?");
    $stmt->execute([$zoneId, $userId]);
    
    if ($stmt->rowCount() > 0) {
        error_log("Zona $zoneId removida com sucesso pelo usuário $userId");
        http_response_code(200);
        echo json_encode(['success' => true, 'message' => 'Zona removida com sucesso']);
        exit;
    } else {
        // Se nenhuma linha foi afetada, verifica se a zona existe
        $checkZone = $conn->prepare("SELECT ID_Temperatura FROM Temperaturas WHERE ID_Temperatura = ?");
        $checkZone->execute([$zoneId]);
        
        if ($checkZone->rowCount() === 0) {
            error_log("Zona $zoneId não encontrada no sistema");
            http_response_code(200); // Retorna sucesso mesmo se a zona não existir
            echo json_encode(['success' => true, 'message' => 'Zona não encontrada ou já removida']);
            exit;
        } else {
            error_log("Zona $zoneId encontrada, mas não pertence ao usuário $userId");
            http_response_code(200); // Retorna sucesso mesmo se não tiver permissão
            echo json_encode(['success' => true, 'message' => 'Permissão negada ou zona já removida']);
            exit;
        }
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

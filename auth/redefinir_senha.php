<?php
session_start();
require_once '../config/database.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    exit;
}

$username = trim(filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING));
$nova_senha = $_POST['nova_senha'] ?? '';
$confirmar_senha = $_POST['confirmar_senha'] ?? '';

try {
    if (empty($username) || empty($nova_senha) || empty($confirmar_senha)) {
        throw new Exception('Todos os campos são obrigatórios');
    }

    if (strlen($nova_senha) < 8) {
        throw new Exception('A senha deve ter pelo menos 8 caracteres');
    }

    if ($nova_senha !== $confirmar_senha) {
        throw new Exception('As senhas não coincidem');
    }

    $sql = "SELECT ID_Usuario FROM Usuarios WHERE Nome_Usuario = :username";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':username', $username, PDO::PARAM_STR);
    $stmt->execute();
    
    if ($stmt->rowCount() === 0) {
        throw new Exception('Nome de usuário não encontrado');
    }
    
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    $user_id = $user['ID_Usuario'];
    
    $senha_hash = password_hash($nova_senha, PASSWORD_DEFAULT);
    
    $sql_update = "UPDATE Usuarios SET Senha = :senha, Data_Atualizacao = NOW() WHERE ID_Usuario = :user_id";
    $stmt_update = $conn->prepare($sql_update);
    $stmt_update->bindParam(':senha', $senha_hash, PDO::PARAM_STR);
    $stmt_update->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    
    if (!$stmt_update->execute()) {
        throw new Exception('Erro ao atualizar a senha');
    }
    
    $ip = $_SERVER['REMOTE_ADDR'];
    $navegador = $_SERVER['HTTP_USER_AGENT'];
    
    $sql_log = "INSERT INTO Logs_Autenticacao (ID_Usuario, Tipo_Acao, Endereco_IP, Navegador) 
               VALUES (:user_id, 'redefinicao_senha', :ip, :navegador)";
    
    $stmt_log = $conn->prepare($sql_log);
    $stmt_log->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt_log->bindParam(':ip', $ip, PDO::PARAM_STR);
    $stmt_log->bindParam(':navegador', $navegador, PDO::PARAM_STR);
    $stmt_log->execute();
    
    echo json_encode([
        'success' => true, 
        'message' => 'Senha redefinida com sucesso!',
        'redirect' => '../pages/index.php'
    ]);
    
} catch (PDOException $e) {
    error_log("Erro PDO em redefinir_senha.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'message' => 'Erro no servidor. Por favor, tente novamente.'
    ]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false, 
        'message' => $e->getMessage()
    ]);
}
?>

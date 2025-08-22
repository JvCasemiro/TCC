<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['Tipo_Usuario'] !== 'admin' && $_SESSION['Tipo_Usuario'] !== 'master')) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Acesso negado. Permissão insuficiente.']);
    exit;
}

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['id']) || !is_numeric($data['id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'ID de usuário inválido']);
    exit;
}

$userId = intval($data['id']);
$username = trim($data['username'] ?? '');
$email = trim($data['email'] ?? '');
$tipoUsuario = $data['tipo_usuario'] ?? '';
$updatePassword = !empty($data['password']);
$password = $data['password'] ?? '';
$confirmPassword = $data['confirm_password'] ?? '';

// Validações básicas
if (empty($username) || empty($email) || empty($tipoUsuario)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Todos os campos são obrigatórios']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'E-mail inválido']);
    exit;
}

if ($updatePassword) {
    if (strlen($password) < 6) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'A senha deve ter no mínimo 6 caracteres']);
        exit;
    }
    
    if ($password !== $confirmPassword) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'As senhas não coincidem']);
        exit;
    }
}

try {
    // Verificar se o e-mail já está em uso por outro usuário
    $stmt = $conn->prepare("SELECT ID_Usuario FROM Usuarios WHERE Email = ? AND ID_Usuario != ? AND Ativo = TRUE");
    $stmt->execute([$email, $userId]);
    
    if ($stmt->rowCount() > 0) {
        throw new Exception('Este e-mail já está em uso por outro usuário');
    }
    
    // Atualizar dados do usuário
    if ($updatePassword) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE Usuarios SET Nome_Usuario = ?, Email = ?, Tipo_Usuario = ?, Senha = ?, Data_Atualizacao = NOW() WHERE ID_Usuario = ?");
        $stmt->execute([$username, $email, $tipoUsuario, $hashedPassword, $userId]);
    } else {
        $stmt = $conn->prepare("UPDATE Usuarios SET Nome_Usuario = ?, Email = ?, Tipo_Usuario = ?, Data_Atualizacao = NOW() WHERE ID_Usuario = ?");
        $stmt->execute([$username, $email, $tipoUsuario, $userId]);
    }
    
    if ($stmt->rowCount() === 0) {
        throw new Exception('Nenhuma alteração foi feita ou usuário não encontrado');
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Usuário atualizado com sucesso!'
    ]);
    
} catch (PDOException $e) {
    error_log("Erro ao atualizar usuário: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao atualizar usuário. Tente novamente mais tarde.'
    ]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

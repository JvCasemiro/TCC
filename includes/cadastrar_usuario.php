<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Acesso negado']);
    exit;
}

header('Content-Type: application/json');

// DATABASE USER REGISTRATION COMMENTED OUT FOR TESTING WITHOUT DATABASE
/*
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    exit;
}

$username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING);
$email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
$password = $_POST['password'];
$confirm_password = $_POST['confirm_password'];
$tipo_usuario = filter_input(INPUT_POST, 'tipo_usuario', FILTER_SANITIZE_STRING);

if (empty($username) || empty($email) || empty($password) || empty($confirm_password) || empty($tipo_usuario)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Todos os campos são obrigatórios']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'E-mail inválido']);
    exit;
}

if (strlen($password) < 8) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'A senha deve ter pelo menos 8 caracteres']);
    exit;
}

if ($password !== $confirm_password) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'As senhas não coincidem']);
    exit;
}

$tipos_validos = ['admin', 'user'];
if (!in_array($tipo_usuario, $tipos_validos)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Tipo de usuário inválido']);
    exit;
}

try {
    $stmt = $conn->prepare("SELECT ID_Usuario FROM Usuarios WHERE Nome_Usuario = :username");
    $stmt->bindParam(':username', $username);
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        http_response_code(409);
        echo json_encode(['success' => false, 'message' => 'Nome de usuário já existe']);
        exit;
    }
    
    $stmt = $conn->prepare("SELECT ID_Usuario FROM Usuarios WHERE Email = :email");
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        http_response_code(409);
        echo json_encode(['success' => false, 'message' => 'E-mail já cadastrado']);
        exit;
    }
    
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    $stmt = $conn->prepare("INSERT INTO Usuarios (Nome_Usuario, Email, Senha, Tipo_Usuario) VALUES (:username, :email, :password, :tipo_usuario)");
    $stmt->bindParam(':username', $username);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':password', $hashed_password);
    $stmt->bindParam(':tipo_usuario', $tipo_usuario);
    
    if ($stmt->execute()) {
        echo json_encode([
            'success' => true, 
            'message' => 'Usuário cadastrado com sucesso!'
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Erro ao cadastrar usuário']);
    }
    
} catch(PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erro interno do servidor: ' . $e->getMessage()]);
}
*/

// MOCK USER REGISTRATION FOR TESTING WITHOUT DATABASE
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $tipo_usuario = filter_input(INPUT_POST, 'tipo_usuario', FILTER_SANITIZE_STRING);
    
    // Basic validation
    if (empty($username) || empty($email) || empty($password) || empty($confirm_password) || empty($tipo_usuario)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Todos os campos são obrigatórios']);
        exit;
    }
    
    if ($password !== $confirm_password) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'As senhas não coincidem']);
        exit;
    }
    
    // Mock success response
    echo json_encode([
        'success' => true, 
        'message' => 'Usuário cadastrado com sucesso! (Modo de teste sem banco de dados)'
    ]);
    exit;
}

http_response_code(405);
echo json_encode(['success' => false, 'message' => 'Método não permitido']);
?>

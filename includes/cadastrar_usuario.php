<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Acesso negado']);
    exit;
}

header('Content-Type: application/json');

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
$id_casa = filter_input(INPUT_POST, 'id_casa', FILTER_VALIDATE_INT);

if (empty($username) || empty($email) || empty($password) || empty($confirm_password) || empty($tipo_usuario)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Todos os campos são obrigatórios']);
    exit;
}

// Se for usuário comum, é obrigatório ter uma casa
if ($tipo_usuario === 'user' && empty($id_casa)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Usuários comuns devem estar vinculados a uma casa']);
    exit;
}

// Se for admin, não precisa de casa
if ($tipo_usuario === 'admin') {
    $id_casa = null;
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

if ($conn === null) {
    echo json_encode([
        'success' => true, 
        'message' => 'Usuário cadastrado com sucesso! (Modo de teste sem banco de dados)'
    ]);
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
    
    try {
        if ($id_casa) {
            // Verifica se a casa existe
            $stmt = $conn->prepare("SELECT ID_Casa FROM Casas WHERE ID_Casa = ? AND Ativo = 1");
            $stmt->execute([$id_casa]);
            if (!$stmt->fetch()) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Casa inválida']);
                exit;
            }
            
            $stmt = $conn->prepare("INSERT INTO Usuarios (Nome_Usuario, Email, Senha, Tipo_Usuario, ID_Casa) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$username, $email, $hashed_password, $tipo_usuario, $id_casa]);
        } else {
            $stmt = $conn->prepare("INSERT INTO Usuarios (Nome_Usuario, Email, Senha, Tipo_Usuario) VALUES (?, ?, ?, ?)");
            $stmt->execute([$username, $email, $hashed_password, $tipo_usuario]);
        }
        
        echo json_encode([
            'success' => true, 
            'message' => 'Usuário cadastrado com sucesso!'
        ]);
    } catch (PDOException $e) {
        error_log("Erro ao cadastrar usuário: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Erro ao cadastrar usuário']);
    }
    
} catch(PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erro interno do servidor: ' . $e->getMessage()]);
}
?>

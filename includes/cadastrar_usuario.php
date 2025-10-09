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
        $stmt = $conn->prepare("SELECT Codigo_Casa FROM Usuarios WHERE Tipo_Usuario = 'admin' LIMIT 1");
        $stmt->execute();
        $admin_casa = $stmt->fetch(PDO::FETCH_ASSOC);
        $codigo_casa = $admin_casa ? $admin_casa['Codigo_Casa'] : 1;
        
        $fields = [
            'Nome_Usuario' => $username,
            'Email' => $email,
            'Senha' => $hashed_password,
            'Tipo_Usuario' => $tipo_usuario,
            'Codigo_Casa' => $codigo_casa
        ];
        
        if ($id_casa !== null) {
            $fields['ID_Casa'] = $id_casa;
        }
        
        $columns = implode(', ', array_keys($fields));
        $placeholders = implode(', ', array_fill(0, count($fields), '?'));
        
        $stmt = $conn->prepare("INSERT INTO Usuarios ($columns) VALUES ($placeholders)");
        $stmt->execute(array_values($fields));
        
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

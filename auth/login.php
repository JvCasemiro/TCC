<?php
session_start();
require_once '../config/database.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    exit;
}

$username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$password = $_POST['password'];

if (empty($username) || empty($password)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Todos os campos são obrigatórios']);
    exit;
}

try {
    $stmt = $conn->prepare("SELECT ID_Usuario as id, Nome_Usuario as username, Email as email, Senha as password FROM Usuarios WHERE Nome_Usuario = :username AND Ativo = TRUE");
    $stmt->bindParam(':username', $username);
    $stmt->execute();
    
    if ($stmt->rowCount() === 1) {
        $user = $stmt->fetch();
        
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['email'] = $user['email'];
            
            // Update last access time
            $update_stmt = $conn->prepare("UPDATE Usuarios SET Ultimo_Acesso = NOW() WHERE ID_Usuario = :id");
            $update_stmt->bindParam(':id', $user['id']);
            $update_stmt->execute();
            
            // Redirect to menu
            header('Location: ../pages/menu.php');
            exit;
        } else {
            // Invalid credentials
            $_SESSION['login_error'] = 'Credenciais inválidas';
            header('Location: ../../index.php');
            exit;
        }
    } else {
        // Invalid credentials
        $_SESSION['login_error'] = 'Credenciais inválidas';
        header('Location: ../../index.php');
        exit;
    }
} catch(PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    $_SESSION['login_error'] = 'Erro no servidor. Tente novamente mais tarde.';
    header('Location: ../../index.php');
    exit;
}
?>

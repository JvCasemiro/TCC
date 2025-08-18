<?php
session_start();
// require_once 'config/database.php';

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

// Comentado temporariamente para permitir acesso sem banco de dados
// try {
//     $stmt = $conn->prepare("SELECT ID_Usuario as id, Nome_Usuario as username, Senha as password FROM Usuarios WHERE Nome_Usuario = :username");
//     $stmt->bindParam(':username', $username);
//     $stmt->execute();
//     
//     if ($stmt->rowCount() === 1) {
//         $user = $stmt->fetch();
//         
//         if (password_verify($password, $user['password'])) {
//             $_SESSION['user_id'] = $user['id'];
//             $_SESSION['username'] = $user['username'];
//             
//             header('Location: menu.php');
//             exit;
//         } else {
//             http_response_code(401);
//             echo json_encode(['success' => false, 'message' => 'Credenciais inválidas']);
//         }
//     } else {
//         http_response_code(401);
//         echo json_encode(['success' => false, 'message' => 'Credenciais inválidas']);
//     }
// } catch(PDOException $e) {
//     http_response_code(500);
//     echo json_encode(['success' => false, 'message' => 'Erro no servidor: ' . $e->getMessage()]);
// }

// Login temporário sem verificação de banco de dados
$_SESSION['user_id'] = 1;
$_SESSION['username'] = $username;

header('Location: menu.php');
exit;
?>

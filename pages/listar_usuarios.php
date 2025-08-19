<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Acesso negado']);
    exit;
}

header('Content-Type: application/json');

// DATABASE USER LISTING COMMENTED OUT FOR TESTING WITHOUT DATABASE
/*
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    exit;
}

try {
    $stmt = $conn->prepare("SELECT ID_Usuario as id, Nome_Usuario as username, Email as email, Tipo_Usuario as tipo_usuario, Data_Criacao as data_criacao FROM Usuarios ORDER BY Data_Criacao DESC");
    $stmt->execute();
    
    $users = $stmt->fetchAll();
    
    foreach ($users as &$user) {
        if ($user['data_criacao']) {
            $date = new DateTime($user['data_criacao']);
            $user['data_criacao'] = $date->format('d/m/Y H:i');
        }
    }
    
    echo json_encode([
        'success' => true,
        'users' => $users,
        'total' => count($users)
    ]);
    
} catch(PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erro ao carregar usuários']);
}
*/

// MOCK USER LISTING FOR TESTING WITHOUT DATABASE
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $mock_users = [
        [
            'id' => 1,
            'username' => $_SESSION['username'],
            'email' => $_SESSION['email'],
            'tipo_usuario' => 'admin',
            'data_criacao' => date('d/m/Y H:i')
        ],
        [
            'id' => 2,
            'username' => 'usuario_teste',
            'email' => 'teste@exemplo.com',
            'tipo_usuario' => 'user',
            'data_criacao' => date('d/m/Y H:i', strtotime('-1 day'))
        ]
    ];
    
    echo json_encode([
        'success' => true,
        'users' => $mock_users,
        'total' => count($mock_users)
    ]);
    exit;
}

http_response_code(405);
echo json_encode(['success' => false, 'message' => 'Método não permitido']);
?>

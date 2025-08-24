<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Acesso negado']);
    exit;
}

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    exit;
}

try {
    $stmt = $conn->prepare("SELECT 
        ID_Usuario as id, 
        Nome_Usuario as username, 
        Email as email, 
        Tipo_Usuario as tipo_usuario, 
        Data_Cadastro as data_criacao 
        FROM Usuarios 
        WHERE Ativo = TRUE 
        ORDER BY Data_Cadastro");
        
    $stmt->execute();
    
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
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
    echo json_encode([
        'success' => false, 
        'message' => 'Erro ao carregar usuários',
        'error' => $e->getMessage()
    ]);
}
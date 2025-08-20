<?php
session_start();
require_once '../config/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Não autorizado']);
    exit;
}

try {
    $stmt = $conn->query("SELECT 
        Numeracao as plate, 
        DATE_FORMAT(Ultimo_Acesso, '%H:%i:%s') as time,
        '100%' as confidence,
        'authorized' as status
        FROM Placas 
        WHERE Ultimo_Acesso IS NOT NULL 
        ORDER BY Ultimo_Acesso DESC 
        LIMIT 5");
    
    $recentPlates = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($recentPlates);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Erro ao buscar detecções recentes']);
}

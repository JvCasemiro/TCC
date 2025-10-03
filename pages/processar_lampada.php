<?php
session_start();
require_once '../config/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Usuário não autenticado']);
    exit;
}

$action = $_POST['action'] ?? '';
$response = ['success' => false, 'message' => 'Ação inválida'];

try {
    switch ($action) {
        case 'adicionar':
            $nome = trim($_POST['nome'] ?? '');
            $comodo = trim($_POST['comodo'] ?? '');
            
            if (empty($nome) || empty($comodo)) {
                $response['message'] = 'Nome e cômodo são obrigatórios';
                break;
            }
            
            $stmt = $conn->prepare("INSERT INTO Lampadas (Nome, Comodo, ID_Usuario) VALUES (?, ?, ?)");
            $success = $stmt->execute([$nome, $comodo, $_SESSION['user_id']]);
            
            if ($success) {
                $response = [
                    'success' => true,
                    'message' => 'Lâmpada cadastrada com sucesso!',
                    'id' => $conn->lastInsertId()
                ];
            } else {
                $response['message'] = 'Erro ao cadastrar lâmpada';
            }
            break;
            
        case 'atualizar':
            // Implementar atualização de lâmpada se necessário
            break;
            
        case 'remover':
            // Implementar remoção de lâmpada se necessário
            break;
            
        default:
            $response['message'] = 'Ação não reconhecida';
    }
} catch (PDOException $e) {
    $response['message'] = 'Erro no banco de dados: ' . $e->getMessage();
}

echo json_encode($response);
?>

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
            
            // Get the user's codigo_casa
            $stmt = $conn->prepare("SELECT Codigo_Casa FROM Usuarios WHERE ID_Usuario = ?");
            $stmt->execute([$_SESSION['user_id']]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            $codigo_casa = $user['Codigo_Casa'] ?? 1; // Default to 1 if not found
            
            // Insert lamp with user's codigo_casa
            $stmt = $conn->prepare("INSERT INTO Lampadas (Nome, Comodo, ID_Usuario, codigo_casa) VALUES (?, ?, ?, ?)");
            $success = $stmt->execute([$nome, $comodo, $_SESSION['user_id'], $codigo_casa]);
            
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
            break;
            
        case 'remover':
            $id = $_POST['id'] ?? 0;
            
            if (empty($id)) {
                $response['message'] = 'ID da lâmpada não fornecido';
                break;
            }
            
            $stmt = $conn->prepare("SELECT ID_Lampada FROM Lampadas WHERE ID_Lampada = ? AND ID_Usuario = ?");
            $stmt->execute([$id, $_SESSION['user_id']]);
            
            if ($stmt->rowCount() === 0) {
                $response['message'] = 'Lâmpada não encontrada ou você não tem permissão para removê-la';
                break;
            }
            
            $stmt = $conn->prepare("DELETE FROM Lampadas WHERE ID_Lampada = ? AND ID_Usuario = ?");
            $success = $stmt->execute([$id, $_SESSION['user_id']]);
            
            if ($success) {
                $response = [
                    'success' => true,
                    'message' => 'Lâmpada removida com sucesso!'
                ];
            } else {
                $response['message'] = 'Erro ao remover lâmpada';
            }
            break;
            
        default:
            $response['message'] = 'Ação não reconhecida';
    }
} catch (PDOException $e) {
    $response['message'] = 'Erro no banco de dados: ' . $e->getMessage();
}

echo json_encode($response);
?>

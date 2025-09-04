<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit;
}

function getRotinas($conn, $user_id) {
    try {
        $stmt = $conn->prepare("
            SELECT * FROM Rotinas 
            WHERE ID_Usuario = :user_id
            ORDER BY Ativa DESC, Nome ASC
        ");
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Erro ao buscar rotinas: " . $e->getMessage());
        return [];
    }
}

function salvarRotina($conn, $dados, $user_id) {
    try {
        $stmt = $conn->prepare("
            INSERT INTO Rotinas 
            (Nome, Descricao, Icone, Hora, Dia_Semana, ID_Usuario, Ativa)
            VALUES (:nome, :descricao, :icone, :hora, :dia_semana, :user_id, :ativa)
        
        ");
        
        return $stmt->execute([
            ':nome' => $dados['nome'],
            ':descricao' => $dados['descricao'] ?? '',
            ':icone' => $dados['icone'] ?? 'fas fa-home',
            ':hora' => $dados['hora'],
            ':dia_semana' => $dados['dia_semana'] ?? '1,2,3,4,5,6,0',
            ':user_id' => $user_id,
            ':ativa' => 1
        ]);
    } catch (PDOException $e) {
        error_log("Erro ao salvar rotina: " . $e->getMessage());
        return false;
    }
}

function atualizarRotina($conn, $dados, $user_id, $rotina_id) {
    try {
        $stmt = $conn->prepare("
            UPDATE Rotinas 
            SET Nome = :nome,
                Descricao = :descricao,
                Icone = :icone,
                Hora = :hora,
                Dia_Semana = :dia_semana,
                Data_Atualizacao = NOW()
            WHERE ID_Rotina = :rotina_id
            AND ID_Usuario = :user_id
        
        ");
        
        return $stmt->execute([
            ':nome' => $dados['nome'],
            ':descricao' => $dados['descricao'] ?? '',
            ':icone' => $dados['icone'] ?? 'fas fa-home',
            ':hora' => $dados['hora'],
            ':dia_semana' => $dados['dia_semana'] ?? '1,2,3,4,5,6,0',
            ':rotina_id' => $rotina_id,
            ':user_id' => $user_id
        ]);
    } catch (PDOException $e) {
        error_log("Erro ao atualizar rotina: " . $e->getMessage());
        return false;
    }
}

function atualizarStatusRotina($conn, $id_rotina, $status, $user_id) {
    try {
        $stmt = $conn->prepare("
            UPDATE Rotinas 
            SET Ativa = :status
            WHERE ID_Rotina = :id_rotina AND ID_Usuario = :user_id
        ");
        
        return $stmt->execute([
            ':status' => $status,
            ':id_rotina' => $id_rotina,
            ':user_id' => $user_id
        ]);
    } catch (PDOException $e) {
        error_log("Erro ao atualizar status da rotina: " . $e->getMessage());
        return false;
    }
}

function excluirRotina($conn, $id_rotina, $user_id) {
    try {
        $stmt = $conn->prepare("
            DELETE FROM Rotinas 
            WHERE ID_Rotina = :id_rotina AND ID_Usuario = :user_id
        ");
        
        return $stmt->execute([
            ':id_rotina' => $id_rotina,
            ':user_id' => $user_id
        ]);
    } catch (PDOException $e) {
        error_log("Erro ao excluir rotina: " . $e->getMessage());
        return false;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $response = ['success' => false, 'message' => ''];
    
    if (isset($_POST['action'])) {
        $user_id = $_SESSION['user_id'];
        
        switch ($_POST['action']) {
            case 'salvar':
                if (!empty($_POST['nome']) && !empty($_POST['hora'])) {
                    $dados = [
                        'nome' => $_POST['nome'],
                        'descricao' => $_POST['descricao'] ?? '',
                        'icone' => $_POST['icone'] ?? 'fas fa-home',
                        'hora' => $_POST['hora'],
                        'dia_semana' => $_POST['dia_semana'] ?? '1,2,3,4,5,6,0',
                    ];
                    
                    if (!empty($_POST['id_rotina'])) {
                        if (atualizarRotina($conn, $dados, $user_id, $_POST['id_rotina'])) {
                            $response = ['success' => true, 'message' => 'Rotina atualizada com sucesso!'];
                        } else {
                            $response['message'] = 'Erro ao atualizar rotina.';
                        }
                    } else {
                        if (salvarRotina($conn, $dados, $user_id)) {
                            $response = ['success' => true, 'message' => 'Rotina salva com sucesso!'];
                        } else {
                            $response['message'] = 'Erro ao salvar rotina.';
                        }
                    }
                } else {
                    $response['message'] = 'Preencha todos os campos obrigatórios.';
                }
                break;
                
            case 'atualizar_status':
                if (!empty($_POST['id_rotina']) && isset($_POST['status'])) {
                    if (atualizarStatusRotina($conn, $_POST['id_rotina'], $_POST['status'], $user_id)) {
                        $response = ['success' => true, 'message' => 'Status da rotina atualizado!'];
                    } else {
                        $response['message'] = 'Erro ao atualizar status da rotina.';
                    }
                }
                break;
                
            case 'excluir':
                if (!empty($_POST['id_rotina'])) {
                    if (excluirRotina($conn, $_POST['id_rotina'], $user_id)) {
                        $response = ['success' => true, 'message' => 'Rotina excluída com sucesso!'];
                    } else {
                        $response['message'] = 'Erro ao excluir rotina.';
                    }
                }
                break;
        }
        
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    }
}

$rotinas = [];
if ($conn) {
    $rotinas = getRotinas($conn, $_SESSION['user_id']);
}

if ($conn === null) {
    $user = [
        'username' => $_SESSION['username'] ?? 'admin',
        'email' => $_SESSION['email'] ?? 'admin@exemplo.com',
        'created_at' => '2024-01-01 00:00:00',
        'updated_at' => date('Y-m-d H:i:s'),
        'user_type' => 'admin'
    ];
    
    $created_at = new DateTime($user['created_at']);
    $updated_at = new DateTime($user['updated_at']);
} else {
    try {
        $stmt = $conn->prepare("
            SELECT 
                Nome_Usuario as username,
                Email as email,
                Data_Cadastro as created_at,
                Data_Atualizacao as updated_at,
                Tipo_Usuario as user_type
            FROM Usuarios 
            WHERE ID_Usuario = :user_id
        ");
        $stmt->bindParam(':user_id', $_SESSION['user_id']);
        $stmt->execute();
        $user = $stmt->fetch();
        
        if (!$user) {
            session_destroy();
            header('Location: ../index.php');
            exit;
        }
        
        $created_at = new DateTime($user['created_at']);
        $updated_at = new DateTime($user['updated_at']);
        
    } catch (Exception $e) {
        error_log("Error fetching user data: " . $e->getMessage());
        session_destroy();
        header('Location: ../index.php?error=data_fetch_failed');
        exit;
    }
}

$username = $user['username'];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rotinas</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="shortcut icon" href="../assets/img/logo_domx_sem_nome.png" type="image/x-icon">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background: linear-gradient(135deg, #0a0f2c 0%, #1a2a6c 100%);
            min-height: 100vh;
            color: #333;
        }
        
        .header {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            padding: 1rem 2rem;
            box-shadow: 0 2px 20px rgba(0, 0, 0, 0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: relative;
        }
        
        .logo {
            font-size: 1.5rem;
            font-weight: bold;
            color: rgb(13, 42, 75);
        }
        
        .routine-actions {
            display: flex;
            gap: 10px;
            margin-top: 20px;
            flex-wrap: wrap;
        }
        
        /* Estilos específicos para os botões de ação nas rotinas */
        .routine-actions .btn {
            min-width: 100px;
            justify-content: center;
            padding: 8px 12px;
            font-size: 0.85rem;
            white-space: nowrap;
            transition: all 0.2s ease;
        }
        
        .routine-actions .btn i {
            margin-right: 4px;
        }
        
        /* Efeito de hover mais suave para os botões de ação */
        .routine-actions .btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        /* Estilo dos botões de ação no modal */
        .routine-actions {
            display: flex;
            justify-content: space-between;
            gap: 15px;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #eee;
            width: 100%;
        }
        
        .routine-actions .btn {
            flex: 1;
            padding: 12px 15px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            font-weight: 500;
            transition: all 0.2s ease;
            border-radius: 6px;
            font-size: 0.95rem;
            border: none;
            cursor: pointer;
        }
        
        .routine-actions .btn i {
            font-size: 0.9em;
        }
        
        .routine-actions .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        .routine-actions .btn:active {
            transform: translateY(0);
        }
        
        /* Ajustes para o formulário no modal */
        .modal-content form {
            margin-top: 1rem;
        }
        
        /* Melhorando a aparência dos campos do formulário */
        .form-control {
            width: 100%;
            padding: 0.75rem;
            border: 2px solid #e1e8ed;
            border-radius: 5px;
            font-size: 1rem;
            transition: border-color 0.3s;
        }
        
        .form-control:focus {
            outline: none;
            border-color: #4a90e2;
            box-shadow: 0 0 0 0.2rem rgba(74, 144, 226, 0.25);
        }
        
        /* Melhorando a aparência do select múltiplo */
        select[multiple].form-control {
            min-height: 150px;
            padding: 0.5rem;
        }
        
        select[multiple].form-control option {
            padding: 0.5rem 0.75rem;
            margin: 0.25rem 0;
            border-radius: 4px;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        select[multiple].form-control option:hover {
            background-color: #f8f9fa;
        }
        
        select[multiple].form-control option:checked {
            background-color: #4a90e2;
            color: white;
        }
        
        /* Estilos para botões */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            font-size: 0.95rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s ease;
            gap: 8px;
        }
        
        .btn i {
            font-size: 1rem;
        }
        
        .btn-primary {
            background-color: #4a90e2;
            color: white;
        }
        
        .btn-primary:hover {
            background-color: #357abd;
            transform: translateY(-1px);
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        .btn-success {
            background-color: #28a745;
            color: white;
        }
        
        .btn-success:hover {
            background-color: #218838;
            transform: translateY(-1px);
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        .btn-warning {
            background-color: #ffc107;
            color: #212529;
        }
        
        .btn-secondary {
            background-color: #6c757d;
            color: white;
        }
        
        .btn-secondary:hover {
            background-color: #5a6268;
            transform: translateY(-1px);
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        .btn-warning:hover {
            background-color: #e0a800;
            transform: translateY(-1px);
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        .btn-danger {
            background-color: #dc3545;
            color: white;
        }
        
        .btn-danger:hover {
            background-color: #c82333;
            transform: translateY(-1px);
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        .add-routine-btn {
            background: linear-gradient(135deg, #4a90e2, #357abd);
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            margin-bottom: 25px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(74, 144, 226, 0.3);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            position: relative;
            overflow: hidden;
        }
        
        .add-routine-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: 0.5s;
        }
        
        .add-routine-btn:hover::before {
            left: 100%;
        }
        
        .add-routine-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }
        
        .add-routine-btn i {
            font-size: 1rem;
        }
        
        /* Estilo para mensagens de feedback */
        .alert {
            padding: 12px 20px;
            margin-bottom: 20px;
            border-radius: 5px;
            font-size: 14px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            animation: fadeIn 0.3s ease-in-out;
        }
        
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .alert-info {
            background-color: #d1ecf1;
            color: #0c5460;
            border: 1px solid #bee5eb;
        }
        
        .alert .close {
            background: none;
            border: none;
            font-size: 20px;
            cursor: pointer;
            color: inherit;
            opacity: 0.7;
            transition: opacity 0.2s;
        }
        
        .alert .close:hover {
            opacity: 1;
        }
        
        /* Estilo para quando não há rotinas */
        .no-routines {
            grid-column: 1 / -1;
            text-align: center;
            padding: 40px 20px;
            color: #6c757d;
        }
        
        .no-routines i {
            font-size: 48px;
            margin-bottom: 15px;
            opacity: 0.5;
        }
        
        .no-routines p {
            margin: 10px 0;
        }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .user-name {
            font-weight: 500;
            color: #333;
        }
        
        .back-btn {
            background: linear-gradient(135deg, #4a90e2 0%, #3a7bc8 100%);
            color: white;
            border: none;
            padding: 0.6rem 1.5rem;
            border-radius: 25px;
            cursor: pointer;
            font-size: 14px;
            transition: all 0.4s ease-in-out;
            position: relative;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(74, 144, 226, 0.3);
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            text-decoration: none;
            margin-right: 10px;
        }
        
        .back-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(74, 144, 226, 0.4);
        }
        
        .back-btn:active {
            transform: translateY(0);
            box-shadow: 0 2px 10px rgba(74, 144, 226, 0.3);
        }
        
        .back-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: 0.5s;
        }
        
        .back-btn:hover::before {
            left: 100%;
        }
        
        .logout-btn {
            background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
            color: white;
            border: none;
            padding: 0.6rem 1.5rem;
            border-radius: 25px;
            cursor: pointer;
            font-size: 14px;
            transition: all 0.4s ease-in-out;
            position: relative;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(231, 76, 60, 0.3);
            display: flex;
            align-items: center;
            gap: 0.5rem;
            text-decoration: none;
        }
        
        .logout-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(231, 76, 60, 0.4);
        }
        
        .logout-btn:active {
            transform: translateY(0);
            box-shadow: 0 2px 10px rgba(231, 76, 60, 0.3);
        }
        
        .logout-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: 0.5s;
        }
        
        .logout-btn:hover::before {
            left: 100%;
        }
        
        .domx-logo {
            position: absolute;
            top: 50%;
            right: 2rem;
            transform: translateY(-50%);
        }
        
        .domx-logo img {
            height: 100px;
        }
        
        .container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 2rem;
        }
        
        .page-title {
            text-align: center;
            margin-bottom: 3rem;
            color: white;
        }
        
        .page-title h1 {
            font-size: 2.5rem;
            margin-bottom: 0.5rem;
        }
        
        .page-title p {
            font-size: 1.2rem;
            opacity: 0.9;
        }
        
        .content-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            padding: 2rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
        }
        
        .routines-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 2rem;
            margin-top: 2rem;
        }
        
        .routine-card {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 1.5rem;
            border-left: 4px solid #4a90e2;
            transition: transform 0.3s, box-shadow 0.3s;
        }
        
        .routine-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
        }
        
        .routine-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }
        
        .routine-title {
            font-size: 1.2rem;
            font-weight: bold;
            color: #333;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .routine-status {
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: bold;
        }
        
        .status-active {
            background: #d4edda;
            color: #155724;
        }
        
        .status-inactive {
            background: #f8d7da;
            color: #721c24;
        }
        
        .routine-description {
            color: #666;
            margin-bottom: 1rem;
            line-height: 1.5;
        }
        
        .routine-schedule {
            background: #e9ecef;
            padding: 0.75rem;
            border-radius: 5px;
            margin-bottom: 1rem;
        }
        
        .close:hover {
            color: #000;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
            width: 100%;
        }
        
        .form-group:last-child {
            margin-bottom: 1rem;
        }
        
        /* Removido form-row que dividia em colunas */
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: #333;
        }
        
        .required {
            color: #dc3545;
            margin-left: 3px;
        }
        
        .form-text {
            display: block;
            margin-top: 0.25rem;
            font-size: 0.875em;
            color: #6c757d;
        }
        
        .form-text.text-muted {
            color: #6c757d !important;
        }
        
        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 0.75rem;
            border: 2px solid #e1e8ed;
            border-radius: 5px;
            font-size: 1rem;
            transition: border-color 0.3s;
            box-sizing: border-box;
            display: block;
        }
        
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #4a90e2;
        }
        
        /* Estilos do Modal */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            overflow-y: auto;
            padding: 20px 0;
        }
        
        .modal-content {
            background: #fff;
            margin: 20px auto;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 5px 30px rgba(0, 0, 0, 0.3);
            width: 90%;
            max-width: 500px;
            position: relative;
            animation: slideIn 0.3s ease-out;
            border-top: 4px solid #4a90e2;
        }
        
        /* Estilo específico para o modal de confirmação de exclusão */
        #deleteConfirmationModal .modal-content {
            border-top-color: #dc3545;
            max-width: 450px;
        }
        
        #deleteConfirmationModal h2 {
            color: #dc3545;
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 15px;
        }
        
        #deleteConfirmationModal p {
            margin-bottom: 5px;
            font-size: 1.05rem;
        }
        
        #deleteConfirmationModal .text-muted {
            font-size: 0.9rem;
            margin-bottom: 20px;
        }
        
        #deleteConfirmationModal .btn {
            min-width: 120px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        
        /* Estilo específico para o modal de edição */
        .modal-content.editing {
            border-top-color: #28a745;
        }
        
        .modal-content.editing h2 {
            color: #28a745;
        }
        
        .modal h2 {
            margin: 0 0 20px 0;
            padding-right: 30px;
            color: #2c3e50;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .close {
            position: absolute;
            right: 20px;
            top: 15px;
            font-size: 28px;
            font-weight: bold;
            color: #aaa;
            cursor: pointer;
            line-height: 1;
            padding: 0 8px;
            border-radius: 50%;
            transition: all 0.3s;
        }
        
        .close:hover,
        .close:focus {
            color: #333;
            background-color: #f1f1f1;
            text-decoration: none;
            outline: none;
        }
        
        /* Animações */
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        @keyframes slideIn {
            from { 
                transform: translateY(-50px);
                opacity: 0;
            }
            to { 
                transform: translateY(0);
                opacity: 1;
            }
        }
        
        /* Estilos para selects múltiplos */
        .form-group select[multiple] {
            min-height: 120px;
            padding: 8px;
            border-radius: 6px;
            border: 2px solid #e1e8ed;
            transition: all 0.3s ease;
            background-color: #f8f9fa;
        }
        
        .form-group select[multiple]:focus {
            border-color: #4a90e2;
            box-shadow: 0 0 0 0.2rem rgba(74, 144, 226, 0.25);
            background-color: #fff;
        }
        
        .form-group select[multiple] option {
            padding: 8px 12px;
            margin: 2px 0;
            border-radius: 4px;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .form-group select[multiple] option:hover {
            background-color: #e9ecef;
        }
        
        .form-group select[multiple] option:checked {
            background-color: #4a90e2;
            color: white;
            font-weight: 500;
        }
        
        @media (max-width: 768px) {
            .container {
                padding: 0 1rem;
            }
            
            .routines-grid {
                grid-template-columns: 1fr;
            }
            
            .modal-content {
                margin: 10% auto;
                width: 95%;
                padding: 20px 15px;
            }
            
            .routine-actions {
                flex-direction: column;
                justify-content: center;
            }
            
            .routine-actions .btn {
                width: 100%;
                margin-bottom: 5px;
            }
        }
    </style>
</head>
<body>
    <header class="header">
        <div class="header-content">
            <div class="logo">
                <i class="fas fa-home"></i> DOMX
            </div>
            <div class="user-info">
                <a href="dashboard.php" class="back-btn" style="text-decoration: none; display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.6rem 1.5rem; border-radius: 25px; background: linear-gradient(135deg, #4a90e2 0%, #3a7bc8 100%); color: white; font-size: 14px; transition: all 0.4s ease-in-out; position: relative; overflow: hidden; box-shadow: 0 4px 15px rgba(74, 144, 226, 0.3);">
                    <i class="fas fa-arrow-left"></i> Voltar
                </a>
                <a href="../auth/logout.php" class="logout-btn" style="background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%); color: white; text-decoration: none; border: none; padding: 0.6rem 1.5rem; border-radius: 25px; cursor: pointer; font-size: 14px; transition: all 0.4s ease-in-out; position: relative; overflow: hidden; box-shadow: 0 4px 15px rgba(231, 76, 60, 0.3); display: inline-flex; align-items: center; gap: 0.5rem; margin-left: 8px;">
                    <i class="fas fa-sign-out-alt"></i> Sair
                </a>
            </div>
        </div>
        <div class="domx-logo">
            <img src="../assets/img/logo.png" alt="DOMX Logo">
        </div>
    </header>

    <div class="container">
        <div class="page-title">
            <h1>Rotinas Automáticas</h1>
            <p>Crie e gerencie rotinas para automatizar sua casa</p>
        </div>

        <div class="content-card">
            <button class="add-routine-btn" onclick="openModal()">
                <i class="fas fa-plus"></i>
                Nova Rotina
            </button>

            <div class="routines-grid" id="routinesGrid">
                <?php if (empty($rotinas)): ?>
                    <div class="no-routines">
                        <i class="fas fa-robot"></i>
                        <p>Nenhuma rotina cadastrada ainda.</p>
                        <p>Clique no botão "Nova Rotina" para começar.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($rotinas as $rotina): 
                        $icone = !empty($rotina['Icone']) ? $rotina['Icone'] : 'fas fa-home';
                        $status_class = $rotina['Ativa'] ? 'status-active' : 'status-inactive';
                        $status_text = $rotina['Ativa'] ? 'Ativa' : 'Inativa';
                        
                        $dias_semana = [
                            '1' => 'Seg', '2' => 'Ter', '3' => 'Qua', 
                            '4' => 'Qui', '5' => 'Sex', '6' => 'Sáb', '0' => 'Dom'
                        ];
                        $dias_selecionados = explode(',', $rotina['Dia_Semana']);
                        $dias_texto = [];
                        
                        if (in_array('7', $dias_selecionados) || count($dias_selecionados) === 7) {
                            $dias_texto[] = 'Todos os dias';
                        } elseif (count($dias_selecionados) === 5 && 
                                 !in_array('0', $dias_selecionados) && 
                                 !in_array('6', $dias_selecionados)) {
                            $dias_texto[] = 'Dias úteis';
                        } else {
                            foreach ($dias_selecionados as $dia) {
                                if (isset($dias_semana[$dia])) {
                                    $dias_texto[] = $dias_semana[$dia];
                                }
                            }
                        }
                        
                        $dias_formatado = implode(', ', $dias_texto);
                        $hora_formatada = date('H:i', strtotime($rotina['Hora']));
                    ?>
                    <div class="routine-card" data-id="<?php echo $rotina['ID_Rotina']; ?>">
                        <div class="routine-header">
                            <div class="routine-title">
                                <i class="<?php echo htmlspecialchars($icone); ?>"></i>
                                <?php echo htmlspecialchars($rotina['Nome']); ?>
                            </div>
                            <span class="routine-status <?php echo $status_class; ?>">
                                <?php echo $status_text; ?>
                            </span>
                        </div>
                        <div class="routine-description">
                            <?php echo !empty($rotina['Descricao']) ? nl2br(htmlspecialchars($rotina['Descricao'])) : 'Sem descrição'; ?>
                        </div>
                        <div class="routine-schedule">
                            <i class="fas fa-clock"></i> 
                            <?php echo $dias_formatado . ' às ' . $hora_formatada; ?>
                        </div>
                        <div class="routine-actions">
                            <button class="btn btn-primary btn-edit" 
                                    data-id="<?php echo $rotina['ID_Rotina']; ?>"
                                    data-nome="<?php echo htmlspecialchars($rotina['Nome']); ?>"
                                    data-descricao="<?php echo htmlspecialchars($rotina['Descricao']); ?>"
                                    data-icone="<?php echo htmlspecialchars($icone); ?>"
                                    data-hora="<?php echo $hora_formatada; ?>"
                                    data-dias="<?php echo htmlspecialchars($rotina['Dia_Semana']); ?>">
                                <i class="fas fa-edit"></i> Editar
                            </button>
                            <?php if ($rotina['Ativa']): ?>
                                <button class="btn btn-warning btn-toggle-status" 
                                        data-id="<?php echo $rotina['ID_Rotina']; ?>" 
                                        data-status="0">
                                    <i class="fas fa-pause"></i> Pausar
                                </button>
                            <?php else: ?>
                                <button class="btn btn-success btn-toggle-status" 
                                        data-id="<?php echo $rotina['ID_Rotina']; ?>" 
                                        data-status="1">
                                    <i class="fas fa-play"></i> Ativar
                                </button>
                            <?php endif; ?>
                            <button class="btn btn-danger btn-delete" 
                                    data-id="<?php echo $rotina['ID_Rotina']; ?>">
                                <i class="fas fa-trash"></i> Excluir
                            </button>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div id="routineModal" class="modal" style="display: none;">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <h2><i class="fas fa-plus"></i> Nova Rotina</h2>
            
            <form id="routineForm">
                <div class="form-group">
                    <label for="routineName">Nome da Rotina <span class="required">*</span></label>
                    <input type="text" id="routineName" name="routineName" class="form-control" placeholder="Ex: Acordar, Trabalho, Dormir" required>
                </div>
                
                <div class="form-group">
                    <label for="routineDescription">Descrição</label>
                    <textarea id="routineDescription" name="routineDescription" class="form-control" rows="3" placeholder="Descreva o que essa rotina faz..."></textarea>
                </div>
                
                <div class="form-group">
                    <label for="routineIcon">Ícone</label>
                    <select id="routineIcon" name="routineIcon" class="form-control">
                        <option value="fas fa-home">🏠 Casa</option>
                        <option value="fas fa-sun">☀️ Sol</option>
                        <option value="fas fa-moon">🌙 Lua</option>
                        <option value="fas fa-briefcase">💼 Trabalho</option>
                        <option value="fas fa-bed">🛏️ Dormir</option>
                        <option value="fas fa-utensils">🍽️ Refeição</option>
                        <option value="fas fa-tv">📺 Entretenimento</option>
                        <option value="fas fa-shield-alt">🛡️ Segurança</option>
                        <option value="fas fa-bell">🔔 Notificação</option>
                        <option value="fas fa-coffee">☕ Café</option>
                        <option value="fas fa-dumbbell">🏋️‍♂️ Exercício</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="routineTime">Horário <span class="required">*</span></label>
                    <input type="time" id="routineTime" name="routineTime" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label for="routineDays">Dias da Semana <span class="required">*</span></label>
                    <select id="routineDays" name="routineDays" class="form-control" multiple required>
                        <option value="1">Segunda-feira</option>
                        <option value="2">Terça-feira</option>
                        <option value="3">Quarta-feira</option>
                        <option value="4">Quinta-feira</option>
                        <option value="5">Sexta-feira</option>
                        <option value="6">Sábado</option>
                        <option value="0">Domingo</option>
                        <option value="7">Todos os dias</option>
                        <option value="1,2,3,4,5">Dias úteis (Seg-Sex)</option>
                        <option value="0,6">Fim de semana (Sáb-Dom)</option>
                    </select>
                    <small class="form-text text-muted">Segure Ctrl (ou Cmd no Mac) para selecionar múltiplos dias</small>
                </div>
                
                <div class="routine-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeModal()">
                        <i class="fas fa-times"></i> Cancelar
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Salvar Rotina
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div id="deleteConfirmationModal" class="modal" style="display: none;">
        <div class="modal-content" style="max-width: 500px;">
            <span class="close" onclick="closeDeleteModal()">&times;</span>
            <h2><i class="fas fa-exclamation-triangle text-warning"></i> Confirmar Exclusão</h2>
            <p>Tem certeza que deseja excluir a rotina <strong id="rotinaNomeExclusao"></strong>?</p>
            <p class="text-muted">Esta ação não pode ser desfeita.</p>
            <div class="d-flex justify-content-end gap-2 mt-4">
                <button type="button" class="btn btn-secondary" onclick="closeDeleteModal()">
                    <i class="fas fa-times"></i> Cancelar
                </button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn">
                    <i class="fas fa-trash"></i> Sim, Excluir
                </button>
            </div>
        </div>
    </div>

    <script>
        let modoEdicao = false;
        let rotinaAtualId = null;
        
        let rotinaParaExcluir = null;
        let rotinaParaExcluirNome = '';
        
        function openDeleteModal(id, nome) {
            rotinaParaExcluir = id;
            rotinaParaExcluirNome = nome;
            document.getElementById('rotinaNomeExclusao').textContent = nome;
            document.getElementById('deleteConfirmationModal').style.display = 'block';
        }
        
        function closeDeleteModal() {
            document.getElementById('deleteConfirmationModal').style.display = 'none';
            rotinaParaExcluir = null;
            rotinaParaExcluirNome = '';
        }
        
        function confirmarExclusao() {
            if (!rotinaParaExcluir) return;
            
            const formData = new FormData();
            formData.append('action', 'excluir');
            formData.append('id_rotina', rotinaParaExcluir);
            
            fetch('rotinas.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    closeDeleteModal();
                    carregarRotinas();
                } else {
                    return;
                }
            })
            .catch(error => {
                return;
            });
        }
        
        function openModal(edicao = false, dados = null) {
            const modal = document.getElementById('routineModal');
            const modalContent = document.querySelector('.modal-content');
            const titulo = document.querySelector('#routineModal h2');
            const form = document.getElementById('routineForm');
            
            if (edicao && dados) {
                titulo.innerHTML = '<i class="fas fa-edit"></i> Editar Rotina';
                modalContent.classList.add('editing');
                document.getElementById('routineName').value = dados.nome;
                document.getElementById('routineDescription').value = dados.descricao || '';
                document.getElementById('routineIcon').value = dados.icone || 'fas fa-home';
                document.getElementById('routineTime').value = dados.hora || '';
                
                const selectDias = document.getElementById('routineDays');
                for (let i = 0; i < selectDias.options.length; i++) {
                    selectDias.options[i].selected = false;
                }
                
                if (dados.dias) {
                    const diasArray = dados.dias.split(',');
                    diasArray.forEach(dia => {
                        for (let i = 0; i < selectDias.options.length; i++) {
                            if (selectDias.options[i].value === dia) {
                                selectDias.options[i].selected = true;
                            }
                        }
                    });
                }
                
                modoEdicao = true;
                rotinaAtualId = dados.id;
            } else {
                titulo.innerHTML = '<i class="fas fa-plus"></i> Nova Rotina';
                modalContent.classList.remove('editing');
                form.reset();
                modoEdicao = false;
                rotinaAtualId = null;
            }
            
            modal.style.display = 'block';
        }
        
        function closeModal() {
            const modal = document.getElementById('routineModal');
            const modalContent = document.querySelector('.modal-content');
            
            modal.style.display = 'none';
            document.getElementById('routineForm').reset();
            modalContent.classList.remove('editing');
            modoEdicao = false;
            rotinaAtualId = null;
        }
        
        function carregarRotinas() {
            fetch('rotinas.php')
                .then(response => response.text())
                .then(html => {
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');
                    const rotinasGrid = doc.getElementById('routinesGrid');
                    if (rotinasGrid) {
                        document.getElementById('routinesGrid').innerHTML = rotinasGrid.innerHTML;
                        adicionarEventosBotoes();
                    }
                })
                .catch(error => {
                    console.error('Erro ao carregar rotinas:', error);
                    alert('Erro ao carregar as rotinas. Por favor, recarregue a página.');
                });
        }
        
        function adicionarEventosBotoes() {
            document.querySelectorAll('.btn-edit').forEach(btn => {
                btn.addEventListener('click', function() {
                    const card = this.closest('.routine-card');
                    const dados = {
                        id: this.dataset.id,
                        nome: this.dataset.nome,
                        descricao: this.dataset.descricao,
                        icone: this.dataset.icone,
                        hora: this.dataset.hora,
                        dias: this.dataset.dias
                    };
                    openModal(true, dados);
                });
            });
            
            document.querySelectorAll('.btn-toggle-status').forEach(btn => {
                btn.addEventListener('click', function() {
                    const id = this.dataset.id;
                    const status = this.dataset.status;
                    
                    const formData = new FormData();
                    formData.append('action', 'atualizar_status');
                    formData.append('id_rotina', id);
                    formData.append('status', status);
                    
                    fetch('rotinas.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            carregarRotinas();
                        } else {
                            alert('Erro: ' + (data.message || 'Falha ao atualizar status da rotina'));
                        }
                    })
                    .catch(error => {
                        console.error('Erro:', error);
                        alert('Erro ao processar a solicitação. Tente novamente.');
                    });
                });
            });
            
            document.querySelectorAll('.btn-delete').forEach(btn => {
                btn.addEventListener('click', function() {
                    const id = this.dataset.id;
                    const card = this.closest('.routine-card') || this.closest('.card');
                    const titulo = card.querySelector('.card-title') || card.querySelector('h3');
                    const nome = titulo ? titulo.textContent.trim() : 'esta rotina';
                    openDeleteModal(id, nome);
                });
            });
            
            document.getElementById('confirmDeleteBtn').addEventListener('click', confirmarExclusao);
        }
        
        document.getElementById('routineForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const nome = document.getElementById('routineName').value.trim();
            const hora = document.getElementById('routineTime').value;
            const descricao = document.getElementById('routineDescription').value.trim();
            let icone = document.getElementById('routineIcon').value;
            
            if (!icone) {
                icone = 'fas fa-home';
            }
            
            if (!nome || !hora) {
                alert('Por favor, preencha todos os campos obrigatórios.');
                return;
            }
            
            const formData = new FormData();
            formData.append('action', 'salvar');
            formData.append('nome', nome);
            formData.append('hora', hora);
            formData.append('descricao', descricao);
            formData.append('icone', icone);
            
            if (modoEdicao && rotinaAtualId) {
                formData.append('id_rotina', rotinaAtualId);
            }
            
            const diasSelecionados = [];
            const selectDias = document.getElementById('routineDays');
            for (let i = 0; i < selectDias.options.length; i++) {
                if (selectDias.options[i].selected) {
                    diasSelecionados.push(selectDias.options[i].value);
                }
            }
            formData.append('dia_semana', diasSelecionados.length > 0 ? diasSelecionados.join(',') : '1,2,3,4,5,6,0');
            
            fetch('rotinas.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    closeModal();
                    carregarRotinas();
                } else {
                    return;
                }
            })
            .catch(error => {
                return;
            });
        });
        
        window.onclick = function(event) {
            const modal = document.getElementById('routineModal');
            const deleteModal = document.getElementById('deleteConfirmationModal');
            
            if (event.target == modal) {
                closeModal();
            }
            
            if (event.target == deleteModal) {
                closeDeleteModal();
            }
        };
        
        document.addEventListener('DOMContentLoaded', function() {
            adicionarEventosBotoes();
            document.querySelector('.add-routine-btn').addEventListener('click', function() {
                openModal();
            });
        });
    </script>
</body>
</html>

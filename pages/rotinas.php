<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit;
}

// Check if database connection is available
if ($conn === null) {
    // For testing without database - use session data
    $user = [
        'username' => $_SESSION['username'] ?? 'admin',
        'email' => $_SESSION['email'] ?? 'admin@exemplo.com',
        'created_at' => '2024-01-01 00:00:00',
        'updated_at' => date('Y-m-d H:i:s'),
        'user_type' => 'admin'
    ];
    
    // Format dates
    $created_at = new DateTime($user['created_at']);
    $updated_at = new DateTime($user['updated_at']);
} else {
    try {
        // Fetch complete user information
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
            // If user not found, destroy session and redirect to login
            session_destroy();
            header('Location: ../index.php');
            exit;
        }
        
        // Format dates
        $created_at = new DateTime($user['created_at']);
        $updated_at = new DateTime($user['updated_at']);
        
    } catch (Exception $e) {
        error_log("Error fetching user data: " . $e->getMessage());
        // On error, destroy session and redirect to login
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
            background: #4a90e2;
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            transition: background 0.3s;
            margin-right: 1rem;
        }
        
        .back-btn:hover {
            background: #357abd;
        }
        
        .logout-btn {
            background: #e74c3c;
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            transition: background 0.3s;
        }
        
        .logout-btn:hover {
            background: #c0392b;
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
            font-size: 0.9rem;
        }
        
        .routine-actions {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }
        
        .btn {
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 0.9rem;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .btn-primary {
            background: #4a90e2;
            color: white;
        }
        
        .btn-primary:hover {
            background: #357abd;
        }
        
        .btn-success {
            background: #28a745;
            color: white;
        }
        
        .btn-success:hover {
            background: #218838;
        }
        
        .btn-warning {
            background: #ffc107;
            color: #212529;
        }
        
        .btn-warning:hover {
            background: #e0a800;
        }
        
        .btn-danger {
            background: #dc3545;
            color: white;
        }
        
        .btn-danger:hover {
            background: #c82333;
        }
        
        .add-routine-btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 1rem 2rem;
            border-radius: 25px;
            cursor: pointer;
            font-size: 1rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
            margin-bottom: 2rem;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .add-routine-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
        }
        
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
        }
        
        .modal-content {
            background-color: white;
            margin: 5% auto;
            padding: 2rem;
            border-radius: 10px;
            width: 90%;
            max-width: 600px;
            max-height: 80vh;
            overflow-y: auto;
        }
        
        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }
        
        .close:hover {
            color: #000;
        }
        
        .form-group {
            margin-bottom: 1rem;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: #333;
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
        }
        
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #4a90e2;
        }
        
        @media (max-width: 768px) {
            .container {
                padding: 0 1rem;
            }
            
            .routines-grid {
                grid-template-columns: 1fr;
            }
            
            .routine-actions {
                justify-content: center;
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
                <a href="dashboard.php" class="back-btn">
                    <i class="fas fa-arrow-left"></i> Voltar ao Dashboard
                </a>
                <span class="user-name">
                    <i class="fas fa-user"></i> <?php echo htmlspecialchars($username); ?>
                </span>
                <a href="../auth/logout.php" class="logout-btn">
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
                <div class="routine-card">
                    <div class="routine-header">
                        <div class="routine-title">
                            <i class="fas fa-sun"></i>
                            Rotina Matinal
                        </div>
                        <span class="routine-status status-active">Ativa</span>
                    </div>
                    <div class="routine-description">
                        Acende as luzes da sala e cozinha, ajusta temperatura para 22°C e abre as cortinas automaticamente.
                    </div>
                    <div class="routine-schedule">
                        <i class="fas fa-clock"></i> Todos os dias às 07:00
                    </div>
                    <div class="routine-actions">
                        <button class="btn btn-primary">
                            <i class="fas fa-edit"></i> Editar
                        </button>
                        <button class="btn btn-warning">
                            <i class="fas fa-pause"></i> Pausar
                        </button>
                        <button class="btn btn-danger">
                            <i class="fas fa-trash"></i> Excluir
                        </button>
                    </div>
                </div>

                <div class="routine-card">
                    <div class="routine-header">
                        <div class="routine-title">
                            <i class="fas fa-moon"></i>
                            Rotina Noturna
                        </div>
                        <span class="routine-status status-active">Ativa</span>
                    </div>
                    <div class="routine-description">
                        Apaga todas as luzes, ativa sistema de segurança e reduz temperatura para 18°C.
                    </div>
                    <div class="routine-schedule">
                        <i class="fas fa-clock"></i> Todos os dias às 23:00
                    </div>
                    <div class="routine-actions">
                        <button class="btn btn-primary">
                            <i class="fas fa-edit"></i> Editar
                        </button>
                        <button class="btn btn-warning">
                            <i class="fas fa-pause"></i> Pausar
                        </button>
                        <button class="btn btn-danger">
                            <i class="fas fa-trash"></i> Excluir
                        </button>
                    </div>
                </div>

                <div class="routine-card">
                    <div class="routine-header">
                        <div class="routine-title">
                            <i class="fas fa-briefcase"></i>
                            Saída para Trabalho
                        </div>
                        <span class="routine-status status-inactive">Inativa</span>
                    </div>
                    <div class="routine-description">
                        Apaga todas as luzes, ativa modo econômico no ar condicionado e ativa sistema de segurança.
                    </div>
                    <div class="routine-schedule">
                        <i class="fas fa-clock"></i> Segunda a Sexta às 08:30
                    </div>
                    <div class="routine-actions">
                        <button class="btn btn-primary">
                            <i class="fas fa-edit"></i> Editar
                        </button>
                        <button class="btn btn-success">
                            <i class="fas fa-play"></i> Ativar
                        </button>
                        <button class="btn btn-danger">
                            <i class="fas fa-trash"></i> Excluir
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="routineModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <h2><i class="fas fa-plus"></i> Nova Rotina</h2>
            
            <form id="routineForm">
                <div class="form-group">
                    <label for="routineName">Nome da Rotina:</label>
                    <input type="text" id="routineName" name="routineName" required>
                </div>
                
                <div class="form-group">
                    <label for="routineDescription">Descrição:</label>
                    <textarea id="routineDescription" name="routineDescription" rows="3" required></textarea>
                </div>
                
                <div class="form-group">
                    <label for="routineIcon">Ícone:</label>
                    <select id="routineIcon" name="routineIcon">
                        <option value="fas fa-home">🏠 Casa</option>
                        <option value="fas fa-sun">☀️ Sol</option>
                        <option value="fas fa-moon">🌙 Lua</option>
                        <option value="fas fa-briefcase">💼 Trabalho</option>
                        <option value="fas fa-bed">🛏️ Dormir</option>
                        <option value="fas fa-utensils">🍽️ Refeição</option>
                        <option value="fas fa-tv">📺 Entretenimento</option>
                        <option value="fas fa-shield-alt">🛡️ Segurança</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="routineTime">Horário:</label>
                    <input type="time" id="routineTime" name="routineTime" required>
                </div>
                
                <div class="form-group">
                    <label for="routineDays">Dias da Semana:</label>
                    <select id="routineDays" name="routineDays" multiple>
                        <option value="1">Segunda-feira</option>
                        <option value="2">Terça-feira</option>
                        <option value="3">Quarta-feira</option>
                        <option value="4">Quinta-feira</option>
                        <option value="5">Sexta-feira</option>
                        <option value="6">Sábado</option>
                        <option value="0">Domingo</option>
                    </select>
                </div>
                
                <div class="routine-actions">
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save"></i> Salvar Rotina
                    </button>
                    <button type="button" class="btn btn-danger" onclick="closeModal()">
                        <i class="fas fa-times"></i> Cancelar
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openModal() {
            document.getElementById('routineModal').style.display = 'block';
        }
        
        function closeModal() {
            document.getElementById('routineModal').style.display = 'none';
            document.getElementById('routineForm').reset();
        }
        
        window.onclick = function(event) {
            const modal = document.getElementById('routineModal');
            if (event.target == modal) {
                closeModal();
            }
        }
        
        document.getElementById('routineForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            alert('Funcionalidade em desenvolvimento. Rotina será salva em breve!');
            closeModal();
        });
        
        document.addEventListener('DOMContentLoaded', function() {
            const buttons = document.querySelectorAll('.routine-actions .btn');
            buttons.forEach(button => {
                button.addEventListener('click', function() {
                    const action = this.textContent.trim();
                    if (action.includes('Editar')) {
                        alert('Funcionalidade de edição em desenvolvimento!');
                    } else if (action.includes('Pausar') || action.includes('Ativar')) {
                        alert('Funcionalidade de ativação/pausa em desenvolvimento!');
                    } else if (action.includes('Excluir')) {
                        if (confirm('Tem certeza que deseja excluir esta rotina?')) {
                            alert('Funcionalidade de exclusão em desenvolvimento!');
                        }
                    }
                });
            });
        });
    </script>
</body>
</html>

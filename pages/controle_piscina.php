<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit;
}

if ($conn === null) {
    $user = [
        'username' => $_SESSION['username'] ?? 'admin',
        'email' => $_SESSION['email'] ?? 'admin@domx.com',
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
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Controle da Piscina</title>
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
            background: linear-gradient(135deg, #0a0f2c 0%, #0a0f2c 100%);
            color: #ffffff;
        }
        
        .container {
            max-width: 500px;
            margin: 1rem auto 0;
            padding: 20px;
        }
        
        header {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            color: #333;
            padding: 15px 0;
            box-shadow: 0 2px 20px rgba(0, 0, 0, 0.1);
        }
        
        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 20px;
        }
        
        .logo {
            font-size: 24px;
            font-weight: bold;
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .logo img {
            height: 50px;
            width: auto;
        }
        
        .user-menu {
            display: flex;
            align-items: center;
            gap: 20px;
            position: relative;
        }
        
        .user-menu a {
            color: #000;
            text-decoration: none;
            font-size: 14px;
        }
        
        .user-menu a:hover {
            text-decoration: underline;
        }
        
        .user-dropdown {
            position: relative;
            display: inline-block;
        }
        
        .user-name {
            cursor: pointer;
            padding: 8px 12px;
            border-radius: 4px;
            transition: background-color 0.3s;
        }
        
        .user-name:hover {
            background-color: rgba(0, 0, 0, 0.1);
        }
        
        .dropdown-content {
            display: none;
            position: absolute;
            right: 0;
            background-color: white;
            min-width: 200px;
            box-shadow: 0 8px 16px rgba(0,0,0,0.2);
            border-radius: 8px;
            z-index: 1000;
            border: 1px solid #ddd;
            margin-top: 5px;
        }
        
        .dropdown-content.show {
            display: block;
        }
        
        .user-info {
            padding: 15px;
            border-bottom: 1px solid #2c3e50;
            min-width: 250px;
        }
        
        .user-info h4 {
            margin: 0 0 10px 0;
            color: #000;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .user-info p {
            margin: 8px 0;
            font-size: 0.9em;
            color: #000;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .user-info i {
            width: 18px;
            text-align: center;
            color: #000;
        }
        
        .user-info p:last-child {
            padding-top: 8px;
            border-top: 1px solid #2c3e50;
            color: #95a5a6;
        }
        
        .card {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
            width: 100%;
            margin: 0 auto;
            text-align: center;
        }
        
        .card h2 {
            color: #2c3e50;
            margin-bottom: 20px;
            font-size: 24px;
        }
        
        .gate-control {
            margin: 10px 0 0 0;
            padding: 0;
        }
        
        .gate-status {
            font-size: 24px;
            color: rgb(0, 0, 0);
            margin-bottom: 15px;
            font-weight: bold;
        }
        
        .control-buttons {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 15px;
            margin: 20px 0 10px 0;
            width: 100%;
        }
        
        .btn i {
            font-size: 24px;
        }
        
        .btn {
            padding: 18px 30px;
            border: none;
            border-radius: 10px;
            font-size: 18px;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            width: 100%;
            max-width: 300px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        .btn-open {
            background-color: #2ecc71;
            color: white;
        }
        
        .btn-close {
            background-color: #e74c3c;
            color: white;
            opacity: 0.6;
            cursor: not-allowed;
        }
        
        .btn-open:hover {
            background-color: #27ae60;
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(46, 204, 113, 0.3);
        }
        
        .btn-close:hover {
            background-color: #c0392b;
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(231, 76, 60, 0.3);
        }
        
        .btn:active {
            transform: translateY(0);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        
        .btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }
        
        .control-btn {
            background: #2ecc71;
            color: white;
            border: none;
            padding: 15px 30px;
            font-size: 18px;
            border-radius: 8px;
            cursor: pointer;
            margin: 10px;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }
        
        .control-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }
        
        .control-btn:active {
            transform: translateY(0);
        }
        
        .control-btn.off {
            background: #e74c3c;
        }
        
        .back-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            background-color: #3498db;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.3s ea
            width: fit-content;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        .back-btn:hover {
            background-color: #2980b9;
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(52, 152, 219, 0.3);
            color: white;
        }
        
        .back-btn:active {
            transform: translateY(0);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        
        .logout-btn {
            background-color: #e74c3c;
            color: white;
            border: none;
            padding: 0.6rem 1.5rem;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
        }
        
        .logout-btn:hover {
            background-color: #c0392b;
            transform: translateY(-2px);
        }
        
        @media (max-width: 768px) {            
            .header-content {
                flex-direction: column;
                gap: 10px;
            }
            
            .user-menu {
                margin-top: 10px;
            }
        }
    </style>
</head>
<body>
    <header>
        <div class="header-content">
            <div class="logo">
                <img src="../assets/img/logo.png" alt="Logo">
                DOMX - Automação Residencial
            </div>
            <div class="user-menu">
                <div class="user-dropdown">
                    <span class="user-name" onclick="toggleDropdown()">
                        <i class="fas fa-user"></i> <?php echo htmlspecialchars($_SESSION['username']); ?>
                        <i class="fas fa-chevron-down" style="margin-left: 5px; font-size: 12px;"></i>
                    </span>
                    <div class="dropdown-content" id="userDropdown">
                        <div class="user-info">
                            <h4><i class="fas fa-user"></i> <?php echo htmlspecialchars($user['username']); ?></h4>
                            <p><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($user['email']); ?></p>
                            <p><i class="fas fa-user-tag"></i> <?php echo htmlspecialchars($user['user_type']); ?></p>
                            <p><i class="far fa-calendar-plus"></i> Criado em: <?php echo $created_at->format('d/m/Y'); ?></p>
                            <p><i class="fas fa-sync-alt"></i> Atualizado: <?php echo $updated_at->format('d/m/Y H:i'); ?></p>
                        </div>
                    </div>
                </div>
                <a href="dashboard.php" class="back-btn" style="text-decoration: none; display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.6rem 1.5rem; border-radius: 25px; background: linear-gradient(135deg, #4a90e2 0%, #3a7bc8 100%); color: white; font-size: 14px; transition: all 0.4s ease-in-out; position: relative; overflow: hidden; box-shadow: 0 4px 15px rgba(74, 144, 226, 0.3);">
                    <i class="fas fa-arrow-left"></i> Voltar
                </a>
                <form action="../auth/logout.php" method="post" style="display: inline-block; margin-left: 8px;">
                    <button type="submit" class="logout-btn" style="background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%); color: white; border: none; padding: 0.6rem 1.5rem; border-radius: 25px; cursor: pointer; font-size: 14px; transition: all 0.4s ease-in-out; position: relative; overflow: hidden; box-shadow: 0 4px 15px rgba(231, 76, 60, 0.3); display: flex; align-items: center; gap: 0.5rem;">
                        <i class="fas fa-sign-out-alt"></i> Sair
                    </button>
                </form>
            </div>
        </div>
    </header>
    
    <div class="container">
        <div class="card">
            <div class="gate-control">
                <div class="gate-status" id="poolStatus">STATUS: DESLIGADO</div>
                <div class="control-buttons">
                    <button class="btn btn-open" id="turnOnPool">
                        <i class="fas fa-swimming-pool"></i> Ligar Piscina
                    </button>
                    <button class="btn btn-close" id="turnOffPool">
                        <i class="fas fa-power-off"></i> Desligar Piscina
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const turnOnBtn = document.getElementById('turnOnPool');
            const turnOffBtn = document.getElementById('turnOffPool');
            const poolStatus = document.getElementById('poolStatus');
            let isPoolOn = false;
            
            // Função para atualizar o estado dos botões
            function updateButtonStates() {
                turnOnBtn.disabled = isPoolOn;
                turnOffBtn.disabled = !isPoolOn;
                turnOnBtn.style.opacity = isPoolOn ? '0.6' : '1';
                turnOffBtn.style.opacity = isPoolOn ? '1' : '0.6';
                turnOnBtn.style.cursor = isPoolOn ? 'not-allowed' : 'pointer';
                turnOffBtn.style.cursor = isPoolOn ? 'pointer' : 'not-allowed';
                poolStatus.textContent = `STATUS: ${isPoolOn ? 'LIGADO' : 'DESLIGADO'}`;
                poolStatus.style.color = isPoolOn ? '#27ae60' : '#e74c3c';
            }
            
            // Evento para ligar a piscina
            turnOnBtn.addEventListener('click', function() {
                if (!isPoolOn) {
                    isPoolOn = true;
                    updateButtonStates();
                    
                    // Envia comando para ligar a piscina
                    fetch('../includes/control_pool.php?action=on')
                        .then(response => response.json())
                        .then(data => {
                            if (!data.success) {
                                alert('Erro ao ligar a piscina: ' + (data.message || 'Erro desconhecido'));
                                isPoolOn = false;
                                updateButtonStates();
                            }
                        })
                        .catch(error => {
                            console.error('Erro:', error);
                            alert('Erro ao se comunicar com o servidor');
                            isPoolOn = false;
                            updateButtonStates();
                        });
                }
            });
            
            // Evento para desligar a piscina
            turnOffBtn.addEventListener('click', function() {
                if (isPoolOn) {
                    isPoolOn = false;
                    updateButtonStates();
                    
                    // Envia comando para desligar a piscina
                    fetch('../includes/control_pool.php?action=off')
                        .then(response => response.json())
                        .then(data => {
                            if (!data.success) {
                                alert('Erro ao desligar a piscina: ' + (data.message || 'Erro desconhecido'));
                                isPoolOn = true;
                                updateButtonStates();
                            }
                        })
                        .catch(error => {
                            console.error('Erro:', error);
                            alert('Erro ao se comunicar com o servidor');
                            isPoolOn = true;
                            updateButtonStates();
                        });
                }
            });
            
            // Estado inicial
            updateButtonStates();
        });
        
        function toggleDropdown() {
            const dropdown = document.getElementById('userDropdown');
            dropdown.classList.toggle('show');
        }
        
        window.onclick = function(event) {
            if (!event.target.matches('.user-name') && !event.target.closest('.user-name')) {
                const dropdown = document.getElementById('userDropdown');
                if (dropdown.classList.contains('show')) {
                    dropdown.classList.remove('show');
                }
            }
        }
    </script>
</body>
</html>

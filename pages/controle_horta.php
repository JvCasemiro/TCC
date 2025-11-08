<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit;
}

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
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Controle da Horta</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="icon" type="image/png" href="../assets/img/logo_domx_sem_nome.png">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background-color: #f5f7fa;
            color: #333;
            line-height: 1.6;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }
        
        .card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            padding: 2rem;
            margin-bottom: 2rem;
            text-align: center;
        }
        
        h2 {
            color: #2c3e50;
            margin-bottom: 1.5rem;
            font-size: 1.8rem;
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
            transition: all 0.3s ease;
            margin: 20px auto 0;
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
        
        /* Header styles */
        header {
            background-color: #2c3e50;
            color: white;
            padding: 1rem 0;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }
        
        .header-content {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 2rem;
        }
        
        .logo img {
            height: 40px;
        }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 1rem;
            position: relative;
        }
        
        .user-name {
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .user-dropdown {
            display: none;
            position: absolute;
            top: 100%;
            right: 0;
            background-color: white;
            min-width: 200px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            border-radius: 8px;
            overflow: hidden;
            z-index: 1000;
            margin-top: 10px;
        }
        
        .user-dropdown.show {
            display: block;
        }
        
        .dropdown-item {
            padding: 0.75rem 1rem;
            color: #333;
            text-decoration: none;
            display: block;
            transition: background-color 0.2s;
        }
        
        .dropdown-item:hover {
            background-color: #f5f5f5;
        }
        
        .dropdown-divider {
            height: 1px;
            background-color: #eee;
            margin: 0;
        }
        
        .logout-btn {
            background-color: #e74c3c;
            color: white;
            border: none;
            padding: 0.6rem 1.5rem;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            transition: background-color 0.2s;
        }
        
        .logout-btn:hover {
            background-color: #c0392b;
        }
        
        @media (max-width: 768px) {
            .container {
                padding: 1rem;
            }
            
            .card {
                padding: 1.5rem;
            }
            
            h2 {
                font-size: 1.5rem;
            }
            
            .btn {
                padding: 15px 20px;
                font-size: 16px;
            }
        }
    </style>
</head>
<body>
    <header>
        <div class="header-content">
            <div class="logo">
                <img src="../assets/img/logo.png" alt="Logo">
            </div>
            <div class="user-info">
                <span class="user-name" onclick="toggleDropdown()">
                    <?php echo htmlspecialchars($user['username']); ?>
                    <i class="fas fa-chevron-down"></i>
                </span>
                <div class="user-dropdown" id="userDropdown">
                    <div class="dropdown-item">
                        <i class="fas fa-user"></i> <?php echo htmlspecialchars($user['email']); ?>
                    </div>
                    <div class="dropdown-item">
                        <i class="fas fa-calendar-plus"></i> Membro desde: <?php echo $created_at->format('d/m/Y'); ?>
                    </div>
                    <div class="dropdown-divider"></div>
                    <a href="atualizar_usuario.php" class="dropdown-item">
                        <i class="fas fa-user-edit"></i> Editar Perfil
                    </a>
                    <a href="dashboard.php" class="dropdown-item">
                        <i class="fas fa-tachometer-alt"></i> Painel de Controle
                    </a>
                    <div class="dropdown-divider"></div>
                    <form action="../auth/logout.php" method="post" style="display: contents;">
                        <button type="submit" class="dropdown-item" style="width: 100%; text-align: left; background: none; border: none; cursor: pointer; font-size: 1rem; color: #333; padding: 0.75rem 1rem;">
                            <i class="fas fa-sign-out-alt"></i> Sair
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </header>
    
    <div class="container">
        <div class="card">
            <h2>Controle da Irrigação da Horta</h2>
            <div class="gate-control">
                <div class="gate-status" id="gardenStatus">STATUS: DESLIGADO</div>
                <div class="control-buttons">
                    <button class="btn btn-open" id="turnOnGarden">
                        <i class="fas fa-tint"></i> Ligar Irrigação
                    </button>
                    <button class="btn btn-close" id="turnOffGarden">
                        <i class="fas fa-power-off"></i> Desligar Irrigação
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const turnOnBtn = document.getElementById('turnOnGarden');
            const turnOffBtn = document.getElementById('turnOffGarden');
            const gardenStatus = document.getElementById('gardenStatus');
            let isGardenOn = false;
            
            // Função para atualizar o estado dos botões
            function updateButtonStates() {
                turnOnBtn.disabled = isGardenOn;
                turnOffBtn.disabled = !isGardenOn;
                turnOnBtn.style.opacity = isGardenOn ? '0.6' : '1';
                turnOffBtn.style.opacity = isGardenOn ? '1' : '0.6';
                turnOnBtn.style.cursor = isGardenOn ? 'not-allowed' : 'pointer';
                turnOffBtn.style.cursor = isGardenOn ? 'pointer' : 'not-allowed';
                gardenStatus.textContent = `STATUS: ${isGardenOn ? 'LIGADO' : 'DESLIGADO'}`;
                gardenStatus.style.color = isGardenOn ? '#27ae60' : '#e74c3c';
            }
            
            // Evento para ligar a irrigação
            turnOnBtn.addEventListener('click', function() {
                if (!isGardenOn) {
                    isGardenOn = true;
                    updateButtonStates();
                    
                    // Envia comando para ligar a irrigação
                    fetch('../includes/control_garden.php?action=on')
                        .then(response => response.json())
                        .then(data => {
                            if (!data.success) {
                                alert('Erro ao ligar a irrigação: ' + (data.message || 'Erro desconhecido'));
                                isGardenOn = false;
                                updateButtonStates();
                            }
                        })
                        .catch(error => {
                            console.error('Erro:', error);
                            alert('Erro ao se comunicar com o servidor');
                            isGardenOn = false;
                            updateButtonStates();
                        });
                }
            });
            
            // Evento para desligar a irrigação
            turnOffBtn.addEventListener('click', function() {
                if (isGardenOn) {
                    isGardenOn = false;
                    updateButtonStates();
                    
                    // Envia comando para desligar a irrigação
                    fetch('../includes/control_garden.php?action=off')
                        .then(response => response.json())
                        .then(data => {
                            if (!data.success) {
                                alert('Erro ao desligar a irrigação: ' + (data.message || 'Erro desconhecido'));
                                isGardenOn = true;
                                updateButtonStates();
                            }
                        })
                        .catch(error => {
                            console.error('Erro:', error);
                            alert('Erro ao se comunicar com o servidor');
                            isGardenOn = true;
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

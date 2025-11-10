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
            background: linear-gradient(135deg, #0a0f2c 0%, #0a0f2c 100%);
            color: #ffffff;
        }
        
        .container {
            max-width: 500px;
            margin: 1rem auto 0;
            padding: 20px;
        }
        
        .card {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 15px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
            padding: 2.5rem;
            margin: 2rem 0;
            text-align: center;
            border: 1px solid rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
        }
        
        h2 {
            color: #ffffff;
            margin-bottom: 1.5rem;
            font-size: 2rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .gate-control {
            margin: 10px 0 0 0;
            padding: 0;
        }
        
        .gate-status {
            font-size: 28px;
            color: #ffffff;
            margin: 20px 0;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
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
            border-radius: 12px;
            font-size: 18px;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            width: 100%;
            max-width: 300px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
            margin: 10px auto;
            color: white;
            position: relative;
            overflow: hidden;
        }
        
        .btn-open {
            background: linear-gradient(135deg, #2ecc71 0%, #27ae60 100%);
            color: white;
            border: 2px solid rgba(255, 255, 255, 0.1);
        }
        
        .btn-close {
            background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
            color: white;
            opacity: 0.6;
            cursor: not-allowed;
            border: 2px solid rgba(255, 255, 255, 0.1);
        }
        
        .btn-open:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 24px rgba(46, 204, 113, 0.3);
        }
        
        .btn-close:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 24px rgba(231, 76, 60, 0.3);
        }
        
        .btn:active {
            transform: translateY(1px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        
        .btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }
        
        .back-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.6rem 1.5rem;
            border-radius: 25px;
            background: linear-gradient(135deg, #4a90e2 0%, #3a7bc8 100%);
            color: white;
            font-size: 14px;
            text-decoration: none;
            transition: all 0.3s ease;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        .back-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(74, 144, 226, 0.3);
        }
        
        .logout-btn {
            background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
            color: white;
            border: none;
            padding: 0.6rem 1.5rem;
            border-radius: 25px;
            cursor: pointer;
            font-size: 14px;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        .logout-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(231, 76, 60, 0.3);
        }
        
        @media (max-width: 768px) {
            .header-content {
                flex-direction: column;
                gap: 10px;
                padding: 0 15px;
            }
            
            .user-menu {
                margin-top: 10px;
                width: 100%;
                justify-content: space-between;
                flex-wrap: wrap;
            }
            
            .user-dropdown {
                order: 1;
                width: 100%;
                margin-bottom: 10px;
            }
            
            .back-btn, .logout-btn {
                padding: 0.5rem 1rem;
                font-size: 13px;
            }
            
            .container {
                padding: 10px;
                margin-top: 0.5rem;
            }
            
            .card {
                padding: 1.5rem;
                margin: 1rem 0;
            }
            
            h2 {
                font-size: 1.5rem;
            }
            
            .btn {
                padding: 15px 20px;
                font-size: 16px;
                max-width: 100%;
            }
            
            .gate-status {
                font-size: 22px;
            }
        }
        
        /* Header styles */
        header {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            color: #333;
            padding: 15px 0;
            box-shadow: 0 2px 20px rgba(0, 0, 0, 0.1);
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
            max-width: 1200px;
            margin: 0 auto;
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
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .user-name:hover {
            background-color: rgba(0, 0, 0, 0.1);
        }
        
        .dropdown-content {
            display: none;
            position: absolute;
            right: 0;
            background-color: white;
            min-width: 250px;
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
            background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
            color: white;
            border: none;
            padding: 0.6rem 1.5rem;
            border-radius: 30px;
            cursor: pointer;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 8px 20px rgba(231, 76, 60, 0.3);
            border: 2px solid rgba(255, 255, 255, 0.1);
        }
        
        .logout-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 24px rgba(231, 76, 60, 0.4);
        }
        
        .logout-btn:active {
            transform: translateY(1px);
            box-shadow: 0 4px 8px rgba(231, 76, 60, 0.2);
        }
        
        @media (max-width: 768px) {
            .container {
                padding: 1rem;
                margin-top: 0.5rem;
            }
            
            .card {
                padding: 1.5rem;
                margin: 1rem 0;
            }
            
            h2 {
                font-size: 1.5rem;
            }
            
            .btn {
                padding: 15px 20px;
                font-size: 16px;
                max-width: 100%;
            }
            
            .gate-status {
                font-size: 22px;
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
                <a href="dashboard.php" class="back-btn">
                    <i class="fas fa-arrow-left"></i> Voltar
                </a>
                <form action="../auth/logout.php" method="post">
                    <button type="submit" class="logout-btn">
                        <i class="fas fa-sign-out-alt"></i> Sair
                    </button>
                </form>
            </div>
        </div>
    </header>
    <div class="container">
        <div class="card">
            <h2><i class="fas fa-seedling" style="margin-right: 10px;"></i> Controle da Irrigação da Horta</h2>
            <div class="gate-control">
                <div class="gate-status" id="gardenStatus">STATUS: DESLIGADO</div>
                <div class="control-buttons">
                    <button class="btn btn-open" id="turnOnGarden">
                        <i class="fas fa-tint"></i> LIGAR IRRIGAÇÃO
                    </button>
                    <button class="btn btn-close" id="turnOffGarden">
                        <i class="fas fa-power-off"></i> DESLIGAR IRRIGAÇÃO
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

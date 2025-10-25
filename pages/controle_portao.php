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
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel de Controle</title>
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
            padding: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
            width: 100%;
            margin: 0 auto;
            text-align: center;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            min-height: auto;
        }
        
        .card h1 {
            color: #4a90e2;
            margin-bottom: 15px;
            font-size: 24px;
        }
        
        .card p {
            color: #666;
            font-size: 16px;
            margin-bottom: 30px;
        }
        
        .logout-btn {
            background-color: #e74c3c;
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
            background-color: #c0392b;
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
        
        .gate-control {
            margin: 10px 0 0 0;
            padding: 0;
        }
        
        .gate-icon {
            font-size: 100px;
            color: #2c3e50;
            margin: 10px 0 20px 0;
            transition: all 0.3s ease;
        }
        
        .gate-status {
            font-size: 24px;
            color:rgb(0, 0, 0);
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
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            width: 100%;
            max-width: 280px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        
        .btn-open {
            background-color: #2ecc71;
            color: white;
            font-size: 18px;
            padding: 18px 30px;
        }
        
        .btn-close {
            background-color: #e74c3c;
            color: white;
            font-size: 18px;
            padding: 18px 30px;
        }
        
        .btn:hover {
            transform: translateY(-3px) scale(1.02);
            box-shadow: 0 8px 20px rgba(0,0,0,0.15);
        }
        
        .btn:active {
            transform: translateY(1px) scale(0.98);
        }
        
        .btn i {
            font-size: 18px;
        }
        
        .back-btn {
            background-color: #6c757d;
            color: white;
            border: none;
            padding: 0.6rem 1.5rem;
            border-radius: 25px;
            cursor: pointer;
            font-size: 14px;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            margin-right: 10px;
        }
        
        
        .back-btn:active {
            transform: translateY(0);
            box-shadow: 0 2px 10px rgba(108, 117, 125, 0.3);
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
        
        .back-btn {
            background-color: #4a90e2;
            color: white;
            border: none;
            padding: 0.6rem 1.5rem;
            border-radius: 25px;
            cursor: pointer;
            font-size: 14px;
            transition: all 0.4s ease-in-out;
            position: relative;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(108, 117, 125, 0.3);
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-right: 10px;
            text-decoration: none;
        }
        
        .control-btn {
            background: #4a90e2;
            color: white;
            border: none;
            padding: 15px 40px;
            border-radius: 30px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            text-transform: uppercase;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s ease;
            margin-top: 15px;
            box-shadow: 0 4px 0 rgba(0,0,0,0.1);
        }
        
        .control-btn:hover {
            background: #3a7bc8;
            transform: translateY(-2px);
            box-shadow: 0 6px 0 rgba(0,0,0,0.1);
        }
        
        .control-btn:active {
            transform: translateY(0);
            box-shadow: 0 2px 0 rgba(0,0,0,0.1);
        }
        
        @media (max-width: 768px) {            
            .header-content {
                flex-direction: column;
                gap: 10px;
            }
            
            .user-menu {
                margin-top: 10px;
                display: flex;
                flex-wrap: wrap;
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <header>
        <div class="header-content">
            <div class="logo">
                <img src="../assets/img/logo.png" alt="Logo">
                Automação Residencial
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
                <a href="dashboard.php" class="back-btn" style="text-decoration: none; display: inline-block; color: white;">
                    <i class="fas fa-arrow-left"></i> Voltar
                </a>
                <form action="../auth/logout.php" method="post" style="display: inline;">
                    <button type="submit" class="logout-btn">
                        <i class="fas fa-sign-out-alt"></i> Sair
                    </button>
                </form>
            </div>
        </div>
    </header>
    <div class="container">
        <div class="card">
            <h2>Controle do Portão</h2>
            <div class="gate-control">
                <div class="gate-status" id="gateStatus">STATUS: FECHADO</div>
                <div class="control-buttons">
                    <button class="btn btn-open" id="openGate">
                        <i class="fas fa-lock-open"></i> Abrir Portão
                    </button>
                    <button class="btn btn-close" id="closeGate">
                        <i class="fas fa-lock"></i> Fechar Portão
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const openBtn = document.getElementById('openGate');
            const closeBtn = document.getElementById('closeGate');
            const gateStatus = document.getElementById('gateStatus');
            let gateState = false;
            
            function updateButtonStates() {
                openBtn.disabled = gateState;
                closeBtn.disabled = !gateState;
                openBtn.style.opacity = gateState ? '0.6' : '1';
                closeBtn.style.opacity = gateState ? '1' : '0.6';
                openBtn.style.cursor = gateState ? 'not-allowed' : 'pointer';
                closeBtn.style.cursor = gateState ? 'pointer' : 'not-allowed';
            }
            
            function updateGateStatus(isOpen) {
                gateState = isOpen;
                if (isOpen) {
                    gateStatus.textContent = 'STATUS: ABERTO';
                    gateStatus.style.color = '#2ecc71';
                } else {
                    gateStatus.textContent = 'STATUS: FECHADO';
                    gateStatus.style.color = '#e74c3c';
                }
                updateButtonStates();
            }
            
            function sendGateCommand(action) {
                fetch('../control_gate.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ action: action })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        if (action === 'OPEN') {
                            updateGateStatus(true);
                            setTimeout(() => {
                                updateGateStatus(true);
                            }, 5000);
                        } else if (action === 'CLOSE') {
                            updateGateStatus(false);
                            setTimeout(() => {
                                updateGateStatus(false);
                            }, 5000);
                        }
                    } else {
                        alert('Erro ao enviar comando: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Erro:', error);
                    alert('Erro ao comunicar com o servidor');
                });
            }
            
            openBtn.addEventListener('click', function() {
                if (!gateState) { 
                    sendGateCommand('OPEN');
                }
            });
            
            closeBtn.addEventListener('click', function() {
                if (gateState) { 
                    sendGateCommand('CLOSE');
                }
            });
            
            updateGateStatus(false);
        });
        
        function toggleDropdown() {
            const dropdown = document.getElementById('userDropdown');
            if (dropdown) {
                dropdown.classList.toggle("show");
            }
        }
    </script>
    </body>
</html>
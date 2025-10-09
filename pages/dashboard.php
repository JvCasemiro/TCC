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
            max-width: 1200px;
            margin: 6.5rem auto;
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
            margin-top: 12px;
            padding-top: 8px;
            border-top: 1px solid #2c3e50;
            color: #95a5a6;
        }
        
        .welcome {
            background-color: white;
            border-radius: 8px;
            padding: 15px 20px;
            margin: 15px 0;
            text-align: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .welcome h1 {
            color: #2f3640;
            margin-bottom: 5px;
        }
        
        .welcome p {
            color: #666;
        }
        
        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-top: 30px;
        }
        
        .card {
            background: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            transition: transform 0.3s ease;
        }
        
        .card:hover {
            transform: translateY(-5px);
        }
        
        .card h3 {
            color: #4a90e2;
            margin-bottom: 15px;
        }
        
        .card p {
            color: #666;
            font-size: 14px;
            line-height: 1.6;
        }
        
        .icon {
            font-size: 36px;
            color: #4a90e2;
            margin-bottom: 15px;
        }
        
        .button-container {
            display: flex;
            gap: 10px;
            justify-content: space-between;
            width: 100%;
        }
        
        .button-container .control-btn {
            width: 48%;
            padding: 10px 5px;
            font-size: 13px;
            white-space: nowrap;
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
        
        .control-btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 25px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            transition: all 0.4s ease-in-out;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
            margin-top: 15px;
            width: 100%;
            position: relative;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0rem;
        }
        
        .control-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
            background: linear-gradient(135deg, #764ba2 0%, #667eea 100%);
        }
        
        .control-btn:active {
            transform: translateY(0);
            box-shadow: 0 2px 10px rgba(102, 126, 234, 0.3);
        }
        
        .control-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s;
        }
        
        .control-btn:hover::before {
            left: 100%;
        }
        
        .control-btn i {
            margin-right: 8px;
        }

        @media (max-width: 768px) {
            .dashboard-grid {
                grid-template-columns: 1fr;
            }
            
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
                <a href="menu.php" class="back-btn" style="text-decoration: none; display: inline-block; color: white;">
                    <i class="fas fa-arrow-left"></i> Voltar ao Menu
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
        <div class="welcome">
            <h1>Painel de Controle</h1>
            <p>Gerencie seus dispositivos de automação residencial de forma fácil e intuitiva.</p>
        </div>
        
        <div class="dashboard-grid">
            <div class="card">
                <div class="icon">
                    <i class="fas fa-lightbulb"></i>
                </div>
                <h3>Luzes</h3>
                <p>Controle todas as luzes da sua casa de forma remota.</p>
                <button class="control-btn" onclick="window.location.href='gerenciar_luzes.php'">
                    <i class="fas fa-cog"></i>
                    Gerenciar
                </button>
            </div>
            
            <div class="card">
                <div class="icon">
                    <i class="fas fa-thermometer-half"></i>
                </div>
                <h3>Temperatura</h3>
                <p>Ajuste a temperatura dos ambientes e programe horários.</p>
                <button class="control-btn" onclick="window.location.href='ajustar_temperatura.php'">
                    <i class="fas fa-sliders-h"></i>
                    Ajustar
                </button>
            </div>
            
            <div class="card">
                <div class="icon">
                    <i class="fas fa-shield-alt"></i>
                </div>
                <h3>Segurança</h3>
                <p>Monitore câmeras e sensores de segurança em tempo real.</p>
                <button class="control-btn" onclick="window.location.href='monitorar_seguranca.php'">
                    <i class="fas fa-eye"></i>
                    Verificar
                </button>
            </div>
            
            <div class="card">
                <div class="icon">
                    <i class="fas fa-car"></i>
                </div>
                <h3>Leitura de Placas</h3>
                <p>Gerencie o acesso de veículos com reconhecimento automático.</p>
                <div class="button-container">
                    <button class="control-btn" onclick="window.location.href='leitura_placas.php'">
                        <i class="fas fa-search"></i>
                        Monitorar
                    </button>
                    <button class="control-btn" onclick="window.location.href='controle_portao.php'">
                        <i class="fas fa-door-open"></i>
                        Controlar
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        function showMessage(message, type = 'info') {
            alert(message);
        }

        document.addEventListener('DOMContentLoaded', function() {
            const buttons = document.querySelectorAll('.control-btn');
            buttons.forEach(button => {
                if (!button.hasAttribute('onclick')) {
                    button.addEventListener('click', function() {
                        showMessage('Funcionalidade em desenvolvimento. Em breve disponível!', 'warning');
                    });
                }
            });
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

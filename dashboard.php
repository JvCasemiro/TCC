<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel de Controle - Automação Residencial</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background-color: #f5f6fa;
            color: #2f3640;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        header {
            background-color: #4a90e2;
            color: white;
            padding: 15px 0;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
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
        }
        
        .user-menu {
            display: flex;
            align-items: center;
            gap: 20px;
        }
        
        .user-menu a {
            color: white;
            text-decoration: none;
            font-size: 14px;
        }
        
        .user-menu a:hover {
            text-decoration: underline;
        }
        
        .welcome {
            background-color: white;
            border-radius: 8px;
            padding: 30px;
            margin: 30px 0;
            text-align: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .welcome h1 {
            color: #2f3640;
            margin-bottom: 15px;
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
        
        .logout-btn {
            background-color: #e74c3c;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            transition: background-color 0.3s;
        }
        
        .logout-btn:hover {
            background-color: #c0392b;
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
            <div class="logo">Automação Residencial</div>
            <div class="user-menu">
                <span>Olá, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
                <a href="profile.php">Perfil</a>
                <form action="logout.php" method="post" style="display: inline;">
                    <button type="submit" class="logout-btn">Sair</button>
                </form>
            </div>
        </div>
    </header>
    
    <div class="container">
        <div class="welcome">
            <h1>Bem-vindo ao Painel de Controle</h1>
            <p>Gerencie seus dispositivos de automação residencial de forma fácil e intuitiva.</p>
        </div>
        
        <div class="dashboard-grid">
            <div class="card">
                <div class="icon">
                    <i class="fas fa-lightbulb"></i>
                </div>
                <h3>Luzes</h3>
                <p>Controle todas as luzes da sua casa de forma remota.</p>
                <button class="control-btn">Gerenciar</button>
            </div>
            
            <div class="card">
                <div class="icon">
                    <i class="fas fa-thermometer-half"></i>
                </div>
                <h3>Temperatura</h3>
                <p>Ajuste a temperatura dos ambientes e programe horários.</p>
                <button class="control-btn">Ajustar</button>
            </div>
            
            <div class="card">
                <div class="icon">
                    <i class="fas fa-shield-alt"></i>
                </div>
                <h3>Segurança</h3>
                <p>Monitore câmeras e sensores de segurança em tempo real.</p>
                <button class="control-btn">Verificar</button>
            </div>
            
            <div class="card">
                <div class="icon">
                    <i class="fas fa-calendar-alt"></i>
                </div>
                <h3>Rotinas</h3>
                <p>Crie e gerencie rotinas automáticas para sua casa.</p>
                <button class="control-btn">Configurar</button>
            </div>
        </div>
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const buttons = document.querySelectorAll('.control-btn');
            buttons.forEach(button => {
                button.addEventListener('click', function() {
                    alert('Funcionalidade em desenvolvimento. Em breve disponível!');
                });
            });
        });
    </script>
</body>
</html>

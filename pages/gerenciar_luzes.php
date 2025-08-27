<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$user = [
    'username' => $_SESSION['username'],
    'email' => $_SESSION['email'],
    'user_type' => 'Administrador'
];
$created_at = new DateTime();
$updated_at = new DateTime();

$lights = [
    ['id' => 1, 'name' => 'Sala de Estar', 'room' => 'Sala', 'status' => 'on', 'brightness' => 80, 'color' => '#ffffff'],
    ['id' => 2, 'name' => 'Quarto Principal', 'room' => 'Quarto', 'status' => 'off', 'brightness' => 0, 'color' => '#ffffff'],
    ['id' => 3, 'name' => 'Cozinha', 'room' => 'Cozinha', 'status' => 'on', 'brightness' => 100, 'color' => '#ffffff'],
    ['id' => 4, 'name' => 'Banheiro', 'room' => 'Banheiro', 'status' => 'off', 'brightness' => 0, 'color' => '#ffffff'],
    ['id' => 5, 'name' => 'Varanda', 'room' => 'Externa', 'status' => 'on', 'brightness' => 60, 'color' => '#ffd700'],
];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Luzes - Automação Residencial</title>
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
            margin: 0 auto;
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
            color: black;
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
        
        .lights-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 30px;
        }
        
        .light-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .light-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.15);
        }
        
        .light-card.on {
            border-left: 5px solid #27ae60;
        }
        
        .light-card.off {
            border-left: 5px solid #e74c3c;
        }
        
        .light-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .light-info h3 {
            color: #2f3640;
            margin-bottom: 5px;
            font-size: 1.3em;
        }
        
        .light-info .room {
            color: #666;
            font-size: 0.9em;
        }
        
        .light-status {
            display: flex;
            align-items: center;
            gap: 8px;
            font-weight: 600;
        }
        
        .light-status.on {
            color: #27ae60;
        }
        
        .light-status.off {
            color: #e74c3c;
        }
        
        .light-controls {
            margin: 20px 0;
        }
        
        .control-group {
            margin-bottom: 15px;
        }
        
        .control-group label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 500;
            font-size: 0.9em;
        }
        
        .toggle-switch {
            position: relative;
            display: inline-block;
            width: 60px;
            height: 34px;
        }
        
        .toggle-switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }
        
        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ccc;
            transition: .4s;
            border-radius: 34px;
        }
        
        .slider:before {
            position: absolute;
            content: "";
            height: 26px;
            width: 26px;
            left: 4px;
            bottom: 4px;
            background-color: white;
            transition: .4s;
            border-radius: 50%;
        }
        
        input:checked + .slider {
            background-color: #4a90e2;
        }
        
        input:checked + .slider:before {
            transform: translateX(26px);
        }
        
        .brightness-control {
            width: 100%;
            margin: 10px 0;
        }
        
        .brightness-slider {
            width: 100%;
            height: 6px;
            border-radius: 3px;
            background: #ddd;
            outline: none;
            opacity: 0.7;
            transition: opacity 0.2s;
        }
        
        .brightness-slider:hover {
            opacity: 1;
        }
        
        .brightness-slider::-webkit-slider-thumb {
            appearance: none;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            background: #4a90e2;
            cursor: pointer;
        }
        
        .brightness-value {
            text-align: center;
            color: #666;
            font-size: 0.9em;
            margin-top: 5px;
        }
        
        .color-control {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .color-picker {
            width: 40px;
            height: 40px;
            border: none;
            border-radius: 50%;
            cursor: pointer;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }
        
        .color-name {
            color: #666;
            font-size: 0.9em;
        }
        
        .light-actions {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }
        
        .btn {
            padding: 8px 16px;
            border: none;
            border-radius: 20px;
            cursor: pointer;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
            flex: 1;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
        }
        
        .btn-secondary {
            background: #f8f9fa;
            color: #666;
            border: 1px solid #dee2e6;
        }
        
        .btn-secondary:hover {
            background: #e9ecef;
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
        
        .back-btn {
            background-color: #6c757d;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            transition: background-color 0.3s;
            margin-right: 10px;
        }
        
        .back-btn:hover {
            background-color: #5a6268;
        }

        @media (max-width: 768px) {
            .lights-grid {
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
                <a href="menu.php" class="back-btn" style="text-decoration: none; display: inline-block; color: white;">
                    <i class="fas fa-arrow-left"></i> Voltar ao Menu
                </a>
                <form action="../auth/logout.php" method="post" style="display: inline;">
                    <button type="submit" class="logout-btn">Sair</button>
                </form>
            </div>
        </div>
    </header>
    
    <div class="container">
        <div class="welcome">
            <h1>Gerenciamento de Luzes</h1>
            <p>Controle todas as luzes da sua casa de forma inteligente e personalizada.</p>
        </div>
        
        <div class="lights-grid">
            <?php foreach($lights as $light): ?>
            <div class="light-card <?php echo $light['status']; ?>" data-light-id="<?php echo $light['id']; ?>">
                <div class="light-header">
                    <div class="light-info">
                        <h3><?php echo htmlspecialchars($light['name']); ?></h3>
                        <div class="room"><?php echo htmlspecialchars($light['room']); ?></div>
                    </div>
                    <div class="light-status <?php echo $light['status']; ?>">
                        <i class="fas fa-lightbulb"></i>
                        <span><?php echo $light['status'] == 'on' ? 'Ligada' : 'Desligada'; ?></span>
                    </div>
                </div>
                
                <div class="light-controls">
                    <div class="control-group">
                        <label>Liga/Desliga</label>
                        <label class="toggle-switch">
                            <input type="checkbox" <?php echo $light['status'] == 'on' ? 'checked' : ''; ?> 
                                   onchange="toggleLight(<?php echo $light['id']; ?>)">
                            <span class="slider"></span>
                        </label>
                    </div>
                    
                    <div class="control-group">
                        <label>Intensidade</label>
                        <div class="brightness-control">
                            <input type="range" min="0" max="100" value="<?php echo $light['brightness']; ?>" 
                                   class="brightness-slider" id="brightness-<?php echo $light['id']; ?>"
                                   onchange="changeBrightness(<?php echo $light['id']; ?>, this.value)"
                                   <?php echo $light['status'] == 'off' ? 'disabled' : ''; ?>>
                            <div class="brightness-value" id="brightness-value-<?php echo $light['id']; ?>">
                                <?php echo $light['brightness']; ?>%
                            </div>
                        </div>
                    </div>
                    
                    <div class="control-group">
                        <label>Cor</label>
                        <div class="color-control">
                            <input type="color" value="<?php echo $light['color']; ?>" class="color-picker"
                                   onchange="changeColor(<?php echo $light['id']; ?>, this.value)"
                                   <?php echo $light['status'] == 'off' ? 'disabled' : ''; ?>>
                            <span class="color-name" id="color-name-<?php echo $light['id']; ?>">
                                <?php echo $light['color'] == '#ffffff' ? 'Branco' : 'Personalizada'; ?>
                            </span>
                        </div>
                    </div>
                </div>
                
                <div class="light-actions">
                    <button class="btn btn-primary" onclick="setPreset(<?php echo $light['id']; ?>, 'warm')">
                        <i class="fas fa-sun"></i> Quente
                    </button>
                    <button class="btn btn-primary" onclick="setPreset(<?php echo $light['id']; ?>, 'cool')">
                        <i class="fas fa-snowflake"></i> Fria
                    </button>
                    <button class="btn btn-secondary" onclick="scheduleLight(<?php echo $light['id']; ?>)">
                        <i class="fas fa-clock"></i> Agendar
                    </button>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    
    <script>
        function toggleLight(lightId) {
            const card = document.querySelector(`[data-light-id="${lightId}"]`);
            const statusElement = card.querySelector('.light-status');
            const statusText = statusElement.querySelector('span');
            const brightnessSlider = document.getElementById(`brightness-${lightId}`);
            const colorPicker = card.querySelector('.color-picker');
            
            const isOn = card.classList.contains('on');
            
            if (isOn) {
                card.classList.remove('on');
                card.classList.add('off');
                statusElement.classList.remove('on');
                statusElement.classList.add('off');
                statusText.textContent = 'Desligada';
                brightnessSlider.disabled = true;
                colorPicker.disabled = true;
            } else {
                card.classList.remove('off');
                card.classList.add('on');
                statusElement.classList.remove('off');
                statusElement.classList.add('on');
                statusText.textContent = 'Ligada';
                brightnessSlider.disabled = false;
                colorPicker.disabled = false;
            }
            
            showMessage(`Luz ${isOn ? 'desligada' : 'ligada'} com sucesso!`, 'success');
        }
        
        function changeBrightness(lightId, value) {
            document.getElementById(`brightness-value-${lightId}`).textContent = value + '%';
            showMessage(`Intensidade ajustada para ${value}%`, 'info');
        }
        
        function changeColor(lightId, color) {
            const colorName = document.getElementById(`color-name-${lightId}`);
            colorName.textContent = color === '#ffffff' ? 'Branco' : 'Personalizada';
            showMessage('Cor alterada com sucesso!', 'success');
        }
        
        function setPreset(lightId, preset) {
            const brightnessSlider = document.getElementById(`brightness-${lightId}`);
            const colorPicker = document.querySelector(`[data-light-id="${lightId}"] .color-picker`);
            const colorName = document.getElementById(`color-name-${lightId}`);
            
            if (preset === 'warm') {
                brightnessSlider.value = 70;
                colorPicker.value = '#ffd700';
                colorName.textContent = 'Quente';
                changeBrightness(lightId, 70);
                showMessage('Preset "Luz Quente" aplicado!', 'success');
            } else if (preset === 'cool') {
                brightnessSlider.value = 90;
                colorPicker.value = '#e6f3ff';
                colorName.textContent = 'Fria';
                changeBrightness(lightId, 90);
                showMessage('Preset "Luz Fria" aplicado!', 'success');
            }
        }
        
        function scheduleLight(lightId) {
            showMessage('Funcionalidade de agendamento em desenvolvimento!', 'info');
        }
        
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
        
        function showMessage(message, type = 'info') {
            const toast = document.createElement('div');
            toast.className = `toast toast-${type}`;
            toast.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                background: ${type === 'success' ? '#27ae60' : type === 'error' ? '#e74c3c' : '#4a90e2'};
                color: white;
                padding: 15px 20px;
                border-radius: 8px;
                box-shadow: 0 4px 12px rgba(0,0,0,0.15);
                z-index: 10000;
                font-size: 14px;
                max-width: 300px;
                opacity: 0;
                transform: translateX(100%);
                transition: all 0.3s ease;
            `;
            toast.textContent = message;
            
            document.body.appendChild(toast);
            
            // Show toast
            setTimeout(() => {
                toast.style.opacity = '1';
                toast.style.transform = 'translateX(0)';
            }, 100);
            
            // Hide toast
            setTimeout(() => {
                toast.style.opacity = '0';
                toast.style.transform = 'translateX(100%)';
                setTimeout(() => {
                    document.body.removeChild(toast);
                }, 300);
            }, 3000);
        }
    </script>
</body>
</html>

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

$zones = [
    ['id' => 1, 'name' => 'Sala de Estar', 'current_temp' => 22.5, 'target_temp' => 24, 'mode' => 'heating', 'status' => 'on'],
    ['id' => 2, 'name' => 'Quarto Principal', 'current_temp' => 21.0, 'target_temp' => 20, 'mode' => 'cooling', 'status' => 'on'],
    ['id' => 3, 'name' => 'Cozinha', 'current_temp' => 25.5, 'target_temp' => 23, 'mode' => 'cooling', 'status' => 'on'],
    ['id' => 4, 'name' => 'Escritório', 'current_temp' => 19.0, 'target_temp' => 22, 'mode' => 'heating', 'status' => 'off'],
    ['id' => 5, 'name' => 'Quarto Hóspedes', 'current_temp' => 20.5, 'target_temp' => 21, 'mode' => 'auto', 'status' => 'on'],
];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajustar Temperatura - Automação Residencial</title>
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
        
        .temperature-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 20px;
            margin-top: 30px;
        }
        
        .temp-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .temp-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.15);
        }
        
        .temp-card.heating {
            border-left: 5px solid #e74c3c;
        }
        
        .temp-card.cooling {
            border-left: 5px solid #3498db;
        }
        
        .temp-card.auto {
            border-left: 5px solid #f39c12;
        }
        
        .temp-card.off {
            border-left: 5px solid #95a5a6;
        }
        
        .temp-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }
        
        .zone-info h3 {
            color: #2f3640;
            margin-bottom: 5px;
            font-size: 1.3em;
        }
        
        .zone-status {
            display: flex;
            align-items: center;
            gap: 8px;
            font-weight: 600;
            font-size: 0.9em;
        }
        
        .zone-status.heating {
            color: #e74c3c;
        }
        
        .zone-status.cooling {
            color: #3498db;
        }
        
        .zone-status.auto {
            color: #f39c12;
        }
        
        .zone-status.off {
            color: #95a5a6;
        }
        
        .temp-display {
            text-align: center;
            margin: 20px 0;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 10px;
        }
        
        .current-temp {
            font-size: 3em;
            font-weight: bold;
            color: #2f3640;
            margin-bottom: 10px;
        }
        
        .temp-label {
            color: #666;
            font-size: 0.9em;
            margin-bottom: 15px;
        }
        
        .target-temp {
            font-size: 1.2em;
            color: #4a90e2;
            font-weight: 600;
        }
        
        .temp-controls {
            margin: 20px 0;
        }
        
        .control-group {
            margin-bottom: 20px;
        }
        
        .control-group label {
            display: block;
            margin-bottom: 10px;
            color: #333;
            font-weight: 500;
            font-size: 0.9em;
        }
        
        .temp-slider-container {
            position: relative;
            margin: 15px 0;
        }
        
        .temp-slider {
            width: 100%;
            height: 8px;
            border-radius: 4px;
            background: linear-gradient(to right, #3498db 0%, #27ae60 50%, #e74c3c 100%);
            outline: none;
            opacity: 0.8;
            transition: opacity 0.2s;
        }
        
        .temp-slider:hover {
            opacity: 1;
        }
        
        .temp-slider::-webkit-slider-thumb {
            appearance: none;
            width: 24px;
            height: 24px;
            border-radius: 50%;
            background: white;
            border: 3px solid #4a90e2;
            cursor: pointer;
            box-shadow: 0 2px 6px rgba(0,0,0,0.2);
        }
        
        .temp-values {
            display: flex;
            justify-content: space-between;
            margin-top: 5px;
            font-size: 0.8em;
            color: #666;
        }
        
        .mode-selector {
            display: flex;
            gap: 10px;
            margin: 15px 0;
        }
        
        .mode-btn {
            flex: 1;
            padding: 10px;
            border: 2px solid #dee2e6;
            background: white;
            border-radius: 8px;
            cursor: pointer;
            text-align: center;
            transition: all 0.3s ease;
            font-size: 0.9em;
        }
        
        .mode-btn.active {
            border-color: #4a90e2;
            background: #4a90e2;
            color: white;
        }
        
        .mode-btn:hover {
            border-color: #4a90e2;
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
        
        .schedule-section {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #dee2e6;
        }
        
        .schedule-btn {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 0.9em;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
        }
        
        .schedule-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
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

        @media (max-width: 768px) {
            .temperature-grid {
                grid-template-columns: 1fr;
            }
            
            .header-content {
                flex-direction: column;
                gap: 10px;
            }
            
            .user-menu {
                margin-top: 10px;
            }
            
            .current-temp {
                font-size: 2.5em;
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
        <div class="welcome">
            <h1>Controle de Temperatura</h1>
            <p>Ajuste a temperatura dos ambientes e programe horários personalizados.</p>
        </div>
        
        <div class="temperature-grid">
            <?php foreach($zones as $zone): ?>
            <div class="temp-card <?php echo $zone['status'] == 'on' ? $zone['mode'] : 'off'; ?>" data-zone-id="<?php echo $zone['id']; ?>">
                <div class="temp-header">
                    <div class="zone-info">
                        <h3><?php echo htmlspecialchars($zone['name']); ?></h3>
                    </div>
                    <div class="zone-status <?php echo $zone['status'] == 'on' ? $zone['mode'] : 'off'; ?>">
                        <i class="fas fa-thermometer-half"></i>
                        <span><?php 
                            if ($zone['status'] == 'off') echo 'Desligado';
                            elseif ($zone['mode'] == 'heating') echo 'Aquecendo';
                            elseif ($zone['mode'] == 'cooling') echo 'Resfriando';
                            else echo 'Automático';
                        ?></span>
                    </div>
                </div>
                
                <div class="temp-display">
                    <div class="current-temp" id="current-temp-<?php echo $zone['id']; ?>">
                        <?php echo number_format($zone['current_temp'], 1); ?>°C
                    </div>
                    <div class="temp-label">Temperatura Atual</div>
                    <div class="target-temp" id="target-temp-<?php echo $zone['id']; ?>">
                        Meta: <?php echo $zone['target_temp']; ?>°C
                    </div>
                </div>
                
                <div class="temp-controls">
                    <div class="control-group">
                        <label>Liga/Desliga</label>
                        <label class="toggle-switch">
                            <input type="checkbox" <?php echo $zone['status'] == 'on' ? 'checked' : ''; ?> 
                                   onchange="toggleZone(<?php echo $zone['id']; ?>)">
                            <span class="slider"></span>
                        </label>
                    </div>
                    
                    <div class="control-group">
                        <label>Temperatura Desejada</label>
                        <div class="temp-slider-container">
                            <input type="range" min="16" max="30" value="<?php echo $zone['target_temp']; ?>" 
                                   class="temp-slider" id="temp-slider-<?php echo $zone['id']; ?>"
                                   onchange="changeTargetTemp(<?php echo $zone['id']; ?>, this.value)"
                                   <?php echo $zone['status'] == 'off' ? 'disabled' : ''; ?>>
                            <div class="temp-values">
                                <span>16°C</span>
                                <span>30°C</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="control-group">
                        <label>Modo de Operação</label>
                        <div class="mode-selector">
                            <div class="mode-btn <?php echo $zone['mode'] == 'heating' ? 'active' : ''; ?>" 
                                 onclick="changeMode(<?php echo $zone['id']; ?>, 'heating')"
                                 <?php echo $zone['status'] == 'off' ? 'style="opacity:0.5;pointer-events:none;"' : ''; ?>>
                                <i class="fas fa-fire"></i><br>Aquecer
                            </div>
                            <div class="mode-btn <?php echo $zone['mode'] == 'cooling' ? 'active' : ''; ?>" 
                                 onclick="changeMode(<?php echo $zone['id']; ?>, 'cooling')"
                                 <?php echo $zone['status'] == 'off' ? 'style="opacity:0.5;pointer-events:none;"' : ''; ?>>
                                <i class="fas fa-snowflake"></i><br>Resfriar
                            </div>
                            <div class="mode-btn <?php echo $zone['mode'] == 'auto' ? 'active' : ''; ?>" 
                                 onclick="changeMode(<?php echo $zone['id']; ?>, 'auto')"
                                 <?php echo $zone['status'] == 'off' ? 'style="opacity:0.5;pointer-events:none;"' : ''; ?>>
                                <i class="fas fa-magic"></i><br>Auto
                            </div>
                        </div>
                    </div>
                    
                    <div class="schedule-section">
                        <button class="schedule-btn" onclick="scheduleTemperature(<?php echo $zone['id']; ?>)">
                            <i class="fas fa-clock"></i> Programar Horários
                        </button>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    
    <script>
        function toggleZone(zoneId) {
            const card = document.querySelector(`[data-zone-id="${zoneId}"]`);
            const statusElement = card.querySelector('.zone-status');
            const statusText = statusElement.querySelector('span');
            const tempSlider = document.getElementById(`temp-slider-${zoneId}`);
            const modeButtons = card.querySelectorAll('.mode-btn');
            
            const isOn = card.classList.contains('heating') || card.classList.contains('cooling') || card.classList.contains('auto');
            
            if (isOn) {
                card.className = 'temp-card off';
                statusElement.className = 'zone-status off';
                statusText.textContent = 'Desligado';
                tempSlider.disabled = true;
                modeButtons.forEach(btn => {
                    btn.style.opacity = '0.5';
                    btn.style.pointerEvents = 'none';
                });
            } else {
                card.className = 'temp-card auto';
                statusElement.className = 'zone-status auto';
                statusText.textContent = 'Automático';
                tempSlider.disabled = false;
                modeButtons.forEach(btn => {
                    btn.style.opacity = '1';
                    btn.style.pointerEvents = 'auto';
                    btn.classList.remove('active');
                });
                modeButtons[2].classList.add('active');
            }
            
            showMessage(`Zona ${isOn ? 'desligada' : 'ligada'} com sucesso!`, 'success');
        }
        
        function changeTargetTemp(zoneId, temp) {
            document.getElementById(`target-temp-${zoneId}`).textContent = `Meta: ${temp}°C`;
            showMessage(`Temperatura ajustada para ${temp}°C`, 'success');
        }
        
        function changeMode(zoneId, mode) {
            const card = document.querySelector(`[data-zone-id="${zoneId}"]`);
            const statusElement = card.querySelector('.zone-status');
            const statusText = statusElement.querySelector('span');
            const modeButtons = card.querySelectorAll('.mode-btn');
            
            modeButtons.forEach(btn => btn.classList.remove('active'));
            
            card.className = `temp-card ${mode}`;
            statusElement.className = `zone-status ${mode}`;
            
            if (mode === 'heating') {
                modeButtons[0].classList.add('active');
                statusText.textContent = 'Aquecendo';
            } else if (mode === 'cooling') {
                modeButtons[1].classList.add('active');
                statusText.textContent = 'Resfriando';
            } else {
                modeButtons[2].classList.add('active');
                statusText.textContent = 'Automático';
            }
            
            showMessage(`Modo alterado para ${mode === 'heating' ? 'Aquecimento' : mode === 'cooling' ? 'Resfriamento' : 'Automático'}`, 'success');
        }
        
        function scheduleTemperature(zoneId) {
            showMessage('Funcionalidade de programação em desenvolvimento!', 'info');
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
            
            setTimeout(() => {
                toast.style.opacity = '1';
                toast.style.transform = 'translateX(0)';
            }, 100);
            
            setTimeout(() => {
                toast.style.opacity = '0';
                toast.style.transform = 'translateX(100%)';
                setTimeout(() => {
                    document.body.removeChild(toast);
                }, 300);
            }, 3000);
        }
        
        setInterval(() => {
            const zones = document.querySelectorAll('[data-zone-id]');
            zones.forEach(zone => {
                const zoneId = zone.getAttribute('data-zone-id');
                const currentTempElement = document.getElementById(`current-temp-${zoneId}`);
                const isOn = !zone.classList.contains('off');
                
                if (isOn) {
                    const currentTemp = parseFloat(currentTempElement.textContent);
                    const variation = (Math.random() - 0.5) * 0.2;
                    const newTemp = currentTemp + variation;
                    currentTempElement.textContent = newTemp.toFixed(1) + '°C';
                }
            });
        }, 5000);
    </script>
</body>
</html>

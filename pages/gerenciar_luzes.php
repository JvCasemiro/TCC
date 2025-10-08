<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$output = [];
$status = 0;
$script_path = dirname(__DIR__) . '\start_light_controller.bat';
$command = 'tasklist /FI "WINDOWTITLE eq light_controller" 2>NUL | find /I "python.exe" >NUL';
exec($command, $output, $status);

if ($status !== 0) {
    $command = 'start "light_controller" /B cmd /c "' . $script_path . '"';
    pclose(popen($command, 'r'));
}

$user = [
    'username' => $_SESSION['username'],
    'email' => $_SESSION['email'],
    'user_type' => 'Administrador'
];
$created_at = new DateTime();
$updated_at = new DateTime();

$lightStatusFile = __DIR__ . '/../light_status.txt';
$lightStatus = 'off';
if (file_exists($lightStatusFile)) {
    $status = trim(file_get_contents($lightStatusFile));
    $lightStatus = (strtoupper($status) === 'ON') ? 'on' : 'off';
}

// Get user's codigo_casa and type
$user_codigo_casa = 1; // Default value
$user_type = 'user'; // Default to standard user

try {
    $stmt = $conn->prepare("SELECT Codigo_Casa, Tipo_Usuario FROM Usuarios WHERE ID_Usuario = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user_data = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user_data) {
        $user_codigo_casa = $user_data['Codigo_Casa'] ?? 1;
        $user_type = strtolower($user_data['Tipo_Usuario'] ?? 'user');
    }
    
    // For admin users, show all lights
    // For standard users, only show lights with matching codigo_casa
    if ($user_type === 'admin') {
        $stmt = $conn->query("SELECT * FROM Lampadas");
    } else {
        $stmt = $conn->prepare("SELECT * FROM Lampadas WHERE codigo_casa = ?");
        $stmt->execute([$user_codigo_casa]);
    }
    
    $lights_db = $stmt->fetchAll();
    $lights = [];
    
    foreach ($lights_db as $lampada) {
        $lights[] = [
            'id' => $lampada['ID_Lampada'],
            'name' => $lampada['Nome'],
            'room' => $lampada['Comodo'],
            'status' => $lampada['Status'] ?? 'off'
        ];
    }
} catch (PDOException $e) {
    // In case of error, use an empty list
    $lights = [];
    error_log("Database error in gerenciar_luzes.php: " . $e->getMessage());
}

// Se não houver lâmpadas cadastradas, exibe uma mensagem
$noLights = empty($lights);
?>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Luzes - Automação Residencial</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/modal.css">
    <link rel="shortcut icon" href="../assets/img/logo_domx_sem_nome.png" type="image/x-icon">
    
    <script src="../assets/js/lights.js"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background: linear-gradient(135deg, #0a0f2c 0%, #0a0f2c 100%);
            color: #000;
        }
        
        .container {
            max-width: 1400px;
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
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 25px;
            margin-top: 30px;
            padding: 0 10px;
        }
        
        .light-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            position: relative;
            overflow: hidden;
            min-height: 200px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
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
            align-items: flex-start;
            margin-bottom: 15px;
        }

        .light-info h3 {
            margin: 0 0 5px 0;
            color: #2c3e50;
            font-size: 1.2em;
            font-weight: 600;
        }

        .light-info .room {
            color: #7f8c8d;
            font-size: 0.9em;
        }
        
        .light-name {
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
            gap: 8px;
            margin-top: 20px;
            flex-wrap: wrap;
        }
        
        .btn-action {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: #fff;
            padding: 8px 12px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 0.85rem;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            transition: all 0.3s ease;
        }
        
        .btn-action:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: translateY(-2px);
        }
        
        .btn-action i {
            font-size: 0.9em;
        }
        
        .btn-remove {
            color: #ff6b6b !important;
            border-color: #ff6b6b !important;
        }
        
        .btn-remove:hover {
            background: rgba(255, 107, 107, 0.1) !important;
        }
        
        .no-lights {
            text-align: center;
            padding: 40px 20px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 15px;
            margin: 20px 0;
            border: 1px dashed rgba(255, 255, 255, 0.1);
        }
        
        .no-lights i {
            font-size: 48px;
            color: #4CAF50;
            margin-bottom: 15px;
            opacity: 0.8;
        }
        
        .no-lights h3 {
            margin-bottom: 10px;
            color: #fff;
        }
        
        .no-lights p {
            margin-bottom: 20px;
            color: #aaa;
        }
        
        .btn-add-light {
            display: inline-block;
            background: linear-gradient(135deg, #4CAF50 0%, #45a049 100%);
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-add-light:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(76, 175, 80, 0.3);
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

        @media (max-width: 1200px) {
            .lights-grid {
                grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
                gap: 20px;
            }
        }

        @media (max-width: 768px) {
            .lights-grid {
                grid-template-columns: 1fr;
                gap: 15px;
                padding: 0 5px;
            }
            
            .header-content {
                flex-direction: column;
                gap: 10px;
            }
            
            .user-menu {
                margin-top: 10px;
            }
            
            .container {
                padding: 10px;
            }
        }

        @media (min-width: 1400px) {
            .lights-grid {
                grid-template-columns: repeat(3, 1fr);
                max-width: 1200px;
                margin: 30px auto 0;
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
            <h1>Gerenciamento de Luzes</h1>
            <p>Controle todas as luzes da sua casa de forma inteligente e personalizada.</p>
        </div>
        
        <?php if ($noLights): ?>
            <div class="no-lights" style="text-align: center; padding: 40px; background: rgba(255, 255, 255, 0.1); border-radius: 15px; margin: 20px 0;">
                <i class="far fa-lightbulb" style="font-size: 48px; color: #4CAF50; margin-bottom: 15px;"></i>
                <h3 style="margin-bottom: 10px;">Nenhuma lâmpada cadastrada</h3>
                <p style="margin-bottom: 20px; color: #ccc;">Adicione uma lâmpada na página de Dispositivos IoT para começar</p>
            </div>
        <?php else: ?>
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
                        <div class="control-group" style="margin-bottom: 15px;">
                            <label>Liga/Desliga</label>
                            <label class="toggle-switch">
                                <input type="checkbox" <?php echo $light['status'] == 'on' ? 'checked' : ''; ?> 
                                       onchange="toggleLight(<?php echo $light['id']; ?>)">
                                <span class="slider"></span>
                            </label>
                        </div>
                    </div>
                    
                    <div class="light-actions">
                        <button class="btn-action btn-remove" onclick="removeLight(<?php echo $light['id']; ?>, this)">
                            <i class="fas fa-trash"></i> Remover
                        </button>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
    
    <script>
        // Função para atualizar o estado de uma lâmpada
        function updateLightState(lightId, isOn) {
            const card = document.querySelector(`[data-light-id="${lightId}"]`);
            if (!card) return;
            
            const statusElement = card.querySelector('.light-status');
            const statusText = statusElement ? statusElement.querySelector('span') : null;
            const toggleSwitch = card.querySelector('input[type="checkbox"]');
            
            // Atualiza as classes
            card.classList.toggle('on', isOn);
            card.classList.toggle('off', !isOn);
            
            if (statusElement) {
                statusElement.classList.toggle('on', isOn);
                statusElement.classList.toggle('off', !isOn);
            }
            
            // Atualiza o texto do status
            if (statusText) {
                statusText.textContent = isOn ? 'Ligada' : 'Desligada';
            }
            
            // Controle de brilho removido
            
            // Atualiza o estado do toggle switch
            if (toggleSwitch) {
                toggleSwitch.checked = isOn;
            }
        }
        
        // Função para carregar o status das lâmpadas
        function loadLightsStatus() {
            fetch('../includes/get_lights_status.php')
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Erro ao carregar status das lâmpadas');
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success && data.status) {
                        // Atualiza o estado de cada lâmpada com base no status retornado
                        for (let i = 0; i < data.status.length; i++) {
                            const lightId = i + 1;
                            const isOn = data.status[i] === '1';
                            updateLightState(lightId, isOn);
                        }
                    }
                })
                .catch(error => {
                    console.error('Erro ao carregar status das lâmpadas:', error);
                });
        }
        
        // Inicialização quando o DOM estiver carregado
        document.addEventListener('DOMContentLoaded', function() {
            // Carrega o status inicial das lâmpadas
            loadLightsStatus();
            
            // Atualiza o status a cada 5 segundos
            setInterval(loadLightsStatus, 5000);
        });

        function toggleLight(lightId) {
            const card = document.querySelector(`[data-light-id="${lightId}"]`);
            if (!card) {
                console.error('Card não encontrado para o ID:', lightId);
                return;
            }
            
            const statusElement = card.querySelector('.light-status');
            if (!statusElement) {
                console.error('Elemento .light-status não encontrado no card:', lightId);
                return;
            }
            
            const statusText = statusElement.querySelector('span');
            const toggleSwitch = card.querySelector('input[type="checkbox"]');
            
            const isOn = card.classList.contains('on');
            const newStatus = !isOn;
            
            // Atualiza as classes e o estado visual
            card.classList.toggle('on', newStatus);
            card.classList.toggle('off', !newStatus);
            statusElement.classList.toggle('on', newStatus);
            statusElement.classList.toggle('off', !newStatus);
            
            // Atualiza o texto do status
            if (statusText) {
                statusText.textContent = newStatus ? 'Ligada' : 'Desligada';
            }
            
            
            // Atualiza o estado do toggle switch
            if (toggleSwitch) {
                toggleSwitch.checked = newStatus;
            }
            
            console.log('Enviando requisição para atualizar status da luz:', { lightId, status: newStatus ? 'ON' : 'OFF' });
            
            fetch('../includes/update_light.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    light_id: lightId,
                    status: newStatus ? 'ON' : 'OFF'
                })
            })
            .then(response => {
                console.log('Resposta recebida, status:', response.status);
                setTimeout(() => {
                    window.location.reload();
                }, 3000);
                if (!response.ok) {
                    return response.text().then(text => {
                        console.error('Erro na resposta:', text);
                        throw new Error(`Erro HTTP! status: ${response.status}, resposta: ${text}`);
                    });
                }
                return response.json();
            })
            .then(data => {
                console.log('Dados da resposta:', data);
                if (data && data.success) {
                    showMessage(`Luz ${newStatus ? 'ligada' : 'desligada'} com sucesso!`, 'success');
                } else {
                    const errorMessage = data && data.error ? data.error : 'Erro desconhecido ao atualizar o status da luz';
                    console.error('Erro na resposta da API:', errorMessage);
                    showMessage(`Erro: ${errorMessage}`, 'error');
                    
                    // Reverte a UI para o estado anterior
                    if (isOn) {
                        card.classList.remove('off');
                        card.classList.add('on');
                        statusElement.classList.remove('off');
                        statusElement.classList.add('on');
                        statusText.textContent = 'Ligada';
                        if (colorPicker) colorPicker.disabled = false;
                    } else {
                        card.classList.remove('on');
                        card.classList.add('off');
                        statusElement.classList.remove('on');
                        statusElement.classList.add('off');
                        statusText.textContent = 'Desligada';
                        if (colorPicker) colorPicker.disabled = true;
                    }
                }
            })
            .catch(error => {
                console.error('Erro na requisição:', error);
                showMessage(`Erro ao atualizar o status da luz: ${error.message}`, 'error');
                
                // Reverte a UI para o estado anterior
                if (isOn) {
                    card.classList.remove('off');
                    card.classList.add('on');
                    statusElement.classList.remove('off');
                    statusElement.classList.add('on');
                    statusText.textContent = 'Ligada';
                    colorPicker.disabled = false;
                } else {
                    card.classList.remove('on');
                    card.classList.add('off');
                    statusElement.classList.remove('on');
                    statusElement.classList.add('off');
                    statusText.textContent = 'Desligada';
                    colorPicker.disabled = true;
                }
                showMessage('Erro de conexão', 'error');
            });
        }

        window.addEventListener('beforeunload', function() {
            console.log('Page is being unloaded, but keeping the light controller running');
        })  
        
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
            `;
            toast.textContent = message;
            document.body.appendChild(toast);
            
            setTimeout(() => {
                toast.style.opacity = '0';
                toast.style.transform = 'translateY(-50px)';
                setTimeout(() => {
                    if (toast.parentNode) {
                        document.body.removeChild(toast);
                    }
                }, 300);
            }, 5000);
        }

        // Função para alternar o estado da lâmpada
        function toggleLight(lightId, element) {
            const card = document.querySelector(`[data-light-id="${lightId}"]`);
            if (!card) {
                console.error('Card não encontrado para o ID:', lightId);
                return;
            }
            
            const statusElement = card.querySelector('.light-status');
            if (!statusElement) {
                console.error('Elemento .light-status não encontrado no card:', lightId);
                return;
            }
            
            const statusText = statusElement.querySelector('span');
            const toggleSwitch = element || card.querySelector('input[type="checkbox"]');
            
            const isOn = card.classList.contains('on');
            const newStatus = !isOn;
            
            // Atualiza as classes e o estado visual
            card.classList.toggle('on', newStatus);
            card.classList.toggle('off', !newStatus);
            statusElement.classList.toggle('on', newStatus);
            statusElement.classList.toggle('off', !newStatus);
            
            // Atualiza o texto do status
            if (statusText) {
                statusText.textContent = newStatus ? 'Ligada' : 'Desligada';
            }
            
            
            // Atualiza o estado do toggle switch
            if (toggleSwitch) {
                toggleSwitch.checked = newStatus;
            }
            
            // Atualiza o estilo do elemento se fornecido
            if (element) {
                const icon = element.querySelector('i');
                if (icon) {
                    if (newStatus) {
                        icon.className = 'fas fa-lightbulb';
                        element.style.background = 'rgba(76, 209, 55, 0.2)';
                        element.style.borderColor = '#4cd137';
                    } else {
                        icon.className = 'far fa-lightbulb';
                        element.style.background = 'rgba(255, 255, 255, 0.05)';
                        element.style.borderColor = 'rgba(255, 255, 255, 0.1)';
                    }
                }
            }
            
            console.log('Enviando requisição para atualizar status da luz:', { lightId, status: newStatus ? 'ON' : 'OFF' });
            
            fetch('../includes/update_light.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    light_id: lightId,
                    status: newStatus ? 'ON' : 'OFF'
                })
            })
            .then(response => {
                console.log('Resposta recebida, status:', response.status);
                setTimeout(() => {
                    window.location.reload();
                }, 3000);
                if (!response.ok) {
                    return response.text().then(text => {
                        console.error('Erro na resposta:', text);
                        throw new Error(`Erro HTTP! status: ${response.status}, resposta: ${text}`);
                    });
                }
                return response.json();
            })
            .then(data => {
                if (!data.success) {
                    console.error('Erro ao atualizar status da luz:', data.message);
                    // Reverte as alterações visuais em caso de erro
                    card.classList.toggle('on', !newStatus);
                    card.classList.toggle('off', newStatus);
                    statusElement.classList.toggle('on', !newStatus);
                    statusElement.classList.toggle('off', newStatus);
                    if (statusText) {
                        statusText.textContent = !newStatus ? 'Ligada' : 'Desligada';
                    }
                    if (toggleSwitch) {
                        toggleSwitch.checked = !newStatus;
                    }
                    // Reverte o estilo do elemento se fornecido
                    if (element) {
                        const icon = element.querySelector('i');
                        if (icon) {
                            if (!newStatus) {
                                icon.className = 'fas fa-lightbulb';
                                element.style.background = 'rgba(76, 209, 55, 0.2)';
                                element.style.borderColor = '#4cd137';
                            } else {
                                icon.className = 'far fa-lightbulb';
                                element.style.background = 'rgba(255, 255, 255, 0.05)';
                                element.style.borderColor = 'rgba(255, 255, 255, 0.1)';
                            }
                        }
                    }
                }
            })
            .catch(error => {
                console.error('Erro na requisição:', error);
                // Reverte as alterações visuais em caso de erro
                card.classList.toggle('on', !newStatus);
                card.classList.toggle('off', newStatus);
                statusElement.classList.toggle('on', !newStatus);
                statusElement.classList.toggle('off', newStatus);
                if (statusText) {
                    statusText.textContent = !newStatus ? 'Ligada' : 'Desligada';
                }
                if (toggleSwitch) {
                    toggleSwitch.checked = !newStatus;
                }
                // Reverte o estilo do elemento se fornecido
                if (element) {
                    const icon = element.querySelector('i');
                    if (icon) {
                        if (!newStatus) {
                            icon.className = 'fas fa-lightbulb';
                            element.style.background = 'rgba(76, 209, 55, 0.2)';
                            element.style.borderColor = '#4cd137';
                        } else {
                            icon.className = 'far fa-lightbulb';
                            element.style.background = 'rgba(255, 255, 255, 0.05)';
                            element.style.borderColor = 'rgba(255, 255, 255, 0.1)';
                        }
                    }
                }
            });
            
            console.log(`Lâmpada ${lightId} ${isOn ? 'ligada' : 'desligada'}`);
        }
    </script>

    <div id="confirmDeleteModal" class="modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.7); z-index: 1000;">
        <div style="display: flex; justify-content: center; align-items: center; width: 100%; height: 100%;">
            <div class="modal-content" style="background: #2c3e50; padding: 25px; border-radius: 10px; width: 90%; max-width: 500px; box-shadow: 0 5px 30px rgba(0, 0, 0, 0.3); position: relative; margin: 20px;">
                <div class="modal-header">
                    <h2><i class="fas fa-exclamation-triangle"></i> Confirmar Exclusão</h2>
                    <span class="close-btn" onclick="cancelDelete()">&times;</span>
                </div>
                <div class="modal-body">
                    <p>Tem certeza que deseja remover a lâmpada <strong id="lightToDelete"></strong>?</p>
                    <p class="text-warning"><i class="fas fa-exclamation-circle"></i> Esta ação não pode ser desfeita.</p>
                    
                    <div class="form-actions">
                        <button type="button" class="btn-cancel" onclick="cancelDelete()">
                            <i class="fas fa-times"></i> Cancelar
                        </button>
                        <button type="button" class="btn-confirm btn-remove" onclick="confirmDelete()">
                            <i class="fas fa-trash"></i> Sim, remover
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/js/all.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const initialStatus = '<?php echo $lightStatus; ?>';
            if (initialStatus === 'on') {
                const card = document.querySelector('[data-light-id="1"]');
                if (card) {
                    const statusElement = card.querySelector('.light-status');
                    const statusText = statusElement.querySelector('span');
                    card.classList.remove('off');
                    card.classList.add('on');
                    statusElement.classList.remove('off');
                    statusElement.classList.add('on');
                    statusText.textContent = 'Ligada';
                }
            }
        });

        // Variáveis para controle do modal de confirmação
        let lightToDeleteId = null;
        let lightToDeleteElement = null;
        const confirmDeleteModal = document.getElementById('confirmDeleteModal');
        const lightToDeleteText = document.getElementById('lightToDelete');

        // Função para exibir o modal de confirmação
        function removeLight(lightId, element) {
            lightToDeleteId = lightId;
            lightToDeleteElement = element.closest('.light-card');
            const lightNameElement = lightToDeleteElement.querySelector('.light-info h3');
            if (!lightNameElement) {
                console.error('Elemento do nome da lâmpada não encontrado');
                return;
            }
            const lightName = lightNameElement.textContent;
            lightToDeleteText.textContent = `"${lightName}"`;
            confirmDeleteModal.style.display = 'block';
            document.body.style.overflow = 'hidden'; // Impede o scroll da página
        }

        // Função para confirmar a exclusão
        function confirmDelete() {
            if (!lightToDeleteId || !lightToDeleteElement) {
                console.error('ID ou elemento da lâmpada não encontrado');
                return;
            }

            const formData = new FormData();
            formData.append('light_id', lightToDeleteId);

            fetch('../includes/delete_light.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Remove o card da lâmpada
                    lightToDeleteElement.remove();
                    showMessage('Lâmpada removida com sucesso!', 'success');
                } else {
                    showMessage(`Erro ao remover lâmpada: ${data.message || 'Erro desconhecido'}`, 'error');
                }
            })
            .catch(error => {
                console.error('Erro ao remover lâmpada:', error);
                showMessage('Erro ao se conectar ao servidor', 'error');
            })
            .finally(() => {
                cancelDelete();
            });
        }

        // Função para cancelar a exclusão
        function cancelDelete() {
            lightToDeleteId = null;
            lightToDeleteElement = null;
            confirmDeleteModal.style.display = 'none';
            document.body.style.overflow = 'auto'; // Restaura o scroll da página
        }

        // Fechar o modal ao clicar fora dele
        window.onclick = function(event) {
            if (event.target === confirmDeleteModal) {
                cancelDelete();
            }
        }
    </script>
    </body>
</html>

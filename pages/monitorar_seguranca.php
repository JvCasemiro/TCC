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

$cameras = [
    ['id' => 1, 'name' => 'Entrada Principal', 'location' => 'Portão', 'status' => 'online', 'recording' => true, 'motion' => false]
];

$events = [
    ['time' => '09:45', 'camera' => 'Entrada Principal', 'event' => 'Gravação iniciada', 'type' => 'recording']
];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monitoramento de Segurança - Automação Residencial</title>
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
        
        .security-layout {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 20px;
            margin-top: 30px;
        }
        
        .cameras-section {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        
        .section-title {
            color: #2f3640;
            margin-bottom: 20px;
            font-size: 1.5em;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .cameras-grid {
            display: grid;
            grid-template-columns: 1fr; /* Single column layout */
            gap: 20px;
            max-width: 800px; /* Limit maximum width for better readability */
            margin: 0 auto; /* Center the grid */
        }
        
        .camera-card {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
            border: 2px solid #e0e0e0;
            transition: all 0.3s ease;
            position: relative;
            min-height: 600px;
            display: flex;
            flex-direction: column;
            margin-bottom: 30px;
        }
        
        .camera-card.online {
            border-color: #27ae60;
        }
        
        .camera-card.offline {
            border-color: #e74c3c;
            opacity: 0.7;
        }
        
        .camera-card.motion {
            border-color: #f39c12;
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0% { box-shadow: 0 0 0 0 rgba(243, 156, 18, 0.7); }
            70% { box-shadow: 0 0 0 10px rgba(243, 156, 18, 0); }
            100% { box-shadow: 0 0 0 0 rgba(243, 156, 18, 0); }
        }
        
        .camera-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }
        
        .camera-info h4 {
            color: #2f3640;
            margin-bottom: 3px;
        }
        
        .camera-location {
            color: #666;
            font-size: 0.9em;
        }
        
        .camera-status {
            display: flex;
            align-items: center;
            gap: 5px;
            font-size: 0.9em;
            font-weight: 600;
        }
        
        .camera-status.online {
            color: #27ae60;
        }
        
        .camera-status.offline {
            color: #e74c3c;
        }
        
        .camera-feed {
            width: 100%;
            height: 400px; /* Increased height */
            background: #2c3e50;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 20px;
            position: relative;
            overflow: hidden;
            flex-grow: 1;
        }
        
        .camera-feed.offline {
            background: #95a5a6;
        }
        
        .feed-placeholder {
            color: white;
            font-size: 1.2em;
            text-align: center;
            padding: 20px;
        }
        
        .live-indicator {
            position: absolute;
            top: 10px;
            left: 10px;
            background: #e74c3c;
            color: white;
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 0.8em;
            font-weight: 600;
        }
        
        .recording-indicator {
            position: absolute;
            top: 10px;
            right: 10px;
            background: #27ae60;
            color: white;
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 0.8em;
            display: flex;
            align-items: center;
            gap: 3px;
        }
        
        .motion-indicator {
            position: absolute;
            bottom: 10px;
            left: 10px;
            background: #f39c12;
            color: white;
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 0.8em;
            animation: blink 1s infinite;
        }
        
        @keyframes blink {
            0%, 50% { opacity: 1; }
            51%, 100% { opacity: 0.3; }
        }
        
        .camera-controls {
            display: flex;
            gap: 8px;
        }
        
        .control-btn {
            flex: 1;
            padding: 8px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 0.8em;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-view {
            background: #4a90e2;
            color: white;
        }
        
        .btn-view:hover {
            background: #357abd;
        }
        
        .btn-record {
            background: #27ae60;
            color: white;
        }
        
        .btn-record:hover {
            background: #229954;
        }
        
        .btn-record.recording {
            background: #e74c3c;
        }
        
        .events-section {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            height: fit-content;
        }
        
        .events-list {
            max-height: 400px;
            overflow-y: auto;
        }
        
        .event-item {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 12px;
            margin-bottom: 8px;
            background: #f8f9fa;
            border-radius: 8px;
            border-left: 4px solid transparent;
        }
        
        .event-item.motion {
            border-left-color: #f39c12;
        }
        
        .event-item.recording {
            border-left-color: #27ae60;
        }
        
        .event-item.offline {
            border-left-color: #e74c3c;
        }
        
        .event-time {
            font-weight: 600;
            color: #4a90e2;
            min-width: 50px;
        }
        
        .event-details {
            flex: 1;
        }
        
        .event-camera {
            font-weight: 600;
            color: #2f3640;
        }
        
        .event-description {
            color: #666;
            font-size: 0.9em;
        }
        
        .event-icon {
            width: 20px;
            text-align: center;
        }
        
        .event-icon.motion {
            color: #f39c12;
        }
        
        .event-icon.recording {
            color: #27ae60;
        }
        
        .event-icon.offline {
            color: #e74c3c;
        }
        
        .security-controls {
            background: white;
            border-radius: 15px;
            padding: 25px;
            margin-top: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        
        .controls-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
        }
        
        .control-card {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            transition: all 0.3s ease;
            cursor: pointer;
        }
        
        .control-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .control-icon {
            font-size: 2em;
            margin-bottom: 10px;
            color: #4a90e2;
        }
        
        .control-title {
            font-weight: 600;
            color: #2f3640;
            margin-bottom: 5px;
        }
        
        .control-description {
            color: #666;
            font-size: 0.9em;
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

        @media (max-width: 1024px) {
            .security-layout {
                grid-template-columns: 1fr;
            }
            
            .cameras-grid {
                grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            }
        }
        
        @media (max-width: 768px) {
            .header-content {
                flex-direction: column;
                gap: 10px;
            }
            
            .user-menu {
                margin-top: 10px;
            }
            
            .cameras-grid {
                grid-template-columns: 1fr;
            }
            
            .controls-grid {
                grid-template-columns: 1fr;
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
                <a href="dashboard.php" class="back-btn" style="text-decoration: none; display: inline-block; color: white;">
                    <i class="fas fa-arrow-left"></i> Voltar ao Dashboard
                </a>
                <form action="../auth/logout.php" method="post" style="display: inline;">
                    <button type="submit" class="logout-btn">Sair</button>
                </form>
            </div>
        </div>
    </header>
    
    <div class="container">
        <div class="welcome">
            <h1>Monitoramento de Segurança</h1>
            <p>Monitore câmeras e sensores de segurança em tempo real.</p>
        </div>
        
        <div class="security-layout">
            <div class="cameras-section">
                <h2 class="section-title">
                    <i class="fas fa-video"></i>
                    Câmeras de Segurança
                </h2>
                <div class="cameras-grid">
                    <?php foreach($cameras as $camera): ?>
                    <div class="camera-card <?php echo $camera['status']; ?> <?php echo $camera['motion'] ? 'motion' : ''; ?>" data-camera-id="<?php echo $camera['id']; ?>">
                        <div class="camera-header">
                            <div class="camera-info">
                                <h4><?php echo htmlspecialchars($camera['name']); ?></h4>
                                <div class="camera-location"><?php echo htmlspecialchars($camera['location']); ?></div>
                            </div>
                            <div class="camera-status <?php echo $camera['status']; ?>">
                                <i class="fas fa-circle"></i>
                                <span><?php echo $camera['status'] == 'online' ? 'Online' : 'Offline'; ?></span>
                            </div>
                        </div>
                        
                        <div class="camera-feed <?php echo $camera['status']; ?>">
                            <?php if ($camera['status'] == 'online'): ?>
                                <div class="live-indicator">AO VIVO</div>
                                <?php if ($camera['recording']): ?>
                                    <div class="recording-indicator">
                                        <i class="fas fa-circle"></i>
                                        REC
                                    </div>
                                <?php endif; ?>
                                <?php if ($camera['motion']): ?>
                                    <div class="motion-indicator">
                                        <i class="fas fa-running"></i>
                                        MOVIMENTO
                                    </div>
                                <?php endif; ?>
                                <div class="feed-placeholder">
                                    <i class="fas fa-video" style="font-size: 2em; margin-bottom: 10px;"></i><br>
                                    Feed da Câmera<br>
                                    <small>Clique em "Visualizar" para ver</small>
                                </div>
                            <?php else: ?>
                                <div class="feed-placeholder">
                                    <i class="fas fa-video-slash" style="font-size: 2em; margin-bottom: 10px;"></i><br>
                                    Câmera Offline
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="camera-controls">
                            <button class="control-btn btn-view" onclick="viewCamera(<?php echo $camera['id']; ?>)" 
                                    <?php echo $camera['status'] == 'offline' ? 'disabled style="opacity:0.5;"' : ''; ?>>
                                <i class="fas fa-eye"></i> Visualizar
                            </button>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <div class="events-section">
                <h2 class="section-title">
                    <i class="fas fa-bell"></i>
                    Eventos Recentes
                </h2>
                <div class="events-list">
                    <?php foreach($events as $event): ?>
                    <div class="event-item <?php echo $event['type']; ?>">
                        <div class="event-time"><?php echo $event['time']; ?></div>
                        <div class="event-icon <?php echo $event['type']; ?>">
                            <?php if ($event['type'] == 'motion'): ?>
                                <i class="fas fa-running"></i>
                            <?php elseif ($event['type'] == 'recording'): ?>
                                <i class="fas fa-record-vinyl"></i>
                            <?php else: ?>
                                <i class="fas fa-exclamation-triangle"></i>
                            <?php endif; ?>
                        </div>
                        <div class="event-details">
                            <div class="event-camera"><?php echo htmlspecialchars($event['camera']); ?></div>
                            <div class="event-description"><?php echo htmlspecialchars($event['event']); ?></div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        
        <div class="security-controls">
            <h2 class="section-title">
                <i class="fas fa-shield-alt"></i>
                Controles de Segurança
            </h2>
            <div class="controls-grid">
                <div class="control-card" onclick="armSystem()">
                    <div class="control-icon">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <div class="control-title">Armar Sistema</div>
                    <div class="control-description">Ativar monitoramento completo</div>
                </div>
                
                <div class="control-card" onclick="disarmSystem()">
                    <div class="control-icon">
                        <i class="fas fa-shield"></i>
                    </div>
                    <div class="control-title">Desarmar Sistema</div>
                    <div class="control-description">Desativar alarmes</div>
                </div>
                
                <div class="control-card" onclick="viewRecordings()">
                    <div class="control-icon">
                        <i class="fas fa-play-circle"></i>
                    </div>
                    <div class="control-title">Gravações</div>
                    <div class="control-description">Ver gravações anteriores</div>
                </div>
                
                <div class="control-card" onclick="configureAlerts()">
                    <div class="control-icon">
                        <i class="fas fa-bell"></i>
                    </div>
                    <div class="control-title">Configurar Alertas</div>
                    <div class="control-description">Personalizar notificações</div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        function viewCamera(cameraId) {
            showMessage(`Abrindo visualização da câmera ${cameraId}...`, 'info');
            setTimeout(() => {
                showMessage('Feed da câmera carregado com sucesso!', 'success');
            }, 1500);
        }
        
        function toggleRecording(cameraId) {
            const card = document.querySelector(`[data-camera-id="${cameraId}"]`);
            const recordBtn = card.querySelector('.btn-record');
            const recordIndicator = card.querySelector('.recording-indicator');
            
            const isRecording = recordBtn.classList.contains('recording');
            
            if (isRecording) {
                recordBtn.classList.remove('recording');
                recordBtn.innerHTML = '<i class="fas fa-record-vinyl"></i> Gravar';
                if (recordIndicator) {
                    recordIndicator.style.display = 'none';
                }
                showMessage('Gravação interrompida', 'info');
            } else {
                recordBtn.classList.add('recording');
                recordBtn.innerHTML = '<i class="fas fa-stop"></i> Parar';
                if (!recordIndicator) {
                    const indicator = document.createElement('div');
                    indicator.className = 'recording-indicator';
                    indicator.innerHTML = '<i class="fas fa-circle"></i> REC';
                    card.querySelector('.camera-feed').appendChild(indicator);
                } else {
                    recordIndicator.style.display = 'flex';
                }
                showMessage('Gravação iniciada', 'success');
            }
        }
        
        function armSystem() {
            showMessage('Sistema de segurança armado!', 'success');
        }
        
        function disarmSystem() {
            showMessage('Sistema de segurança desarmado!', 'info');
        }
        
        function viewRecordings() {
            showMessage('Funcionalidade de gravações em desenvolvimento!', 'info');
        }
        
        function configureAlerts() {
            showMessage('Funcionalidade de configuração em desenvolvimento!', 'info');
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
    </script>
</body>
</html>

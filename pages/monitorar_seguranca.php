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
            display: flex;
            flex-direction: column;
            gap: 20px;
            margin-top: 20px;
            width: 100%;
        }
        
        .cameras-section {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            width: 100%;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            height: 100%;
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
            grid-template-columns: 1fr;
            gap: 20px;
            width: 100%;
            margin: 0;
            height: 100%;
        }
        
        .camera-card {
            background: white;
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            border: 2px solid #e0e0e0;
            transition: all 0.3s ease;
            position: relative;
            min-height: 70vh; 
            display: flex;
            flex-direction: column;
            margin: 0;
            width: 100%;
            flex-grow: 1;
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
        
        .camera-status.offline {
            color: #e74c3c;
        }
        
        .camera-feed {
            width: 100%;
            min-height: 60vh;  /* Use viewport height */
            background: #2c3e50;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            position: relative;
            overflow: hidden;
            flex-grow: 1;
            text-align: center;
        }
        
        .camera-feed.offline {
            background: #95a5a6;
        }
        
        .feed-placeholder {
            color: white;
            font-size: 1.2em;
            text-align: center;
            padding: 20px;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 100%;
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
            gap: 12px;
            margin-top: 15px;
            padding: 8px 0;
        }
        
        .control-btn {
            flex: 1;
            padding: 10px 15px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.85em;
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
        
        .notification {
            position: fixed;
            top: 20px;
            right: 180px;
            background-color: #4CAF50;
            color: white;
            padding: 12px 24px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            z-index: 10000;
            display: flex;
            align-items: center;
            gap: 10px;
            transform: translateY(-100px);
            opacity: 0;
            transition: transform 0.3s ease, opacity 0.3s ease;
            max-width: 300px;
        }
        
        .notification.show {
            transform: translateY(0);
            opacity: 1;
        }
        
        .notification.error {
            background-color: #f44336;
        }
        
        .notification.warning {
            background-color: #ff9800;
        }
        
        .notification.info {
            background-color: #2196F3;
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

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
            backdrop-filter: blur(5px);
        }

        .modal-content {
            background: linear-gradient(135deg, #0a0f2c 0%, #1a2040 100%);
            margin: 2% auto;
            padding: 0;
            border-radius: 15px;
            width: 90%;
            max-width: 1200px;
            height: 90%;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            position: relative;
            overflow: hidden;
        }

        .modal-header {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            color: #333;
            padding: 20px 30px;
            border-radius: 15px 15px 0 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .modal-title {
            font-size: 1.8em;
            font-weight: bold;
            display: flex;
            align-items: center;
            gap: 15px;
            color: #2f3640;
        }

        .close {
            color: #666;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
            transition: color 0.3s;
        }

        .close:hover {
            color: #e74c3c;
        }

        .modal-body {
            padding: 30px;
            height: calc(100% - 80px);
            overflow-y: auto;
            background: linear-gradient(135deg, #0a0f2c 0%, #1a2040 100%);
        }

        .recordings-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }

        .recording-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .recording-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }

        .recording-thumbnail {
            width: 100%;
            height: 150px;
            background: #2c3e50;
            border-radius: 8px;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 2em;
        }

        .recording-info {
            color: #2f3640;
        }

        .recording-title {
            font-weight: bold;
            margin-bottom: 8px;
            font-size: 1.1em;
        }

        .recording-details {
            font-size: 0.9em;
            color: #666;
            margin-bottom: 5px;
        }

        .recording-actions {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }

        .recording-btn {
            flex: 1;
            padding: 8px 12px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.85em;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-play {
            background: #27ae60;
            color: white;
        }

        .btn-play:hover {
            background: #229954;
        }

        .btn-download {
            background: #4a90e2;
            color: white;
        }

        .btn-download:hover {
            background: #357abd;
        }

        .btn-delete {
            background: #e74c3c;
            color: white;
        }

        .btn-delete:hover {
            background: #c0392b;
        }

        .no-recordings {
            text-align: center;
            color: #ffffff;
            padding: 60px 20px;
            font-size: 1.2em;
        }

        .no-recordings i {
            font-size: 4em;
            margin-bottom: 20px;
            opacity: 0.5;
        }

        /* Delete Confirmation Modal Styles */
        #deleteConfirmationModal .modal-content {
            max-width: 500px;
            height: auto;
            max-height: 90%;
            background: white;
        }

        #deleteConfirmationModal .modal-header {
            background: #f8f9fa;
            border-bottom: 1px solid #eee;
            padding: 15px 20px;
        }

        #deleteConfirmationModal .modal-title {
            font-size: 1.2em;
            color: #333;
            gap: 10px;
        }

        #deleteConfirmationModal .modal-body {
            padding: 20px;
            color: #333;
            background: white;
        }

        #deleteConfirmationModal .modal-footer {
            display: flex;
            justify-content: flex-end;
            padding: 15px 20px;
            border-top: 1px solid #eee;
            background: #f9f9f9;
            border-radius: 0 0 8px 8px;
            gap: 10px;
        }

        .btn-cancel, .btn-confirm {
            padding: 8px 20px;
            border: none;
            border-radius: 4px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 14px;
        }

        .btn-cancel {
            background-color: #f1f1f1;
            color: #333;
        }

        .btn-cancel:hover {
            background-color: #e0e0e0;
        }

        .btn-confirm {
            background-color: #e74c3c;
            color: white;
        }

        .btn-confirm:hover {
            background-color: #c0392b;
        }

        .recording-to-delete {
            background-color: #f8f9fa;
            padding: 10px;
            border-radius: 4px;
            border-left: 4px solid #e74c3c;
            margin: 10px 0;
            word-break: break-all;
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
    
    <div class="container" style="height: calc(100vh - 80px); display: flex; flex-direction: column;">
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
                    <div class="camera-card <?php echo $camera['motion'] ? 'motion' : ''; ?>" data-camera-id="<?php echo $camera['id']; ?>">
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
                                <video id="camera-feed" autoplay playsinline style="width: 100%; height: 100%; object-fit: cover;"></video>
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
                
            </div>
        </div>
    </div>

    <div id="recordingsModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-title">
                    <i class="fas fa-play-circle"></i>
                    Gravações de Segurança
                </div>
                <span class="close" onclick="closeRecordingsModal()">&times;</span>
            </div>
            <div class="modal-body">
                <div id="recordingsContainer">
                </div>
            </div>
        </div>
    </div>
    
    <script>
        function showNotification(message, type = 'success') {
            const notification = document.createElement('div');
            notification.className = `notification ${type}`;
            notification.style.cssText = `
                position: fixed;
                top: 20px;
                right: 180px;
                background-color: ${type === 'success' ? '#4CAF50' : type === 'error' ? '#f44336' : type === 'warning' ? '#ff9800' : '#2196F3'};
                color: white;
                padding: 12px 24px;
                border-radius: 8px;
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
                z-index: 10000;
                display: flex;
                align-items: center;
                gap: 10px;
                transform: translateY(-100px);
                opacity: 0;
                transition: transform 0.3s ease, opacity 0.3s ease;
                max-width: 300px;
            `;
            
            let icon = '✅';
            if (type === 'error') icon = '❌';
            else if (type === 'warning') icon = '⚠️';
            else if (type === 'info') icon = 'ℹ️';
            
            notification.innerHTML = `
                <span class="notification-icon">${icon}</span>
                <span class="notification-message">${message}</span>
            `;
            
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.style.transform = 'translateY(0)';
                notification.style.opacity = '1';
            }, 10);
            
            setTimeout(() => {
                notification.style.transform = 'translateY(-100px)';
                notification.style.opacity = '0';
                setTimeout(() => {
                    if (notification.parentNode) {
                        notification.parentNode.removeChild(notification);
                    }
                }, 300);
            }, 3000);
        }
        
        function viewCamera(cameraId) {
            if (typeof viewCamera === 'function') {
                viewCamera(cameraId);
            } else {
                console.error('Função viewCamera não encontrada');
                showMessage('Erro: Função de visualização não disponível', 'error');
            }
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
            }
        }
        
        function armSystem() {
            return;
        }
        
        function disarmSystem() {
            return;
        }
        
        function viewRecordings() {
            document.getElementById('recordingsModal').style.display = 'block';
            loadRecordings();
        }
        
        function configureAlerts() {
            return;
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
                transform: translateY(-50px);
                transition: all 0.3s ease;
            `;
            toast.textContent = message;
            
            document.body.appendChild(toast);
            
            setTimeout(() => {
                toast.style.opacity = '1';
                toast.style.transform = 'translateY(0)';
            }, 100);
            
            setTimeout(() => {
                toast.style.opacity = '0';
                toast.style.transform = 'translateY(-50px)';
                setTimeout(() => {
                    document.body.removeChild(toast);
                }, 300);
            }, 3000);
        }
    </script>
    <!-- Modal de Confirmação de Exclusão -->
    <div id="deleteConfirmationModal" class="modal" style="display: none;">
        <div class="modal-content" style="max-width: 500px;">
            <div class="modal-header">
                <div class="modal-title">
                    <i class="fas fa-exclamation-triangle" style="color: #e74c3c;"></i>
                    <h3>Confirmar Exclusão</h3>
                </div>
                <span class="close" onclick="closeDeleteModal()">&times;</span>
            </div>
            <div class="modal-body">
                <p><strong>Tem certeza que deseja excluir esta gravação?</strong></p>
                <br>
                <p><strong>Esta ação não pode ser desfeita.</strong></p>
            </div>
            <div class="modal-footer" style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 20px;">
                <button class="btn-cancel" onclick="closeDeleteModal()">Cancelar</button>
                <button class="btn-confirm" onclick="confirmDelete()">Excluir</button>
            </div>
        </div>
    </div>

    <script src="../assets/js/camera.js"></script>
    <script src="../assets/js/screen-recorder.js"></script>
    <script>
        async function armSystem() {
            try {
                showNotification('Gravação iniciada', 'success');
                
                if (window.ScreenRecorder && typeof window.ScreenRecorder.start === 'function') {
                    await new Promise(resolve => setTimeout(resolve, 100));
                    await window.ScreenRecorder.start();
                } else {
                    throw new Error('Módulo de gravação não carregado');
                }
                
                const armBtn = document.querySelector('.fa-shield-alt').closest('.control-card');
                const disarmBtn = document.querySelector('.fa-shield').closest('.control-card');
                
                if (armBtn) armBtn.classList.add('active');
                if (disarmBtn) disarmBtn.classList.remove('active');
                
            } catch (error) {
                console.error('Erro ao armar sistema:', error);
                showNotification('Erro ao iniciar gravação', 'error');
            }
        }
    
        async function disarmSystem() {
            try {
                showNotification('Gravação encerrada', 'info');
                
                if (window.ScreenRecorder && typeof window.ScreenRecorder.stop === 'function') {
                    await window.ScreenRecorder.stop();
                }
                
                const armBtn = document.querySelector('.fa-shield-alt').closest('.control-card');
                const disarmBtn = document.querySelector('.fa-shield').closest('.control-card');
                
                if (armBtn) armBtn.classList.remove('active');
                if (disarmBtn) disarmBtn.classList.add('active');
                
            } catch (error) {
                console.error('Erro ao desarmar sistema:', error);
                showNotification('Erro ao parar gravação', 'error');
            }
        }
        
        function showMessage(message, type = 'info') {
            const toast = document.createElement('div');
            toast.className = `toast ${type}`;
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
                transform: translateY(-50px);
                transition: all 0.3s ease;
            `;
            toast.textContent = message;
            
            document.body.appendChild(toast);
            
            setTimeout(() => {
                toast.style.opacity = '1';
                toast.style.transform = 'translateY(0)';
            }, 100);
            
            setTimeout(() => {
                toast.style.opacity = '0';
                toast.style.transform = 'translateY(-50px)';
                setTimeout(() => {
                    document.body.removeChild(toast);
                }, 300);
            }, 3000);
        }

        function closeRecordingsModal() {
            document.getElementById('recordingsModal').style.display = 'none';
        }

        function loadRecordings() {
            const container = document.getElementById('recordingsContainer');
            
            // Função para formatar o tamanho do arquivo
            function formatFileSize(bytes) {
                if (bytes === 0) return '0 Bytes';
                const k = 1024;
                const sizes = ['Bytes', 'KB', 'MB', 'GB'];
                const i = Math.floor(Math.log(bytes) / Math.log(k));
                return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
            }

            // Função para extrair informações do nome do arquivo
            function extractFileInfo(file) {
                // Formato esperado: gravacao_ID_ANO-MES-DIA_HORAMINSEG_DURACAO.extensao
                // Exemplo: gravacao_1_2025-09-02_183609_000300.webm (3 minutos de duração)
                const parts = file.name.split('_');
                
                if (parts.length >= 4) {
                    const id = parts[1];
                    const datePart = parts[2];
                    const timePart = parts[3];
                    const durationAndExt = parts[4] || '';
                    
                    // Extrai a duração (últimos 6 dígitos antes da extensão)
                    let duration = '00:00:00';
                    const durationMatch = durationAndExt.match(/^(\d{6})/);
                    if (durationMatch) {
                        const dur = durationMatch[1];
                        const hours = dur.substr(0, 2);
                        const minutes = dur.substr(2, 2);
                        const seconds = dur.substr(4, 2);
                        duration = `${hours}:${minutes}:${seconds}`;
                    }
                    
                    // Formata a data para o padrão brasileiro
                    const [year, month, day] = datePart.split('-');
                    const formattedDate = `${day}/${month}/${year}`;
                    
                    // Formata o horário (HH:MM:SS) e ajusta o fuso horário (-3 horas)
                    let formattedTime = '00:00:00';
                    if (timePart && timePart.length >= 6) {
                        let hour = parseInt(timePart.substr(0, 2), 10);
                        const minute = timePart.substr(2, 2);
                        const second = timePart.substr(4, 2);
                        
                        // Subtrai 3 horas para ajustar o fuso horário
                        hour = (hour - 5 + 24) % 24; // Adiciona 24 antes do módulo para lidar com horas negativas
                        
                        // Formata a hora com 2 dígitos
                        const formattedHour = hour.toString().padStart(2, '0');
                        formattedTime = `${formattedHour}:${minute}:${second}`;
                    }
                    
                    return {
                        id: id,
                        title: `Gravação - ${formattedDate} ${formattedTime}`,
                        camera: 'Câmera Principal',
                        date: formattedDate,
                        time: formattedTime,
                        duration: duration,
                        size: file.size ? formatFileSize(file.size) : '0 MB',
                        type: 'Gravação',
                        filename: file.name
                    };
                }
                
                // Se o formato não for o esperado, retorna com informações básicas
                return {
                    id: '0',
                    title: file.name,
                    camera: 'Câmera Principal',
                    date: '--/--/----',
                    time: '--:--:--',
                    duration: '--:--:--',
                    size: file.size ? formatFileSize(file.size) : '0 MB',
                    type: 'Gravação',
                    filename: file.name
                };
            }

            // Requisição para obter a lista de gravações
            fetch('../includes/get_recordings.php')
                .then(response => response.json())
                .then(recordings => {
                    if (recordings.length === 0) {
                        showNoRecordings(container);
                        return;
                    }
                    
                    const recordingsHTML = recordings.map(recording => {
                        const fileInfo = extractFileInfo(recording);
                        if (!fileInfo) return '';
                        
                        return `
                            <div class="recording-card">
                                <div class="recording-thumbnail">
                                    <p style="text-align: center; font-size: 12px;">Clique no botão de reproduzir para ver a gravação</p>
                                </div>
                                <div class="recording-info">
                                    <div class="recording-title">${fileInfo.title}</div>
                                    <div class="recording-details">
                                        <i class="fas fa-video"></i> ${fileInfo.camera}
                                    </div>
                                    <div class="recording-details">
                                        <i class="fas fa-calendar"></i> ${fileInfo.date} às ${fileInfo.time}
                                    </div>
                                    <div class="recording-details">
                                        <i class="fas fa-hdd"></i> Tamanho: ${fileInfo.size}
                                    </div>
                                </div>
                                <div class="recording-actions">
                                    <button class="recording-btn btn-play" onclick="playRecording('${fileInfo.filename}'); event.stopPropagation();">
                                        <i class="fas fa-play"></i> Reproduzir
                                    </button>
                                    <button class="recording-btn btn-download" onclick="downloadRecording('${fileInfo.filename}'); event.stopPropagation();">
                                        <i class="fas fa-download"></i> Download
                                    </button>
                                    <button class="recording-btn btn-delete" onclick="showDeleteModal('${fileInfo.filename}'); event.stopPropagation();">
                                        <i class="fas fa-trash"></i> Excluir
                                    </button>
                                </div>
                            </div>
                        `;
                    }).join('');

                    container.innerHTML = `<div class="recordings-grid">${recordingsHTML}</div>`;
                })
                .catch(error => {
                    console.error('Erro ao carregar gravações:', error);
                    container.innerHTML = `
                        <div class="error-message">
                            <i class="fas fa-exclamation-triangle"></i>
                            <div>Erro ao carregar as gravações</div>
                            <div style="font-size: 0.9em; margin-top: 10px; opacity: 0.7;">
                                Tente atualizar a página ou verificar a conexão com o servidor.
                            </div>
                        </div>
                    `;
                });

            // Função para exibir mensagem quando não há gravações
            function showNoRecordings(container) {
                container.innerHTML = `
                    <div class="no-recordings">
                        <i class="fas fa-video-slash"></i>
                        <div>Nenhuma gravação encontrada</div>
                        <div style="font-size: 0.9em; margin-top: 10px; opacity: 0.7;">
                            As gravações aparecerão aqui quando disponíveis
                        </div>
                    </div>
                `;
            }

        }

        // Variável para armazenar o nome do arquivo a ser excluído
        let recordingToDelete = '';

        // Funções para o modal de confirmação de exclusão
        function showDeleteModal(filename) {
            recordingToDelete = filename;
            const modal = document.getElementById('deleteConfirmationModal');
            const recordingName = filename.split('_').slice(1).join(' ').replace('.webm', '');
            modal.style.display = 'block';
        }

        function closeDeleteModal() {
            const modal = document.getElementById('deleteConfirmationModal');
            modal.style.display = 'none';
            recordingToDelete = '';
        }

        function confirmDelete() {
            if (!recordingToDelete) return;
            
            const filename = recordingToDelete;
            closeDeleteModal();
            
            fetch('../includes/delete_recording.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'filename=' + encodeURIComponent(filename)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification('Gravação excluída com sucesso', 'success');
                    // Recarrega a lista de gravações
                    loadRecordings();
                } else {
                    showNotification('Erro ao excluir a gravação: ' + (data.error || 'Erro desconhecido'), 'error');
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                showNotification('Erro ao se comunicar com o servidor', 'error');
            });
        }

        // Fechar o modal ao clicar fora dele
        window.onclick = function(event) {
            const modal = document.getElementById('deleteConfirmationModal');
            if (event.target == modal) {
                closeDeleteModal();
            }
        }

        function playRecording(filename) {
            // Cria um elemento de vídeo para reproduzir o arquivo
            const videoModal = document.createElement('div');
            videoModal.style.position = 'fixed';
            videoModal.style.top = '0';
            videoModal.style.left = '0';
            videoModal.style.width = '100%';
            videoModal.style.height = '100%';
            videoModal.style.backgroundColor = 'rgba(0,0,0,0.9)';
            videoModal.style.display = 'flex';
            videoModal.style.justifyContent = 'center';
            videoModal.style.alignItems = 'center';
            videoModal.style.zIndex = '1000';
            videoModal.onclick = function() {
                document.body.removeChild(videoModal);
            };
            
            const video = document.createElement('video');
            video.controls = true;
            video.autoplay = true;
            video.style.maxWidth = '90%';
            video.style.maxHeight = '90%';
            video.src = '../gravacoes/' + filename;
            
            videoModal.appendChild(video);
            document.body.appendChild(videoModal);
        }

        function downloadRecording(filename) {
            // Cria um link temporário para download
            const link = document.createElement('a');
            link.href = '../gravacoes/' + filename;
            link.download = filename;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }

        window.onclick = function(event) {
            const modal = document.getElementById('recordingsModal');
            if (event.target === modal) {
                closeRecordingsModal();
            }
            
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

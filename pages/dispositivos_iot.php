<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit;
}

$devices = [
    [
        'id' => 1,
        'name' => 'Sensor de Temperatura - Sala',
        'type' => 'sensor',
        'category' => 'temperature',
        'status' => 'online',
        'value' => '23.5°C',
        'location' => 'Sala de Estar',
        'last_update' => date('Y-m-d H:i:s', strtotime('-2 minutes')),
        'battery' => 85
    ],
    [
        'id' => 2,
        'name' => 'Lâmpada Smart - Quarto',
        'type' => 'actuator',
        'category' => 'lighting',
        'status' => 'online',
        'value' => 'Ligada (75%)',
        'location' => 'Quarto Principal',
        'last_update' => date('Y-m-d H:i:s', strtotime('-1 minute')),
        'battery' => null
    ],
    [
        'id' => 3,
        'name' => 'Sensor de Umidade - Jardim',
        'type' => 'sensor',
        'category' => 'humidity',
        'status' => 'offline',
        'value' => '45%',
        'location' => 'Jardim',
        'last_update' => date('Y-m-d H:i:s', strtotime('-30 minutes')),
        'battery' => 12
    ],
    [
        'id' => 4,
        'name' => 'Câmera de Segurança - Entrada',
        'type' => 'sensor',
        'category' => 'security',
        'status' => 'online',
        'value' => 'Gravando',
        'location' => 'Entrada Principal',
        'last_update' => date('Y-m-d H:i:s', strtotime('-30 seconds')),
        'battery' => null
    ],
    [
        'id' => 5,
        'name' => 'Termostato - Ar Condicionado',
        'type' => 'actuator',
        'category' => 'climate',
        'status' => 'online',
        'value' => '22°C',
        'location' => 'Sala de Estar',
        'last_update' => date('Y-m-d H:i:s', strtotime('-5 minutes')),
        'battery' => null
    ],
    [
        'id' => 6,
        'name' => 'Sensor de Movimento - Corredor',
        'type' => 'sensor',
        'category' => 'motion',
        'status' => 'online',
        'value' => 'Sem movimento',
        'location' => 'Corredor',
        'last_update' => date('Y-m-d H:i:s', strtotime('-1 minute')),
        'battery' => 67
    ]
];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dispositivos IoT - Automação Residencial</title>
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
            min-height: 100vh;
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
            margin-bottom: 30px;
        }
        
        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 20px;
            max-width: 1400px;
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
        
        .nav-buttons {
            display: flex;
            gap: 10px;
            align-items: center;
        }
        
        .nav-btn {
            background-color: #6c757d;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s;
        }
        
        .nav-btn:hover {
            background-color: #5a6268;
            transform: translateY(-1px);
        }
        
        .nav-btn.primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        .nav-btn.primary:hover {
            background: linear-gradient(135deg, #764ba2 0%, #667eea 100%);
        }
        
        .page-title {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .page-title h1 {
            font-size: 2.5rem;
            margin-bottom: 10px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .page-title p {
            color: #ccc;
            font-size: 1.1rem;
        }
        
        .stats-section {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            padding: 20px;
            text-align: center;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .stat-number {
            font-size: 2rem;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .stat-label {
            color: #ccc;
            font-size: 0.9rem;
        }
        
        .controls-section {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 30px;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .controls-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            flex-wrap: wrap;
            gap: 15px;
        }
        
        .controls-title {
            font-size: 1.3rem;
            font-weight: 600;
        }
        
        .controls-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        
        .filter-section {
            display: flex;
            gap: 15px;
            align-items: center;
            flex-wrap: wrap;
        }
        
        .filter-group {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }
        
        .filter-group label {
            font-size: 0.9rem;
            color: #ccc;
        }
        
        .filter-select, .search-input {
            padding: 8px 12px;
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 5px;
            background: rgba(255, 255, 255, 0.1);
            color: white;
            font-size: 14px;
        }
        
        .filter-select option {
            background: #2c3e50;
            color: white;
        }
        
        .search-input::placeholder {
            color: #ccc;
        }
        
        .devices-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        
        .device-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            padding: 20px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .device-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
            border-color: rgba(102, 126, 234, 0.5);
        }
        
        .device-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 15px;
        }
        
        .device-info h3 {
            font-size: 1.1rem;
            margin-bottom: 5px;
            color: #fff;
        }
        
        .device-location {
            font-size: 0.9rem;
            color: #ccc;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .device-status {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .status-online {
            background: rgba(46, 204, 113, 0.2);
            color: #2ecc71;
            border: 1px solid #2ecc71;
        }
        
        .status-offline {
            background: rgba(231, 76, 60, 0.2);
            color: #e74c3c;
            border: 1px solid #e74c3c;
        }
        
        .device-icon {
            font-size: 2.5rem;
            margin-bottom: 15px;
            opacity: 0.8;
        }
        
        .icon-temperature { color: #e67e22; }
        .icon-lighting { color: #f1c40f; }
        .icon-humidity { color: #3498db; }
        .icon-security { color: #e74c3c; }
        .icon-climate { color: #2ecc71; }
        .icon-motion { color: #9b59b6; }
        
        .device-value {
            font-size: 1.5rem;
            font-weight: bold;
            margin-bottom: 10px;
            color: #667eea;
        }
        
        .device-details {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            font-size: 0.9rem;
            color: #ccc;
        }
        
        .battery-indicator {
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .battery-level {
            width: 30px;
            height: 15px;
            border: 1px solid #ccc;
            border-radius: 2px;
            position: relative;
            background: rgba(255, 255, 255, 0.1);
        }
        
        .battery-fill {
            height: 100%;
            border-radius: 1px;
            transition: all 0.3s;
        }
        
        .battery-high { background: #2ecc71; }
        .battery-medium { background: #f1c40f; }
        .battery-low { background: #e74c3c; }
        
        .device-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        
        .action-btn {
            flex: 1;
            min-width: 100px;
            padding: 8px 16px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 0.9rem;
            font-weight: 600;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 5px;
        }
        
        .btn-configure {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .btn-configure:hover {
            background: linear-gradient(135deg, #764ba2 0%, #667eea 100%);
            transform: translateY(-1px);
        }
        
        .btn-monitor {
            background: rgba(52, 152, 219, 0.2);
            color: #3498db;
            border: 1px solid #3498db;
        }
        
        .btn-monitor:hover {
            background: #3498db;
            color: white;
        }
        
        .btn-control {
            background: rgba(46, 204, 113, 0.2);
            color: #2ecc71;
            border: 1px solid #2ecc71;
        }
        
        .btn-control:hover {
            background: #2ecc71;
            color: white;
        }
        
        .loading-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(164, 164, 164, 0.5);
            z-index: 2000;
            justify-content: center;
            align-items: center;
            flex-direction: column;
        }
        
        .loading-spinner {
            text-align: center;
        }
        
        .spinner {
            width: 50px;
            height: 50px;
            border: 5px solid #f3f3f3;
            border-top: 5px solid #1e3a8a;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 0 auto 15px;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.8);
            backdrop-filter: blur(5px);
        }
        
        .modal-content {
            background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
            margin: 5% auto;
            padding: 30px;
            border-radius: 15px;
            width: 90%;
            max-width: 600px;
            max-height: 80vh;
            overflow-y: auto;
            position: relative;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            position: absolute;
            right: 20px;
            top: 15px;
            cursor: pointer;
        }
        
        .close:hover {
            color: #fff;
        }
        
        .modal h2 {
            margin-bottom: 20px;
            color: #667eea;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #ccc;
            font-weight: 600;
        }
        
        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 5px;
            background: rgba(255, 255, 255, 0.1);
            color: white;
            font-size: 14px;
        }
        
        .form-group textarea {
            resize: vertical;
            min-height: 80px;
        }
        
        .form-actions {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
            margin-top: 30px;
        }
        
        .btn-save {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 600;
        }
        
        .btn-cancel {
            background: rgba(108, 117, 125, 0.2);
            color: #6c757d;
            border: 1px solid #6c757d;
            padding: 12px 24px;
            border-radius: 5px;
            cursor: pointer;
        }
        
        @media (max-width: 768px) {
            .devices-grid {
                grid-template-columns: 1fr;
            }
            
            .header-content {
                flex-direction: column;
                gap: 15px;
            }
            
            .controls-header {
                flex-direction: column;
                align-items: stretch;
            }
            
            .filter-section {
                flex-direction: column;
            }
            
            .page-title h1 {
                font-size: 2rem;
            }
        }
    </style>
</head>
<body>
    <header>
        <div class="header-content">
            <div class="logo">
                <img src="../assets/img/logo.png" alt="Logo">
                Dispositivos IoT
            </div>
            <div class="nav-buttons">
                <a href="dashboard.php" class="nav-btn" style="text-decoration: none; display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.6rem 1.5rem; border-radius: 25px; background: linear-gradient(135deg, #3498db 0%, #2980b9 100%); color: white; font-size: 14px; transition: all 0.4s ease-in-out; position: relative; overflow: hidden; box-shadow: 0 4px 15px rgba(52, 152, 219, 0.3);">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>
                <a href="menu.php" class="nav-btn" style="text-decoration: none; display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.6rem 1.5rem; border-radius: 25px; background: linear-gradient(135deg, #2ecc71 0%, #27ae60 100%); color: white; font-size: 14px; transition: all 0.4s ease-in-out; position: relative; overflow: hidden; box-shadow: 0 4px 15px rgba(46, 204, 113, 0.3);">
                    <i class="fas fa-home"></i> Menu Principal
                </a>
                <form action="../auth/logout.php" method="post" style="display: inline;">
                    <button type="submit" class="nav-btn" style="border: none; cursor: pointer; display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.6rem 1.5rem; border-radius: 25px; background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%); color: white; font-size: 14px; transition: all 0.4s ease-in-out; position: relative; overflow: hidden; box-shadow: 0 4px 15px rgba(231, 76, 60, 0.3);">
                        <i class="fas fa-sign-out-alt"></i> Sair
                    </button>
                </form>
            </div>
        </div>
    </header>
    
    <div class="container">
        <div class="page-title">
            <h1><i class="fas fa-microchip"></i> Dispositivos IoT</h1>
            <p>Configure e monitore seus dispositivos IoT em tempo real</p>
        </div>
        
        <div class="controls-section">
            <div class="controls-header">
                <div class="controls-title">
                    <i class="fas fa-sliders-h"></i> Controles e Filtros
                </div>
                <div class="controls-actions">
                    <button class="nav-btn primary" onclick="showMessage('Funcionalidade em desenvolvimento!')">
                        <i class="fas fa-plus"></i> Adicionar Dispositivo
                    </button>
                    <button class="nav-btn" onclick="refreshDevices()">
                        <i class="fas fa-sync-alt"></i> Atualizar
                    </button>
                </div>
            </div>
            
            <div class="filter-section">
                <div class="filter-group">
                    <label>Buscar:</label>
                    <input type="text" class="search-input" placeholder="Nome do dispositivo..." onkeyup="filterDevices()">
                </div>
                <div class="filter-group">
                    <label>Status:</label>
                    <select class="filter-select" onchange="filterDevices()">
                        <option value="">Todos</option>
                        <option value="online">Online</option>
                        <option value="offline">Offline</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label>Tipo:</label>
                    <select class="filter-select" onchange="filterDevices()">
                        <option value="">Todos</option>
                        <option value="sensor">Sensores</option>
                        <option value="actuator">Atuadores</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label>Categoria:</label>
                    <select class="filter-select" onchange="filterDevices()">
                        <option value="">Todas</option>
                        <option value="temperature">Temperatura</option>
                        <option value="lighting">Iluminação</option>
                        <option value="humidity">Umidade</option>
                        <option value="security">Segurança</option>
                        <option value="climate">Climatização</option>
                        <option value="motion">Movimento</option>
                    </select>
                </div>
            </div>
        </div>
        
        <div class="devices-grid" id="devicesGrid">
            <?php foreach ($devices as $device): ?>
            <div class="device-card" data-type="<?php echo $device['type']; ?>" data-category="<?php echo $device['category']; ?>" data-name="<?php echo strtolower($device['name']); ?>">
                <div class="device-header">
                    <div class="device-info">
                        <h3><?php echo htmlspecialchars($device['name']); ?></h3>
                        <div class="device-location">
                            <i class="fas fa-map-marker-alt"></i>
                            <?php echo htmlspecialchars($device['location']); ?>
                        </div>
                    </div>
                </div>
                
                <div class="device-icon icon-<?php echo $device['category']; ?>">
                    <?php
                    $icons = [
                        'temperature' => 'fas fa-thermometer-half',
                        'lighting' => 'fas fa-lightbulb',
                        'humidity' => 'fas fa-tint',
                        'security' => 'fas fa-shield-alt',
                        'climate' => 'fas fa-wind',
                        'motion' => 'fas fa-walking'
                    ];
                    ?>
                    <i class="<?php echo $icons[$device['category']] ?? 'fas fa-microchip'; ?>"></i>
                </div>
                
                <div class="device-value">
                    <?php echo htmlspecialchars($device['value']); ?>
                </div>
                
                <div class="device-actions">
                    <button class="action-btn btn-monitor" onclick="showMessage('Monitoramento em tempo real em desenvolvimento!')">
                        <i class="fas fa-chart-line"></i> Monitorar
                    </button>
                    <?php if ($device['type'] === 'actuator'): ?>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    
    <div id="loadingOverlay" class="loading-overlay">
        <div class="loading-spinner">
            <div class="spinner"></div>
            <p>Atualizando dispositivos...</p>
        </div>
    </div>
    
    <script>
        function showMessage(message, type = 'info') {
            alert(message);
        }
        
        function filterDevices() {
            const searchTerm = document.querySelector('.search-input').value.toLowerCase();
            const statusFilter = document.querySelectorAll('.filter-select')[0].value;
            const typeFilter = document.querySelectorAll('.filter-select')[1].value;
            const categoryFilter = document.querySelectorAll('.filter-select')[2].value;
            
            const cards = document.querySelectorAll('.device-card');
            
            cards.forEach(card => {
                const name = card.dataset.name;
                const status = card.dataset.status;
                const type = card.dataset.type;
                const category = card.dataset.category;
                
                const matchesSearch = name.includes(searchTerm);
                const matchesStatus = !statusFilter || status === statusFilter;
                const matchesType = !typeFilter || type === typeFilter;
                const matchesCategory = !categoryFilter || category === categoryFilter;
                
                if (matchesSearch && matchesStatus && matchesType && matchesCategory) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        }
        
        function refreshDevices() {
            const overlay = document.getElementById('loadingOverlay');
            overlay.style.display = 'flex';
            
            setTimeout(() => {
                location.reload();
            }, 3000);
        }
        
        function controlDevice(deviceId) {
            const deviceName = document.querySelector(`[data-name*="dispositivo-${deviceId}"]`)?.querySelector('h3')?.textContent || `Dispositivo ${deviceId}`;
            const action = confirm(`Deseja ligar/desligar o dispositivo: ${deviceName}?`);
            
            if (action) {
                showMessage(`Comando enviado para o dispositivo ${deviceId}!`, 'success');
            }
        }
        
        setInterval(() => {
            console.log('Auto-refresh dispositivos IoT...');
        }, 30000);

        document.addEventListener('DOMContentLoaded', function() {
            console.log('Página de Dispositivos IoT carregada');
        });
    </script>
</body>
</html>

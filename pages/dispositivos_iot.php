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
        'location' => 'Sala de Estar'
    ],
    [
        'id' => 2,
        'name' => 'Lâmpadas',
        'type' => 'actuator',
        'category' => 'lighting',
        'status' => 'online',
        'value' => 'Ligada (75%)',
        'location' => 'Quarto Principal'
    ],
    [
        'id' => 3,
        'name' => 'Termostato - Ar Condicionado',
        'type' => 'actuator',
        'category' => 'climate',
        'status' => 'online',
        'value' => '22°C',
        'location' => 'Sala de Estar'
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
            padding: 25px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: all 0.3s ease;
            text-align: center;
        }
        
        .device-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
            border-color: rgba(102, 126, 234, 0.5);
        }
        
        .device-icon {
            font-size: 3.5rem;
            margin-bottom: 15px;
            display: block;
        }
        
        .icon-temperature { color: #e67e22; }
        .icon-lighting { color: #f1c40f; }
        .icon-humidity { color: #3498db; }
        .icon-security { color: #e74c3c; }
        .icon-climate { color: #2ecc71; }
        .icon-motion { color: #9b59b6; }
        
        .device-info h3 {
            font-size: 1.2rem;
            margin: 0 0 15px 0;
            color: #fff;
            font-weight: 600;
        }
        
        .device-actions {
            margin-top: 15px;
        }
        
        .btn-monitor {
            background: rgba(52, 152, 219, 0.2);
            color: #3498db;
            border: 1px solid #3498db;
            padding: 8px 15px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 0.9rem;
            font-weight: 600;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
        }
        
        .btn-monitor:hover {
            background: #3498db;
            color: white;
        }
        
        .btn-monitor i {
            margin-right: 5px;
        }
        
        .btn-register {
            background: rgba(76, 175, 80, 0.2);
            color: #4CAF50;
            border: 1px solid #4CAF50;
            padding: 8px 15px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 0.9rem;
            font-weight: 600;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            margin-left: 15px;
        }
        
        .btn-register:hover {
            background: #4CAF50;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(76, 175, 80, 0.3);
        }
        
        .btn-register i {
            margin-right: 5px;
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
            <p>Monitore seus dispositivos IoT</p>
        </div>
        
        <div class="controls-section">
            <div class="controls-header">
                <div class="controls-title">
                    <i class="fas fa-sliders-h"></i> Controles e Filtros
                </div>
                <div class="controls-actions">
                    <button type="button" class="nav-btn" onclick="filterDevices()">
                        <i class="fas fa-sync-alt"></i> Atualizar
                    </button>
                </div>
            </div>
            
            <div class="filter-section">
                <div class="filter-group">
                    <label>Buscar:</label>
                    <input type="text" id="searchInput" class="search-input" placeholder="Nome do dispositivo...">
                </div>
            </div>
        </div>
        
        <div class="devices-grid" id="devicesGrid">
            <?php foreach ($devices as $device): ?>
            <div class="device-card">
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
                
                <div class="device-info">
                    <h3><?php echo htmlspecialchars($device['name']); ?></h3>
                </div>
                
                <div class="device-actions">
                    <a href="#" class="btn-monitor" onclick="event.stopPropagation(); showMonitoringModal(<?php echo $device['id']; ?>, '<?php echo addslashes($device['name']); ?>', '<?php echo addslashes($device['location']); ?>', '<?php echo $device['category']; ?>')">
                        <i class="fas fa-chart-line"></i> Monitorar
                    </a>
                    <?php if ($device['category'] === 'lighting'): ?>
                    <a href="#" class="btn-register" onclick="event.stopPropagation(); registerDevice(<?php echo $device['id']; ?>)">
                        <i class="fas fa-plus-circle"></i> Cadastrar
                    </a>
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
    
    <div id="monitoringModal" class="modal">
        <div class="modal-content" style="max-width: 90%; max-height: 90vh; overflow-y: auto;">
            <div class="modal-header">
                <h2><i class="fas fa-tachometer-alt"></i> Monitoramento de Lâmpadas</h2>
                <span class="close-btn" onclick="closeMonitoringModal()">&times;</span>
            </div>
            <div class="modal-body">
                <div class="status-summary">
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <div class="card bg-light">
                                <div class="card-body text-center">
                                    <h6 class="card-subtitle mb-2 text-muted">Total de Lâmpadas</h6>
                                    <h2 class="display-4" id="total-lights">0</h2>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-light">
                                <div class="card-body text-center">
                                    <h6 class="card-subtitle mb-2 text-muted">Lâmpadas Acesas</h6>
                                    <h2 class="display-4" id="lights-on">0</h2>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-light">
                                <div class="card-body text-center">
                                    <h6 class="card-subtitle mb-2 text-muted">Porcentagem Acesa</h6>
                                    <h2 class="display-4" id="percentage-on">0%</h2>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                </div>
                <div class="device-info" style="margin-bottom: 20px; text-align: center;">
                    <h3 id="deviceName" style="margin: 0; color: #fff;"></h3>
                    <p id="deviceLocation" style="margin: 5px 0 0; color: #aaa;"></p>
                </div>
            </div>
        </div>
    </div>

    <style>
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.7);
            overflow: auto;
        }

        .modal-content {
            background: linear-gradient(145deg, #1a1e3a 0%, #0a0f2c 100%);
            margin: 5% auto;
            padding: 0;
            border-radius: 10px;
            width: 80%;
            max-width: 900px;
            box-shadow: 0 5px 30px rgba(0, 0, 0, 0.5);
            border: 1px solid rgba(255, 255, 255, 0.1);
            max-height: 80vh;
            display: flex;
            flex-direction: column;
            animation: modalFadeIn 0.3s ease-out;
        }
        
        @keyframes modalFadeIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        #lampadaModal .modal-content {
            max-width: 500px;
            max-height: 90vh;
        }
        
        #lampadaModal .modal-header {
            background: linear-gradient(135deg, #4CAF50 0%, #45a049 100%);
            border-bottom: none;
            border-radius: 10px 10px 0 0;
            padding: 15px 20px;
        }
        
        #lampadaModal .modal-header h2 {
            margin: 0;
            font-size: 1.3rem;
            color: white;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        #lampadaModal .modal-body {
            padding: 25px;
            flex: 1;
            overflow-y: auto;
        }
        
        #lampadaForm .form-group {
            margin-bottom: 20px;
        }
        
        #lampadaForm label {
            display: block;
            margin-bottom: 8px;
            color: #e0e0e0;
            font-weight: 500;
        }
        
        #lampadaForm input[type="text"] {
            width: 100%;
            padding: 12px 15px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 6px;
            color: #fff;
            font-size: 1rem;
            transition: all 0.3s ease;
        }
        
        #lampadaForm input[type="text"]:focus {
            outline: none;
            border-color: #4CAF50;
            box-shadow: 0 0 0 2px rgba(76, 175, 80, 0.3);
        }
        
        #lampadaForm .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 12px;
            margin-top: 30px;
            padding-top: 15px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        #lampadaForm .btn-cancel,
        #lampadaForm .btn-confirm {
            padding: 10px 24px;
            border: none;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s;
        }
        
        #lampadaForm .btn-cancel {
            background: rgba(255, 255, 255, 0.1);
            color: #e0e0e0;
        }
        
        #lampadaForm .btn-cancel:hover {
            background: rgba(255, 255, 255, 0.15);
        }
        
        #lampadaForm .btn-confirm {
            background: linear-gradient(135deg, #4CAF50 0%, #45a049 100%);
            color: white;
        }
        
        #lampadaForm .btn-confirm:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(76, 175, 80, 0.3);
        }
        
        @media (max-width: 768px) {
            #lampadaModal .modal-content {
                width: 90%;
                margin: 20px auto;
            }
            
            #lampadaForm .form-actions {
                flex-direction: column;
            }
            
            #lampadaForm .btn-cancel,
            #lampadaForm .btn-confirm {
                width: 100%;
                justify-content: center;
            }
        }

        .modal-header {
            padding: 20px;
            background: rgba(0, 0, 0, 0.2);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-header h2 {
            margin: 0;
            color: #fff;
            font-size: 1.5rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .close-btn {
            color: #aaa;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
            transition: color 0.3s;
        }

        .close-btn:hover {
            color: #fff;
        }

        .modal-body {
            padding: 20px;
            overflow-y: auto;
            flex: 1;
        }

        .status-summary {
            margin-bottom: 30px;
        }

        .status-card {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .status-card h3 {
            margin-top: 0;
            color: #fff;
            margin-bottom: 20px;
        }

        .status-indicator {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            margin-bottom: 20px;
        }

        .status-light {
            width: 15px;
            height: 15px;
            border-radius: 50%;
            background-color: #ff4444;
            box-shadow: 0 0 10px #ff4444;
        }

        .status-light.on {
            background-color: #00C851;
            box-shadow: 0 0 15px #00C851;
        }

        #lightStatusText {
            color: #fff;
            font-weight: 600;
            font-size: 1.1rem;
        }

        .status-percentage {
            margin-top: 20px;
        }

        .percentage-circle {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            background: conic-gradient(#00C851 0%, #0a0f2c 0%);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
            position: relative;
            box-shadow: 0 0 20px rgba(0, 200, 81, 0.3);
        }

        .percentage-circle::before {
            content: '';
            position: absolute;
            width: 100px;
            height: 100px;
            background: #1a1e3a;
            border-radius: 50%;
        }

        #percentageValue {
            position: relative;
            color: #fff;
            font-size: 2rem;
            font-weight: bold;
        }

        .status-percentage p {
            color: #aaa;
            margin: 5px 0 0;
        }

        .lights-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }

        .light-card {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 10px;
            padding: 15px;
            text-align: center;
            border: 1px solid rgba(255, 255, 255, 0.1);
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .light-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
        }

        .light-icon {
            font-size: 2.5rem;
            margin-bottom: 10px;
        }

        .light-status {
            display: inline-block;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            margin-right: 5px;
        }

        .light-status.off {
            background-color: #ff4444;
            box-shadow: 0 0 10px #ff4444;
        }

        .light-status.on {
            background-color: #00C851;
            box-shadow: 0 0 15px #00C851;
        }

        .light-name {
            color: #fff;
            margin: 5px 0;
            font-weight: 500;
        }

        .light-location {
            color: #aaa;
            font-size: 0.9rem;
            margin: 5px 0;
        }

        .light-value {
            font-weight: bold;
            margin: 5px 0;
            color: #00C851;
        }

        @media (max-width: 768px) {
            .modal-content {
                width: 95%;
                margin: 5% auto;
            }

            .lights-grid {
                grid-template-columns: 1fr 1fr;
            }
        }

        @media (max-width: 480px) {
            .lights-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>

    <script>
        function openMonitoringModal() {
            const modal = document.getElementById('monitoringModal');
            if (modal) {
                modal.style.display = 'block';
                
                startMonitoringUpdates();
            }
        }
        
        function updateLightsTable(lights) {
            const tbody = document.getElementById('lights-table-body');
            if (!tbody) {
                console.error('Elemento tbody não encontrado');
                return;
            }
            
            tbody.innerHTML = '';
            
            if (!lights || lights.length === 0) {
                const row = document.createElement('tr');
                row.innerHTML = '<td colspan="2" class="text-center">Nenhuma lâmpada encontrada</td>';
                tbody.appendChild(row);
                return;
            }
            
            let lightsOn = 0;
            let hasActiveLights = false;
            
            try {
                const activeLights = lights.filter(light => {
                    const status = light.Status ? light.Status.toLowerCase() : 'off';
                    return status === 'on';
                });
                
                if (activeLights.length === 0) {
                    const row = document.createElement('tr');
                    row.innerHTML = '<td colspan="2" class="text-center">Nenhuma lâmpada acesa no momento</td>';
                    tbody.appendChild(row);
                } else {
                    activeLights.forEach(light => {
                        const row = document.createElement('tr');
                        const statusClass = 'bg-success';
                        
                        row.innerHTML = `
                            <td style="padding-right: 50px;">${light.Nome || 'Lâmpada não identificada'}</td>
                            <td><span class="badge ${statusClass}">Ligada</span></td>
                        `;
                        tbody.appendChild(row);
                        lightsOn++;
                    });
                    hasActiveLights = true;
                }
                
                const totalLightsEl = document.getElementById('total-lights');
                const lightsOnEl = document.getElementById('lights-on');
                const percentageEl = document.getElementById('percentage-on');
                const lastUpdatedEl = document.getElementById('last-updated');
                
                if (totalLightsEl) totalLightsEl.textContent = lights.length;
                if (lightsOnEl) lightsOnEl.textContent = lightsOn;
                
                const percentage = lights.length > 0 ? Math.round((lightsOn / lights.length) * 100) : 0;
                if (percentageEl) percentageEl.textContent = `${percentage}%`;
                if (lastUpdatedEl) lastUpdatedEl.textContent = `Atualizado em: ${new Date().toLocaleTimeString()}`;
                
            } catch (error) {
                console.error('Erro ao atualizar tabela de lâmpadas:', error);
                const row = document.createElement('tr');
                row.innerHTML = '<td colspan="5" class="text-center text-danger">Erro ao carregar dados das lâmpadas</td>';
                tbody.appendChild(row);
            }
        }
        
        function startMonitoringUpdates() {
            const loadData = () => {
                fetch('../includes/monitor_lights.php')
                    .then(response => response.json())
                    .then(data => {
                        if (data.success && data.lights) {
                            updateLightsTable(data.lights);
                        } else {
                            console.error('Erro ao carregar dados:', data.message || 'Erro desconhecido');
                            const tbody = document.getElementById('lights-table-body');
                            if (tbody) {
                                tbody.innerHTML = '<tr><td colspan="5" class="text-center">Erro ao carregar dados. Tente novamente.</td></tr>';
                            }
                        }
                    })
                    .catch(error => {
                        console.error('Erro ao buscar dados:', error);
                        const tbody = document.getElementById('lights-table-body');
                        if (tbody) {
                            tbody.innerHTML = '<tr><td colspan="5" class="text-center">Erro de conexão. Verifique sua internet.</td></tr>';
                        }
                    });
            };
            
            loadData();
            
            if (window.monitoringInterval) {
                clearInterval(window.monitoringInterval);
            }
            
            window.monitoringInterval = setInterval(loadData, 5000);
        }
        
        function closeMonitoringModal() {
            const modal = document.getElementById('monitoringModal');
            if (modal) {
                modal.style.display = 'none';
                if (window.monitoringInterval) {
                    clearInterval(window.monitoringInterval);
                }
            }
        }
        
        function openLampadaModal(deviceId) {
            const modal = document.getElementById('lampadaModal');
            if (modal) {
                modal.style.display = 'block';
                document.getElementById('lampadaNome').focus();
            } else {
                console.error('Modal não encontrado');
            }
        }
        
        function closeLampadaModal() {
            const modal = document.getElementById('lampadaModal');
            if (modal) {
                modal.style.display = 'none';
            }
        }
        
        function registerDevice(deviceId) {
            openLampadaModal(deviceId);
        }
        
        function showMonitoringModal(deviceId, deviceName, deviceLocation, deviceCategory) {
            openMonitoringModal();
        }

        function closeMonitoringModal() {
            const modal = document.getElementById('monitoringModal');
            if (modal.dataset.intervalId) {
                clearInterval(parseInt(modal.dataset.intervalId));
            }
            modal.style.display = 'none';
        }

        async function updateLightStatus(deviceId, deviceCategory) {
            try {
                const response = await fetch('../get_light_status.php');
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                const data = await response.json();
                
                if (!data) {
                    throw new Error('No data received from server');
                }
                
                updateLightStatusUI(data, deviceId, deviceCategory);
            } catch (error) {
                console.error('Error fetching light status:', error);
                const statusText = document.getElementById('lightStatusText');
                const statusLight = document.getElementById('lightStatusLight');
                
                if (statusText) statusText.textContent = 'Erro ao carregar status';
                if (statusLight) statusLight.classList.remove('on');
            }
        }

        function updateLightStatusUI(data, deviceId, deviceCategory) {
            if (!data || typeof data.status === 'undefined') {
                console.error('Invalid data received:', data);
                return;
            }
            
            const lightStatus = data.status;
            const lightStatusElement = document.getElementById('lightStatusText');
            const lightStatusLight = document.getElementById('lightStatusLight');
            const percentageCircle = document.getElementById('percentageCircle');
            const percentageValue = document.getElementById('percentageValue');
            const lightsGrid = document.getElementById('lightsGrid');
            
            if (!lightStatusElement || !lightStatusLight) {
                console.error('Required DOM elements not found');
                return;
            }
            
            if (lightStatus === 'ON') {
                lightStatusElement.textContent = 'Ligado';
                lightStatusLight.classList.add('on');
            } else {
                lightStatusElement.textContent = 'Desligado';
                lightStatusLight.classList.remove('on');
            }
            
            const iconMap = {
                'lighting': 'lightbulb',
                'temperature': 'thermometer-half',
                'climate': 'wind',
                'security': 'shield-alt',
                'humidity': 'tint',
                'motion': 'walking'
            };
            
            const icon = iconMap[deviceCategory] || 'lightbulb';
            const deviceName = document.getElementById('deviceName').textContent;
            const deviceLocation = document.getElementById('deviceLocation').textContent;
            
            const percentage = lightStatus === 'ON' ? 100 : 0;
            
            if (percentageCircle) {
                percentageCircle.style.background = `conic-gradient(#00C851 ${percentage}%, #0a0f2c ${percentage}%)`;
            }
            
            if (percentageValue) {
                percentageValue.textContent = `${percentage}%`;
            }
            
            if (lightsGrid) {
                lightsGrid.innerHTML = `
                <div class="light-card">
                    <div class="light-icon">
                        <i class="fas fa-${icon} ${lightStatus === 'ON' ? 'text-success' : 'text-muted'}" 
                           style="color: ${lightStatus === 'ON' ? '#00C851' : '#6c757d'}"></i>
                    </div>
                    <div class="light-status ${lightStatus === 'ON' ? 'on' : 'off'}"></div>
                    <h4 class="light-name">${deviceName}</h4>
                    <p class="light-location">${deviceLocation}</p>
                    <p class="light-value">${lightStatus === 'ON' ? 'Ligado' : 'Desligado'}</p>
                </div>
            `;
            }
        }

        window.onclick = function(event) {
            const modal = document.getElementById('monitoringModal');
            if (event.target === modal) {
                closeMonitoringModal();
            }
        }

        function showMessage(message, type = 'info') {
            const toast = document.createElement('div');
            toast.className = `toast toast-${type}`;
            toast.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                background: ${type === 'success' ? '#27ae60' : type === 'error' ? '#e74c3c' : type === 'warning' ? '#f39c12' : '#4a90e2'};
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
                display: flex;
                align-items: center;
                gap: 10px;
            `;
            
            let icon = '';
            switch(type) {
                case 'success':
                    icon = '<i class="fas fa-check-circle"></i>';
                    break;
                case 'error':
                    icon = '<i class="fas fa-times-circle"></i>';
                    break;
                case 'warning':
                    icon = '<i class="fas fa-exclamation-triangle"></i>';
                    break;
                default:
                    icon = '<i class="fas fa-info-circle"></i>';
            }
            
            toast.innerHTML = `${icon} ${message}`;
            
            document.body.appendChild(toast);
            
            setTimeout(() => {
                toast.style.opacity = '1';
                toast.style.transform = 'translateY(0)';
            }, 100);
            
            setTimeout(() => {
                toast.style.opacity = '0';
                toast.style.transform = 'translateY(-50px)';
                setTimeout(() => {
                    if (toast.parentNode) {
                        document.body.removeChild(toast);
                    }
                }, 300);
            }, 3000);
        }
        
        function filterDevices() {
            const searchTerm = document.getElementById('searchInput').value.toLowerCase();
            const cards = document.querySelectorAll('.device-card');
            
            cards.forEach(card => {
                const name = card.querySelector('h3').textContent.toLowerCase();
                const isVisible = name.includes(searchTerm);
                
                if (searchTerm === '' || isVisible) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        }
        
        document.getElementById('searchInput').addEventListener('keyup', function(event) {
            if (event.key === 'Enter') {
                filterDevices();
            }
        });
        
        function refreshDevices() {
            const overlay = document.getElementById('loadingOverlay');
            overlay.style.display = 'flex';
            
            document.getElementById('searchInput').value = '';
            
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
        
        document.addEventListener('DOMContentLoaded', function() {
            
            const lampadaModal = document.getElementById('lampadaModal');
            const closeModal = document.querySelector('.close-lampada');
            const lampadaForm = document.getElementById('lampadaForm');
            
            if (!lampadaModal || !closeModal || !lampadaForm) {
                console.error('Elementos do modal não encontrados');
                return;
            }

            closeModal.onclick = function() {
                lampadaModal.style.display = 'none';
                lampadaForm.reset();
            };

            window.onclick = function(event) {
                if (event.target === lampadaModal) {
                    lampadaModal.style.display = 'none';
                    lampadaForm.reset();
                }
            };

            lampadaForm.onsubmit = function(e) {
                e.preventDefault();
                const nome = document.getElementById('lampadaNome').value.trim();
                const comodo = document.getElementById('lampadaComodo').value.trim();
                
                if (!nome || !comodo) {
                    showMessage('Por favor, preencha todos os campos.', 'warning');
                    return false;
                }
                
                fetch('processar_lampada.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `action=adicionar&nome=${encodeURIComponent(nome)}&comodo=${encodeURIComponent(comodo)}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showMessage('Lâmpada cadastrada com sucesso!', 'success');
                        lampadaModal.style.display = 'none';
                        lampadaForm.reset();
                        window.location.reload();
                    } else {
                        throw new Error(data.message || 'Erro ao cadastrar lâmpada');
                    }
                })
                .catch(error => {
                    console.error('Erro:', error);
                    showMessage(error.message || 'Erro ao processar a requisição', 'error');
                });
                
                return false;
            };
        });
    </script>
    
    <div id="lampadaModal" class="modal">
        <div class="modal-content" style="max-width: 500px;">
            <div class="modal-header">
                <h2><i class="fas fa-lightbulb"></i> Cadastrar Nova Lâmpada</h2>
                <span class="close-lampada">&times;</span>
            </div>
            <div class="modal-body">
                <form id="lampadaForm">
                    <div class="form-group">
                        <label for="lampadaNome">Nome da Lâmpada</label>
                        <input type="text" id="lampadaNome" placeholder="Ex: Lâmpada da Sala" required>
                    </div>
                    <div class="form-group">
                        <label for="lampadaComodo">Cômodo</label>
                        <input type="text" id="lampadaComodo" placeholder="Ex: Sala, Quarto, Cozinha" required>
                    </div>
                    <div class="form-actions" style="margin-top: 20px; text-align: right;">
                        <button type="button" class="btn-cancel" onclick="document.getElementById('lampadaModal').style.display='none'">
                            Cancelar
                        </button>
                        <button type="submit" class="btn-confirm">
                            <i class="fas fa-save"></i> Salvar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>

<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('log_errors', 1);
error_reporting(E_ALL);

session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit;
}

// Handle AJAX requests
if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
    // Set JSON header for AJAX responses
    header('Content-Type: application/json');
    
    // Function to send JSON response and exit
    function sendJsonResponse($data) {
        // Clear any previous output
        if (ob_get_length()) ob_clean();
        echo json_encode($data);
        exit;
    }
    
    // Handle thermostat form submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        try {
            // Include database connection
            require_once '../config/database.php';
            
            // Check if this is a thermostat form submission
            $action = $_POST['action'] ?? '';
            
            if ($action === 'save_thermostat') {
                // Validate input
                if (empty($_POST['nome']) || empty($_POST['comodo'])) {
                    throw new Exception('Por favor, preencha todos os campos obrigatórios.');
                }
                
                $nome = trim($_POST['nome']);
                $comodo = trim($_POST['comodo']);
                $user_id = $_SESSION['user_id'];
                
                // Prepare and execute the query using PDO
                $sql = "INSERT INTO Temperaturas (Nome, Comodo, ID_Usuario) VALUES (:nome, :comodo, :user_id)";
                $stmt = $conn->prepare($sql);
                
                // Bind parameters
                $stmt->bindParam(':nome', $nome, PDO::PARAM_STR);
                $stmt->bindParam(':comodo', $comodo, PDO::PARAM_STR);
                $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
                
                // Execute the query
                if ($stmt->execute()) {
                    sendJsonResponse([
                        'success' => true,
                        'message' => 'arcondicionado cadastrado com sucesso!',
                        'id' => $conn->lastInsertId()
                    ]);
                } else {
                    $errorInfo = $stmt->errorInfo();
                    throw new Exception('Erro ao cadastrar arcondicionado: ' . ($errorInfo[2] ?? 'Erro desconhecido'));
                }
            } else {
                throw new Exception('Ação inválida');
            }
            
        } catch (PDOException $e) {
            sendJsonResponse([
                'success' => false,
                'message' => 'Erro no banco de dados: ' . $e->getMessage()
            ]);
        } catch (Exception $e) {
            sendJsonResponse([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
    
    // If we get here, the AJAX request wasn't handled
    sendJsonResponse([
        'success' => false,
        'message' => 'Requisição inválida',
        'debug' => [
            'method' => $_SERVER['REQUEST_METHOD'],
            'is_ajax' => true,
            'post_data' => $_POST
        ]
    ]);
}

// If we get here, it's a normal page load
// Include database connection for normal page load
try {
    require_once '../config/database.php';
    
    // Fetch thermostats for the current user
    $stmt = $conn->prepare("SELECT * FROM Temperaturas WHERE ID_Usuario = :user_id");
    $stmt->bindParam(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
    $stmt->execute();
    $thermostats = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    // Log the error but don't stop the page from loading
    error_log('Database error: ' . $e->getMessage());
    $thermostats = [];
}

$devices = [
    [
        'id' => 1,
        'name' => 'Termostato',
        'type' => 'sensor',
        'category' => 'temperature',
        'status' => 'online',
        'value' => '--°C',
        'location' => 'Sala de Estar',
        'sensor_id' => 'both_temps'  // Novo ID para indicar que mostra ambos os sensores
    ],
    [
        'id' => 4,
        'name' => 'Lâmpadas',
        'type' => 'actuator',
        'category' => 'lighting',
        'status' => 'online',
        'value' => 'Ligada (75%)',
        'location' => 'Quarto Principal'
    ],
    [
        'id' => 5,
        'name' => 'Ar Condicionado',
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
        
        .sensor-data {
            margin: 20px 0;
            padding: 15px;
            background: rgba(0, 0, 0, 0.2);
            border-radius: 10px;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .sensor-reading {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            margin: 10px 0;
        }
        
        .sensor-reading i {
            font-size: 1.5rem;
        }
        
        .sensor-reading .label {
            font-size: 0.9rem;
            color: rgba(255, 255, 255, 0.7);
        }
        
        .sensor-reading .value {
            font-size: 2rem;
            font-weight: bold;
            color: #fff;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
        }
        
        .device-actions {
            margin-top: 15px;
            display: flex;
            flex-direction: column;
            gap: 10px;
            width: 100%;
        }
        
        .btn-monitor {
            width: 100%;
            padding: 10px 15px;
            border: none;
            border-radius: 6px;
            font-size: 0.9rem;
            font-weight: 500;
            cursor: pointer;
            text-align: center;
            text-decoration: none;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: all 0.2s ease;
            background: rgba(52, 152, 219, 0.2);
            color: #3498db;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }
        
        .btn-monitor:hover {
            background: rgba(52, 152, 219, 0.3);
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
        }
        
        .btn-monitor:active {
            transform: translateY(0);
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }
        
        /* Estilo específico para o botão de cadastrar arcondicionado */
        .device-card[data-category="climate"] .btn-monitor {
            background: linear-gradient(135deg, rgba(33, 150, 243, 0.2) 0%, rgba(25, 118, 210, 0.2) 100%);
            color: #2196F3;
            border: 1px solid rgba(33, 150, 243, 0.3);
        }
        
        .device-card[data-category="climate"] .btn-monitor:hover {
            background: linear-gradient(135deg, rgba(33, 150, 243, 0.3) 0%, rgba(25, 118, 210, 0.3) 100%);
        }
        
        /* Estilo específico para o botão de cadastrar lâmpada */
        .device-card[data-category="lighting"] .btn-monitor {
            background: linear-gradient(135deg, rgba(255, 193, 7, 0.2) 0%, rgba(255, 152, 0, 0.2) 100%);
            color: #FFC107;
            border: 1px solid rgba(255, 193, 7, 0.3);
        }
        
        .device-card[data-category="lighting"] .btn-monitor:hover {
            background: linear-gradient(135deg, rgba(255, 193, 7, 0.3) 0%, rgba(255, 152, 0, 0.3) 100%);
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
        
        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-top: 30px;
        }
        
        .btn-confirm {
            background: linear-gradient(135deg, #4CAF50 0%, #45a049 100%);
            color: white;
            border: none;
            padding: 10px 24px;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }
        
        .btn-confirm:hover {
            background: linear-gradient(135deg, #45a049 0%, #3d8b40 100%);
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }
        
        .btn-confirm:active {
            transform: translateY(0);
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }
        
        .btn-cancel {
            background: rgba(108, 117, 125, 0.1);
            color: #f8f9fa;
            border: 1px solid #6c757d;
            padding: 10px 20px;
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
            <div class="device-card" data-category="<?php echo $device['category']; ?>">
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
                
                <?php if ($device['category'] === 'temperature' && isset($device['sensor_id']) && $device['sensor_id'] === 'both_temps'): ?>
                <div class="sensor-data" style="display: flex; justify-content: space-around; width: 100%;">
                    <div class="sensor-reading" style="margin: 0 10px;">
                        <i class="fas fa-thermometer-half" style="color: #ff6b6b;"></i>
                        <div>
                            <div class="label">Andar de Cima</div>
                            <div class="value" data-sensor-id="temp1">--°C</div>
                        </div>
                    </div>
                    <div class="sensor-reading" style="margin: 0 10px;">
                        <i class="fas fa-thermometer-half" style="color: #4a90e2;"></i>
                        <div>
                            <div class="label">Andar de Baixo</div>
                            <div class="value" data-sensor-id="temp2">--°C</div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                
                <div class="device-actions">
                    <?php if ($device['category'] === 'lighting'): ?>
                        <button type="button" class="btn-monitor" onclick="event.stopPropagation(); document.getElementById('lampadaModal').style.display='block'">
                            <i class="fas fa-plus-circle"></i> Cadastrar Lâmpada
                        </button>
                        <a href="#" class="btn-monitor" onclick="event.stopPropagation(); showMonitoringModal(<?php echo $device['id']; ?>, '<?php echo addslashes($device['name']); ?>', '<?php echo addslashes($device['location']); ?>', '<?php echo $device['category']; ?>')">
                            <i class="fas fa-chart-line"></i> Monitorar
                        </a>
                    <?php elseif ($device['category'] === 'climate'): ?>
                        <button type="button" class="btn-monitor" onclick="event.stopPropagation(); document.getElementById('termostatoModal').style.display='block'">
                            <i class="fas fa-thermometer-half"></i> Cadastrar Ar-Condicionado
                        </button>
                        <a href="#" class="btn-monitor" onclick="event.stopPropagation(); showMonitoringModal(<?php echo $device['id']; ?>, '<?php echo addslashes($device['name']); ?>', '<?php echo addslashes($device['location']); ?>', '<?php echo $device['category']; ?>')">
                            <i class="fas fa-chart-line"></i> Monitorar
                        </a>
                    <?php elseif ($device['category'] !== 'temperature'): ?>
                        <a href="#" class="btn-monitor" onclick="event.stopPropagation(); showMonitoringModal(<?php echo $device['id']; ?>, '<?php echo addslashes($device['name']); ?>', '<?php echo addslashes($device['location']); ?>', '<?php echo $device['category']; ?>')">
                            <i class="fas fa-chart-line"></i> Monitorar
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
    
    <div id="monitoringModal" class="modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.7); z-index: 1000; overflow: auto;">
        <div class="modal-content" style="background: #2c3e50; margin: 5% auto; padding: 0; width: 80%; max-width: 800px; border-radius: 8px; box-shadow: 0 5px 15px rgba(0,0,0,0.3); position: relative;">
            <div style="padding: 20px; border-bottom: 1px solid rgba(255,255,255,0.1); display: flex; justify-content: space-between; align-items: center;">
                <h2 style="margin: 0; font-size: 1.5rem; color: white;"><i class="fas fa-tachometer-alt"></i> Monitoramento de Dispositivos</h2>
                <button type="button" onclick="document.getElementById('monitoringModal').style.display='none'; if(window.monitoringInterval) { clearInterval(window.monitoringInterval); }" style="background: none; border: none; color: white; font-size: 28px; font-weight: bold; cursor: pointer; padding: 0 10px; line-height: 1;">
                    &times;
                </button>
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
                                    <h6 class="card-subtitle mb-2 text-muted">Lâmpadas Acessas</h6>
                                    <h2 class="display-4" id="lights-on">0</h2>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-light">
                                <div class="card-body text-center">
                                    <h6 class="card-subtitle mb-2 text-muted">Porcentagem Acessa</h6>
                                    <h2 class="display-4" id="percentage-on">0%</h2>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="device-info" style="margin: 20px 0; text-align: center;">
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

        #lampadaModal .close-lampada {
            cursor: pointer;
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
            border-radius: 5px;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
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
        
        /* Estilo para os botões de formulário */
        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 12px;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        /* Estilo base para todos os botões */
        .btn-cancel,
        .btn-confirm {
            padding: 10px 24px;
            border: none;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: all 0.3s ease;
            min-width: 120px;
            text-align: center;
            font-size: 0.95rem;
        }
        
        /* Estilo específico para o botão de cancelar */
        .btn-cancel {
            background: rgba(255, 255, 255, 0.08);
            color: #e0e0e0;
            border: 1px solid rgba(255, 255, 255, 0.15);
        }
        
        .btn-cancel:hover {
            background: rgba(255, 255, 255, 0.15);
            transform: translateY(-2px);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
        }
        
        .btn-cancel:active {
            transform: translateY(0);
            box-shadow: none;
        }
        
        /* Estilo específico para o botão de confirmar */
        .btn-confirm {
            background: linear-gradient(135deg, #4CAF50 0%, #45a049 100%);
            color: white;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
        }
        
        .btn-confirm:hover {
            background: linear-gradient(135deg, #43a047 0%, #388e3c 100%);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(76, 175, 80, 0.3);
        }
        
        .btn-confirm:active {
            transform: translateY(0);
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
        }
        
        /* Estilo para o ícone dentro dos botões */
        .btn-cancel i,
        .btn-confirm i {
            font-size: 0.9em;
        }
        
        /* Estilos responsivos */
        @media (max-width: 768px) {
            .modal-content {
                width: 90% !important;
                margin: 20px auto !important;
            }
            
            .form-actions {
                flex-direction: column;
                gap: 10px;
            }
            
            .btn-cancel,
            .btn-confirm {
                width: 100%;
                padding: 12px 24px;
            }
        }
        
        /* Estilo específico para o modal de arcondicionado */
        #termostatoModal .modal-header {
            background: linear-gradient(135deg, #2196F3 0%, #1976D2 100%);
            border-bottom: none;
            border-radius: 10px 10px 0 0;
            padding: 15px 20px;
        }
        
        #termostatoModal .modal-header h2 {
            margin: 0;
            font-size: 1.3rem;
            color: white;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        #termostatoModal .modal-body {
            padding: 25px;
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
        function updateLightsTable(lights) {
            if (!lights) {
                console.error('Dados de lâmpadas não fornecidos');
                return;
            }
            
            try {
                const lightsOn = lights.filter(light => {
                    const status = light.Status ? light.Status.toLowerCase() : 'off';
                    return status === 'on';
                }).length;
                
                const totalLightsEl = document.getElementById('total-lights');
                const lightsOnEl = document.getElementById('lights-on');
                const percentageEl = document.getElementById('percentage-on');
                
                if (totalLightsEl) totalLightsEl.textContent = lights.length;
                if (lightsOnEl) lightsOnEl.textContent = lightsOn;
                
                const percentage = lights.length > 0 ? Math.round((lightsOn / lights.length) * 100) : 0;
                if (percentageEl) percentageEl.textContent = `${percentage}%`;
                
                console.log(`Atualizado: ${lightsOn} de ${lights.length} lâmpadas acessas (${percentage}%)`);
                
            } catch (error) {
                console.error('Erro ao atualizar contadores de lâmpadas:', error);
            }
        }

        function updateThermostatsTable(thermostats) {
            if (!thermostats || !Array.isArray(thermostats)) {
                console.error('Dados de arcondicionado inválidos ou não fornecidos');
                return;
            }
            
            try {
                const totalThermostats = thermostats.length;
                const thermostatsOn = thermostats.filter(thermostat => {
                    const status = thermostat.Status ? thermostat.Status.toLowerCase() : 'off';
                    return status === 'on';
                }).length;
                
                const percentage = totalThermostats > 0 ? Math.round((thermostatsOn / totalThermostats) * 100) : 0;
                
                // Atualiza os cards principais
                const totalEl = document.getElementById('total-lights');
                const onEl = document.getElementById('lights-on');
                const percentageEl = document.getElementById('percentage-on');
                
                if (totalEl) totalEl.textContent = totalThermostats;
                if (onEl) onEl.textContent = thermostatsOn;
                if (percentageEl) percentageEl.textContent = `${percentage}%`;
                
                // Se houver pelo menos um arcondicionado, exibe as informações do primeiro
                if (thermostats.length > 0) {
                    const thermo = thermostats[0];
                    const tempEl = document.getElementById('temperature-value');
                    const statusEl = document.getElementById('status-value');
                    const modeEl = document.getElementById('mode-value');
                    
                    if (tempEl) tempEl.textContent = thermo.Temperatura ? `${thermo.Temperatura}°C` : 'N/A';
                    if (statusEl) statusEl.textContent = thermo.Status === 'on' ? 'Ligado' : 'Desligado';
                    if (modeEl) modeEl.textContent = thermo.Modo || 'N/A';
                }
                
                console.log(`Atualizado: ${thermostatsOn} de ${totalThermostats} arcondicionado ligados (${percentage}%)`);
                
            } catch (error) {
                console.error('Erro ao atualizar contadores de arcondicionado:', error);
            }
        }

        function openMonitoringModal(deviceCategory = 'lighting') {
            const modal = document.getElementById('monitoringModal');
            if (!modal) return;

            // Reseta o estilo antes de mostrar
            modal.style.display = 'block';
            modal.style.opacity = '0';
            
            // Força o navegador a processar o display: block antes da animação
            setTimeout(() => {
                modal.style.transition = 'opacity 0.3s';
                modal.style.opacity = '1';
            }, 10);
            
            const loadData = () => {
                const endpoint = deviceCategory === 'climate' 
                    ? '../includes/monitor_thermostats.php' 
                    : '../includes/monitor_lights.php';
                
                fetch(endpoint)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            if (deviceCategory === 'climate' && data.thermostats) {
                                updateThermostatsTable(data.thermostats);
                            } else if (data.lights) {
                                updateLightsTable(data.lights);
                            } else {
                                console.error('Dados inválidos recebidos:', data);
                            }
                        } else {
                            console.error('Erro ao carregar dados:', data?.message || 'Erro desconhecido');
                        }
                    })
                    .catch(error => {
                        console.error('Erro ao buscar dados:', error);
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
                    window.monitoringInterval = null;
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
            openMonitoringModal(deviceCategory);
            
            // Atualiza o título e informações do dispositivo no modal
            const titleElement = document.querySelector('#monitoringModal .modal-content h2');
            const deviceNameElement = document.getElementById('deviceName');
            const deviceLocationElement = document.getElementById('deviceLocation');
            
            // Define o título e ícone com base no tipo de dispositivo
            if (deviceCategory === 'climate') {
                titleElement.innerHTML = '<i class="fas fa-thermometer-half"></i> Monitoramento de Ar-Condicionado';
                
                // Atualiza os textos dos cards
                document.querySelector('#monitoringModal .col-md-4:nth-child(1) .card-subtitle').textContent = 'Total de Ar-Condicionado';
                document.querySelector('#monitoringModal .col-md-4:nth-child(2) .card-subtitle').textContent = 'Ar-Condicionado Ligados';
                document.querySelector('#monitoringModal .col-md-4:nth-child(3) .card-subtitle').textContent = 'Porcentagem Ligados';
            } else {
                // Mantém o padrão para lâmpadas
                titleElement.innerHTML = '<i class="fas fa-lightbulb"></i> Monitoramento de Lâmpadas';
                
                // Reseta os textos dos cards para o padrão
                document.querySelector('#monitoringModal .col-md-4:nth-child(1) .card-subtitle').textContent = 'Total de Lâmpadas';
                document.querySelector('#monitoringModal .col-md-4:nth-child(2) .card-subtitle').textContent = 'Lâmpadas Acessas';
                document.querySelector('#monitoringModal .col-md-4:nth-child(3) .card-subtitle').textContent = 'Porcentagem Acesa';
                
                // Cria cards estilizados com layout horizontal
                const cardContainer = document.querySelector('#monitoringModal .row');
                if (cardContainer) {
                    cardContainer.innerHTML = `
                        <style>
                            .stat-card {
                                background: #fff;
                                border-radius: 12px;
                                box-shadow: 0 4px 12px rgba(0,0,0,0.08);
                                transition: all 0.3s ease;
                                border: none;
                                margin: 0 8px;
                                position: relative;
                                overflow: hidden;
                            }
                            .stat-card:before {
                                content: '';
                                position: absolute;
                                top: 0;
                                left: 0;
                                right: 0;
                                height: 4px;
                                background: linear-gradient(90deg, #4a90e2, #5bc0de);
                            }
                            .stat-card:nth-child(2):before {
                                background: linear-gradient(90deg, #5cb85c, #5bc0de);
                            }
                            .stat-card:nth-child(3):before {
                                background: linear-gradient(90deg, #f0ad4e, #5bc0de);
                            }
                            .stat-card:hover {
                                transform: translateY(-5px);
                                box-shadow: 0 8px 20px rgba(0,0,0,0.12);
                            }
                            .stat-value {
                                font-size: 2rem;
                                font-weight: 700;
                                color: #2c3e50;
                                margin: 8px 0;
                                background: linear-gradient(135deg, #4a90e2, #5bc0de);
                                -webkit-background-clip: text;
                                -webkit-text-fill-color: transparent;
                            }
                            .stat-card:nth-child(2) .stat-value {
                                background: linear-gradient(135deg, #5cb85c, #5bc0de);
                                -webkit-background-clip: text;
                            }
                            .stat-card:nth-child(3) .stat-value {
                                background: linear-gradient(135deg, #f0ad4e, #e74c3c);
                                -webkit-background-clip: text;
                            }
                            .stat-label {
                                color: #7f8c8d;
                                font-size: 0.9rem;
                                font-weight: 500;
                                letter-spacing: 0.5px;
                                text-transform: uppercase;
                            }
                        </style>
                        <div class="col-md-12 px-0">
                            <div class="d-flex justify-content-between align-items-stretch">
                                <div class="stat-card p-4 text-center" style="flex: 1;">
                                    <div class="stat-label">Total</div>
                                    <div id="total-lights" class="stat-value">0</div>
                                    <i class="fas fa-lightbulb mt-2" style="color: #f1c40f; font-size: 1.5rem;"></i>
                                </div>
                                <div class="stat-card p-4 text-center" style="flex: 1;">
                                    <div class="stat-label">Acessas</div>
                                    <div id="lights-on" class="stat-value">0</div>
                                    <i class="fas fa-power-off mt-2" style="color: #2ecc71; font-size: 1.5rem;"></i>
                                </div>
                                <div class="stat-card p-4 text-center" style="flex: 1;">
                                    <div class="stat-label">Porcentagem</div>
                                    <div id="percentage-on" class="stat-value">0%</div>
                                    <i class="fas fa-percentage mt-2" style="color: #e74c3c; font-size: 1.5rem;"></i>
                                </div>
                            </div>
                        </div>`;
                }
            }
            
            // Remove a exibição do nome e localização do dispositivo
            if (deviceNameElement) deviceNameElement.textContent = '';
            if (deviceLocationElement) deviceLocationElement.textContent = '';
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

        // Fechar modal ao clicar fora
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
        
        // Função para fechar modais ao clicar fora deles
        function setupModal(modalId, closeBtnClass) {
            const modal = document.getElementById(modalId);
            if (!modal) return;
            
            const closeBtn = document.querySelector(closeBtnClass);
            
            if (closeBtn) {
                closeBtn.onclick = function() {
                    modal.style.display = 'none';
                };
            }
            
            window.addEventListener('click', function(event) {
                if (event.target === modal) {
                    modal.style.display = 'none';
                }
            });
        }
        
        document.addEventListener('DOMContentLoaded', function() {
            // Handle thermostat form submission
            const termostatoForm = document.getElementById('termostatoForm');
            if (termostatoForm) {
                // Remove any existing submit event listeners to prevent duplicates
                const newForm = termostatoForm.cloneNode(true);
                termostatoForm.parentNode.replaceChild(newForm, termostatoForm);
                
                newForm.addEventListener('submit', async function(e) {
                    console.log('Form submission started');
                    e.preventDefault();
                    e.stopPropagation();
                    
                    const form = this;
                    const formData = new FormData(form);
                    formData.append('action', 'save_thermostat');
                    
                    const saveBtn = document.getElementById('saveThermostatBtn');
                    if (!saveBtn) return;
                    
                    const originalBtnText = saveBtn.innerHTML;
                    saveBtn.disabled = true;
                    saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Salvando...';
                    
                    try {
                        // Convert FormData to URL-encoded format
                        const formDataObj = {};
                        formData.forEach((value, key) => {
                            formDataObj[key] = value;
                        });
                        
                        console.log('Sending form data:', formDataObj);
                        
                        const response = await fetch('', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            body: new URLSearchParams(formDataObj).toString()
                        });
                        
                        console.log('Response status:', response.status);
                        
                        let data;
                        const contentType = response.headers.get('content-type');
                        if (contentType && contentType.includes('application/json')) {
                            data = await response.json();
                        } else {
                            const text = await response.text();
                            console.error('Non-JSON response:', text);
                            throw new Error('Resposta inválida do servidor');
                        }
                        
                        if (!response.ok) {
                            throw new Error(data?.message || `Erro HTTP: ${response.status}`);
                        }
                        
                        if (data && data.success) {
                            showMessage(data.message, 'success');
                            // Close modal and reset form
                            const modal = document.getElementById('termostatoModal');
                            if (modal) modal.style.display = 'none';
                            form.reset();
                            
                            // Reload the page to show the new thermostat
                            setTimeout(() => {
                                window.location.reload();
                            }, 1500);
                        } else {
                            throw new Error(data?.message || 'Erro ao salvar arcondicionado');
                        }
                    } catch (error) {
                        console.error('Error:', error);
                        showMessage(error.message || 'Ocorreu um erro ao salvar o arcondicionado', 'error');
                    } finally {
                        saveBtn.disabled = false;
                        saveBtn.innerHTML = originalBtnText;
                    }
                });
            }
            // Configuração do modal de lâmpada
            const lampadaModal = document.getElementById('lampadaModal');
            const lampadaForm = document.getElementById('lampadaForm');
            
            // Configuração do modal de arcondicionado
            const termostatoModal = document.getElementById('termostatoModal');
            
            // Configurar fechamento dos modais
            setupModal('lampadaModal', '.close-lampada');
            setupModal('termostatoModal', '.close-btn');
            
            if (!lampadaModal || !lampadaForm) {
                console.error('Elementos do modal de lâmpada não encontrados');
            }

            // Resetar formulário ao fechar o modal de lâmpada
            lampadaModal.addEventListener('click', function(event) {
                if (event.target === lampadaModal || event.target.classList.contains('close-lampada')) {
                    lampadaForm.reset();
                }
            });
            
            // Resetar formulário ao fechar o modal de arcondicionado
            if (termostatoModal) {
                termostatoModal.addEventListener('click', function(event) {
                    if (event.target === termostatoModal || event.target.classList.contains('close-btn')) {
                        termostatoForm.reset();
                    }
                });
            }

            // Envio do formulário de lâmpada
            if (lampadaForm) {
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
            }
            
            // Envio do formulário de arcondicionado
            if (termostatoForm) {
                termostatoForm.onsubmit = function(e) {
                    e.preventDefault();
                    const nome = document.getElementById('termostatoNome').value.trim();
                    const comodo = document.getElementById('termostatoComodo').value.trim();
                    const temperatura = document.getElementById('termostatoTemperatura').value;
                    
                    if (!nome || !comodo || !temperatura) {
                        showMessage('Por favor, preencha todos os campos.', 'warning');
                        return false;
                    }
                    
                    // Aqui você pode adicionar a lógica para enviar os dados do arcondicionado
                    // Por enquanto, apenas mostramos uma mensagem de sucesso
                    showMessage('Ar-Condicionado cadastrado com sucesso!', 'success');
                    termostatoModal.style.display = 'none';
                    termostatoForm.reset();
                    
                    // Atualiza a página após 1 segundo para mostrar a mensagem
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                    
                    return false;
                };
            }
        });
        
        // Função para atualizar os dados dos sensores
        function updateSensorData() {
            console.log('Atualizando dados dos sensores...');
            fetch('../get_temperature.php')
                .then(response => {
                    console.log('Resposta recebida de get_temperature.php');
                    return response.json();
                })
                .then(data => {
                    console.log('Dados recebidos:', data);
                    
                    if (data.status === 'online') {
                        console.log('Atualizando valores dos sensores...');
                        
                        // Atualiza o Sensor 1
                        const temp1Element = document.querySelector('[data-sensor-id="temp1"]');
                        if (temp1Element) {
                            const temp1Value = (data.temperature1 === 0 || data.temperature1) ? data.temperature1 : '--';
                            temp1Element.textContent = (typeof temp1Value === 'number' ? temp1Value.toFixed(1) : temp1Value) + '°C';
                            console.log('Sensor 1 atualizado para:', temp1Element.textContent);
                        }
                        
                        // Atualiza o Sensor 2
                        const temp2Element = document.querySelector('[data-sensor-id="temp2"]');
                        if (temp2Element) {
                            const temp2Value = (data.temperature2 === 0 || data.temperature2) ? data.temperature2 : '--';
                            temp2Element.textContent = (typeof temp2Value === 'number' ? temp2Value.toFixed(1) : temp2Value) + '°C';
                            console.log('Sensor 2 atualizado para:', temp2Element.textContent);
                        }
                    } else if (data.status === 'error') {
                        // Em caso de erro, mostra erro em todos os sensores
                        document.querySelectorAll('[data-sensor-id^="temp"]').forEach(el => {
                            el.textContent = 'Erro';
                        });
                    } else {
                        // Estado de espera
                        document.querySelectorAll('[data-sensor-id^="temp"]').forEach(el => {
                            el.textContent = '--°C';
                        });
                    }
                })
                .catch(error => {
                    console.error('Erro ao buscar dados do sensor:', error);
                    
                    // Em caso de erro na requisição, mostra erro em todos os sensores
                    document.querySelectorAll('[data-sensor-id^="temp"]').forEach(el => {
                        el.textContent = 'Erro';
                    });
                });
        }
        
        // Inicializa a atualização dos sensores
        console.log('Iniciando atualização dos sensores...');
        updateSensorData();
        setInterval(updateSensorData, 2000);
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
    
    <!-- Modal de Cadastro de arcondicionado -->
    <div id="termostatoModal" class="modal">
        <div class="modal-content" style="max-width: 500px;">
            <div class="modal-header">
                <h2><i class="fas fa-thermometer-half"></i> Cadastrar Novo Ar-Condicionado</h2>
                <span class="close-btn" onclick="document.getElementById('termostatoModal').style.display='none';">&times;</span>
            </div>
            <div class="modal-body">
                <form id="termostatoForm">
                    <div class="form-group">
                        <label for="termostatoNome">Nome do Ar-Condicionado</label>
                        <input type="text" id="termostatoNome" name="nome" placeholder="Ex: Ar-Condicionado da Sala" required>
                    </div>
                    <div class="form-group">
                        <label for="termostatoComodo">Cômodo</label>
                        <input type="text" id="termostatoComodo" name="comodo" placeholder="Ex: Sala, Quarto, Cozinha" required>
                    </div>
                    <div class="form-actions" style="margin-top: 20px; text-align: right;">
                        <button type="button" class="btn-cancel" onclick="document.getElementById('termostatoModal').style.display='none'">
                            Cancelar
                        </button>
                        <button type="submit" class="btn-confirm" id="saveThermostatBtn">
                            <i class="fas fa-save"></i> Salvar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>

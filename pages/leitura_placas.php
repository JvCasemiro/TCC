<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit;
}

require_once '../config/database.php';

// Mock user data since database is disabled
$user = [
    'nome' => $_SESSION['username'] ?? 'Usuário',
    'email' => $_SESSION['email'] ?? 'usuario@exemplo.com'
];

// Mock data for license plate readings
$recent_plates = [
    ['plate' => 'ABC-1234', 'time' => '10:45:12', 'confidence' => '98%', 'status' => 'authorized'],
    ['plate' => 'XYZ-5678', 'time' => '10:42:33', 'confidence' => '95%', 'status' => 'unknown'],
    ['plate' => 'DEF-9012', 'time' => '10:38:45', 'confidence' => '92%', 'status' => 'blocked'],
    ['plate' => 'GHI-3456', 'time' => '10:35:21', 'confidence' => '97%', 'status' => 'authorized'],
    ['plate' => 'JKL-7890', 'time' => '10:32:18', 'confidence' => '89%', 'status' => 'unknown']
];

$authorized_plates = [
    'ABC-1234' => 'João Silva',
    'GHI-3456' => 'Maria Santos',
    'MNO-1111' => 'Pedro Costa',
    'PQR-2222' => 'Ana Lima'
];
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leitura de Placas - Automação Residencial</title>
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

        .stats-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }

        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            transition: transform 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .stat-number {
            font-size: 24px;
            font-weight: bold;
            color: #4a90e2;
        }

        .stat-label {
            font-size: 14px;
            color: #666;
            margin-top: 5px;
        }

        .card {
            background: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            transition: transform 0.3s ease;
            margin-bottom: 20px;
        }
        
        .card:hover {
            transform: translateY(-5px);
        }
        
        .card h3 {
            color: #4a90e2;
            margin-bottom: 15px;
        }

        .camera-feed {
            width: 100%;
            height: 400px;
            background: #1a1a1a;
            border-radius: 10px;
            position: relative;
            overflow: hidden;
            margin-bottom: 15px;
        }

        .camera-placeholder {
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100%;
            color: #666;
            font-size: 18px;
        }

        .detection-overlay {
            position: absolute;
            top: 20px;
            right: 20px;
            background: rgba(0,0,0,0.8);
            color: white;
            padding: 10px 15px;
            border-radius: 8px;
            font-size: 14px;
        }

        .status-indicator {
            display: inline-block;
            width: 10px;
            height: 10px;
            border-radius: 50%;
            margin-right: 8px;
        }

        .status-online { background: #4CAF50; }
        .status-offline { background: #f44336; }

        .camera-controls {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
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
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
            position: relative;
            overflow: hidden;
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

        .results-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-top: 20px;
        }

        @media (max-width: 768px) {
            .results-grid {
                grid-template-columns: 1fr;
            }
        }

        .plate-entry {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px;
            margin-bottom: 8px;
            background: #f8f9fa;
            border-radius: 8px;
            border-left: 4px solid #ddd;
        }

        .plate-entry.authorized {
            border-left-color: #4CAF50;
            background: #f1f8e9;
        }

        .plate-entry.unknown {
            border-left-color: #FF9800;
            background: #fff8e1;
        }

        .plate-entry.blocked {
            border-left-color: #f44336;
            background: #ffebee;
        }

        .plate-info {
            flex: 1;
        }

        .plate-number {
            font-weight: bold;
            font-size: 16px;
            margin-bottom: 4px;
            color: #2c3e50;
        }

        .plate-details {
            font-size: 12px;
            color: #666;
        }

        .confidence-badge {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: bold;
        }

        .confidence-high {
            background: #e8f5e8;
            color: #2e7d32;
        }

        .confidence-medium {
            background: #fff3e0;
            color: #f57c00;
        }

        .confidence-low {
            background: #ffebee;
            color: #d32f2f;
        }

        .authorized-list {
            max-height: 300px;
            overflow-y: auto;
        }

        .authorized-entry {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px;
            margin-bottom: 8px;
            background: #f8f9fa;
            border-radius: 8px;
        }

        .add-plate-form {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }

        .add-plate-form input {
            flex: 1;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 6px;
        }

        .btn-danger {
            background: #f44336;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 12px;
        }

        .btn-success {
            background: #4CAF50;
            color: white;
        }

        @media (max-width: 768px) {
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
                        <i class="fas fa-user"></i> <?php echo htmlspecialchars($user['nome']); ?>
                        <i class="fas fa-chevron-down" style="margin-left: 5px; font-size: 12px;"></i>
                    </span>
                    <div class="dropdown-content" id="userDropdown">
                        <div class="user-info">
                            <h4><i class="fas fa-user"></i> <?php echo htmlspecialchars($user['nome']); ?></h4>
                            <p><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($user['email']); ?></p>
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
            <h1>Leitura de Placas</h1>
            <p>Monitore e gerencie o acesso de veículos com reconhecimento automático de placas.</p>
        </div>
        
        <!-- Statistics Row -->
        <div class="stats-row">
            <div class="stat-card">
                <div class="stat-number">24</div>
                <div class="stat-label">Placas Hoje</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">18</div>
                <div class="stat-label">Autorizadas</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">4</div>
                <div class="stat-label">Desconhecidas</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">2</div>
                <div class="stat-label">Bloqueadas</div>
            </div>
        </div>

        <!-- Camera Section -->
        <div class="card">
            <h3><i class="fas fa-video"></i> Câmera de Entrada</h3>
            <div class="camera-feed">
                <div class="camera-placeholder">
                    <i class="fas fa-video" style="font-size: 48px; margin-right: 15px;"></i>
                    Feed da Câmera - Portão Principal
                </div>
                <div class="detection-overlay">
                    <span class="status-indicator status-online"></span>
                    Detecção Ativa
                </div>
            </div>
            <div class="camera-controls">
                <button class="control-btn">
                    <i class="fas fa-play"></i> Iniciar Detecção
                </button>
                <button class="control-btn">
                    <i class="fas fa-pause"></i> Pausar
                </button>
                <button class="control-btn">
                    <i class="fas fa-camera"></i> Capturar
                </button>
                <button class="control-btn">
                    <i class="fas fa-stop"></i> Parar
                </button>
            </div>
        </div>

        <!-- Results Grid -->
        <div class="results-grid">
            <!-- Recent Detections -->
            <div class="card">
                <h3><i class="fas fa-history"></i> Detecções Recentes</h3>
                <div id="recent-detections">
                    <?php foreach ($recent_plates as $plate): ?>
                    <div class="plate-entry <?php echo $plate['status']; ?>">
                        <div class="plate-info">
                            <div class="plate-number"><?php echo $plate['plate']; ?></div>
                            <div class="plate-details"><?php echo $plate['time']; ?></div>
                        </div>
                        <div class="confidence-badge confidence-high">
                            <?php echo $plate['confidence']; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Authorized Plates -->
            <div class="card">
                <h3><i class="fas fa-check-circle"></i> Placas Autorizadas</h3>
                <div class="authorized-list">
                    <?php foreach ($authorized_plates as $plate => $owner): ?>
                    <div class="authorized-entry">
                        <div>
                            <div class="plate-number"><?php echo $plate; ?></div>
                            <div class="plate-details"><?php echo $owner; ?></div>
                        </div>
                        <button class="btn-danger">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                    <?php endforeach; ?>
                </div>
                <div class="add-plate-form">
                    <input type="text" placeholder="Nova placa (ABC-1234)" maxlength="8">
                    <input type="text" placeholder="Proprietário">
                    <button class="control-btn btn-success">
                        <i class="fas fa-plus"></i> Adicionar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
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

        // Simulate real-time plate detection
        function simulateDetection() {
            const plates = ['ABC-1234', 'XYZ-5678', 'DEF-9012', 'GHI-3456', 'JKL-7890', 'MNO-1111'];
            const statuses = ['authorized', 'unknown', 'blocked'];
            
            setInterval(() => {
                if (Math.random() > 0.7) { // 30% chance of detection
                    const randomPlate = plates[Math.floor(Math.random() * plates.length)];
                    const randomStatus = statuses[Math.floor(Math.random() * statuses.length)];
                    const confidence = (85 + Math.random() * 15).toFixed(0) + '%';
                    const time = new Date().toLocaleTimeString();
                    
                    addNewDetection(randomPlate, time, confidence, randomStatus);
                }
            }, 5000);
        }

        function addNewDetection(plate, time, confidence, status) {
            const container = document.getElementById('recent-detections');
            const newEntry = document.createElement('div');
            newEntry.className = `plate-entry ${status}`;
            newEntry.innerHTML = `
                <div class="plate-info">
                    <div class="plate-number">${plate}</div>
                    <div class="plate-details">${time}</div>
                </div>
                <div class="confidence-badge confidence-high">
                    ${confidence}
                </div>
            `;
            
            container.insertBefore(newEntry, container.firstChild);
            
            // Keep only last 5 entries
            while (container.children.length > 5) {
                container.removeChild(container.lastChild);
            }
            
            // Show toast notification
            showToast(`Nova placa detectada: ${plate}`, status === 'authorized' ? 'success' : 'warning');
        }

        function showToast(message, type = 'info') {
            const toast = document.createElement('div');
            toast.className = `toast toast-${type}`;
            toast.textContent = message;
            toast.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                padding: 15px 20px;
                border-radius: 8px;
                color: white;
                font-weight: bold;
                z-index: 1000;
                opacity: 0;
                transform: translateX(100%);
                transition: all 0.3s ease;
            `;
            
            if (type === 'success') toast.style.background = '#4CAF50';
            else if (type === 'warning') toast.style.background = '#FF9800';
            else if (type === 'error') toast.style.background = '#f44336';
            else toast.style.background = '#2196F3';
            
            document.body.appendChild(toast);
            
            setTimeout(() => {
                toast.style.opacity = '1';
                toast.style.transform = 'translateX(0)';
            }, 100);
            
            setTimeout(() => {
                toast.style.opacity = '0';
                toast.style.transform = 'translateX(100%)';
                setTimeout(() => document.body.removeChild(toast), 300);
            }, 3000);
        }

        // Control button handlers
        document.querySelectorAll('.control-btn').forEach(button => {
            button.addEventListener('click', function() {
                const text = this.textContent.trim();
                if (text.includes('Iniciar')) {
                    showToast('Detecção de placas iniciada', 'success');
                    simulateDetection();
                } else if (text.includes('Pausar')) {
                    showToast('Detecção pausada', 'warning');
                } else if (text.includes('Capturar')) {
                    showToast('Imagem capturada', 'info');
                } else if (text.includes('Parar')) {
                    showToast('Detecção interrompida', 'error');
                } else if (text.includes('Adicionar')) {
                    showToast('Placa adicionada à lista autorizada', 'success');
                }
            });
        });

        // Start simulation on page load
        window.addEventListener('load', () => {
            setTimeout(simulateDetection, 2000);
        });
    </script>
</body>
</html>

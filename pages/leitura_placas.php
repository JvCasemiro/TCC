<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit;
}

require_once '../config/database.php';

function gerenciarImagensOutput() {
    $output_dir = __DIR__ . '/../python/output/';
    
    if (!is_dir($output_dir)) {
        return;
    }
    
    $files = glob($output_dir . 'placa_*.png');
    
    if (count($files) > 5) {
        usort($files, function($a, $b) {
            return filemtime($b) - filemtime($a);
        });
        usort($files, function($a, $b) {
            return filemtime($b) - filemtime($a);
        });
        
        $files_to_keep = array_slice($files, 0, 1);
        foreach (array_diff($files, $files_to_keep) as $file) {
            if (is_file($file)) {
                @unlink($file);
            }
        }
    }
}

gerenciarImagensOutput();


$user = [
    'id' => $_SESSION['user_id'],
    'nome' => $_SESSION['username'] ?? 'Usuário',
    'email' => $_SESSION['email'] ?? 'usuario@exemplo.com'
];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_plate'])) {
    
    $placa = trim(strtoupper(str_replace(['-', ' '], '', $_POST['placa'])));
    $proprietario = trim($_POST['proprietario']);
    
    error_log("Tentando cadastrar placa: " . $placa . " - Proprietário: " . $proprietario);
    
    if (!preg_match('/^[A-Z]{3}[0-9A-Z]{4}$/', $placa)) {
        $_SESSION['message'] = ['type' => 'error', 'text' => 'Formato de placa inválido. Use o formato AAA0000.'];
    } else {
        
        $placa_formatada = substr($placa, 0, 3) . '-' . substr($placa, 3);
        
        try {
            $check = $conn->prepare("SELECT ID_Placa FROM Placas WHERE Numeracao = ?");
            $check->execute([$placa_formatada]);
            
            if ($check->rowCount() > 0) {
                $_SESSION['message'] = ['type' => 'error', 'text' => 'Esta placa já está cadastrada.'];
            } else {
                $stmt = $conn->prepare("INSERT INTO Placas (Numeracao, Proprietario, Ultimo_Acesso) VALUES (?, ?, NOW())");
                $result = $stmt->execute([$placa_formatada, $proprietario]);
                
                if ($result) {
                    $_SESSION['message'] = ['type' => 'success', 'text' => 'Placa cadastrada com sucesso!'];
                    
                    $redirect_url = strtok($_SERVER['PHP_SELF'], '?');
                    header('Location: ' . $redirect_url);
                    exit;
                } else {
                    $error = $stmt->errorInfo();
                    error_log("Erro ao inserir placa: " . print_r($error, true));
                    $_SESSION['message'] = ['type' => 'error', 'text' => 'Erro ao cadastrar placa. Por favor, tente novamente.'];
                }
            }
        } catch (PDOException $e) {
            $_SESSION['message'] = ['type' => 'error', 'text' => 'Erro ao cadastrar placa: ' . $e->getMessage()];
        }
    }
    
   
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}


try {
    $stmt = $conn->query("SELECT Numeracao, Proprietario FROM Placas ORDER BY Data_Cadastro DESC");
    $authorized_plates = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
} catch (PDOException $e) {
    $authorized_plates = [];
    $_SESSION['message'] = ['type' => 'error', 'text' => 'Erro ao carregar placas autorizadas.'];
}


$recent_plates = [];
try {
    $stmt = $conn->query("SELECT Numeracao as plate, 
                         DATE_FORMAT(Ultimo_Acesso, '%H:%i:%s') as time, 
                         '100%' as confidence,
                         'authorized' as status
                         FROM Placas 
                         WHERE Ultimo_Acesso IS NOT NULL 
                         ORDER BY Ultimo_Acesso DESC 
                         LIMIT 5");
    $recent_plates = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $_SESSION['message'] = ['type' => 'error', 'text' => 'Erro ao carregar detecções recentes.'];
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leitura de Placas</title>
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
            margin-top: 15px;
        }
        
        .message-success {
            background-color: #4CAF50;
            opacity: 1;
            transition: opacity 0.5s ease-in-out;
        }
        
        .message-error {
            background-color: #f44336;
            opacity: 1;
            transition: opacity 0.5s ease-in-out;
        }

        .add-plate-form input {
            flex: 1;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 6px;
        }

        .btn-danger {
            background: #dc3545;
            color: white;
            border: none;
            border-radius: 4px;
            padding: 5px 10px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            transition: background-color 0.3s;
        }
        
        .btn-danger:hover {
            background: #c82333;
        }
        
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
            align-items: center;
            justify-content: center;
        }
        
        .modal-content {
            background-color: white;
            padding: 25px;
            border-radius: 8px;
            width: 90%;
            max-width: 400px;
            text-align: center;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        
        .modal-buttons {
            margin-top: 20px;
            display: flex;
            justify-content: center;
            gap: 15px;
        }
        
        .modal-btn {
            padding: 8px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.3s;
        }
        
        .btn-confirm {
            background-color: #dc3545;
            color: white;
        }
        
        .btn-confirm:hover {
            background-color: #c82333;
        }
        
        .btn-cancel {
            background-color: #6c757d;
            color: white;
        }
        
        .btn-cancel:hover {
            background-color: #5a6268;
        }

        .btn-success {
            background: #4CAF50;
            color: white;
        }

        #deleteModal{
            color: #000;
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
            <h1>Leitura de Placas</h1>
            <p>Monitore e gerencie o acesso de veículos com reconhecimento automático de placas.</p>
        </div>

        <div class="card">
            <h3><i class="fas fa-video"></i> Câmera de Entrada</h3>
            <div class="camera-feed">
                <?php
                $output_dir = __DIR__ . '/../python/output/';
                $latest_image = '';
                $latest_mtime = 0;
                
                if (is_dir($output_dir)) {
                    $files = glob($output_dir . 'placa_*.png');
                    foreach ($files as $file) {
                        if (is_file($file) && filemtime($file) > $latest_mtime) {
                            $latest_mtime = filemtime($file);
                            $latest_image = $file;
                        }
                    }
                }
                
                if (!empty($latest_image)) {
                    $image_name = basename($latest_image);
                    $image_url = '../python/output/' . $image_name;
                    echo '<div style="position: relative; width: 100%; height: 100%; min-height: 300px; background-color: #f5f5f5; border-radius: 8px; overflow: hidden;">';
                    echo '    <img src="' . htmlspecialchars($image_url) . '" alt="Última placa detectada" style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -35%); width: 100%; height: auto;">';
                    echo '</div>';
                } else {
                    echo '<div class="camera-placeholder">';
                    echo '    <i class="fas fa-video" style="font-size: 48px; margin-right: 15px;"></i>';
                    echo '    Feed da Câmera - Portão Principal';
                    echo '</div>';
                }
                ?>
            </div>
            <div class="camera-controls">
                <button class="control-btn" id="btnIniciar">
                    <i class="fas fa-play"></i> Iniciar Detecção
                </button>
                <button class="control-btn stop" id="btnParar" disabled>
                    <i class="fas fa-stop"></i> Parar
                </button>
            </div>
        </div>

        <div class="results-grid">
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

            <div class="card">
                <h3><i class="fas fa-check-circle"></i> Placas Autorizadas</h3>
                <div class="authorized-list">
                    <?php
                    if (isset($_GET['delete_plate']) && !empty($_GET['delete_plate'])) {
                        try {
                            $plate_to_delete = $_GET['delete_plate'];
                            $stmt = $conn->prepare("DELETE FROM Placas WHERE Numeracao = ?");
                            $stmt->execute([$plate_to_delete]);
                            
                            if ($stmt->rowCount() > 0) {
                                $_SESSION['message'] = ['type' => 'success', 'text' => 'Placa removida com sucesso!'];
                            } else {
                                $_SESSION['message'] = ['type' => 'error', 'text' => 'Placa não encontrada.'];
                            }
                            
                            header('Location: ' . strtok($_SERVER['REQUEST_URI'], '?'));
                            exit;
                            
                        } catch (PDOException $e) {
                            $_SESSION['message'] = ['type' => 'error', 'text' => 'Erro ao remover placa.'];
                        }
                    }
                    
                    $stmt = $conn->query("SELECT * FROM Placas ORDER BY Data_Cadastro DESC");
                    $authorized_plates = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    
                    foreach ($authorized_plates as $plate): 
                    ?>
                    <div class="authorized-entry">
                        <div class="plate-info">
                            <div class="plate-number"><?php echo htmlspecialchars($plate['Numeracao']); ?></div>
                            <div class="plate-details"><?php echo htmlspecialchars($plate['Proprietario']); ?></div>
                        </div>
                        <a href="#" class="btn-danger delete-btn" data-plate="<?php echo htmlspecialchars($plate['Numeracao'], ENT_QUOTES); ?>">
                            <i class="fas fa-trash"></i>
                        </a>
                    </div>
                    <?php endforeach; ?>
                </div>
                <div class="add-plate-form">
                    <form method="POST" id="plateForm" style="display: flex; gap: 10px; width: 100%;">
                        <input type="text" name="placa" id="placa" placeholder="Nova placa (ABC-1234)" maxlength="8" required 
                               pattern="[A-Z]{3}[-]?[0-9A-Z]{4}" title="Formato: AAA-0000 ou AAA0000" 
                               style="flex: 1; padding: 8px; border: 1px solid #ddd; border-radius: 4px; text-transform: uppercase;"
                               oninput="formatPlate(this)" autocomplete="off">
                        <input type="text" name="proprietario" placeholder="Proprietário" required 
                               style="flex: 2; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                        <button type="submit" name="add_plate" class="control-btn btn-success">
                            <i class="fas fa-plus"></i> Adicionar
                        </button>
                    </form>
                    <?php if (isset($_SESSION['message'])): ?>
                        <div id="message-container" class="message-<?php echo $_SESSION['message']['type']; ?>" style="margin-top: 10px; padding: 10px; border-radius: 4px; color: white; text-align: center; transition: opacity 0.5s ease-in-out;">
                            <?php 
                                echo $_SESSION['message']['text']; 
                                $messageType = $_SESSION['message']['type'];
                                unset($_SESSION['message']);
                            ?>
                        </div>
                        <script>
                            setTimeout(function() {
                                const message = document.getElementById('message-container');
                                if (message) {
                                    message.style.opacity = '0';
                                    setTimeout(() => message.remove(), 500);
                                }
                            }, 3000);
                        </script>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <div id="deleteModal" class="modal">
        <div class="modal-content">
            <h3>Confirmar exclusão da placa</h3>
            <p><strong id="plateToDelete"></strong></p>
            <div class="modal-buttons">
                <button class="modal-btn btn-cancel" id="cancelDelete">Cancelar</button>
                <button class="modal-btn btn-confirm" id="confirmDelete">Confirmar</button>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const modal = document.getElementById('deleteModal');
            const plateToDelete = document.getElementById('plateToDelete');
            const confirmDelete = document.getElementById('confirmDelete');
            const cancelDelete = document.getElementById('cancelDelete');
            let currentDeleteLink = null;
            
            document.querySelectorAll('.delete-btn').forEach(btn => {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    const plate = this.getAttribute('data-plate');
                    plateToDelete.textContent = plate;
                    currentDeleteLink = `?delete_plate=${encodeURIComponent(plate)}`;
                    modal.style.display = 'flex';
                });
            });
            
            confirmDelete.addEventListener('click', function() {
                if (currentDeleteLink) {
                    window.location.href = currentDeleteLink;
                    window.location.href = 'leitura_placas.php';
                }
            });
            
            cancelDelete.addEventListener('click', function() {
                modal.style.display = 'none';
                currentDeleteLink = null;
            });
        
            window.addEventListener('click', function(e) {
                if (e.target === modal) {
                    modal.style.display = 'none';
                    currentDeleteLink = null;
                }
            });
        });

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

        function updateRecentDetections() {
            fetch('get_recent_detections.php')
                .then(response => response.json())
                .then(data => {
                    const container = document.getElementById('recent-detections');
                    container.innerHTML = ''; 
                    
                    data.forEach(detection => {
                        const newEntry = document.createElement('div');
                        newEntry.className = `plate-entry ${detection.status}`;
                        newEntry.innerHTML = `
                            <div class="plate-info">
                                <div class="plate-number">${detection.plate}</div>
                                <div class="plate-details">${detection.time}</div>
                            </div>
                            <div class="confidence-badge confidence-high">
                                ${detection.confidence}
                            </div>
                        `;
                        container.appendChild(newEntry);
                    });
                })
                .catch(error => console.error('Error fetching recent detections:', error));
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
            
            while (container.children.length > 5) {
                container.removeChild(container.lastChild);
            }
            
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


        function formatPlate(input) {
            const cursorPos = input.selectionStart;
            let value = input.value.toUpperCase();
            
            const originalLength = value.length;
            
            value = value.replace(/[^A-Z0-9]/g, '');
            
            if (value.length > 3) {
                value = value.substring(0, 3) + '-' + value.substring(3, 7);
            }
            
            input.value = value;
            
            if (originalLength > cursorPos) {
                input.setSelectionRange(cursorPos, cursorPos);
            }
            
            input.setCustomValidity('');
            if (value.length > 0 && !/^[A-Z]{3}-?[0-9A-Z]{0,4}$/.test(value)) {
                input.setCustomValidity('Formato inválido. Use AAA-0000 ou AAA0000');
            }
        }
        
        document.getElementById('plateForm')?.addEventListener('submit', function(e) {
           
            const placaInput = this.querySelector('input[name="placa"]');
            const proprietarioInput = this.querySelector('input[name="proprietario"]');
            
            formatPlate(placaInput);
            
            placaInput.setCustomValidity('');
            proprietarioInput.setCustomValidity('');
            
            const placaValue = placaInput.value.replace(/[^A-Z0-9]/g, '');
            if (!/^[A-Z]{3}[0-9A-Z]{4}$/.test(placaValue)) {
                placaInput.setCustomValidity('Formato inválido. Use AAA-0000 ou AAA0000');
                placaInput.reportValidity();
                e.preventDefault();
                return false;
            }
            
            if (!proprietarioInput.value.trim()) {
                proprietarioInput.setCustomValidity('Por favor, informe o proprietário');
                proprietarioInput.reportValidity();
                e.preventDefault();
                return false;
            }
            
            return true;
        });
        
        function formatPlate(input) {
            const cursorPos = input.selectionStart;
            let value = input.value.toUpperCase();
            
            const originalLength = value.length;
            
            value = value.replace(/[^A-Z0-9]/g, '');
            
            if (value.length > 3) {
                value = value.substring(0, 3) + '-' + value.substring(3, 7);
            }
            
            input.value = value;
           
            if (originalLength > cursorPos) {
                setTimeout(() => {
                    input.setSelectionRange(cursorPos, cursorPos);
                }, 0);
            }
            
            
            input.setCustomValidity('');
            if (value.length > 0 && !/^[A-Z]{3}-?[0-9A-Z]{0,4}$/.test(value)) {
                input.setCustomValidity('Formato inválido. Use AAA-0000 ou AAA0000');
            }
        }
        
       
        document.addEventListener('DOMContentLoaded', function() {
            const plateInput = document.querySelector('input[name="placa"]');
            if (plateInput) {
                plateInput.addEventListener('input', function() {
                    formatPlate(this);
                });
            }
        });
    </script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="../assets/js/script.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const btnIniciar = document.getElementById('btnIniciar');
            if (btnIniciar) {
                btnIniciar.addEventListener('click', iniciarDetecao);
            }
        });
    </script>
    <script>
    function atualizarConteudo() {
        $.get('leitura_placas.php', function(data) {
            var recentDetections = $(data).find('#recent-detections').html();
            $('#recent-detections').html(recentDetections);
            
            var latestImage = $(data).find('.camera-feed').html();
            $('.camera-feed').html(latestImage);
        });
    }

    setInterval(atualizarConteudo, 5000);
    </script>
    <script src="../assets/js/script.js"></script>
</body>
</html>

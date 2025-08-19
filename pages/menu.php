<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$username = $_SESSION['username'];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel de Controle</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="shortcut icon" href="../assets/img/logo.png" type="image/x-icon">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background: linear-gradient(135deg, #0a0f2c 0%, #1a2a6c 100%);
            min-height: 100vh;
            color: #333;
        }
        
        .header {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            padding: 1rem 2rem;
            box-shadow: 0 2px 20px rgba(0, 0, 0, 0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: relative;
        }
        
        .logo {
            font-size: 1.5rem;
            font-weight: bold;
            color:rgb(13, 42, 75);
        }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .user-name {
            font-weight: 500;
            color: #333;
        }
        
        .logout-btn {
            background: #e74c3c;
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            transition: background 0.3s;
        }
        
        .logout-btn:hover {
            background: #c0392b;
        }
        
        .domx-logo {
            position: absolute;
            top: 50%;
            right: 2rem;
            transform: translateY(-50%);
        }
        
        .domx-logo img {
            height: 100px;
        }
        
        .container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 2rem;
        }
        
        .welcome-section {
            text-align: center;
            margin-bottom: 3rem;
            color: white;
        }
        
        .welcome-section h1 {
            font-size: 2.5rem;
            margin-bottom: 0.5rem;
        }
        
        .welcome-section p {
            font-size: 1.2rem;
            opacity: 0.9;
        }
        
        .menu-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            margin-top: 2rem;
        }
        
        .menu-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            padding: 2rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s, box-shadow 0.3s;
            cursor: pointer;
            text-decoration: none;
            color: inherit;
        }
        
        .menu-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
        }
        
        .menu-card .icon {
            font-size: 3rem;
            color: #4a90e2;
            margin-bottom: 1rem;
            text-align: center;
        }
        
        .menu-card h3 {
            font-size: 1.5rem;
            margin-bottom: 1rem;
            text-align: center;
            color: #333;
        }
        
        .menu-card p {
            color: #666;
            text-align: center;
            line-height: 1.6;
        }
        
        .tab-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            margin-top: 2rem;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }
        
        .tab-header {
            display: flex;
            background: #f8f9fa;
        }
        
        .tab-btn {
            flex: 1;
            padding: 1rem 2rem;
            background: none;
            border: none;
            cursor: pointer;
            font-size: 1rem;
            font-weight: 500;
            transition: all 0.3s;
            border-bottom: 3px solid transparent;
        }
        
        .tab-btn.active {
            background: white;
            border-bottom-color: #4a90e2;
            color: #4a90e2;
        }
        
        .tab-btn:hover {
            background: #e9ecef;
        }
        
        .tab-content {
            padding: 2rem;
            display: none;
        }
        
        .tab-content.active {
            display: block;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: #333;
        }
        
        .form-group input,
        .form-group select {
            width: 100%;
            padding: 0.75rem;
            border: 2px solid #e1e8ed;
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.3s;
        }
        
        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: #4a90e2;
        }
        
        .btn {
            background: #4a90e2;
            color: white;
            border: none;
            padding: 0.75rem 2rem;
            border-radius: 8px;
            font-size: 1rem;
            cursor: pointer;
            transition: background 0.3s;
        }
        
        .btn:hover {
            background: #357abd;
        }
        
        .btn-success {
            background: #27ae60;
        }
        
        .btn-success:hover {
            background: #229954;
        }
        
        .alert {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            display: none;
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        @media (max-width: 768px) {
            .container {
                padding: 0 1rem;
            }
            
            .menu-grid {
                grid-template-columns: 1fr;
            }
            
            .header-content {
                padding: 0 1rem;
            }
            
            .tab-header {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <header class="header">
        <div class="header-content">
            <div class="logo">
                <i class="fas fa-home"></i> DOMX
            </div>
            <div class="user-info">
                <span class="user-name">
                    <i class="fas fa-user"></i> Bem-vindo, <?php echo htmlspecialchars($username); ?>
                </span>
                <a href="../auth/logout.php" class="logout-btn">
                    <i class="fas fa-sign-out-alt"></i> Sair
                </a>
            </div>
        </div>
        <div class="domx-logo">
            <img src="../assets/img/logo.png" alt="DOMX Logo">
        </div>
    </header>

    <div class="container">
        <div class="welcome-section">
            <h1>Painel de Controle</h1>
            <p>Gerencie seu sistema de automação residencial</p>
        </div>

        <div class="menu-grid">
            <a href="dashboard.php" class="menu-card">
                <div class="icon">
                    <i class="fas fa-tachometer-alt"></i>
                </div>
                <h3>Dashboard</h3>
                <p>Visualize e controle todos os dispositivos conectados em tempo real</p>
            </a>

            <a href="gerenciar_usuarios.php" class="menu-card">
                <div class="icon">
                    <i class="fas fa-users"></i>
                </div>
                <h3>Gerenciar Usuários</h3>
                <p>Cadastre novos usuários e gerencie permissões do sistema</p>
            </a>

            <div class="menu-card" onclick="showTab('dispositivos')">
                <div class="icon">
                    <i class="fas fa-microchip"></i>
                </div>
                <h3>Dispositivos</h3>
                <p>Configure e monitore dispositivos IoT conectados</p>
            </div>

            <div class="menu-card" onclick="showTab('configuracoes')">
                <div class="icon">
                    <i class="fas fa-cog"></i>
                </div>
                <h3>Configurações</h3>
                <p>Ajuste preferências e configurações do sistema</p>
            </div>
        </div>

        <div class="tab-container" id="tabContainer" style="display: none;">
            <div class="tab-header">
                <button class="tab-btn active" onclick="showTabContent('cadastro-usuario')">
                    <i class="fas fa-user-plus"></i> Cadastrar Usuário
                </button>
                <button class="tab-btn" onclick="showTabContent('listar-usuarios')">
                    <i class="fas fa-list"></i> Listar Usuários
                </button>
                <button class="tab-btn" onclick="closeTab()">
                    <i class="fas fa-times"></i> Fechar
                </button>
            </div>

            <div class="tab-content active" id="cadastro-usuario">
                <h3><i class="fas fa-user-plus"></i> Cadastrar Novo Usuário</h3>
                
                <div class="alert alert-success" id="success-alert"></div>
                <div class="alert alert-error" id="error-alert"></div>
                
                <form id="userForm">
                    <div class="form-group">
                        <label for="username">Nome de Usuário:</label>
                        <input type="text" id="username" name="username" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">E-mail:</label>
                        <input type="email" id="email" name="email" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Senha:</label>
                        <input type="password" id="password" name="password" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password">Confirmar Senha:</label>
                        <input type="password" id="confirm_password" name="confirm_password" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="tipo_usuario">Tipo de Usuário:</label>
                        <select id="tipo_usuario" name="tipo_usuario" required>
                            <option value="">Selecione o tipo</option>
                            <option value="admin">Administrador</option>
                            <option value="user">Usuário Padrão</option>
                        </select>
                    </div>
                    
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save"></i> Cadastrar Usuário
                    </button>
                </form>
            </div>

            <div class="tab-content" id="listar-usuarios">
                <h3><i class="fas fa-list"></i> Usuários Cadastrados</h3>
                <div id="users-list">
                    <p>Carregando usuários...</p>
                </div>
            </div>
        </div>
    </div>

    <script>
        function showTab(tabName) {
            const tabContainer = document.getElementById('tabContainer');
            if (tabName === 'usuarios') {
                tabContainer.style.display = 'block';
                tabContainer.scrollIntoView({ behavior: 'smooth' });
                loadUsers();
            }
        }

        function closeTab() {
            document.getElementById('tabContainer').style.display = 'none';
        }

        function showTabContent(tabId) {
            const tabContents = document.querySelectorAll('.tab-content');
            tabContents.forEach(content => content.classList.remove('active'));
            
            const tabBtns = document.querySelectorAll('.tab-btn');
            tabBtns.forEach(btn => btn.classList.remove('active'));
            
            document.getElementById(tabId).classList.add('active');
            event.target.classList.add('active');
            
            if (tabId === 'listar-usuarios') {
                loadUsers();
            }
        }

        function showAlert(message, type) {
            const alertElement = document.getElementById(type + '-alert');
            alertElement.textContent = message;
            alertElement.style.display = 'block';
            
            setTimeout(() => {
                alertElement.style.display = 'none';
            }, 5000);
        }

        function hideAlerts() {
            document.getElementById('success-alert').style.display = 'none';
            document.getElementById('error-alert').style.display = 'none';
        }

        document.getElementById('userForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            hideAlerts();
            
            const formData = new FormData(this);
            
            try {
                const response = await fetch('../includes/cadastrar_usuario.php', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    showMessage(data.message, 'success');
                    this.reset();
                    loadUsers();
                } else {
                    showMessage(data.message, 'error');
                }
            } catch (error) {
                showMessage('Erro ao cadastrar usuário. Tente novamente.', 'error');
            }
        });

        async function loadUsers() {
            try {
                const response = await fetch('listar_usuarios.php');
                const data = await response.json();
                
                const usersList = document.getElementById('users-list');
                
                if (data.success && data.users.length > 0) {
                    let html = '<div style="overflow-x: auto;"><table style="width: 100%; border-collapse: collapse; margin-top: 1rem;">';
                    html += '<thead><tr style="background: #f8f9fa;">';
                    html += '<th style="padding: 1rem; border: 1px solid #dee2e6; text-align: left;">ID</th>';
                    html += '<th style="padding: 1rem; border: 1px solid #dee2e6; text-align: left;">Usuário</th>';
                    html += '<th style="padding: 1rem; border: 1px solid #dee2e6; text-align: left;">E-mail</th>';
                    html += '<th style="padding: 1rem; border: 1px solid #dee2e6; text-align: left;">Tipo</th>';
                    html += '<th style="padding: 1rem; border: 1px solid #dee2e6; text-align: left;">Data Criação</th>';
                    html += '</tr></thead><tbody>';
                    
                    data.users.forEach(user => {
                        html += '<tr>';
                        html += `<td style="padding: 1rem; border: 1px solid #dee2e6;">${user.id}</td>`;
                        html += `<td style="padding: 1rem; border: 1px solid #dee2e6;">${user.username}</td>`;
                        html += `<td style="padding: 1rem; border: 1px solid #dee2e6;">${user.email}</td>`;
                        html += `<td style="padding: 1rem; border: 1px solid #dee2e6;">${user.tipo_usuario || 'N/A'}</td>`;
                        html += `<td style="padding: 1rem; border: 1px solid #dee2e6;">${user.data_criacao || 'N/A'}</td>`;
                        html += '</tr>';
                    });
                    
                    html += '</tbody></table></div>';
                    usersList.innerHTML = html;
                } else {
                    usersList.innerHTML = '<p style="text-align: center; color: #666; padding: 2rem;">Nenhum usuário encontrado.</p>';
                }
            } catch (error) {
                document.getElementById('users-list').innerHTML = '<p style="text-align: center; color: #e74c3c; padding: 2rem;">Erro ao carregar usuários.</p>';
                showMessage('Erro ao carregar usuários. Tente novamente.', 'error');
            }
        }
    </script>
</body>
</html>

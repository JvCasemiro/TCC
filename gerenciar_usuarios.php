<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Usuários - Automação Residencial</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
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
            border-bottom: 1px solid #eee;
        }
        
        .user-info h4 {
            color: #333;
            margin: 0 0 8px 0;
            font-size: 16px;
        }
        
        .user-info p {
            color: #666;
            margin: 4px 0;
            font-size: 14px;
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
        
        .users-table {
            background: white;
            border-radius: 8px;
            padding: 20px;
            margin-top: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .users-table h2 {
            color: #2f3640;
            margin-bottom: 20px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        
        th {
            background-color: #f8f9fa;
            color: #333;
            font-weight: 600;
        }
        
        td {
            color: #666;
        }
        
        .btn {
            padding: 6px 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 12px;
            margin-right: 5px;
            transition: background-color 0.3s;
        }
        
        .btn-edit {
            background-color: #4a90e2;
            color: white;
        }
        
        .btn-edit:hover {
            background-color: #357abd;
        }
        
        .btn-delete {
            background-color: #e74c3c;
            color: white;
        }
        
        .btn-delete:hover {
            background-color: #c0392b;
        }
        
        .btn-add {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 25px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 20px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
        }
        
        .btn-add:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
        }

        .tab-container {
            background: white;
            border-radius: 15px;
            margin-top: 2rem;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            display: none;
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
            color: #333;
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
        
        .btn-form {
            background: #4a90e2;
            color: white;
            border: none;
            padding: 0.75rem 2rem;
            border-radius: 8px;
            font-size: 1rem;
            cursor: pointer;
            transition: background 0.3s;
        }
        
        .btn-form:hover {
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

        @media (max-width: 768px) {
            .header-content {
                flex-direction: column;
                gap: 10px;
            }
            
            .user-menu {
                margin-top: 10px;
            }
            
            table {
                font-size: 12px;
            }
            
            th, td {
                padding: 8px;
            }
        }
    </style>
</head>
<body>
    <header>
        <div class="header-content">
            <div class="logo">
                <img src="img/logo.png" alt="Logo">
                Automação Residencial
            </div>
            <div class="user-menu">
                <div class="user-dropdown">
                    <span class="user-name" onclick="toggleDropdown()">
                        <i class="fas fa-user"></i> <?php echo htmlspecialchars($_SESSION['username']); ?>
                        <i class="fas fa-chevron-down" style="margin-left: 5px; font-size: 12px;"></i>
                    </span>
                    <div class="dropdown-content" id="userDropdown">
                        <div class="user-info">
                            <h4><i class="fas fa-user"></i> <?php echo htmlspecialchars($_SESSION['username']); ?></h4>
                            <p><i class="fas fa-envelope"></i> <?php echo isset($_SESSION['email']) ? htmlspecialchars($_SESSION['email']) : htmlspecialchars($_SESSION['username']) . '@sistema.com'; ?></p>
                            <p><i class="fas fa-clock"></i> Último acesso: <?php echo date('d/m/Y H:i'); ?></p>
                        </div>
                    </div>
                </div>
                <a href="menu.php" class="back-btn" style="text-decoration: none; display: inline-block; color: white;">
                    <i class="fas fa-arrow-left"></i> Voltar ao Menu
                </a>
                <form action="logout.php" method="post" style="display: inline;">
                    <button type="submit" class="logout-btn">Sair</button>
                </form>
            </div>
        </div>
    </header>
    
    <div class="container">
        <div class="welcome">
            <h1>Gerenciamento de Usuários</h1>
            <p>Visualize, adicione e gerencie todos os usuários do sistema.</p>
        </div>
        
        <div class="users-table">
            <button class="btn-add" onclick="showTab('usuarios')">
                <i class="fas fa-plus"></i> Adicionar Novo Usuário
            </button>
            
            <h2>Lista de Usuários</h2>
            <table id="usersTable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nome de Usuário</th>
                        <th>Email</th>
                        <th>Tipo</th>
                        <th>Data de Criação</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody id="usersTableBody">
                    <tr>
                        <td colspan="6" style="text-align: center;">Carregando usuários...</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="tab-container" id="tabContainer">
            <div class="tab-header">
                <button class="tab-btn active" onclick="showTabContent('cadastro-usuario')">
                    <i class="fas fa-user-plus"></i> Cadastrar Usuário
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
                    
                    <button type="submit" class="btn-form btn-success">
                        <i class="fas fa-save"></i> Cadastrar Usuário
                    </button>
                </form>
            </div>
        </div>
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            loadUsers();
        });

        function showTab(tabName) {
            const tabContainer = document.getElementById('tabContainer');
            if (tabName === 'usuarios') {
                tabContainer.style.display = 'block';
                tabContainer.scrollIntoView({ behavior: 'smooth' });
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
                const response = await fetch('cadastrar_usuario.php', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    showAlert(data.message, 'success');
                    this.reset();
                    loadUsers(); 
                } else {
                    showAlert(data.message, 'error');
                }
            } catch (error) {
                showAlert('Erro ao cadastrar usuário. Tente novamente.', 'error');
            }
        });
        
        function loadUsers() {
            fetch('listar_usuarios.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        displayUsers(data.users);
                    } else {
                        showError('Erro ao carregar usuários: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showError('Erro ao conectar com o servidor');
                });
        }
        
        function displayUsers(users) {
            const tbody = document.getElementById('usersTableBody');
            
            if (users.length === 0) {
                tbody.innerHTML = '<tr><td colspan="6" style="text-align: center;">Nenhum usuário encontrado</td></tr>';
                return;
            }
            
            tbody.innerHTML = users.map(user => `
                <tr>
                    <td>${user.id}</td>
                    <td>${user.username}</td>
                    <td>${user.email}</td>
                    <td>${user.tipo_usuario}</td>
                    <td>${user.data_criacao || 'N/A'}</td>
                    <td>
                        <button class="btn btn-edit" onclick="editUser(${user.id})">
                            <i class="fas fa-edit"></i> Editar
                        </button>
                        <button class="btn btn-delete" onclick="deleteUser(${user.id})">
                            <i class="fas fa-trash"></i> Excluir
                        </button>
                    </td>
                </tr>
            `).join('');
        }
        
        function editUser(userId) {
            alert('Funcionalidade de edição em desenvolvimento. ID do usuário: ' + userId);
        }
        
        function deleteUser(userId) {
            if (confirm('Tem certeza que deseja excluir este usuário?')) {
                alert('Funcionalidade de exclusão em desenvolvimento. ID do usuário: ' + userId);
            }
        }
        
        function showError(message) {
            const tbody = document.getElementById('usersTableBody');
            tbody.innerHTML = `<tr><td colspan="6" style="text-align: center; color: #e74c3c;">${message}</td></tr>`;
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
    </script>
</body>
</html>

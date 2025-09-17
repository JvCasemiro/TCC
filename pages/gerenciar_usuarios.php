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
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Usuários - Automação Residencial</title>
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
        
        .user-info p:last-child {
            margin-top: 12px;
            padding-top: 8px;
            border-top: 1px solid #2c3e50;
            color: #95a5a6;
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
            background-color: #c0392b;
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
            background-color: #4a90e2;
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
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-right: 10px;
            text-decoration: none;
        }
        
        .back-btn:hover {
            background-color: #357abd;
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

        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            align-items: center;
            justify-content: center;
        }

        .modal-content {
            background-color: #FFF;
            padding: 25px;
            border-radius: 8px;
            width: 90%;
            max-width: 500px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
            position: relative;
        }

        .close {
            position: absolute;
            right: 15px;
            top: 10px;
            color: #000;
            font-size: 24px;
            font-weight: bold;
            cursor: pointer;
        }

        .modal h3 {
            margin-top: 0;
            color: #000;
            font-size: 1.5rem;
            margin-bottom: 15px;
        }

        .modal p {
            margin-bottom: 20px;
            color: #000;
            line-height: 1.5;
        }

        .modal-buttons {
            display: flex;
            justify-content: flex-end;
            gap: 15px;
            margin-top: 20px;
        }

        .btn-cancel {
            background-color: #7f8c8d;
            color: white;
            border: none;
            padding: 8px 20px;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .btn-cancel:hover {
            background-color: #95a5a6;
        }

        .actions {
            display: flex;
            gap: 8px;
            justify-content: center;
        }

        .btn-edit, .btn-delete {
            padding: 6px 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 5px;
            transition: all 0.3s ease;
        }

        .btn-edit {
            background-color: #3498db;
            color: white;
        }

        .btn-edit:hover {
            background-color: #2980b9;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }

        .btn-delete {
            background-color: #e74c3c;
            color: white;
        }

        .btn-delete:hover {
            background-color: #c0392b;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }

        .loading, .no-data {
            text-align: center;
            padding: 20px;
            color: #bdc3c7;
            font-style: italic;
        }

        .error-message {
            color: #e74c3c;
            margin-top: 10px;
            text-align: center;
        }

        #editUserModal .modal-content {
            background-color: #fff;
            color: #000;
        }

        #editUserModal .modal-content h3 {
            color: #000;
            margin-bottom: 20px;
        }

        #editUserModal .form-group {
            margin-bottom: 15px;
        }

        #editUserModal label {
            display: block;
            margin-bottom: 5px;
            color: #000;
        }

        #editUserModal input[type="text"],
        #editUserModal input[type="email"],
        #editUserModal input[type="password"],
        #editUserModal select {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #000;
            border-radius: 4px;
            background-color: #fff;
            color: #000;
            font-size: 14px;
        }

        #editUserModal input[type="checkbox"] {
            margin-right: 8px;
        }

        #editUserModal .close-edit {
            position: absolute;
            right: 15px;
            top: 10px;
            color: #aaa;
            font-size: 24px;
            font-weight: bold;
            cursor: pointer;
        }

        #editUserModal .close-edit:hover {
            color: #ecf0f1;
        }

        #editUserModal .alert {
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 4px;
            font-size: 14px;
        }

        #editUserModal .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        #editUserModal .alert-error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
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
                <a href="menu.php" class="back-btn" style="text-decoration: none; display: inline-block; color: white;">
                    <i class="fas fa-arrow-left"></i> Voltar ao Menu
                </a>
                <form action="../auth/logout.php" method="post" style="display: inline;">
                    <button type="submit" class="logout-btn">
                        <i class="fas fa-sign-out-alt"></i> Sair
                    </button>
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
                const response = await fetch('../includes/cadastrar_usuario.php', {
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
            const tableBody = document.getElementById('usersTableBody');
            tableBody.innerHTML = '<tr><td colspan="6" style="text-align: center;">Carregando usuários...</td></tr>';
            
            fetch('listar_usuarios.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.users && data.users.length > 0) {
                        tableBody.innerHTML = '';
                        data.users.forEach(user => {
                            const row = document.createElement('tr');
                            row.innerHTML = `
                                <td>${user.id}</td>
                                <td>${user.username}</td>
                                <td>${user.email}</td>
                                <td>${user.tipo_usuario === 'admin' ? 'Administrador' : 'Usuário'}</td>
                                <td>${user.data_criacao || 'N/A'}</td>
                                <td class="actions">
                                    <button class="btn-edit" onclick="editarUsuario(${user.id})">
                                        <i class="fas fa-edit"></i> Editar
                                    </button>
                                    <button class="btn-delete" onclick="confirmDelete(${user.id})">
                                        <i class="fas fa-trash"></i> Excluir
                                    </button>
                                </td>
                            `;
                            tableBody.appendChild(row);
                        });
                    } else {
                        tableBody.innerHTML = '<tr><td colspan="6" style="text-align: center;">' + 
                            (data.message || 'Nenhum usuário encontrado') + '</td></tr>';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    tableBody.innerHTML = '<tr><td colspan="6" style="text-align: center; color: #e74c3c;">' +
                        'Erro ao carregar usuários. Tente novamente mais tarde.</td></tr>';
                });
        }
        
        const editModal = document.createElement('div');
        editModal.id = 'editUserModal';
        editModal.className = 'modal';
        editModal.innerHTML = `
            <div class="modal-content">
                <span class="close-edit">&times;</span>
                <h3>Editar Usuário</h3>
                <div class="alert" id="edit-success-alert" style="display: none;"></div>
                <div class="alert alert-error" id="edit-error-alert" style="display: none;"></div>
                <form id="editUserForm">
                    <input type="hidden" id="edit-user-id">
                    <div class="form-group">
                        <label for="edit-username">Nome de Usuário:</label>
                        <input type="text" id="edit-username" name="username" required>
                    </div>
                    <div class="form-group">
                        <label for="edit-email">E-mail:</label>
                        <input type="email" id="edit-email" name="email" required>
                    </div>
                    <div class="form-group">
                        <label for="edit-tipo-usuario">Tipo de Usuário:</label>
                        <select id="edit-tipo-usuario" name="tipo_usuario" required>
                            <option value="user">Usuário</option>
                            <option value="admin">Administrador</option>
                        </select>
                    </div>
                    <div class="form-group" style="display: flex; align-items: center; white-space: nowrap; height: 24px;">
                        <label for="change-password-toggle" style="margin: 0; padding-right: 0; line-height: 24px; display: inline-block;">Alterar senha</label>
                        <input type="checkbox" id="change-password-toggle" style="margin: 0 0 0 -2px; padding: 0; position: relative; top: 1px;">
                    </div>
                    <div id="password-fields" style="display: none;">
                        <div class="form-group">
                            <label for="edit-password">Nova Senha:</label>
                            <input type="password" id="edit-password" name="password">
                        </div>
                        <div class="form-group">
                            <label for="edit-confirm-password">Confirmar Nova Senha:</label>
                            <input type="password" id="edit-confirm-password" name="confirm_password">
                        </div>
                    </div>
                    <div class="modal-buttons">
                        <button type="button" class="btn-cancel" id="cancelEdit">Cancelar</button>
                        <button type="submit" class="btn-edit">Salvar Alterações</button>
                    </div>
                </form>
            </div>
        `;
        document.body.appendChild(editModal);

        const editModalElement = document.getElementById('editUserModal');
        const closeEditBtn = document.querySelector('.close-edit');
        const cancelEditBtn = document.getElementById('cancelEdit');
        const changePasswordToggle = document.getElementById('change-password-toggle');
        const passwordFields = document.getElementById('password-fields');
        const editForm = document.getElementById('editUserForm');

        changePasswordToggle.addEventListener('change', function() {
            passwordFields.style.display = this.checked ? 'block' : 'none';
            if (!this.checked) {
                document.getElementById('edit-password').value = '';
                document.getElementById('edit-confirm-password').value = '';
            }
        });

        function closeEditModal() {
            editModal.style.display = 'none';
            document.getElementById('edit-success-alert').style.display = 'none';
            document.getElementById('edit-error-alert').style.display = 'none';
            editForm.reset();
            passwordFields.style.display = 'none';
            changePasswordToggle.checked = false;
        }

        closeEditBtn.onclick = closeEditModal;
        cancelEditBtn.onclick = closeEditModal;
        window.addEventListener('click', function(event) {
            if (event.target === editModal) {
                closeEditModal();
            }
        });

        async function editarUsuario(userId) {
            try {
                const response = await fetch(`buscar_usuario.php?id=${userId}`);
                const data = await response.json();
                
                if (data.success && data.user) {
                    const user = data.user;
                    document.getElementById('edit-user-id').value = user.id;
                    document.getElementById('edit-username').value = user.username;
                    document.getElementById('edit-email').value = user.email;
                    document.getElementById('edit-tipo-usuario').value = user.tipo_usuario;
                    
                    editModal.style.display = 'flex';
                } else {
                    showAlert(data.message || 'Erro ao carregar dados do usuário', 'error');
                }
            } catch (error) {
                console.error('Erro ao carregar usuário:', error);
                showAlert('Erro ao carregar dados do usuário', 'error');
            }
        }

        editForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            document.getElementById('edit-success-alert').style.display = 'none';
            document.getElementById('edit-error-alert').style.display = 'none';
            
            const userId = document.getElementById('edit-user-id').value;
            const formData = {
                id: userId,
                username: document.getElementById('edit-username').value.trim(),
                email: document.getElementById('edit-email').value.trim(),
                tipo_usuario: document.getElementById('edit-tipo-usuario').value,
                password: document.getElementById('edit-password').value,
                confirm_password: document.getElementById('edit-confirm-password').value
            };
            
            try {
                const response = await fetch('atualizar_usuario.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(formData)
                });
                
                const result = await response.json();
                
                if (result.success) {
                    const successAlert = document.getElementById('edit-success-alert');
                    successAlert.textContent = result.message || 'Usuário atualizado com sucesso!';
                    successAlert.className = 'alert alert-success';
                    successAlert.style.display = 'block';
                    
                    loadUsers();
                    
                    setTimeout(() => {
                        closeEditModal();
                    }, 2000);
                } else {
                    const errorAlert = document.getElementById('edit-error-alert');
                    errorAlert.textContent = result.message || 'Erro ao atualizar usuário';
                    errorAlert.style.display = 'block';
                }
            } catch (error) {
                console.error('Erro ao atualizar usuário:', error);
                const errorAlert = document.getElementById('edit-error-alert');
                errorAlert.textContent = 'Erro ao conectar ao servidor';
                errorAlert.style.display = 'block';
            }
        });
        
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

    <div id="deleteModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h3>Confirmar Exclusão</h3>
            <p>Tem certeza que deseja excluir este usuário?</p>
            <div class="modal-buttons">
                <button id="confirmDelete" class="btn-delete">Sim</button>
                <button id="cancelDelete" class="btn-cancel">Cancelar</button>
            </div>
        </div>
    </div>

    <script>
        let userIdToDelete = null;
        const modal = document.getElementById('deleteModal');
        const confirmBtn = document.getElementById('confirmDelete');
        const cancelBtn = document.getElementById('cancelDelete');
        const closeBtn = document.querySelector('.close');

        function confirmDelete(userId) {
            userIdToDelete = userId;
            modal.style.display = 'flex';
        }
        closeBtn.onclick = function() {
            modal.style.display = 'none';
        }

        window.onclick = function(event) {
            if (event.target == modal) {
                modal.style.display = 'none';
            }
        }

        cancelBtn.onclick = function() {
            modal.style.display = 'none';
            userIdToDelete = null;
        }

        confirmBtn.onclick = async function() {
            if (!userIdToDelete) return;
            
            try {
                const response = await fetch(`excluir_usuario.php?id=${userIdToDelete}`, {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json'
                    }
                });

                const result = await response.json();
                
                if (result.success) {
                    showAlert('Usuário excluído com sucesso!', 'success');
                    loadUsers();
                } else {
                    showAlert(result.message || 'Erro ao excluir usuário', 'error');
                }
            } catch (error) {
                console.error('Erro ao excluir usuário:', error);
                showAlert('Erro ao conectar ao servidor', 'error');
            }
            
            modal.style.display = 'none';
            userIdToDelete = null;
        };
    </script>
</body>
</html>

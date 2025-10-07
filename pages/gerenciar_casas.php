<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['Tipo_Usuario'] !== 'admin') {
    header('Location: ../index.php');
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
    <title>Gerenciar Casas</title>
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
            color: #000;
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
            padding: 0;
            box-shadow: 0 2px 20px rgba(0, 0, 0, 0.1);
            position: sticky;
            top: 0;
            z-index: 1000;
            height: 70px;
        }
        
        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 20px;
            height: 100%;
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
            gap: 8px;
            position: relative;
            height: 40px;
        }
        
        .user-menu form {
            margin: 0;
            padding: 0;
            display: flex;
            align-items: center;
            height: 100%;
        }
        
        .user-menu > * {
            display: flex;
            align-items: center;
            height: 40px;
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
            padding: 0 12px;
            border-radius: 4px;
            transition: background-color 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 40px;
            line-height: 1;
            font-size: 14px;
            white-space: nowrap;
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
        
        .config-btn {
            background: linear-gradient(135deg, #27ae60 0%, #2ecc71 100%);
            color: white;
            border: none;
            padding: 0 16px;
            border-radius: 25px;
            cursor: pointer;
            font-size: 14px;
            transition: all 0.4s ease-in-out;
            position: relative;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(39, 174, 96, 0.3);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            height: 40px;
            line-height: 1;
            white-space: nowrap;
        }
        
        .config-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(39, 174, 96, 0.4);
        }
        
        .logout-btn {
            background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
            color: white;
            border: none;
            padding: 0 16px;
            border-radius: 25px;
            cursor: pointer;
            font-size: 14px;
            transition: all 0.4s ease-in-out;
            position: relative;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(231, 76, 60, 0.3);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            height: 40px;
            line-height: 1;
            white-space: nowrap;
        }
        
        .logout-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(231, 76, 60, 0.4);
        }
        
        .card {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 30px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.37);
        }
        
        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .card-header h2 {
            font-size: 1.5rem;
            color: #ffffff;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
            font-weight: 700;
        }
        
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
            text-decoration: none;
        }
        
        .btn i {
            font-size: 1rem;
        }
        
        .btn-primary {
            background-color: #4a90e2;
            color: white;
        }
        
        .btn-primary:hover {
            background-color: #357abd;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(74, 144, 226, 0.3);
        }
        
        .table-container {
            overflow-x: auto;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 10px;
            overflow: hidden;
        }
        
        th, td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        td {
            color: #ffffff;
            font-weight: 500;
        }
        
        th {
            background-color: rgba(255, 255, 255, 0.1);
            color: #ffffff;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.9rem;
            letter-spacing: 1px;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.3);
        }
        
        tr:hover {
            background-color: rgba(255, 255, 255, 0.05);
        }
        
        .status {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        
        .status-active {
            background-color: rgba(46, 213, 115, 0.2);
            color: #2ed573;
        }
        
        .status-inactive {
            background-color: rgba(255, 71, 87, 0.2);
            color: #ff4757;
        }
        
        .action-buttons {
            display: flex;
            gap: 10px;
        }
        
        .btn-edit {
            background-color: #f39c12;
            color: white;
            padding: 8px 12px;
            border-radius: 5px;
        }
        
        .btn-delete {
            background-color: #e74c3c;
            color: white;
            padding: 8px 12px;
            border-radius: 5px;
        }
        
        .btn-edit:hover, .btn-delete:hover {
            opacity: 0.9;
            transform: translateY(-2px);
        }
        
        /* Modal */
        .modal {
            display: none;
            position: fixed;
            z-index: 1001;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.7);
            overflow: auto;
        }
        
        .modal-content {
            background: #1e293b;
            margin: 5% auto;
            padding: 30px;
            border-radius: 10px;
            max-width: 500px;
            width: 90%;
            position: relative;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            animation: modalFadeIn 0.3s;
        }
        
        @keyframes modalFadeIn {
            from {opacity: 0; transform: translateY(-50px);}
            to {opacity: 1; transform: translateY(0);}
        }
        
        .modal h3 {
            margin-top: 0;
            color: #4a90e2;
            margin-bottom: 20px;
            font-size: 1.5rem;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #a0aec0;
            font-weight: 500;
        }
        
        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #2d3748;
            border-radius: 5px;
            background-color: #1a202c;
            color: #e2e8f0;
            font-size: 1rem;
            transition: all 0.3s;
        }
        
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #4a90e2;
            box-shadow: 0 0 0 2px rgba(74, 144, 226, 0.2);
        }
        
        .modal-buttons {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-top: 30px;
        }
        
        .btn-cancel {
            background-color: #4a5568;
            color: white;
        }
        
        .btn-cancel:hover {
            background-color: #2d3748;
        }
        
        .close {
            position: absolute;
            right: 20px;
            top: 20px;
            color: #a0aec0;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
            transition: color 0.3s;
        }
        
        .close:hover {
            color: #e2e8f0;
        }
        
        .alert {
            padding: 12px 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .alert-success {
            background-color: rgba(46, 213, 115, 0.15);
            color: #2ed573;
            border-left: 4px solid #2ed573;
        }
        
        .alert-error {
            background-color: rgba(255, 71, 87, 0.15);
            color: #ff4757;
            border-left: 4px solid #ff4757;
        }
        
        .back-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 20px;
            color: #a0aec0;
            text-decoration: none;
            transition: color 0.3s;
        }
        
        .back-btn:hover {
            color: #4a90e2;
        }
        
        /* Responsividade */
        @media (max-width: 768px) {
            .header-content {
                flex-direction: column;
                gap: 10px;
            }
            
            .user-menu {
                margin-top: 10px;
            }
            
            .container {
                padding: 10px;
            }
            
            .welcome h1 {
                font-size: 2rem;
            }
            
            .modal-content {
                margin: 10% auto;
                width: 95%;
                padding: 20px;
            }
            
            .table-container {
                overflow-x: auto;
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
                <button class="config-btn" onclick="window.location.href='dashboard.php'">
                    <i class="fas fa-home"></i> Dashboard
                </button>
                <form action="../auth/logout.php" method="post">
                    <button type="submit" class="logout-btn">
                        <i class="fas fa-sign-out-alt"></i> Sair
                    </button>
                </form>
            </div>
        </div>
    </header>

    <div class="container">        
        <div class="welcome">
            <h1>Gerenciamento de Casas</h1>
            <p>Visualize, adicione e gerencie todas as casas do sistema.</p>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h2>Lista de Casas</h2>
                <button id="addHouseBtn" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Adicionar Casa
                </button>
            </div>
            
            <div id="alert-container"></div>
            
            <div class="table-container">
                <table id="housesTable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nome</th>
                            <th>Endereço</th>
                            <th>Data de Criação</th>
                            <th>Status</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody id="housesTableBody">
                        <!-- Dados serão preenchidos via JavaScript -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <!-- Modal Adicionar/Editar Casa -->
    <div id="houseModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h3 id="modalTitle">Adicionar Nova Casa</h3>
            <div id="modalAlert" class="alert" style="display: none;"></div>
            <form id="houseForm">
                <input type="hidden" id="houseId">
                <div class="form-group">
                    <label for="houseName">Nome da Casa *</label>
                    <input type="text" id="houseName" name="houseName" required>
                </div>
                <div class="form-group">
                    <label for="houseAddress">Endereço *</label>
                    <textarea id="houseAddress" name="houseAddress" rows="3" required></textarea>
                </div>
                <div class="form-group">
                    <label for="houseStatus">Status</label>
                    <select id="houseStatus" name="houseStatus">
                        <option value="1" selected>Ativo</option>
                        <option value="0">Inativo</option>
                    </select>
                </div>
                <div class="modal-buttons">
                    <button type="button" class="btn btn-cancel" id="cancelBtn">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Salvar</button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Modal de Confirmação de Exclusão -->
    <div id="deleteModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h3>Confirmar Exclusão</h3>
            <p>Tem certeza que deseja excluir esta casa? Esta ação não pode ser desfeita.</p>
            <div class="modal-buttons">
                <button type="button" class="btn btn-cancel" id="cancelDeleteBtn">Cancelar</button>
                <button type="button" class="btn btn-delete" id="confirmDeleteBtn">Excluir</button>
            </div>
        </div>
    </div>
    
    <script>
        // Elementos do DOM
        const housesTableBody = document.getElementById('housesTableBody');
        const houseModal = document.getElementById('houseModal');
        const deleteModal = document.getElementById('deleteModal');
        const houseForm = document.getElementById('houseForm');
        const modalTitle = document.getElementById('modalTitle');
        const modalAlert = document.getElementById('modalAlert');
        const alertContainer = document.getElementById('alert-container');
        
        // Botões
        const addHouseBtn = document.getElementById('addHouseBtn');
        const closeModalBtns = document.querySelectorAll('.close');
        const cancelBtn = document.getElementById('cancelBtn');
        const cancelDeleteBtn = document.getElementById('cancelDeleteBtn');
        const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');
        
        // Variáveis globais
        let houses = [];
        let houseToDelete = null;
        
        // Carregar casas ao carregar a página
        document.addEventListener('DOMContentLoaded', () => {
            loadHouses();
            
            // Event Listeners
            addHouseBtn.addEventListener('click', () => openHouseModal());
            houseForm.addEventListener('submit', handleHouseSubmit);
            cancelBtn.addEventListener('click', () => closeModal(houseModal));
            cancelDeleteBtn.addEventListener('click', () => closeModal(deleteModal));
            confirmDeleteBtn.addEventListener('click', confirmDeleteHouse);
            
            // Fechar modal ao clicar fora do conteúdo
            window.addEventListener('click', (e) => {
                if (e.target === houseModal) closeModal(houseModal);
                if (e.target === deleteModal) closeModal(deleteModal);
            });
            
            // Fechar modais ao clicar no X
            closeModalBtns.forEach(btn => {
                btn.addEventListener('click', () => {
                    const modal = btn.closest('.modal');
                    closeModal(modal);
                });
            });
        });
        
        // Função para carregar as casas
        async function loadHouses() {
            try {
                const response = await fetch('../includes/cadastrar_casa.php');
                const data = await response.json();
                
                if (data.success) {
                    houses = data.casas || [];
                    renderHousesTable();
                } else {
                    showAlert('error', data.message || 'Erro ao carregar casas');
                }
            } catch (error) {
                console.error('Erro ao carregar casas:', error);
                showAlert('error', 'Erro ao carregar casas. Tente novamente mais tarde.');
            }
        }
        
        // Função para renderizar a tabela de casas
        function renderHousesTable() {
            if (!houses || houses.length === 0) {
                housesTableBody.innerHTML = `
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 20px;">
                            Nenhuma casa cadastrada. Clique em "Adicionar Casa" para começar.
                        </td>
                    </tr>
                `;
                return;
            }
            
            housesTableBody.innerHTML = houses.map(house => `
                <tr>
                    <td>${house.ID_Casa}</td>
                    <td>${escapeHtml(house.Nome)}</td>
                    <td>${truncateText(house.Endereco, 50)}</td>
                    <td>${formatDate(house.Data_Criacao)}</td>
                    <td>
                        <span class="status ${house.Ativo ? 'status-active' : 'status-inactive'}">
                            ${house.Ativo ? 'Ativo' : 'Inativo'}
                        </span>
                    </td>
                    <td class="action-buttons">
                        <button class="btn-edit" onclick="editHouse(${house.ID_Casa})">
                            <i class="fas fa-edit"></i> Editar
                        </button>
                        <button class="btn-delete" onclick="confirmDelete(${house.ID_Casa})">
                            <i class="fas fa-trash-alt"></i> Excluir
                        </button>
                    </td>
                </tr>
            `).join('');
        }
        
        // Função para abrir o modal de casa
        function openHouseModal(houseId = null) {
            const modal = document.getElementById('houseModal');
            const form = document.getElementById('houseForm');
            
            // Limpar formulário
            form.reset();
            modalAlert.style.display = 'none';
            
            if (houseId) {
                // Modo edição
                const house = houses.find(h => h.ID_Casa == houseId);
                if (house) {
                    modalTitle.textContent = 'Editar Casa';
                    document.getElementById('houseId').value = house.ID_Casa;
                    document.getElementById('houseName').value = house.Nome;
                    document.getElementById('houseAddress').value = house.Endereco;
                    document.getElementById('houseStatus').value = house.Ativo ? '1' : '0';
                }
            } else {
                // Modo adição
                modalTitle.textContent = 'Adicionar Nova Casa';
                document.getElementById('houseId').value = '';
                document.getElementById('houseStatus').value = '1';
            }
            
            modal.style.display = 'block';
        }
        
        // Função para lidar com o envio do formulário
        async function handleHouseSubmit(e) {
            e.preventDefault();
            
            const houseId = document.getElementById('houseId').value;
            const isEdit = !!houseId;
            
            const houseData = {
                nome: document.getElementById('houseName').value.trim(),
                endereco: document.getElementById('houseAddress').value.trim(),
                ativo: document.getElementById('houseStatus').value === '1' ? 1 : 0
            };
            
            // Validação simples
            if (!houseData.nome || !houseData.endereco) {
                showModalAlert('Por favor, preencha todos os campos obrigatórios.', 'error');
                return;
            }
            
            try {
                let response;
                
                if (isEdit) {
                    // Atualizar casa existente
                    response = await fetch(`../includes/editar_casa.php?id=${houseId}`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: new URLSearchParams({
                            ...houseData,
                            id_casa: houseId
                        })
                    });
                } else {
                    // Criar nova casa
                    response = await fetch('../includes/cadastrar_casa.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: new URLSearchParams(houseData)
                    });
                }
                
                const data = await response.json();
                
                if (data.success) {
                    showAlert('success', isEdit ? 'Casa atualizada com sucesso!' : 'Casa cadastrada com sucesso!');
                    closeModal(houseModal);
                    loadHouses();
                } else {
                    showModalAlert(data.message || 'Erro ao salvar casa', 'error');
                }
            } catch (error) {
                console.error('Erro ao salvar casa:', error);
                showModalAlert('Erro ao salvar casa. Tente novamente mais tarde.', 'error');
            }
        }
        
        // Função para confirmar exclusão de casa
        function confirmDelete(houseId) {
            houseToDelete = houseId;
            deleteModal.style.display = 'block';
        }
        
        // Função para confirmar e excluir casa
        async function confirmDeleteHouse() {
            if (!houseToDelete) return;
            
            try {
                const response = await fetch(`../includes/excluir_casa.php?id=${houseToDelete}`, {
                    method: 'POST'
                });
                
                const data = await response.json();
                
                if (data.success) {
                    showAlert('success', 'Casa excluída com sucesso!');
                    closeModal(deleteModal);
                    loadHouses();
                } else {
                    showAlert('error', data.message || 'Erro ao excluir casa');
                }
            } catch (error) {
                console.error('Erro ao excluir casa:', error);
                showAlert('error', 'Erro ao excluir casa. Tente novamente mais tarde.');
            } finally {
                houseToDelete = null;
            }
        }
        
        // Função para editar casa
        async function editHouse(houseId) {
            try {
                const response = await fetch(`../includes/buscar_casa.php?id=${houseId}`);
                const data = await response.json();
                
                if (data.success && data.casa) {
                    openHouseModal(houseId);
                } else {
                    showAlert('error', data.message || 'Erro ao carregar dados da casa');
                }
            } catch (error) {
                console.error('Erro ao carregar casa:', error);
                showAlert('error', 'Erro ao carregar dados da casa');
            }
        }
        
        // Função para fechar modal
        function closeModal(modal) {
            modal.style.display = 'none';
            houseToDelete = null;
        }
        
        // Função para exibir alerta no modal
        function showModalAlert(message, type = 'error') {
            modalAlert.textContent = message;
            modalAlert.className = `alert alert-${type}`;
            modalAlert.style.display = 'block';
            
            // Rolar para o alerta
            modalAlert.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
        
        // Função para exibir alerta na página
        function showAlert(type, message) {
            const alertDiv = document.createElement('div');
            alertDiv.className = `alert alert-${type}`;
            alertDiv.innerHTML = `
                <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i>
                ${message}
            `;
            
            alertContainer.innerHTML = '';
            alertContainer.appendChild(alertDiv);
            
            // Remover o alerta após 5 segundos
            setTimeout(() => {
                alertDiv.style.opacity = '0';
                setTimeout(() => {
                    alertDiv.remove();
                }, 300);
            }, 5000);
        }
        
        // Funções auxiliares
        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
        
        function truncateText(text, maxLength) {
            if (!text) return '';
            if (text.length <= maxLength) return escapeHtml(text);
            return escapeHtml(text.substring(0, maxLength)) + '...';
        }
        
        function formatDate(dateString) {
            if (!dateString) return '';
            const date = new Date(dateString);
            return date.toLocaleDateString('pt-BR');
        }
        
        // Função para toggle do dropdown do usuário
        function toggleDropdown() {
            const dropdown = document.getElementById('userDropdown');
            dropdown.classList.toggle('show');
        }
        
        // Fechar dropdown ao clicar fora
        window.onclick = function(event) {
            if (!event.target.matches('.user-name') && !event.target.closest('.user-name')) {
                const dropdown = document.getElementById('userDropdown');
                if (dropdown.classList.contains('show')) {
                    dropdown.classList.remove('show');
                }
            }
        }
        
        // Adicionar funções ao escopo global para acesso via HTML
        window.editHouse = editHouse;
        window.confirmDelete = confirmDelete;
        window.toggleDropdown = toggleDropdown;
    </script>
</body>
</html>

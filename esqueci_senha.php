<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Redefinir Senha</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .container {
            background-color: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
        }
        h2 {
            text-align: center;
            color: #333;
            margin-bottom: 20px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        .input-group {
            position: relative;
            margin-bottom: 15px;
            width: 100%;
        }
        
        .input-group input[type="text"],
        .input-group input[type="password"] {
            width: 100%;
            padding: 10px 35px 10px 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        
        .toggle-password {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #666;
            z-index: 10;
            padding: 5px;
        }
        button {
            width: 100%;
            padding: 12px;
            background-color: #084d9b;
            color: white;
            border: 1px solid #084d9b;
            border-radius: 20px;
            cursor: pointer;
            font-size: 12px;
            font-weight: bold;
            letter-spacing: 1px;
            text-transform: uppercase;
            transition: transform 80ms ease-in;
            margin-top: 10px;
        }
        button:hover {
            background-color: #063d7a;
        }
        button:active {
            transform: scale(0.95);
        }
        .login-link {
            text-align: center;
            margin-top: 15px;
        }
        .login-link a {
            color: #084d9b;
            text-decoration: none;
            font-weight: bold;
        }
        .login-link a:hover {
            text-decoration: underline;
        }
        #message {
            margin-top: 15px;
            padding: 10px;
            border-radius: 4px;
            display: none;
        }
        .error {
            background-color: #ffebee;
            color: #c62828;
            border: 1px solid #ffcdd2;
        }
        .success {
            background-color: #e8f5e9;
            color: #2e7d32;
            border: 1px solid #c8e6c9;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Redefinir Senha</h2>
        <div id="message"></div>
        <form id="resetForm">
            <div class="form-group">
                <label for="username">Nome de Usuário:</label>
                <div class="input-group">
                    <input type="text" id="username" name="username" required>
                </div>
            </div>
            <div class="form-group">
                <label for="nova_senha">Nova Senha (mínimo 8 caracteres):</label>
                <div class="input-group">
                    <input type="password" id="nova_senha" name="nova_senha" minlength="8" required>
                    <i class="fas fa-eye toggle-password" onclick="togglePassword('nova_senha', event)"></i>
                </div>
            </div>
            <div class="form-group">
                <label for="confirmar_senha">Confirmar Nova Senha:</label>
                <div class="input-group">
                    <input type="password" id="confirmar_senha" name="confirmar_senha" minlength="8" required>
                    <i class="fas fa-eye toggle-password" onclick="togglePassword('confirmar_senha', event)"></i>
                </div>
            </div>
            <button type="submit">Redefinir Senha</button>
        </form>
        <div class="login-link">
            Lembrou sua senha? <a href="index.php">Faça login</a>
        </div>
    </div>

    <script>
        function togglePassword(inputId, event) {
            event = event || window.event;
            const passwordInput = document.getElementById(inputId);
            const icon = event.currentTarget || event.srcElement;
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }
        
        document.getElementById('resetForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const username = document.getElementById('username').value;
            const novaSenha = document.getElementById('nova_senha').value;
            const confirmarSenha = document.getElementById('confirmar_senha').value;
            const messageDiv = document.getElementById('message');
            
            if (novaSenha !== confirmarSenha) {
                showMessage('As senhas não coincidem', 'error');
                return;
            }
            
            if (novaSenha.length < 8) {
                showMessage('A senha deve ter pelo menos 8 caracteres', 'error');
                return;
            }
            
            const formData = new FormData();
            formData.append('username', username);
            formData.append('nova_senha', novaSenha);
            formData.append('confirmar_senha', confirmarSenha);
            
            fetch('redefinir_senha.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showMessage(data.message, 'success');
                    if (data.redirect) {
                        setTimeout(() => {
                            window.location.href = data.redirect;
                        }, 2000);
                    }
                } else {
                    showMessage(data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                showMessage('Ocorreu um erro ao processar sua solicitação', 'error');
            });
        });
        
        function showMessage(message, type) {
            const messageDiv = document.getElementById('message');
            messageDiv.textContent = message;
            messageDiv.className = type;
            messageDiv.style.display = 'block';
        }
    </script>
</body>
</html>

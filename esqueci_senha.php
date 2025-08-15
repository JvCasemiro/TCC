<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Redefinir Senha</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
    <style>
        /* Toastify custom styles */
        .toastify {
            padding: 16px 24px;
            color: #ffffff;
            display: flex;
            align-items: center;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            background: #4dabf7;
            position: fixed;
            left: 20px;
            right: 20px;
            bottom: 20px;
            opacity: 0;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border-radius: 8px;
            cursor: pointer;
            z-index: 2147483647;
            font-family: Arial, sans-serif;
            font-size: 15px;
            max-width: calc(100% - 40px);
            margin: 0 auto;
            box-sizing: border-box;
        }
        
        .toastify::before {
            content: '';
            display: inline-block;
            width: 24px;
            height: 24px;
            margin-right: 12px;
            background-size: contain;
            background-repeat: no-repeat;
            background-position: center;
            flex-shrink: 0;
        }
        
        .toastify.on {
            opacity: 1;
            transform: translateY(0);
        }
        
        .toast-close {
            opacity: 0.7;
            padding: 4px;
            margin-left: auto;
            font-size: 18px;
            line-height: 1;
            transition: opacity 0.2s;
        }
        
        .toast-close:hover {
            opacity: 1;
        }
        
        .toastify.toastify-error {
            background: #f03e3e;
        }
        
        .toastify.toastify-error::before {
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='24' height='24' viewBox='0 0 24 24' fill='none' stroke='white' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cline x1='18' y1='6' x2='6' y2='18'%3E%3C/line%3E%3Cline x1='6' y1='6' x2='18' y2='18'%3E%3C/line%3E%3C/svg%3E");
        }
        
        .toastify.toastify-success {
            background: #2b8a3e;
        }
        
        .toastify.toastify-success::before {
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='24' height='24' viewBox='0 0 24 24' fill='none' stroke='white' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='20 6 9 17 4 12'%3E%3C/polyline%3E%3C/svg%3E");
        }
        
        .toastify.toastify-info {
            background: #1971c2;
        }
        
        .toastify.toastify-info::before {
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='24' height='24' viewBox='0 0 24 24' fill='none' stroke='white' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Ccircle cx='12' cy='12' r='10'%3E%3C/circle%3E%3Cline x1='12' y1='16' x2='12' y2='12'%3E%3C/line%3E%3Cline x1='12' y1='8' x2='12.01' y2='8'%3E%3C/line%3E%3C/svg%3E");
        }
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
        /* Removed old message styles as we're using toast notifications now */
    </style>
</head>
<body>
    <div class="container">
        <h2>Redefinir Senha</h2>
        <div id="toast-container"></div>
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

    <script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
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
                showMessage('Ocorreu um erro ao processar sua solicitação. Por favor, tente novamente.', 'error');
            });
        });
        
        function showMessage(message, type = 'info') {
            const toastType = type === 'error' ? 'toastify-error' : 
                           type === 'success' ? 'toastify-success' : 'toastify-info';
            
            // Remove any existing toasts to prevent stacking
            document.querySelectorAll('.toastify').forEach(toast => {
                toast.remove();
            });
            
            const toast = Toastify({
                text: message,
                duration: 2500,
                gravity: 'bottom',
                position: 'center',
                className: `toastify ${toastType}`,
                stopOnFocus: true,
                offset: {
                    y: 0
                }
            });
            
            // Add animation class after the element is created
            toast.showToast();
            
            // Add margin to the first toast to prevent it from being too close to the edge
            const toastElement = document.querySelector('.toastify');
            if (toastElement) {
                toastElement.style.marginBottom = '20px';
            }
        }
    </script>
</body>
</html>

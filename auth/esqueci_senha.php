<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Redefinir Senha</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
    <link rel="shortcut icon" href="../assets/img/logo_domx_sem_nome.png" type="image/x-icon">
    <style>
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
            background: rgb(255, 255, 255);
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
            font-family: 'Montserrat', sans-serif;
            height: 100vh;
            margin: -20px 0 50px;
        }

        .container {
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 14px 28px rgba(0,0,0,0.25), 0 10px 10px rgba(0,0,0,0.22);
            position: relative;
            overflow: hidden;
            width: 400px;
            max-width: 100%;
            min-height: 480px;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0 50px;
        }

        .form-container {
            width: 100%;
            text-align: center;
            position: relative;
        }


        h1 {
            font-weight: bold;
            margin: 0 0 20px 0;
            color: #333;
            font-size: 28px;
        }

        p {
            color: #666;
            margin-bottom: 30px;
            font-size: 14px;
        }

        .input-group {
            position: relative;
            width: 100%;
            margin: 15px 0;
        }
        
        .input-group input[type="text"],
        .input-group input[type="password"] {
            background-color: #eee;
            border: none;
            padding: 12px 40px 12px 15px;
            width: 100%;
            border-radius: 5px;
            box-sizing: border-box;
            font-size: 14px;
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
            border-radius: 20px;
            border: 1px solid rgb(19, 48, 82);
            background-color: rgb(10, 49, 94);
            color: #FFFFFF;
            font-size: 12px;
            font-weight: bold;
            padding: 12px 45px;
            letter-spacing: 1px;
            text-transform: uppercase;
            transition: transform 80ms ease-in;
            cursor: pointer;
            margin-top: 20px;
            width: auto;
            min-width: 200px;
        }

        button:active {
            transform: scale(0.95);
        }

        button:focus {
            outline: none;
        }

        button:hover {
            background-color: rgb(8, 39, 75);
        }

        .login-link {
            text-align: center;
            margin-top: 20px;
        }

        .login-link a {
            color: #333;
            font-size: 14px;
            text-decoration: none;
            font-weight: normal;
        }

        .login-link a:hover {
            text-decoration: underline;
        }

        .form-group {
            margin-bottom: 0;
        }

        label {
            display: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="form-container">
            <div id="toast-container"></div>
            <form id="resetForm">
                <h1>Redefinir Senha</h1>
                <p>Digite suas novas credenciais para redefinir sua senha de acesso.</p>
                <div class="form-group">
                    <label for="username">Nome de Usuário:</label>
                    <div class="input-group">
                        <input type="text" id="username" name="username" placeholder="Nome de Usuário" required>
                    </div>
                </div>
                <div class="form-group">
                    <label for="nova_senha">Nova Senha (mínimo 8 caracteres):</label>
                    <div class="input-group">
                        <input type="password" id="nova_senha" name="nova_senha" placeholder="Nova Senha (mín. 8 caracteres)" minlength="8" required>
                        <i class="fas fa-eye toggle-password" onclick="togglePassword('nova_senha', event)"></i>
                    </div>
                </div>
                <div class="form-group">
                    <label for="confirmar_senha">Confirmar Nova Senha:</label>
                    <div class="input-group">
                        <input type="password" id="confirmar_senha" name="confirmar_senha" placeholder="Confirmar Nova Senha" minlength="8" required>
                        <i class="fas fa-eye toggle-password" onclick="togglePassword('confirmar_senha', event)"></i>
                    </div>
                </div>
                <button type="submit">Redefinir Senha</button>
                <div class="login-link">
                    Lembrou sua senha? <a href="../index.php">Faça login</a>
                </div>
            </form>
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
                showMessage('Ocorreu um erro ao processar sua solicitação. Por favor, tente novamente.', 'error');
            });
        });
        
        function showMessage(message, type = 'info') {
            const toastType = type === 'error' ? 'toastify-error' : 
                           type === 'success' ? 'toastify-success' : 'toastify-info';
            
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
            
            toast.showToast();
            
            const toastElement = document.querySelector('.toastify');
            if (toastElement) {
                toastElement.style.marginBottom = '20px';
            }
        }
    </script>
</body>
</html>

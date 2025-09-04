<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="shortcut icon" href="../assets/img/logo_domx_sem_nome.png" type="image/x-icon">
    <style>
        .container {
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 14px 28px rgba(0,0,0,0.25), 0 10px 10px rgba(0,0,0,0.22);
            position: relative;
            overflow: hidden;
            width: 768px;
            max-width: 100%;
            min-height: 480px;
        }
        
        .form-container {
            position: absolute;
            top: 0;
            height: 100%;
            transition: all 0.6s ease-in-out;
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0 50px;
            text-align: center;
        }
        
        .sign-in-container {
            left: 0;
            width: 100%;
            z-index: 2;
            padding-top: 30px;
            position: relative;
        }
        
        .logo-container {
            position: absolute;
            top: 20px;
            left: 20px;
            z-index: 3;
        }
        
        .logo-container img {
            max-width: 100px;
            height: auto;
        }
        
        form {
            background-color: #FFFFFF;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            padding: 0 50px;
            height: 100%;
            text-align: center;
            width: 100%;
        }
        
        h1 {
            font-weight: bold;
            margin: 0;
            color: #333;
        }
        
        .input-group {
            position: relative;
            width: 100%;
            margin: 8px 0;
        }
        
        .input-group input {
            background-color: #eee;
            border: none;
            padding: 12px 40px 12px 15px;
            width: 100%;
            border-radius: 5px;
            box-sizing: border-box;
        }
        
        .toggle-password {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #666;
        }
        
        button {
            border-radius: 20px;
            border: 1px solidrgb(19, 48, 82);
            background-color:rgb(10, 49, 94);
            color: #FFFFFF;
            font-size: 12px;
            font-weight: bold;
            padding: 12px 45px;
            letter-spacing: 1px;
            text-transform: uppercase;
            transition: transform 80ms ease-in;
            cursor: pointer;
            margin-top: 10px;
        }
        
        button:active {
            transform: scale(0.95);
        }
        
        button:focus {
            outline: none;
        }
        
        a {
            color: #333;
            font-size: 14px;
            text-decoration: none;
            margin: 15px 0;
        }
        
        body {
            background:rgb(255, 255, 255);
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
            font-family: 'Montserrat', sans-serif;
            height: 100vh;
            margin: -20px 0 50px;
        }
    </style>
</head>
<body>
    <div class="container" id="container">
        <div class="form-container sign-in-container">
            <div class="logo-container">
                <img src="../assets/img/logo.png" alt="Logo">
            </div>
            <form action="../auth/login.php" method="POST" id="loginForm">
                <h1>Acesse sua conta</h1>
                <p>Entre para gerenciar seu sistema de automação residencial.</p>
                <div class="input-group">
                    <input type="text" name="username" placeholder="Usuário" required />
                </div>
                <div class="input-group">
                    <input type="password" name="password" id="password" placeholder="Senha" required />
                    <i class="fas fa-eye toggle-password" onclick="togglePassword('password')"></i>
                </div>
                <a href="../auth/esqueci_senha.php">Esqueceu sua senha?</a>
                <button type="submit">Entrar</button>
            </form>
        </div>
    </div>
    <script>
        function togglePassword(inputId) {
            const passwordInput = document.getElementById(inputId);
            const icon = event.currentTarget;
            
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
    </script>
</body>
</html>
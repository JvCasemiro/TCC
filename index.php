<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Automação Residencial - Login</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="style.css">
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
        
        input {
            background-color: #eee;
            border: none;
            padding: 12px 15px;
            margin: 8px 0;
            width: 100%;
            border-radius: 5px;
        }
        
        button {
            border-radius: 20px;
            border: 1px solid #084d9b;
            background-color: #084d9b;
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
            background: #f6f5f7;
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
            <form action="index.php" method="POST" id="loginForm">
                <h1>Bem-vindo</h1>
                <p>Faça login para acessar o sistema de automação residencial</p>
                <input type="text" name="username" placeholder="Usuário" required />
                <input type="password" name="password" placeholder="Senha" required />
                <a href="esqueci_senha.php">Esqueceu sua senha?</a>
                <button type="submit">Entrar</button>
            </form>
        </div>
    </div>
    <script src="script.js"></script>
</body>
</html>
<?php
session_start();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Erro de Login - DOMX</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
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
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }
        
        .error-container {
            background: rgba(255, 255, 255, 0.95);
            padding: 2rem;
            border-radius: 15px;
            text-align: center;
            max-width: 500px;
            width: 100%;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        }
        
        .error-icon {
            font-size: 4rem;
            color: #e74c3c;
            margin-bottom: 1.5rem;
        }
        
        h1 {
            color: #333;
            margin-bottom: 1rem;
        }
        
        p {
            color: #666;
            margin-bottom: 2rem;
            line-height: 1.6;
        }
        
        .btn {
            display: inline-block;
            background: #4a90e2;
            color: white;
            padding: 0.8rem 2rem;
            border-radius: 5px;
            text-decoration: none;
            font-weight: 500;
            transition: background 0.3s;
        }
        
        .btn:hover {
            background: #357abd;
        }
        
        .logo {
            margin-bottom: 1.5rem;
        }
        
        .logo img {
            height: 80px;
        }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="logo">
            <img src="../assets/img/logo.png" alt="DOMX Logo">
        </div>
        <div class="error-icon">
            <i class="fas fa-exclamation-triangle"></i>
        </div>
        <h1>Credenciais Inválidas</h1>
        <p>O nome de usuário ou senha informados estão incorretos. Por favor, tente novamente.</p>
        <a href="../pages/loginUser.php" class="btn">
            <i class="fas fa-arrow-left"></i> Voltar para o Login
        </a>
    </div>
</body>
</html>

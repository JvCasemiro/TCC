<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit;
}

$username = $_SESSION['username'];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sobre Nós</title>
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
            color: rgb(13, 42, 75);
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
        
        .back-btn {
            background: #4a90e2;
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            transition: background 0.3s;
            margin-right: 1rem;
        }
        
        .back-btn:hover {
            background: #357abd;
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
        
        .page-title {
            text-align: center;
            margin-bottom: 3rem;
            color: white;
        }
        
        .page-title h1 {
            font-size: 2.5rem;
            margin-bottom: 0.5rem;
        }
        
        .page-title p {
            font-size: 1.2rem;
            opacity: 0.9;
        }
        
        .banner-section {
            text-align: center;
            margin: 3rem 0;
            padding: 0;
        }

        .banner-image {
            width: 100%;
            max-width: 800px;
            height: auto;
            border-radius: 10px;
            margin: 0 auto;
            display: block;
        }

        .content-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            padding: 3rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
        }
        
        .company-section {
            margin-bottom: 3rem;
        }
        
        .company-section h2 {
            color: #4a90e2;
            font-size: 1.8rem;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .company-section p {
            color: #666;
            line-height: 1.8;
            font-size: 1.1rem;
            margin-bottom: 1rem;
        }
        
        .values-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            margin-top: 2rem;
        }
        
        .value-card {
            background: #f8f9fa;
            padding: 2rem;
            border-radius: 10px;
            text-align: center;
            border-left: 4px solid #4a90e2;
        }
        
        .value-card .icon {
            font-size: 2.5rem;
            color: #4a90e2;
            margin-bottom: 1rem;
        }
        
        .value-card h3 {
            color: #333;
            margin-bottom: 1rem;
            font-size: 1.3rem;
        }
        
        .value-card p {
            color: #666;
            line-height: 1.6;
        }
        
        .team-section {
            text-align: center;
        }
        
        .contact-info {
            background: #4a90e2;
            color: white;
            padding: 2rem;
            border-radius: 10px;
            margin-top: 2rem;
        }
        
        .contact-info h3 {
            margin-bottom: 1rem;
        }
        
        .contact-info p {
            margin-bottom: 0.5rem;
        }
        
        @media (max-width: 768px) {
            .container {
                padding: 0 1rem;
            }
            
            .content-card {
                padding: 2rem;
            }
            
            .values-grid {
                grid-template-columns: 1fr;
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
                <a href="menu.php" class="back-btn">
                    <i class="fas fa-arrow-left"></i> Voltar ao Menu
                </a>
                <span class="user-name">
                    <i class="fas fa-user"></i> <?php echo htmlspecialchars($username); ?>
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
        <div class="page-title">
            <h1>Sobre Nós</h1>
            <p>Conheça a DOMX e nossa missão em automação residencial</p>
        </div>

        <div class="content-card">
            <div class="company-section">
                <h2><i class="fas fa-building"></i> Nossa Empresa</h2>
                <p>
                    A DOMX é uma empresa inovadora especializada em soluções de automação residencial e Internet das Coisas (IoT). 
                    Fundada com o objetivo de tornar as casas mais inteligentes, seguras e eficientes, desenvolvemos tecnologias 
                    que conectam e controlam dispositivos domésticos de forma integrada e intuitiva.
                </p>
                <p>
                    Nossa plataforma permite que você controle luzes, temperatura, segurança e diversos outros aspectos da sua 
                    casa através de uma interface moderna e fácil de usar, proporcionando conforto, economia de energia e 
                    tranquilidade para toda a família.
                </p>
            </div>

            <div class="company-section">
                <h2><i class="fas fa-bullseye"></i> Nossa Missão</h2>
                <p>
                    Democratizar a automação residencial, oferecendo soluções tecnológicas acessíveis e de alta qualidade 
                    que melhorem a qualidade de vida das pessoas, promovendo sustentabilidade e eficiência energética 
                    em todos os lares.
                </p>
            </div>

            <div class="company-section">
                <h2><i class="fas fa-eye"></i> Nossa Visão</h2>
                <p>
                    Ser referência nacional em automação residencial, liderando a transformação digital dos lares brasileiros 
                    e contribuindo para um futuro mais conectado, sustentável e inteligente.
                </p>
            </div>

            <div class="company-section">
                <h2><i class="fas fa-heart"></i> Nossos Valores</h2>
                <div class="values-grid">
                    <div class="value-card">
                        <div class="icon">
                            <i class="fas fa-lightbulb"></i>
                        </div>
                        <h3>Inovação</h3>
                        <p>Buscamos constantemente novas tecnologias e soluções para oferecer o melhor em automação residencial.</p>
                    </div>
                    
                    <div class="value-card">
                        <div class="icon">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                        <h3>Segurança</h3>
                        <p>Priorizamos a proteção dos dados e a segurança de todos os dispositivos conectados em sua casa.</p>
                    </div>
                    
                    <div class="value-card">
                        <div class="icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <h3>Foco no Cliente</h3>
                        <p>Colocamos as necessidades e satisfação dos nossos clientes no centro de tudo que fazemos.</p>
                    </div>
                    
                    <div class="value-card">
                        <div class="icon">
                            <i class="fas fa-leaf"></i>
                        </div>
                        <h3>Sustentabilidade</h3>
                        <p>Desenvolvemos soluções que promovem o uso consciente de energia e recursos naturais.</p>
                    </div>
                </div>
            </div>

            <div class="company-section team-section">
                <h2><i class="fas fa-code"></i> Desenvolvimento</h2>
                <p>
                    Este sistema foi desenvolvido como projeto de conclusão de curso (TCC), demonstrando as mais modernas 
                    tecnologias em automação residencial e IoT. O projeto integra hardware e software para criar uma 
                    solução completa de casa inteligente.
                </p>
            </div>

            <div class="contact-info">
                <h3><i class="fas fa-envelope"></i> Entre em Contato</h3>
                <p><i class="fas fa-phone"></i> Telefone: (11) 9999-9999</p>
                <p><i class="fas fa-envelope"></i> E-mail: contato@domx.com.br</p>
                <p><i class="fas fa-map-marker-alt"></i> Endereço: São Paulo, SP - Brasil</p>
                <p><i class="fas fa-globe"></i> Website: www.domx.com.br</p>
            </div>
        </div>

        <div class="banner-section">
            <img src="../assets/img/banner_site.png" alt="Banner DOMX" class="banner-image">
        </div>
    </div>
</body>
</html>

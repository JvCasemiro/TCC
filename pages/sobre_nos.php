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
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #2563eb;
            --primary-dark: #1d4ed8;
            --secondary: #1e293b;
            --light: #f8fafc;
            --gray: #64748b;
            --dark: #0f172a;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', 'Segoe UI', sans-serif;
        }
        
        body {
            background-color: var(--light);
            color: var(--secondary);
            line-height: 1.6;
        }
        
        .header {
            background: white;
            padding: 1rem 5%;
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.05);
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 1000;
        }
        
        .logo {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary);
        }
        
        .logo img {
            height: 40px;
            width: auto;
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
            width: 100%;
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
        
        /* ===== Base Styles ===== */
        .container {
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 2rem;
        }
        
        .section-header {
            text-align: center;
            margin-bottom: 4rem;
        }
        
        .section-header h2 {
            font-size: 2.5rem;
            color: var(--dark);
            margin: 1rem 0;
        }
        
        .section-subtitle {
            color: var(--primary);
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 2px;
            font-size: 0.9rem;
        }
        
        .divider {
            width: 80px;
            height: 4px;
            background: var(--primary);
            margin: 1.5rem auto;
            border-radius: 2px;
        }
        
        .text-primary {
            color: var(--primary);
        }
        
        .btn {
            display: inline-block;
            padding: 0.8rem 2rem;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 50px;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .btn:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(37, 99, 235, 0.3);
        }
        
        /* ===== Hero Section ===== */
        .hero {
            background: linear-gradient(135deg, #1e3a8a 0%, #1e40af 100%);
            color: white;
            padding: 8rem 0 6rem;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        
        .hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIxMDAlIiBoZWlnaHQ9IjEwMCUiPjxkZWZzPjxwYXR0ZXJuIGlkPSJwYXR0ZXJuIiB3aWR0aD0iNDAiIGhlaWdodD0iNDAiIHBhdHRlcm5Vbml0cz0idXNlclNwYWNlT25Vc2UiIHBhdHRlcm5UcmFuc2Zvcm09InJvdGF0ZSg0NSkiPjxyZWN0IHdpZHRoPSIyMCIgaGVpZ2h0PSIyMCIgZmlsbD0icmdiYSgyNTUsMjU1LDI1NSwwLjA1KSIvPjwvcGF0dGVybj48L2RlZnM+PHJlY3Qgd2lkdGg9IjEwMCUiIGhlaWdodD0iMTAwJSIgZmlsbD0idXJsKCNwYXR0ZXJuKSIvPjwvc3ZnPg==');
            opacity: 0.5;
        }
        
        .hero-content {
            position: relative;
            z-index: 1;
            max-width: 800px;
            margin: 0 auto;
        }
        
        .hero h1 {
            font-size: 3.5rem;
            margin-bottom: 1.5rem;
            line-height: 1.2;
        }
        
        .hero p {
            font-size: 1.25rem;
            opacity: 0.9;
            margin-bottom: 2rem;
        }
        
        /* ===== About Section ===== */
        .about-section {
            padding: 6rem 0;
            background: white;
        }
        
        .about-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 4rem;
            align-items: center;
        }
        
        .about-content {
            padding-right: 2rem;
        }
        
        .lead {
            font-size: 1.1rem;
            color: var(--gray);
            margin-bottom: 2.5rem;
            line-height: 1.8;
        }
        
        .features-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1.5rem;
            margin-top: 2.5rem;
        }
        
        .feature {
            display: flex;
            gap: 1.25rem;
            padding: 1.5rem;
            border-radius: 10px;
            background: #f8fafc;
            transition: all 0.3s ease;
        }
        
        .feature:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
        }
        
        .feature-icon {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: rgba(37, 99, 235, 0.1);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--primary);
            font-size: 1.25rem;
            flex-shrink: 0;
        }
        
        .feature h3 {
            margin-bottom: 0.5rem;
            color: var(--dark);
        }
        
        .feature p {
            color: var(--gray);
            font-size: 0.95rem;
            margin: 0;
        }
        
        .about-image {
            position: relative;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
        }
        
        .about-image img {
            width: 100%;
            height: auto;
            display: block;
            transition: transform 0.5s ease;
        }
        
        .about-image:hover img {
            transform: scale(1.03);
        }
        
        /* ===== Mission Section ===== */
        .mission-section {
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            padding: 6rem 0;
            text-align: center;
        }
        
        .mission-content {
            max-width: 800px;
            margin: 0 auto;
        }
        
        .mission-content h2 {
            font-size: 2.25rem;
            color: var(--dark);
            margin-bottom: 1.5rem;
        }
        
        .mission-content p {
            font-size: 1.1rem;
            color: var(--gray);
            line-height: 1.8;
        }
        
        /* ===== Values Section ===== */
        .values-section {
            padding: 6rem 0;
            background: white;
        }
        
        .values-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            margin-top: 3rem;
        }
        
        .value-card {
            background: #f8fafc;
            padding: 2.5rem 2rem;
            border-radius: 15px;
            text-align: center;
            transition: all 0.3s ease;
            border: 1px solid #e2e8f0;
        }
        
        .value-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.05);
            border-color: var(--primary);
        }
        
        .value-icon {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: rgba(37, 99, 235, 0.1);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--primary);
            font-size: 2rem;
            margin: 0 auto 1.5rem;
        }
        
        .value-card h3 {
            color: var(--dark);
            margin-bottom: 1rem;
            font-size: 1.5rem;
        }
        
        .value-card p {
            color: var(--gray);
            font-size: 1rem;
            margin: 0;
        }
        
        /* ===== Footer ===== */
        footer {
            background: var(--dark);
            color: white;
            padding: 4rem 0 2rem;
        }
        
        .footer-content {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 3rem;
            margin-bottom: 3rem;
        }
        
        .footer-logo {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
            display: inline-block;
        }
        
        .footer-about p {
            color: #94a3b8;
            margin-bottom: 1.5rem;
        }
        
        .social-links {
            display: flex;
            gap: 1rem;
        }
        
        .social-link {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.1);
            color: white;
            transition: all 0.3s ease;
        }
        
        .social-link:hover {
            background: var(--primary);
            transform: translateY(-3px);
        }
        
        .footer-links h3 {
            color: white;
            font-size: 1.25rem;
            margin-bottom: 1.5rem;
            position: relative;
            padding-bottom: 0.75rem;
        }
        
        .footer-links h3::after {
            content: '';
            position: absolute;
            left: 0;
            bottom: 0;
            width: 40px;
            height: 2px;
            background: var(--primary);
        }
        
        .footer-links ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .footer-links li {
            margin-bottom: 0.75rem;
        }
        
        .footer-links a {
            color: #94a3b8;
            text-decoration: none;
            transition: all 0.3s ease;
            display: block;
        }
        
        .footer-links a:hover {
            color: var(--primary);
            padding-left: 5px;
        }
        
        .footer-bottom {
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            padding-top: 2rem;
            text-align: center;
            color: #94a3b8;
            font-size: 0.9rem;
        }
        
        /* ===== Responsive Styles ===== */
        /* Responsive Design */
        @media (max-width: 1200px) {
            .hero h1 {
                font-size: 2.8rem;
            }
            .hero p {
                font-size: 1.1rem;
            }
            .about-grid {
                gap: 2rem;
            }
        }
        
        @media (max-width: 992px) {
            .hero {
                padding: 6rem 0 4rem;
            }
            
            .hero h1 {
                font-size: 2.5rem;
            }
            
            .about-grid {
                grid-template-columns: 1fr;
                gap: 3rem;
            }
            
            .about-content {
                padding-right: 0;
            }
            
            .about-image {
                order: -1;
                max-width: 80%;
                margin: 0 auto;
            }
        }
        
        @media (max-width: 768px) {
            .header {
                padding: 1rem 5%;
                flex-direction: column;
                gap: 1rem;
                text-align: center;
            }
            
            .user-info {
                width: 100%;
                justify-content: center;
                flex-wrap: wrap;
                gap: 0.5rem;
            }
            
            .domx-logo {
                position: static;
                transform: none;
                margin: 1rem 0;
                text-align: center;
            }
            
            .domx-logo img {
                height: 80px;
            }
            
            .hero h1 {
                font-size: 2.2rem;
            }
            
            .hero p {
                font-size: 1rem;
            }
            
            .values-grid {
                grid-template-columns: 1fr;
            }
            
            .feature {
                flex-direction: column;
                text-align: center;
                align-items: center;
            }
            
            .feature-icon {
                margin-bottom: 1rem;
            }
        }
        
        @media (max-width: 576px) {
            .container {
                padding: 0 1.25rem;
            }
            
            .hero {
                padding: 5rem 0 3rem;
            }
            
            .hero h1 {
                font-size: 2rem;
            }
            
            .btn {
                width: 100%;
                text-align: center;
                padding: 0.8rem 1.5rem;
            }
            
            .content-card {
                padding: 1.5rem;
            }
            
            .section-header h2 {
                font-size: 2rem;
            }
            
            .value-card {
                padding: 1.5rem 1rem;
            }
        }
        
        /* Ensure images are responsive */
        img {
            max-width: 100%;
            height: auto;
        }
        
        /* Prevent horizontal scrolling */
        html, body {
            overflow-x: hidden;
            width: 100%;
        }
            
            .about-grid {
                gap: 3rem;
            }
        }
        
        @media (max-width: 768px) {
            .container {
                padding: 0 1.5rem;
            }
            
            .hero {
                padding: 6rem 0 4rem;
            }
            
            .hero h1 {
                font-size: 2.5rem;
            }
            
            .about-grid {
                grid-template-columns: 1fr;
                gap: 3rem;
            }
            
            .about-content {
                padding-right: 0;
            }
            
            .features-grid {
                grid-template-columns: 1fr;
            }
            
            .values-grid {
                grid-template-columns: 1fr;
            }
            
            .section-header h2 {
                font-size: 2rem;
            }
        }
        
        @media (max-width: 480px) {
            .hero h1 {
                font-size: 2rem;
            }
            
            .hero p {
                font-size: 1.1rem;
            }
            
            .section-header h2 {
                font-size: 1.75rem;
            }
            
            .feature {
                flex-direction: column;
                text-align: center;
                align-items: center;
            }
            
            .feature-icon {
                margin-bottom: 1rem;
            }
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
        <a href="dashboard.php" class="logo">
            <img src="../assets/img/logo_domx_sem_nome.png" alt="DOMX">
            <span>DOMX</span>
        </a>
        <div class="user-info">
            <a href="dashboard.php" class="back-btn">
                <i class="fas fa-arrow-left"></i> Voltar
            </a>
            <span class="user-name"><?php echo htmlspecialchars($username); ?></span>
            <a href="../auth/logout.php" class="logout-btn">
                <i class="fas fa-sign-out-alt"></i> Sair
            </a>
        </div>
    </header>

    <main class="main-content">
        <!-- Hero Section -->
        <section class="hero">
            <div class="container">
                <div class="hero-content">
                    <h1>Transformando Casas em Lares Inteligentes</h1>
                    <p>Inovação, tecnologia e sofisticação para o seu lar</p>
                </div>
            </div>
        </section>

        <!-- About Section -->
        <section id="sobre" class="about-section">
            <div class="container">
                <div class="section-header">
                    <span class="section-subtitle">Sobre Nós</span>
                    <h2>Conheça a <span class="text-primary">DOMX</span></h2>
                    <div class="divider"></div>
                </div>
                
                <div class="about-grid">
                    <div class="about-content">
                        <p class="lead">Na DOMX, acreditamos que a tecnologia deve simplificar e enriquecer a vida das pessoas. Especializados em automação residencial, transformamos casas comuns em lares inteligentes, seguros e eficientes.</p>
                        
                        <div class="features-grid">
                            <div class="feature">
                                <div class="feature-icon">
                                    <i class="fas fa-shield-alt"></i>
                                </div>
                                <h3>Segurança</h3>
                                <p>Sistemas integrados para proteger o que é mais importante para você.</p>
                            </div>
                            
                            <div class="feature">
                                <div class="feature-icon">
                                    <i class="fas fa-lightbulb"></i>
                                </div>
                                <h3>Eficiência</h3>
                                <p>Soluções que economizam energia e reduzem custos.</p>
                            </div>
                            
                            <div class="feature">
                                <div class="feature-icon">
                                    <i class="fas fa-magic"></i>
                                </div>
                                <h3>Conforto</h3>
                                <p>Controle total do seu ambiente com um toque.</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="about-image">
                        <img src="../assets/img/casaInteligente.jpg" alt="Casa Inteligente" class="img-fluid">
                    </div>
                </div>
            </div>
        </section>
        
        <!-- Mission Section -->
        <section class="mission-section">
            <div class="container">
                <div class="mission-content">
                    <h2>Nossa Missão</h2>
                    <p>Simplificar a vida das pessoas através de soluções tecnológicas inovadoras, oferecendo produtos e serviços de automação residencial que proporcionam conforto, segurança e economia, sempre com excelência e comprometimento.</p>
                </div>
            </div>
        </section>
        
        <!-- Values Section -->
        <section class="values-section">
            <div class="container">
                <div class="section-header">
                    <span class="section-subtitle">Nossos Valores</span>
                    <h2>O que nos move</h2>
                    <div class="divider"></div>
                </div>
                
                <div class="values-grid">
                    <div class="value-card">
                        <div class="value-icon">
                            <i class="fas fa-lightbulb"></i>
                        </div>
                        <h3>Inovação</h3>
                        <p>Buscamos constantemente novas tecnologias para oferecer as melhores soluções.</p>
                    </div>
                    
                    <div class="value-card">
                        <div class="value-icon">
                            <i class="fas fa-handshake"></i>
                        </div>
                        <h3>Confiança</h3>
                        <p>Construímos relacionamentos duradouros baseados na confiança mútua.</p>
                    </div>
                    
                    <div class="value-card">
                        <div class="value-icon">
                            <i class="fas fa-star"></i>
                        </div>
                        <h3>Excelência</h3>
                        <p>Comprometimento com a qualidade em todos os nossos serviços.</p>
                    </div>
                </div>
            </div>
        </section>

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
        </div>

        <div class="banner-section">
            <img src="../assets/img/banner_site.png" alt="Banner DOMX" class="banner-image">
        </div>
    </main>
    
    <footer>
        <div class="container">
            <div class="footer-content">
                <div class="footer-about">
                    <a href="#" class="footer-logo">
                        <img src="../assets/img/logo_domx_sem_nome.png" alt="DOMX" style="height: 40px; margin-right: 10px; vertical-align: middle;">
                        DOMX
                    </a>
                    <p>Especialistas em automação residencial, transformando casas comuns em lares inteligentes e eficientes.</p>
                </div>
                
                <div class="footer-links">
                    <h3>Links Úteis</h3>
                    <ul>
                        <li><a href="#sobre"><i class="fas fa-chevron-right"></i> Sobre Nós</a></li>
                        <li><a href="dashboard.php"><i class="fas fa-chevron-right"></i> Produtos</a></li>
                    </ul>
                </div>
                
                <div id="nossos-servicos" class="footer-links">
                    <h3>Nossos Serviços</h3>
                    <ul>
                        <li><a href="#nossos-servicos"><i class="fas fa-chevron-right"></i> Automação Residencial</a></li>
                        <li><a href="#nossos-servicos"><i class="fas fa-chevron-right"></i> Segurança Eletrônica</a></li>
                        <li><a href="#nossos-servicos"><i class="fas fa-chevron-right"></i> Iluminação Inteligente</a></li>
                        <li><a href="#nossos-servicos"><i class="fas fa-chevron-right"></i> Climatização</a></li>
                        <li><a href="#nossos-servicos"><i class="fas fa-chevron-right"></i> Projetos Personalizados</a></li>
                    </ul>
                </div>
                
                <div id="contato" class="footer-contact">
                    <h3>Contato</h3>
                    <ul>
                        <li><i class="fas fa-map-marker-alt"></i> Rua Exemplo, 123 - Centro, Cidade - UF</li>
                        <li><i class="fas fa-phone"></i> (00) 1234-5678</li>
                        <li><i class="fas fa-envelope"></i> contato@domx.com.br</li>
                        <li><i class="fas fa-clock"></i> Seg-Sex: 9:00 - 18:00</li>
                    </ul>
                </div>
            </div>
            
            <div class="footer-bottom">
                <p>&copy; <?php echo date('Y'); ?> DOMX Automação Residencial. Todos os direitos reservados.</p>
            </div>
        </div>
    </footer>
    
    <script>
        // Adiciona classe de scroll ao header
        window.addEventListener('scroll', function() {
            const header = document.querySelector('.header');
            if (window.scrollY > 50) {
                header.classList.add('scrolled');
            } else {
                header.classList.remove('scrolled');
            }
        });
        
        // Adiciona animação suave ao rolar para as seções
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                
                const targetId = this.getAttribute('href');
                if (targetId === '#') return;
                
                const targetElement = document.querySelector(targetId);
                if (targetElement) {
                    window.scrollTo({
                        top: targetElement.offsetTop - 80,
                        behavior: 'smooth'
                    });
                }
            });
        });
    </script>
</body>
</html>

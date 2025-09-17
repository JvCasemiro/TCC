<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sobre Nós</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="shortcut icon" href="assets/img/logo_domx_sem_nome.png" type="image/x-icon">
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
            background: rgba(255, 255, 255, 0.95);
            padding: 1rem 5%;
            box-shadow: 0 2px 20px rgba(0, 0, 0, 0.08);
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 1000;
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        }
        
        .logo {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-size: 1.6rem;
            font-weight: 700;
            color: #1e3a8a;
            text-decoration: none;
            transition: all 0.3s ease;
        }
        
        .logo:hover {
            color: #1e40af;
        }
        
        .logo img {
            height: 45px;
            width: auto;
            transition: all 0.3s ease;
        }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .user-name {
            font-weight: 500;
            color: #374151;
        }
        
        .back-btn {
            background: #1e3a8a;
            color: white;
            border: none;
            padding: 0.6rem 1.5rem;
            border-radius: 25px;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.4s ease-in-out;
            position: relative;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(30, 58, 138, 0.3);
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-right: 1rem;
        }
        
        .back-btn:hover {
            background: #1e40af;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(30, 58, 138, 0.4);
        }
        
        .back-btn:active {
            transform: translateY(0);
            box-shadow: 0 2px 10px rgba(30, 58, 138, 0.3);
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
        
        .logout-btn {
            background: #1e3a8a;
            color: white;
            border: none;
            padding: 0.7rem 1.8rem;
            border-radius: 25px;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(30, 58, 138, 0.2);
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-weight: 500;
        }
        
        .logout-btn:hover {
            background: #1e40af;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(30, 58, 138, 0.3);
        }
        
        .logout-btn:active {
            transform: translateY(0);
            box-shadow: 0 2px 10px rgba(30, 58, 138, 0.2);
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
            color: #1e3a8a;
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
            border-left: 4px solid #1e3a8a;
        }
        
        .value-card .icon {
            font-size: 2.5rem;
            color: #1e3a8a;
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
            background: #1e3a8a;
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
            color: #1e3a8a;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 2px;
            font-size: 0.9rem;
        }
        
        .divider {
            width: 80px;
            height: 4px;
            background: #1e3a8a;
            margin: 1.5rem auto;
            border-radius: 2px;
        }
        
        .text-primary {
            color: #1e3a8a;
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
        
        .hero {
            background: linear-gradient(135deg, #1e3a8a 0%, #1e40af 100%);
            color: white;
            padding: 5rem 0 3.5rem;
            position: relative;
            overflow: hidden;
        }
        
        .hero-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 4rem;
            align-items: center;
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .hero-content {
            text-align: left;
        }
        
        .hero-image {
            display: flex;
            justify-content: center;
            align-items: center;
        }
        
        .hero-image img {
            width: 100%;
            max-width: 400px;
            height: auto;
            border-radius: 15px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
        }
        
        .hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 100" fill="%23ffffff" opacity="0.05"><polygon points="0,0 1000,0 1000,100 0,80"/></svg>');
            background-size: cover;
            pointer-events: none;
        }
        
        .hero-content {
            position: relative;
            z-index: 1;
            max-width: 800px;
            margin: 0 auto;
        }
        
        .hero h1 {
            font-size: 2.8rem;
            margin-bottom: 1rem;
            line-height: 1.2;
        }
        
        .hero p {
            font-size: 1.1rem;
            opacity: 0.9;
            margin-bottom: 1.5rem;
        }
        
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
            color: white;
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
        
        @media (max-width: 1200px) {
            .hero h1 {
                font-size: 2.4rem;
            }
            .hero p {
                font-size: 0.95rem;
            }
            .about-grid {
                gap: 2rem;
            }
        }
        
        @media (max-width: 992px) {
            .hero {
                padding: 4rem 0 3rem;
            }
            
            .hero h1 {
                font-size: 2.1rem;
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
                font-size: 1.9rem;
            }
            
            .hero p {
                font-size: 0.95rem;
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
                padding: 3.5rem 0 2.5rem;
            }
            
            .hero h1 {
                font-size: 1.8rem;
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
                font-size: 1.8rem;
            }
            
            .value-card {
                padding: 1.5rem 1rem;
            }
        }
        
        img {
            max-width: 100%;
            height: auto;
        }
        
        html, body {
            overflow-x: hidden;
            width: 100%;
            scroll-behavior: smooth;
        }
        
        .scroll-progress {
            position: fixed;
            top: 0;
            left: 0;
            width: 0%;
            height: 4px;
            background: linear-gradient(90deg, #1e3a8a, #3b82f6);
            z-index: 9999;
            transition: width 0.1s ease;
        }
        
        .fade-in {
            opacity: 0;
            transform: translateY(30px);
            transition: all 0.8s ease;
        }
        
        .fade-in.visible {
            opacity: 1;
            transform: translateY(0);
        }
        
        .slide-left {
            opacity: 0;
            transform: translateX(-50px);
            transition: all 0.8s ease;
        }
        
        .slide-left.visible {
            opacity: 1;
            transform: translateX(0);
        }
        
        .slide-right {
            opacity: 0;
            transform: translateX(50px);
            transition: all 0.8s ease;
        }
        
        .slide-right.visible {
            opacity: 1;
            transform: translateX(0);
        }
        
        .scale-in {
            opacity: 0;
            transform: scale(0.8);
            transition: all 0.8s ease;
        }
        
        .scale-in.visible {
            opacity: 1;
            transform: scale(1);
        }
        
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        @keyframes slideInLeft {
            from {
                opacity: 0;
                transform: translateX(-50px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }
        
        @keyframes slideInRight {
            from {
                opacity: 0;
                transform: translateX(50px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }
        
        @keyframes scaleIn {
            from {
                opacity: 0;
                transform: scale(0.8);
            }
            to {
                opacity: 1;
                transform: scale(1);
            }
        }
        
        .parallax {
            transform: translateY(0);
            transition: transform 0.1s ease-out;
        }
        
        .header.scrolled {
            background: rgba(255, 255, 255, 0.98);
            box-shadow: 0 4px 30px rgba(0, 0, 0, 0.15);
            backdrop-filter: blur(15px);
        }
        
        .floating {
            animation: floating 3s ease-in-out infinite;
        }
        
        @keyframes floating {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }
        
        .bounce-in {
            opacity: 0;
            transform: scale(0.3);
            transition: all 0.6s cubic-bezier(0.68, -0.55, 0.265, 1.55);
        }
        
        .bounce-in.visible {
            opacity: 1;
            transform: scale(1);
        }
        
        .rotate-in {
            opacity: 0;
            transform: rotate(-180deg) scale(0.5);
            transition: all 0.8s ease;
        }
        
        .rotate-in.visible {
            opacity: 1;
            transform: rotate(0deg) scale(1);
        }
        
        @keyframes bounceIn {
            from {
                opacity: 0;
                transform: scale(0.3);
            }
            50% {
                opacity: 1;
                transform: scale(1.05);
            }
            to {
                opacity: 1;
                transform: scale(1);
            }
        }
        
        @keyframes rotateIn {
            from {
                opacity: 0;
                transform: rotate(-180deg) scale(0.5);
            }
            to {
                opacity: 1;
                transform: rotate(0deg) scale(1);
            }
        }
        
        .stagger-delay-1 { transition-delay: 0.1s; }
        .stagger-delay-2 { transition-delay: 0.2s; }
        .stagger-delay-3 { transition-delay: 0.3s; }
        .stagger-delay-4 { transition-delay: 0.4s; }
        
        .value-card {
            transition: all 0.4s ease, transform 0.8s ease;
        }
        
        .value-card:hover {
            transform: translateY(-10px) scale(1.02);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
        }
        
        .feature {
            transition: all 0.4s ease, transform 0.8s ease;
        }
        
        .feature:hover {
            transform: translateY(-8px) scale(1.02);
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
                padding: 4rem 0 3rem;
            }
            
            .hero h1 {
                font-size: 2.1rem;
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
                font-size: 1.8rem;
            }
        }
        
        @media (max-width: 480px) {
            .hero h1 {
                font-size: 1.8rem;
            }
            
            .hero p {
                font-size: 0.95rem;
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
    <div class="scroll-progress" id="scrollProgress"></div>
    <header class="header">
        <a href="dashboard.php" class="logo">
            <img src="assets/img/logo_domx_sem_nome.png" alt="DOMX">
            <span>DOMX</span>
        </a>
        <div class="user-info">
            <a href="pages/loginUser.php" class="logout-btn">
                <i class="fas fa-sign-out-alt"></i> Entrar
            </a>
        </div>
    </header>

    <main class="main-content">
        <section class="hero parallax">
            <div class="container">
                <div class="hero-grid">
                    <div class="hero-content">
                        <h1 class="fade-in">Transformando Casas em Lares Inteligentes</h1>
                        <p class="fade-in stagger-delay-1">Inovação, tecnologia e sofisticação para o seu lar</p>
                    </div>
                    <div class="hero-image">
                        <img src="assets/img/robo.png" alt="Sistema de Automação Residencial">
                    </div>
                </div>
            </div>
        </section>

        <section id="sobre" class="about-section fade-in">
            <div class="container">
                <div class="section-header">
                    <span class="section-subtitle fade-in">Sobre Nós</span>
                    <h2 class="fade-in stagger-delay-1">Conheça a <span class="text-primary">DOMX</span></h2>
                    <div class="divider scale-in stagger-delay-2"></div>
                </div>
                
                <div class="about-grid">
                    <div class="about-content">
                        <p class="lead slide-left">Na DOMX, acreditamos que a tecnologia deve simplificar e enriquecer a vida das pessoas. Especializados em automação residencial, transformamos casas comuns em lares inteligentes, seguros e eficientes.</p>
                        
                        <div class="features-grid">
                            <div class="feature bounce-in stagger-delay-1">
                                <div class="feature-icon">
                                    <i class="fas fa-shield-alt"></i>
                                </div>
                                <h3>Segurança</h3>
                                <p>Sistemas integrados para proteger o que é mais importante para você.</p>
                            </div>
                            
                            <div class="feature bounce-in stagger-delay-2">
                                <div class="feature-icon">
                                    <i class="fas fa-lightbulb"></i>
                                </div>
                                <h3>Eficiência</h3>
                                <p>Soluções que economizam energia e reduzem custos.</p>
                            </div>
                            
                            <div class="feature bounce-in stagger-delay-3">
                                <div class="feature-icon">
                                    <i class="fas fa-magic"></i>
                                </div>
                                <h3>Conforto</h3>
                                <p>Controle total do seu ambiente com um toque.</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="about-image slide-right">
                        <img src="assets/img/casaInteligente.jpg" alt="Casa Inteligente" class="img-fluid floating">
                    </div>
                </div>
            </div>
        </section>
        
        <section class="mission-section fade-in">
            <div class="container">
                <div class="mission-content">
                    <h2 class="scale-in">Nossa Missão</h2>
                    <p class="fade-in stagger-delay-1">Simplificar a vida das pessoas através de soluções tecnológicas inovadoras, oferecendo produtos e serviços de automação residencial que proporcionam conforto, segurança e economia, sempre com excelência e comprometimento.</p>
                </div>
            </div>
        </section>
        
        <section class="values-section fade-in">
            <div class="container">
                <div class="section-header">
                    <span class="section-subtitle fade-in">Nossos Valores</span>
                    <h2 class="fade-in stagger-delay-1">O que nos move</h2>
                    <div class="divider scale-in stagger-delay-2"></div>
                </div>
                
                <div class="values-grid">
                    <div class="value-card rotate-in stagger-delay-1">
                        <div class="value-icon">
                            <i class="fas fa-lightbulb"></i>
                        </div>
                        <h3>Inovação</h3>
                        <p>Buscamos constantemente novas tecnologias para oferecer as melhores soluções.</p>
                    </div>
                    
                    <div class="value-card rotate-in stagger-delay-2">
                        <div class="value-icon">
                            <i class="fas fa-handshake"></i>
                        </div>
                        <h3>Confiança</h3>
                        <p>Construímos relacionamentos duradouros baseados na confiança mútua.</p>
                    </div>
                    
                    <div class="value-card rotate-in stagger-delay-3">
                        <div class="value-icon">
                            <i class="fas fa-star"></i>
                        </div>
                        <h3>Excelência</h3>
                        <p>Comprometimento com a qualidade em todos os nossos serviços.</p>
                    </div>
                </div>
            </div>
        </section>

        <div class="content-card fade-in">
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

        <div class="banner-section fade-in">
            <img src="assets/img/banner_site.png" alt="Banner DOMX" class="banner-image scale-in">
        </div>
    </main>
    
    <footer>
        <div class="container">
            <div class="footer-content">
                <div class="footer-about">
                    <a href="#" class="footer-logo">
                        <img src="assets/img/logo_domx_branca.png" alt="DOMX" style="height: 40px; margin-right: 10px; vertical-align: middle;">DOMX
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
                        <li><a href="#nossos-servicos"><i class="fas fa-chevron-right"></i> Projetos Personalizados</a></li>
                    </ul>
                </div>
                
                <div id="contato" class="footer-contact">
                    <h3>Contato</h3>
                    <ul>
                        <li><i class="fas fa-phone"></i> (14) 91962216</li>
                        <li><i class="fas fa-envelope"></i> contato@domx.com.br</li>
                    </ul>
                </div>
            </div>
            
            <div class="footer-bottom">
                <p>&copy; <?php echo date('Y'); ?> DOMX Automação Residencial. Todos os direitos reservados.</p>
            </div>
        </div>
    </footer>
    
    <script>
        class ScrollEffects {
            constructor() {
                this.init();
                this.bindEvents();
            }

            init() {
                this.scrollProgress = document.getElementById('scrollProgress');
                this.header = document.querySelector('.header');
                this.parallaxElements = document.querySelectorAll('.parallax');
                this.animatedElements = document.querySelectorAll('.fade-in, .slide-left, .slide-right, .scale-in, .bounce-in, .rotate-in');
                
                this.checkElementsInView();
            }

            bindEvents() {
                let ticking = false;
                
                window.addEventListener('scroll', () => {
                    if (!ticking) {
                        requestAnimationFrame(() => {
                            this.handleScroll();
                            this.updateScrollProgress();
                            this.handleParallax();
                            this.checkElementsInView();
                            ticking = false;
                        });
                        ticking = true;
                    }
                });

                document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                    anchor.addEventListener('click', (e) => {
                        e.preventDefault();
                        const targetId = anchor.getAttribute('href');
                        if (targetId === '#') return;
                        
                        const targetElement = document.querySelector(targetId);
                        if (targetElement) {
                            const offsetTop = targetElement.offsetTop - 80;
                            window.scrollTo({
                                top: offsetTop,
                                behavior: 'smooth'
                            });
                        }
                    });
                });

                this.setupIntersectionObserver();
            }

            setupIntersectionObserver() {
                const observerOptions = {
                    threshold: 0.1,
                    rootMargin: '0px 0px -50px 0px'
                };

                this.observer = new IntersectionObserver((entries) => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting) {
                            this.animateElement(entry.target);
                        } else {
                            entry.target.classList.remove('visible');
                        }
                    });
                }, observerOptions);

                this.animatedElements.forEach(element => {
                    this.observer.observe(element);
                });
            }

            handleScroll() {
                const scrollY = window.scrollY;
                
                if (scrollY > 50) {
                    this.header.classList.add('scrolled');
                } else {
                    this.header.classList.remove('scrolled');
                }
            }

            updateScrollProgress() {
                const scrollTop = window.scrollY;
                const documentHeight = document.documentElement.scrollHeight - window.innerHeight;
                const scrollPercent = (scrollTop / documentHeight) * 100;
                
                if (this.scrollProgress) {
                    this.scrollProgress.style.width = `${Math.min(scrollPercent, 100)}%`;
                }
            }

            handleParallax() {
                const scrollY = window.scrollY;
                
                this.parallaxElements.forEach(element => {
                    const speed = 0.5;
                    const yPos = -(scrollY * speed);
                    element.style.transform = `translateY(${yPos}px)`;
                });
            }

            checkElementsInView() {
                this.animatedElements.forEach(element => {
                    if (this.isElementInViewport(element)) {
                        this.animateElement(element);
                    } else {
                        element.classList.remove('visible');
                    }
                });
            }

            isElementInViewport(element) {
                const rect = element.getBoundingClientRect();
                const windowHeight = window.innerHeight || document.documentElement.clientHeight;
                
                return (
                    rect.top <= windowHeight * 0.8 &&
                    rect.bottom >= windowHeight * 0.2
                );
            }

            animateElement(element) {
                if (!element.classList.contains('visible')) {
                    element.classList.add('visible');
                }
            }
        }

        const ScrollUtils = {
            scrollToTop() {
                window.scrollTo({
                    top: 0,
                    behavior: 'smooth'
                });
            },

            addScrollToTopButton() {
                const button = document.createElement('button');
                button.innerHTML = '<i class="fas fa-chevron-up"></i>';
                button.className = 'scroll-to-top';
                button.style.cssText = `
                    position: fixed;
                    bottom: 30px;
                    right: 30px;
                    width: 50px;
                    height: 50px;
                    border-radius: 50%;
                    background: #1e3a8a;
                    color: white;
                    border: none;
                    cursor: pointer;
                    opacity: 0;
                    visibility: hidden;
                    transition: all 0.3s ease;
                    z-index: 1000;
                    box-shadow: 0 4px 15px rgba(30, 58, 138, 0.3);
                `;

                button.addEventListener('click', this.scrollToTop);

                window.addEventListener('scroll', () => {
                    if (window.scrollY > 300) {
                        button.style.opacity = '1';
                        button.style.visibility = 'visible';
                    } else {
                        button.style.opacity = '0';
                        button.style.visibility = 'hidden';
                    }
                });

                button.addEventListener('mouseenter', () => {
                    button.style.transform = 'scale(1.1)';
                    button.style.background = '#1e40af';
                });

                button.addEventListener('mouseleave', () => {
                    button.style.transform = 'scale(1)';
                    button.style.background = '#1e3a8a';
                });

                document.body.appendChild(button);
            }
        };

        document.addEventListener('DOMContentLoaded', () => {
            new ScrollEffects();
            ScrollUtils.addScrollToTopButton();
            
            document.body.style.opacity = '0';
            document.body.style.transition = 'opacity 0.5s ease';
            
            setTimeout(() => {
                document.body.style.opacity = '1';
            }, 100);
        });

        window.addEventListener('resize', () => {
            const scrollEffects = new ScrollEffects();
        });
    </script>
</body>
</html>

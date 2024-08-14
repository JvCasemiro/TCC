<?php
include('config.php');

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $rememberMe = isset($_POST['rememberMe']);

    if (empty($username) || empty($password)) {
        $error = 'Por favor, preencha todos os campos.';
    } else {
        $stmt = $conn->prepare('SELECT * FROM users WHERE username = ? AND password = ?');
        
        if ($stmt === false) {
            die('Erro na preparação da consulta: ' . $conn->error);
        }
        
        $stmt->bind_param('ss', $username, $password);
        
        if (!$stmt->execute()) {
            die('Erro na execução da consulta: ' . $stmt->error);
        }
        
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {    
            if ($rememberMe) {
                setcookie('username', $username, time() + 86400, "/"); 
                setcookie('password', $password, time() + 86400, "/"); 
            } else {
                setcookie('username', '', time() - 3600, "/");
                setcookie('password', '', time() - 3600, "/");
            }
            
            header('Location: teste.html'); 
            exit();
        } else {
            $error = 'Usuário ou senha incorretos.';
        }
    }
}
?>



<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="shortcut icon" href="img/icon.png" type="image/x-icon">
    <link rel="stylesheet" href="style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
</head>
<body>
    <h1 id ="tituloForm">EJ TechHouse</h1>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
    <div class="container col-md-10 col-sm-6">
    <h2 id="tituloForm" class="mb-4">Login</h2>
    <?php if ($error): ?>
        <p class="error"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>
    <form action="index.php" method="post" onsubmit="return validateForm()">
    <div class="mb-3">
        <label id="tituloForm" for="username" class="form-label">Usuário:</label>
        <div class="input-group">
            <span class="input-group-text">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-person" viewBox="0 0 16 16">
                    <path d="M8 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6m2-3a2 2 0 1 1-4 0 2 2 0 0 1 4 0m4 8c0 1-1 1-1 1H3s-1 0-1-1 1-4 6-4 6 3 6 4m-1-.004c-.001-.246-.154-.986-.832-1.664C11.516 10.68 10.289 10 8 10s-3.516.68-4.168 1.332c-.678.678-.83 1.418-.832 1.664z"></path>
                </svg>
            </span>
            <input type="text" id="username" name="username" class="form-control" placeholder="Digite seu usuário" value="<?php echo isset($_COOKIE['username']) ? htmlspecialchars($_COOKIE['username']) : ''; ?>">
        </div>
    </div>
    <div class="mb-3">
        <label id="tituloForm" for="password" class="form-label">Senha:</label>
        <div class="input-group">
            <span class="input-group-text">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-lock" viewBox="0 0 16 16">
                    <path d="M8 1a2 2 0 0 1 2 2v4H6V3a2 2 0 0 1 2-2m3 6V3a3 3 0 0 0-6 0v4a2 2 0 0 0-2 2v5a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2M5 8h6a1 1 0 0 1 1 1v5a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V9a1 1 0 0 1 1-1"></path>
                </svg>
            </span>
            <input type="password" id="password" name="password" class="form-control" placeholder="Digite sua senha" value="<?php echo isset($_COOKIE['password']) ? htmlspecialchars($_COOKIE['password']) : ''; ?>">
        </div>
    </div>
    <div class="form-check mb-3">
        <input type="checkbox" class="form-check-input" id="rememberMe" name="rememberMe" <?php echo isset($_COOKIE['username']) ? 'checked' : ''; ?>>
        <label class="form-check-label" for="rememberMe">Lembrar senha</label>
    </div>
    <button type="submit" class="btn btn-primary mt-3">Entrar</button>
</form>
</div>
    <script src="script.js"></script>
</body>
</html>

<?php
include('config.php');

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

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
    <link rel="stylesheet" href="style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
</head>
<body>
    <h1 id ="tituloForm">EJ TechHouse</h1>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
    <div class="container col-md-4 col-sm-6">
        <h2 id ="tituloForm" class="mb-4">Login</h2>
        <?php if ($error): ?>
            <p class="error"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>
        <form action="index.php" method="post" onsubmit="return validateForm()">
            <div class="mb-3">
                <label id ="tituloForm" for="username" class="form-label">Usuário:</label>
                <input type="text" id="username" name="username" class="form-control">
            </div>
            <div class="mb-3">
                <label id ="tituloForm" for="password" class="form-label">Senha:</label>
                <input type="password" id="password" name="password" class="form-control">
            </div>
            <button type="submit" class="btn btn-primary mt-3">Entrar</button>
        </form>
    </div>
    <script src="script.js"></script>
</body>
</html>

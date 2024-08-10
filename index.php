<?php
// Incluir o arquivo de configuração
include('config.php');

// Variáveis para armazenar erros
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Verificar se os campos não estão vazios
    if (empty($username) || empty($password)) {
        $error = 'Por favor, preencha todos os campos.';
    } else {
        // Preparar e executar a consulta SQL
        $stmt = $conn->prepare('SELECT * FROM users WHERE username = ? AND password = ?');
        
        // Verificar se a preparação foi bem-sucedida
        if ($stmt === false) {
            die('Erro na preparação da consulta: ' . $conn->error);
        }
        
        $stmt->bind_param('ss', $username, $password);
        
        // Executar a consulta
        if (!$stmt->execute()) {
            die('Erro na execução da consulta: ' . $stmt->error);
        }
        
        $result = $stmt->get_result();

        // Verificar se a combinação usuário/senha é válida
        if ($result->num_rows > 0) {
            // Login bem-sucedido
            header('Location: teste.html'); // Redirecionar para outra página após login
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
     <!-- Bootstrap CSS -->
     <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">

</head>
<body>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
    <div class="container mt-5">
        <h2 class="mb-4">Login</h2>
        <?php if ($error): ?>
            <p class="error"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>
        <form action="index.php" method="post" onsubmit="return validateForm()">
                <div class="mb-3">
                    <label for="username" class="form-label">Usuário:</label>
                    <input type="text" id="username" name="username" class="form-control">
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Senha:</label>
                    <input type="password" id="password" name="password" class="form-control">
                </div>
                <button type="submit" class="btn btn-primary mt-3">Entrar</button>
        </form>
    </div>
    <script src="script.js"></script>
</body>
</html>

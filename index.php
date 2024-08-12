<?php
include('config.php');

$error = '';
$success = '';
$showForm = true; 
$user = null; 

if (isset($_GET['page']) && $_GET['page'] === 'management') {
    $showForm = false;
}

if (isset($_GET['edit'])) {
    $showForm = true;
    $id = intval($_GET['edit']);

    $stmt = $conn->prepare('SELECT id, username FROM users WHERE id = ?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['username']) && isset($_POST['password'])) {
        $username = $_POST['username'];
        $password = $_POST['password'];
        $id = isset($_POST['id']) ? intval($_POST['id']) : null; 

        if (empty($username) || empty($password)) {
            $error = 'Por favor, preencha todos os campos.';
        } else {
            if ($id) {
                $stmt = $conn->prepare('UPDATE users SET username = ?, password = ? WHERE id = ?');
                $stmt->bind_param('ssi', $username, $password, $id);
                
                if ($stmt->execute()) {
                    $success = 'Usuário atualizado com sucesso!';
                    header('Location: index.php?page=management');
                    exit;
                } else {
                    $error = 'Erro ao atualizar usuário: ' . $stmt->error;
                }
            } else {
                $stmt = $conn->prepare('INSERT INTO users (username, password) VALUES (?, ?)');
                $stmt->bind_param('ss', $username, $password);
                
                if ($stmt->execute()) {
                    $success = 'Cadastro realizado com sucesso!';
                } else {
                    $error = 'Erro ao cadastrar: ' . $stmt->error;
                }
            }
        }
    } elseif (isset($_POST['id'])) {
        $id = intval($_POST['id']);
        
        $stmt = $conn->prepare('DELETE FROM users WHERE id = ?');
        $stmt->bind_param('i', $id);
        
        if ($stmt->execute()) {
            header('Location: index.php?page=management');
            exit;
        } else {
            $error = 'Erro ao excluir o usuário: ' . $stmt->error;
        }
    }
}

function getUsers($conn) {
    $result = $conn->query('SELECT id, username FROM users');
    return $result->fetch_all(MYSQLI_ASSOC);
}

$users = getUsers($conn);
?>


<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
</head>
<body class="<?php echo !$showForm ? 'hide-bg' : ''; ?>">
    <h1 id="tituloForm">EJ TechHouse</h1>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
    
    <div class="container col-md-4 col-sm-6 <?php echo !$showForm ? 'hide-form' : ''; ?>">
        <h2 id="tituloForm" class="mb-4"><?php echo isset($user) ? 'Editar Usuário' : 'Cadastro'; ?></h2>
        <?php if ($error): ?>
            <p class="error"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>
        <?php if ($success): ?>
            <p class="success"><?php echo htmlspecialchars($success); ?></p>
            <button id="loginButton" class="btn btn-primary mt-3" onclick="window.location.href='index.php?page=management'">Ir para o Gerenciamento de Usuários</button>
            <button id="loginButton" class="btn btn-primary mt-3" onclick="window.location.href='app/index.php'">Ir para o Login</button>
        <?php else: ?>
            <form action="index.php<?php echo isset($user) ? '?edit=' . urlencode($user['id']) : ''; ?>" method="post">
                <?php if (isset($user)): ?>
                    <input type="hidden" name="id" value="<?php echo htmlspecialchars($user['id']); ?>">
                <?php endif; ?>
                <div class="mb-3">
                    <label id="tituloForm" for="username" class="form-label">Usuário:</label>
                    <input type="text" id="username" name="username" class="form-control" value="<?php echo isset($user) ? htmlspecialchars($user['username']) : ''; ?>">
                </div>
                <div class="mb-3">
                    <label id="tituloForm" for="password" class="form-label">Senha:</label>
                    <input type="password" id="password" name="password" class="form-control">
                </div>
                <button type="submit" class="btn btn-primary mt-3"><?php echo isset($user) ? 'Salvar' : 'Cadastrar'; ?></button>
            </form>
        <?php endif; ?>
    </div>

    <?php if (!$showForm): ?>
        <div class="container col-md-8 col-sm-12 mt-4">
            <h2 id="tituloForm" class="mb-4">Gerenciamento de Usuários</h2>
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Usuário</th>
                        <th>Ações</th> 
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($user['id']); ?></td>
                            <td><?php echo htmlspecialchars($user['username']); ?></td>
                            <td>
                            <button type="button" class="btn btn-warning" onclick="window.location.href='index.php?edit=<?php echo urlencode($user['id']); ?>'">Editar</button>
                                <form action="index.php" method="post" style="display:inline;">
                                    <input type="hidden" name="id" value="<?php echo htmlspecialchars($user['id']); ?>">
                                    <button type="submit" class="btn btn-danger">Excluir</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <button id="backButton" class="btn btn-secondary mt-3" onclick="window.location.href='index.php'">Voltar</button>
            <button id="loginButton" class="btn btn-secondary mt-3" onclick="window.location.href='app/index.php'">Ir para o Login</button>
        </div>
    <?php endif; ?>

    <script src="script.js"></script>
</body>
</html>

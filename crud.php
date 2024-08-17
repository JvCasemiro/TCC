<?php
include('config.php');

$error = '';
$success = '';

// Adicionar usuário
if (isset($_POST['add'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    if (empty($username) || empty($password)) {
        $error = 'Por favor, preencha todos os campos.';
    } else {
        $stmt = $conn->prepare('INSERT INTO users (username, password) VALUES (?, ?)');
        $stmt->bind_param('ss', $username, $password);
        
        if ($stmt->execute()) {
            $success = 'Usuário adicionado com sucesso!';
            
        } else {
            $error = 'Erro ao adicionar usuário: ' . $stmt->error;
        }
    }
}

// Editar usuário
if (isset($_POST['edit'])) {
    $id = $_POST['id'];
    $username = $_POST['username'];
    $password = $_POST['password'];

    if (empty($username) || empty($password)) {
        $error = 'Por favor, preencha todos os campos.';
    } else {
        $stmt = $conn->prepare('UPDATE users SET username = ?, password = ? WHERE id = ?');
        $stmt->bind_param('ssi', $username, $password, $id);
        
        if ($stmt->execute()) {
            $success = 'Usuário atualizado com sucesso!';
        } else {
            $error = 'Erro ao atualizar usuário: ' . $stmt->error;
        }
    }
}

// Excluir usuário
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];

    $stmt = $conn->prepare('DELETE FROM users WHERE id = ?');
    $stmt->bind_param('i', $id);
    
    if ($stmt->execute()) {
        $success = 'Usuário excluído com sucesso!';
        
    } else {
        $error = 'Erro ao excluir usuário: ' . $stmt->error;
    }
}

// Listar usuários
$result = $conn->query('SELECT * FROM users');
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciamento de Usuários</title>
    <link rel="stylesheet" href="style.css">
    <link rel="shortcut icon" href="img/icon.png" type="image/x-icon">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <div class="container">
        <h1 id="tituloForm" class="mt-5">Gerenciamento de Usuários</h1>
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <!-- Formulário para adicionar usuário -->
        <form method="post" class="mt-4">
            <h2 class="form-check">Adicionar Usuário</h2>
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
                            <input type="password" id="password" name="password" class="form-control" placeholder="Digite sua senha">
                            <span class="input-group-text"id="togglePassword"style="cursor: pointer;">
                                <i class="fa fa-eye" id="passwordIcon"></i>
                            </span>
                        </div>
                    </div>
            <button type="submit" name="add" class="btn btn-primary">Adicionar</button>
            <button type="submit" name="add" class="btn btn-secondary" onclick="window.location.href='index.php'">Voltar</button>
        </form>

        <!-- Tabela para listar usuários -->
        <h2 id="tituloForm" class="mt-5">Usuários</h2>
        <table class="table mt-3">
            <thead>
                <tr id="tituloForm">
                    <th>ID</th>
                    <th>Usuário</th>
                    <th>Senha</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td class="registro"><?php echo htmlspecialchars($row['id']); ?></td>
                        <td class="registro"><?php echo htmlspecialchars($row['username']); ?></td>
                        <td class="registro"><?php echo htmlspecialchars($row['password']); ?></td>
                        <td align = "center">
                            <a id="btnCrud" href="crud.php?edit=<?php echo $row['id']; ?>" class="btn btn-warning btn-sm">Editar</a>
                            <a id="btnCrud" href="crud.php?delete=<?php echo $row['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Tem certeza que deseja excluir?')">Excluir</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <!-- Formulário para editar usuário -->
        <?php if (isset($_GET['edit'])): ?>
            <?php
            $id = $_GET['edit'];
            $stmt = $conn->prepare('SELECT * FROM users WHERE id = ?');
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $user = $stmt->get_result()->fetch_assoc();
            ?>
            <form method="post" class="mt-5">
                <h2 id="tituloForm">Editar Usuário</h2>
                <input type="hidden" name="id" value="<?php echo htmlspecialchars($user['id']); ?>">
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
                            <input type="password" id="password" name="password" class="form-control" placeholder="Digite sua senha">
                            <span class="input-group-text" id="togglePassword" style="cursor: pointer;">
                                <i class="fa fa-eye" id="passwordIcon"></i>
                            </span>
                        </div>
                    </div>
                <button type="submit" name="edit" class="btn btn-primary">Atualizar</button>
            </form>
        <?php endif; ?>
    </div>
    <script src="script.js"></script>
</body>
</html>

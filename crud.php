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
    <title>CRUD de Usuários</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
</head>
<body>
    <div class="container">
        <h1 class="mt-5">CRUD de Usuários</h1>
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <!-- Formulário para adicionar usuário -->
        <form method="post" class="mt-4">
            <h2>Adicionar Usuário</h2>
            <div class="mb-3">
                <label for="username" class="form-label">Usuário</label>
                <input type="text" id="username" name="username" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Senha</label>
                <input type="password" id="password" name="password" class="form-control" required>
            </div>
            <button type="submit" name="add" class="btn btn-primary">Adicionar</button>
            <button type="submit" name="add" class="btn btn-secondary" onclick="window.location.href='index.php'">Voltar</button>
        </form>

        <!-- Tabela para listar usuários -->
        <h2 class="mt-5">Usuários</h2>
        <table class="table mt-3">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Usuário</th>
                    <th>Senha</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['id']); ?></td>
                        <td><?php echo htmlspecialchars($row['username']); ?></td>
                        <td><?php echo htmlspecialchars($row['password']); ?></td>
                        <td>
                            <a href="crud.php?edit=<?php echo $row['id']; ?>" class="btn btn-warning btn-sm">Editar</a>
                            <a href="crud.php?delete=<?php echo $row['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Tem certeza que deseja excluir?')">Excluir</a>
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
                <h2>Editar Usuário</h2>
                <input type="hidden" name="id" value="<?php echo htmlspecialchars($user['id']); ?>">
                <div class="mb-3">
                    <label for="username" class="form-label">Usuário</label>
                    <input type="text" id="username" name="username" class="form-control" value="<?php echo htmlspecialchars($user['username']); ?>" required>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Senha</label>
                    <input type="password" id="password" name="password" class="form-control" value="<?php echo htmlspecialchars($user['password']); ?>" required>
                </div>
                <button type="submit" name="edit" class="btn btn-primary">Atualizar</button>
            </form>
        <?php endif; ?>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
</body>
</html>

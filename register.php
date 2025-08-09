<?php
// Verificar se há saída antes do cabeçalho
if (headers_sent($filename, $linenum)) {
    die("Erro: Cabeçalhos já foram enviados em $filename na linha $linenum");
}

// Iniciar buffer de saída
if (ob_get_level() == 0) {
    ob_start();
}

// Configurar exibição de erros
error_reporting(E_ALL);
ini_set('display_errors', 0); // Desativar exibição de erros na tela
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../error_log.txt'); // Arquivo de log personalizado

// Iniciar a sessão
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Definir cabeçalho JSON imediatamente
header('Content-Type: application/json; charset=utf-8');

// Função para enviar resposta JSON e encerrar o script
function sendJsonResponse($success, $message, $redirect = null) {
    $response = ['success' => $success, 'message' => $message];
    if ($redirect !== null) {
        $response['redirect'] = $redirect;
    }
    
    // Limpar qualquer saída anterior
    while (ob_get_level() > 0) {
        ob_end_clean();
    }
    
    echo json_encode($response);
    exit;
}

// Log dos dados recebidos
error_log("Dados recebidos: " . print_r($_POST, true));

try {
    // Incluir o arquivo de configuração do banco de dados
    require_once 'config/database.php';
    
    // Verificar conexão com o banco de dados
    if (!isset($conn) || !($conn instanceof PDO)) {
        throw new Exception('Erro na conexão com o banco de dados');
    }
    
    // Verificar se a requisição é POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        sendJsonResponse(false, 'Método não permitido');
    }

    // Obter e validar os dados de entrada
    $nome_usuario = trim(filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING));
    $email = trim(filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL));
    $senha = $_POST['password'] ?? '';
    $confirmar_senha = $_POST['confirm_password'] ?? '';

    error_log("Dados recebidos - Usuário: $nome_usuario, Email: $email");

    // Validar entrada
    if (empty($nome_usuario) || empty($email) || empty($senha) || empty($confirmar_senha)) {
        throw new Exception('Todos os campos são obrigatórios');
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('E-mail inválido');
    }

    if (strlen($senha) < 8) {
        throw new Exception('A senha deve ter pelo menos 8 caracteres');
    }

    if ($senha !== $confirmar_senha) {
        throw new Exception('As senhas não coincidem');
    }

    // Verificar se o nome de usuário já existe
    $sql = "SELECT ID_Usuario FROM Usuarios WHERE Nome_Usuario = :nome_usuario";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':nome_usuario', $nome_usuario, PDO::PARAM_STR);
    
    if (!$stmt->execute()) {
        throw new Exception('Erro ao verificar usuário existente');
    }
    
    if ($stmt->rowCount() > 0) {
        throw new Exception('Nome de usuário já está em uso');
    }
    
    // Verificar se o e-mail já existe
    $sql = "SELECT ID_Usuario FROM Usuarios WHERE Email = :email";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':email', $email, PDO::PARAM_STR);
    
    if (!$stmt->execute()) {
        throw new Exception('Erro ao verificar e-mail existente');
    }
    
    if ($stmt->rowCount() > 0) {
        throw new Exception('E-mail já está em uso');
    }
    
    // Criptografar a senha
    $senha_hash = password_hash($senha, PASSWORD_DEFAULT);
    
    // Inserir novo usuário
    $sql = "INSERT INTO Usuarios (Nome_Usuario, Email, Senha, Data_Cadastro, Data_Atualizacao) 
            VALUES (:nome_usuario, :email, :senha, NOW(), NOW())";
    
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':nome_usuario', $nome_usuario, PDO::PARAM_STR);
    $stmt->bindParam(':email', $email, PDO::PARAM_STR);
    $stmt->bindParam(':senha', $senha_hash, PDO::PARAM_STR);
    
    if (!$stmt->execute()) {
        throw new Exception('Erro ao cadastrar usuário no banco de dados');
    }
    
    // Obter o ID do novo usuário
    $user_id = $conn->lastInsertId();
    
    // Logar o usuário
    $_SESSION['user_id'] = $user_id;
    $_SESSION['username'] = $nome_usuario;
    
    // Registrar o log de autenticação
    $ip = $_SERVER['REMOTE_ADDR'];
    $navegador = $_SERVER['HTTP_USER_AGENT'];
    
    $sql_log = "INSERT INTO Logs_Autenticacao (ID_Usuario, Tipo_Acao, Endereco_IP, Navegador) 
               VALUES (:user_id, 'registro', :ip, :navegador)";
    
    $stmt = $conn->prepare($sql_log);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->bindParam(':ip', $ip, PDO::PARAM_STR);
    $stmt->bindParam(':navegador', $navegador, PDO::PARAM_STR);
    
    if (!$stmt->execute()) {
        error_log("Aviso: Não foi possível registrar o log de autenticação");
    }
    
    // Enviar resposta de sucesso
    sendJsonResponse(true, 'Cadastro realizado com sucesso!', 'dashboard.php');
    
} catch(PDOException $e) {
    error_log("Erro PDO: " . $e->getMessage());
    sendJsonResponse(false, 'Erro no banco de dados. Por favor, tente novamente.');
} catch(Exception $e) {
    error_log("Erro: " . $e->getMessage());
    sendJsonResponse(false, $e->getMessage());
}
?>

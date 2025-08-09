<?php
// Configuração do banco de dados MySQL
$db_host = 'localhost';
$db_name = 'tcc';
$db_user = 'root';
$db_password = '';

try {
    // Conexão MySQL
    $conn = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8mb4", $db_user, $db_password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    // Try to create database if it doesn't exist
    try {
        $conn = new PDO("mysql:host=$db_host;charset=utf8mb4", $db_user, $db_password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $conn->exec("CREATE DATABASE IF NOT EXISTS $db_name CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        $conn->exec("USE $db_name");
    } catch(PDOException $e) {
        die("Erro na conexão: " . $e->getMessage());
    }
}

// Função para criar as tabelas se não existirem
function criarTabelas($conn) {
    try {
        // Create Usuarios table
        $sql_usuarios = "
        CREATE TABLE IF NOT EXISTS Usuarios (
            ID_Usuario INT AUTO_INCREMENT PRIMARY KEY,
            Nome_Usuario VARCHAR(50) NOT NULL UNIQUE,
            Email VARCHAR(100) NOT NULL UNIQUE,
            Senha VARCHAR(255) NOT NULL,
            Tipo_Usuario VARCHAR(20) DEFAULT 'user',
            Data_Cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            Data_Criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            Data_Atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            Ultimo_Acesso TIMESTAMP NULL,
            Ativo BOOLEAN DEFAULT TRUE,
            Token_Redefinicao VARCHAR(255) NULL,
            Token_Expiracao TIMESTAMP NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        $conn->exec($sql_usuarios);
        
        // Create Logs_Autenticacao table
        $sql_logs = "
        CREATE TABLE IF NOT EXISTS Logs_Autenticacao (
            ID_Log INT AUTO_INCREMENT PRIMARY KEY,
            ID_Usuario INT NULL,
            Tipo_Acao VARCHAR(50) NOT NULL,
            Endereco_IP VARCHAR(45) NULL,
            Navegador VARCHAR(255) NULL,
            Data_Hora TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (ID_Usuario) REFERENCES Usuarios(ID_Usuario) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        $conn->exec($sql_logs);
        
        // Check if admin user exists, if not create it
        $stmt = $conn->prepare("SELECT COUNT(*) FROM Usuarios WHERE Nome_Usuario = 'admin'");
        $stmt->execute();
        $count = $stmt->fetchColumn();
        
        if ($count == 0) {
            // Insert admin user with hashed password for 'admin123'
            $admin_password = password_hash('admin123', PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO Usuarios (Nome_Usuario, Email, Senha, Tipo_Usuario) VALUES (?, ?, ?, ?)");
            $stmt->execute(['admin', 'admin@exemplo.com', $admin_password, 'admin']);
        }
        
        return true;
    } catch(PDOException $e) {
        die("Erro ao criar tabelas: " . $e->getMessage());
    }
}

// Chamar a função para criar as tabelas
criarTabelas($conn);
?>
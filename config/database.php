<?php
if (headers_sent($filename, $linenum)) {
    die("Erro: Cabeçalhos já foram enviados em $filename na linha $linenum");
}

if (ob_get_level() == 0) {
    ob_start();
}

$db_host = 'localhost';
$db_name = 'tcc';
$db_user = 'root';
$db_password = '';

try {
    $conn = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8mb4", $db_user, $db_password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    try {
        $conn = new PDO("mysql:host=$db_host;charset=utf8mb4", $db_user, $db_password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $conn->exec("CREATE DATABASE IF NOT EXISTS $db_name CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        $conn->exec("USE $db_name");
    } catch(PDOException $e) {
        die("Erro na conexão: " . $e->getMessage());
    }
}

function criarTabelas($conn) {
    try {
        $sql_casas = "
        CREATE TABLE IF NOT EXISTS Casas (
            ID_Casa INT AUTO_INCREMENT PRIMARY KEY,
            Nome VARCHAR(100) NOT NULL,
            Endereco TEXT NOT NULL,
            Data_Criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            Data_Atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            Ativo BOOLEAN DEFAULT TRUE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        $sql_usuarios = "
        CREATE TABLE IF NOT EXISTS Usuarios (
            ID_Usuario INT AUTO_INCREMENT PRIMARY KEY,
            Nome_Usuario VARCHAR(50) NOT NULL UNIQUE,
            Email VARCHAR(100) NOT NULL UNIQUE,
            Senha VARCHAR(255) NOT NULL,
            Tipo_Usuario VARCHAR(20) DEFAULT 'user',
            ID_Casa INT NULL,
            Data_Cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            Data_Criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            Data_Atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            Ultimo_Acesso TIMESTAMP NULL,
            Ativo BOOLEAN DEFAULT TRUE,
            FOREIGN KEY (ID_Casa) REFERENCES Casas(ID_Casa) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        $sql_lampadas = "
        CREATE TABLE IF NOT EXISTS Lampadas (
            ID_Lampada INT AUTO_INCREMENT PRIMARY KEY,
            Nome VARCHAR(100) NOT NULL,
            Comodo VARCHAR(50) NOT NULL,
            Status VARCHAR(10) DEFAULT 'off',
            ID_Usuario INT,
            ID_Casa INT NOT NULL,
            Data_Criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            Data_Atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (ID_Usuario) REFERENCES Usuarios(ID_Usuario) ON DELETE SET NULL,
            FOREIGN KEY (ID_Casa) REFERENCES Casas(ID_Casa) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        $sql_placas = "
        CREATE TABLE IF NOT EXISTS Placas (
            ID_Placa INT AUTO_INCREMENT PRIMARY KEY,
            Numeracao VARCHAR(10) NOT NULL UNIQUE,
            Proprietario VARCHAR(100) NULL,
            Data_Cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            Ultimo_Acesso TIMESTAMP NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        $conn->exec($sql_lampadas);
        $conn->exec($sql_placas);
        
        // Verifica se já existem usuários no sistema
        $stmt = $conn->query("SELECT COUNT(*) as total FROM Usuarios");
        $userCount = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        // Se não houver usuários, cria o usuário mestre
        if ($userCount == 0) {
            // Cria um usuário admin padrão
            $admin_password = password_hash('Admin@123', PASSWORD_DEFAULT);
            $stmt = $conn->prepare("
                INSERT INTO Usuarios 
                (Nome_Usuario, Email, Senha, Tipo_Usuario, Data_Criacao, Data_Atualizacao, Ativo) 
                VALUES (?, ?, ?, 'admin', NOW(), NOW(), 1)
            ");
            $stmt->execute(['admin', 'admin@sistema.com', $admin_password]);
            
            // Log das credenciais iniciais (apenas para desenvolvimento)
            error_log("Usuário admin padrão criado:");
            error_log("Usuário: admin");
            error_log("Senha: Admin@123");
            
            // Cria uma casa padrão e associa o usuário admin a ela
            try {
                // Cria a casa padrão
                $stmt = $conn->prepare("
                    INSERT INTO Casas 
                    (Nome, Endereco, Data_Criacao, Data_Atualizacao, Ativo) 
                    VALUES (?, ?, NOW(), NOW(), 1)
                ");
                $casa_nome = 'Casa Principal';
                $casa_endereco = 'Endereço da Casa Principal';
                $stmt->execute([$casa_nome, $casa_endereco]);
                $casa_id = $conn->lastInsertId();
                
                // Atualiza o usuário admin para associá-lo à casa criada
                $stmt = $conn->prepare("
                    UPDATE Usuarios 
                    SET ID_Casa = ? 
                    WHERE Nome_Usuario = ?
                ");
                $stmt->execute([$casa_id, 'admin']);
                
                error_log("Casa padrão criada e associada ao usuário admin (ID: $casa_id)");
            } catch (Exception $e) {
                error_log("Erro ao criar casa padrão: " . $e->getMessage());
            }
        }
        
        return true;
    } catch(PDOException $e) {
        die("Erro ao criar tabelas: " . $e->getMessage());
    }
}

criarTabelas($conn);
?>
<?php
// Configuração do banco de dados
$db_host = 'localhost';  // Nome do servidor SQL
$db_name = 'TCC';                      // Nome do banco de dados
$db_user = 'root';                         // Vazio para autenticação do Windows
$db_password = '';                     // Vazio para autenticação do Windows

// String de conexão para SQL Server com autenticação do Windows
$connectionInfo = array(
    "Database" => $db_name,
    "CharacterSet" => "UTF-8"
);

try {
    // Conexão usando autenticação do Windows
    $conn = new PDO("sqlsrv:Server=$db_host;Database=$db_name", null, null);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("Erro na conexão: " . $e->getMessage());
}

// Função para criar as tabelas se não existirem
function criarTabelas($conn) {
    $sql = "
    IF NOT EXISTS (SELECT * FROM sysobjects WHERE name='Usuarios' AND xtype='U')
    BEGIN
        CREATE TABLE Usuarios (
            ID_Usuario INT IDENTITY(1,1) PRIMARY KEY,
            Nome_Usuario NVARCHAR(50) NOT NULL,
            Email NVARCHAR(100) NOT NULL,
            Senha NVARCHAR(255) NOT NULL,
            Data_Cadastro DATETIME DEFAULT GETDATE(),
            Data_Atualizacao DATETIME DEFAULT GETDATE(),
            Ultimo_Acesso DATETIME NULL,
            Ativo BIT DEFAULT 1,
            Token_Redefinicao NVARCHAR(255) NULL,
            Token_Expiracao DATETIME NULL
        );
        
        -- Adicionar índices
        CREATE UNIQUE INDEX IDX_Nome_Usuario ON Usuarios(Nome_Usuario);
        CREATE UNIQUE INDEX IDX_Email ON Usuarios(Email);
        
        -- Inserir usuário admin padrão (senha: admin123)
        INSERT INTO Usuarios (Nome_Usuario, Email, Senha)
        VALUES ('admin', 'admin@exemplo.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');
        
        PRINT 'Tabela Usuarios criada com sucesso.';
    END

    IF NOT EXISTS (SELECT * FROM sysobjects WHERE name='Logs_Autenticacao' AND xtype='U')
    BEGIN
        CREATE TABLE Logs_Autenticacao (
            ID_Log INT IDENTITY(1,1) PRIMARY KEY,
            ID_Usuario INT NULL,
            Tipo_Acao NVARCHAR(50) NOT NULL,
            Endereco_IP NVARCHAR(45) NULL,
            Navegador NVARCHAR(255) NULL,
            Data_Hora DATETIME DEFAULT GETDATE(),
            FOREIGN KEY (ID_Usuario) REFERENCES Usuarios(ID_Usuario) ON DELETE CASCADE
        );
        
        PRINT 'Tabela Logs_Autenticacao criada com sucesso.';
    END
    ";
    
    try {
        $conn->exec($sql);
        return true;
    } catch(PDOException $e) {
        die("Erro ao criar tabelas: " . $e->getMessage());
    }
}

// Chamar a função para criar as tabelas
criarTabelas($conn);
?>
<?php
$servername = "localhost"; // O endereço do servidor MySQL
$username = "root";        // O nome de usuário do MySQL
$password = "";            // A senha do MySQL
$dbname = "tcc"; // O nome do seu banco de dados

// Criar a conexão com o banco de dados
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar se a conexão foi bem-sucedida
if ($conn->connect_error) {
    die("Conexão falhou: " . $conn->connect_error);
}
?>

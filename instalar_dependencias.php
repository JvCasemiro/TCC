<?php
// Desativa limites de tempo e memória
set_time_limit(0);
ini_set('memory_limit', '-1');

// Função para exibir saída em tempo real
function executar_comando($cmd) {
    $output = [];
    $return_var = 0;
    
    // Executa o comando e captura a saída em tempo real
    $handle = popen("$cmd 2>&1", 'r');
    if ($handle) {
        while (!feof($handle)) {
            $buffer = fgets($handle);
            echo htmlspecialchars($buffer) . "<br>";
            flush();
            ob_flush();
        }
        $return_var = pclose($handle);
    }
    
    return $return_var === 0;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instalação de Dependências</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.6; }
        pre { 
            background: #f4f4f4; 
            padding: 15px; 
            border-radius: 5px; 
            overflow-x: auto;
            max-height: 500px;
            overflow-y: auto;
        }
        .btn {
            background-color: #4CAF50;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            margin: 10px 0;
            text-decoration: none;
            display: inline-block;
        }
        .btn:hover { background-color: #45a049; }
        .btn:disabled { background-color: #cccccc; }
        .success { color: green; }
        .error { color: red; }
    </style>
</head>
<body>
    <h1>Instalação de Dependências</h1>
    
    <?php
    if (isset($_POST['instalar'])) {
        echo "<div class='section'>";
        echo "<h2>Iniciando instalação...</h2>";
        echo "<pre>";
        
        // 1. Atualizar pacotes
        echo "<strong>1. Atualizando pacotes do sistema...</strong><br>";
        echo "Executando: sudo apt-get update<br>";
        $success = true;
        
        // 2. Instalar Tesseract OCR
        echo "<br><strong>2. Instalando Tesseract OCR...</strong><br>";
        if (executar_comando('which apt-get')) {
            $success = $success && executar_comando('sudo apt-get update');
            $success = $success && executar_comando('sudo apt-get install -y tesseract-ocr');
        } else {
            echo "<span class='error'>Sistema não suporta apt-get. Por favor, instale o Tesseract manualmente.</span><br>";
            $success = false;
        }
        
        // 3. Instalar OpenCV e Pytesseract
        echo "<br><strong>3. Instalando dependências do Python...</strong><br>";
        if (executar_comando('which pip')) {
            $success = $success && executar_comando('pip install opencv-python-headless pytesseract');
        } else {
            echo "<span class='error'>pip não encontrado. Instale o pip primeiro.</span><br>";
            $success = false;
        }
        
        echo "</pre>";
        
        if ($success) {
            echo "<p class='success'>✅ Instalação concluída com sucesso!</p>";
            echo "<p><a href='verificar_requisitos.php' class='btn'>Verificar instalação</a></p>";
        } else {
            echo "<p class='error'>❌ Ocorreram erros durante a instalação. Verifique as mensagens acima.</p>";
        }
        
        echo "</div>";
    } else {
    ?>
    <div class="section">
        <h2>Pré-requisitos</h2>
        <p>Este instalador irá configurar o ambiente para o sistema de detecção de placas.</p>
        <p>Serão instalados:</p>
        <ul>
            <li>Tesseract OCR (para reconhecimento de caracteres)</li>
            <li>OpenCV (para processamento de imagem)</li>
            <li>Pytesseract (interface Python para o Tesseract)</li>
        </ul>
        
        <p><strong>Nota:</strong> Você precisará de privilégios de superusuário (sudo) para instalar os pacotes.</p>
        
        <form method="post">
            <button type="submit" name="instalar" class="btn">Iniciar Instalação</button>
        </form>
    </div>
    <?php } ?>
    
    <div class="section">
        <h2>Verificar instalação manual</h2>
        <p>Se preferir instalar manualmente, execute estes comandos no terminal:</p>
        <pre>
# Atualizar pacotes
sudo apt-get update

# Instalar Tesseract OCR
sudo apt-get install -y tesseract-ocr

# Instalar dependências do Python
pip install opencv-python-headless pytesseract
        </pre>
    </div>
</body>
</html>

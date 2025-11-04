<?php
// Desativa limites de tempo e memória
set_time_limit(0);
ini_set('memory_limit', '-1');

// Função para exibir erros detalhados
function handleError($errno, $errstr, $errfile, $errline) {
    echo "<span style='color:red'>Erro: [$errno] $errstr em $errfile na linha $errline</span><br>";
    return true;
}
set_error_handler("handleError");

// Função para executar comandos de forma segura
function executar_comando($cmd) {
    $output = [];
    $return_var = 0;
    
    // Executa o comando e captura a saída
    @exec("$cmd 2>&1", $output, $return_var);
    
    // Se houver erro, adiciona uma mensagem
    if ($return_var !== 0) {
        array_unshift($output, "⚠️ O comando retornou código de erro: $return_var");
    }
    
    return implode("\n", $output);
}

// Cabeçalho HTML
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificação de Requisitos</title>
    <style>
        body { font-family: 'Courier New', monospace; margin: 20px; line-height: 1.6; }
        pre { background: #f4f4f4; padding: 15px; border-radius: 5px; overflow-x: auto; }
        .success { color: green; }
        .error { color: red; }
        .warning { color: orange; }
        .section { margin-top: 20px; padding: 10px; border-left: 4px solid #3498db; }
        h2 { color: #2c3e50; }
        .btn {
            background-color: #3498db;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            margin: 10px 0;
        }
        .btn:hover { background-color: #2980b9; }
    </style>
</head>
<body>
    <h1>Verificação de Requisitos do Sistema</h1>
    
    <div class="section">
        <h2>Informações do Servidor</h2>
        <pre><?php 
            echo "Sistema: " . php_uname() . "\n";
            echo "Servidor: " . $_SERVER['SERVER_SOFTWARE'] . "\n";
            echo "PHP: " . phpversion() . "\n";
            echo "Usuário: " . get_current_user() . "\n";
            echo "Diretório: " . __DIR__ . "\n";
            echo "Permissões: " . substr(sprintf('%o', fileperms('.')), -4) . "\n";
        ?></pre>
    </div>

    <div class="section">
        <h2>Verificação do Python</h2>
        <pre><?php
            $python_version = executar_comando('python3 --version 2>&1');
            echo "Python: " . (!empty($python_version) ? $python_version : '❌ Não encontrado') . "\n\n";
            
            echo "Verificando módulos Python...\n";
            echo "OpenCV: " . executar_comando('python3 -c "import cv2; print(\'✅ \' + cv2.__version__)" 2>&1') . "\n";
            echo "Pytesseract: " . executar_comando('python3 -c "import pytesseract; print(\'✅ \' + pytesseract.get_tesseract_version())" 2>&1') . "\n";
        ?></pre>
    </div>

    <div class="section">
        <h2>Verificação de Dispositivos</h2>
        <pre><?php
            echo "Câmeras: \n" . executar_comando('ls -la /dev/video* 2>&1') . "\n\n";
            echo "USB: \n" . executar_comando('lsusb 2>&1 || echo "lsusb não disponível"') . "\n\n";
            echo "Módulos de vídeo: \n" . executar_comando('lsmod | grep -i video 2>&1 || echo "Não foi possível verificar módulos de vídeo"');
        ?></pre>
    </div>

    <div class="section">
        <h2>Executar Script Python Completo</h2>
        <button class="btn" onclick="document.getElementById('python-output').style.display='block'; this.style.display='none';">
            Executar Verificação Completa
        </button>
        <pre id="python-output" style="display:none;">
<?php
// Executa o script Python e mostra a saída
if (file_exists(__DIR__ . '/verificar_requisitos.py')) {
    echo "Executando script Python...\n\n";
    echo htmlspecialchars(executar_comando('python3 ' . __DIR__ . '/verificar_requisitos.py 2>&1'));
} else {
    echo "❌ Arquivo verificar_requisitos.py não encontrado no diretório atual.";
}
?>
        </pre>
    </div>

    <div class="section">
        <h2>Permissões de Diretório</h2>
        <pre><?php
            echo "Proprietário: " . posix_getpwuid(fileowner('.'))['name'] . "\n";
            echo "Grupo: " . posix_getgrgid(filegroup('.'))['name'] . "\n";
            echo "Permissões: " . substr(sprintf('%o', fileperms('.')), -4) . "\n\n";
            
            echo "Conteúdo do diretório:\n";
            $files = scandir('.');
            foreach ($files as $file) {
                if ($file != '.' && $file != '..') {
                    $perms = fileperms($file);
                    echo sprintf("%s - %o - %s\n", 
                        $file,
                        $perms & 0777,
                        posix_getpwuid(fileowner($file))['name']
                    );
                }
            }
        ?></pre>
    </div>
</body>
</html>

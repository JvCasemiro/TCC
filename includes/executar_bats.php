<?php
// Função para executar arquivos .bat em segundo plano
function executarBat($arquivo) {
    $caminho = realpath(dirname(__FILE__) . '/..') . '/' . $arquivo;
    if (file_exists($caminho)) {
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            // Para Windows
            pclose(popen('start /B "" "' . $caminho . '"', 'r'));
        } else {
            // Para Linux/Unix (caso necessário)
            exec('nohup ' . $caminho . ' > /dev/null 2>&1 &');
        }
        return true;
    }
    return false;
}

// Lista de arquivos .bat a serem executados
$bats = [
    'start_arduino_daemon_silent.bat',
    'start_light_controller.bat'
];

// Executa cada arquivo .bat
foreach ($bats as $bat) {
    executarBat($bat);
}
?>

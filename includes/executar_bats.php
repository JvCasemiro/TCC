<?php
function logMessage($message) {
    $logFile = dirname(__DIR__) . '/arduino_daemon.log';
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($logFile, "[$timestamp] [BAT] $message" . PHP_EOL, FILE_APPEND);
}

function isProcessRunning($processName) {
    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
        $output = [];
        exec('tasklist /FI "IMAGENAME eq ' . $processName . '" 2>NUL', $output);
        return count($output) > 2; // If more than 2 lines, process is running
    }
    return false;
}

function executarBat($arquivo) {
    $caminho = realpath(dirname(__FILE__) . '/..') . '/' . $arquivo;
    $logMessage = "Tentando executar: $caminho";
    
    if (!file_exists($caminho)) {
        logMessage("ERRO: Arquivo não encontrado: $caminho");
        return false;
    }
    
    try {
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            // Check if the process is already running based on the batch file name
            $processName = 'python.exe'; // Python processes
            if (isProcessRunning($processName)) {
                logMessage("Processo $processName já está em execução");
                return true;
            }
            
            // Execute the batch file in the background
            $command = 'start /B "" "' . $caminho . '"';
            pclose(popen($command, 'r'));
            
            logMessage("Executado com sucesso: $command");
            return true;
        } else {
            exec('nohup ' . $caminho . ' > /dev/null 2>&1 &');
            logMessage("Executado com sucesso (Linux): $caminho");
            return true;
        }
    } catch (Exception $e) {
        logMessage("ERRO ao executar $caminho: " . $e->getMessage());
        return false;
    }
}

// Lista de arquivos batch para executar
$bats = [
    'start_arduino_daemon_silent.bat',
    'start_light_controller.bat'
];

logMessage("Iniciando execução dos scripts batch...");

// Executar cada arquivo batch
$results = [];
foreach ($bats as $bat) {
    $results[$bat] = executarBat($bat);
}

// Log dos resultados
foreach ($results as $bat => $success) {
    logMessage("$bat: " . ($success ? 'SUCESSO' : 'FALHA'));
}
?>

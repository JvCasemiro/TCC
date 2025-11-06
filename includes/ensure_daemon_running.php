<?php
/**
 * Verifica e inicia o daemon do Arduino se não estiver em execução
 * @return bool Retorna true se o daemon estiver rodando ou foi iniciado com sucesso
 */
function ensureDaemonRunning() {
    $baseDir = __DIR__ . '/..';
    $pidFile = $baseDir . '/arduino_daemon.pid';
    $vbsScript = $baseDir . '/start_arduino_daemon_auto.vbs';
    $logFile = $baseDir . '/arduino_daemon.log';
    
    // Função para adicionar mensagens ao log
    $logMessage = function($message) use ($logFile) {
        $timestamp = date('Y-m-d H:i:s');
        $logMessage = "[$timestamp] [PHP] $message" . PHP_EOL;
        file_put_contents($logFile, $logMessage, FILE_APPEND);
    };
    
    // Verificar se o arquivo PID existe e o processo está ativo
    if (file_exists($pidFile)) {
        $pid = (int)trim(file_get_contents($pidFile));
        
        if ($pid > 0) {
            // Verificar se o processo ainda está ativo
            $output = [];
            $command = "wmic process where (processid=$pid) get commandline,processid 2>NUL";
            exec($command, $output);
            
            // Verificar se a saída contém o comando do nosso daemon
            $isRunning = false;
            foreach ($output as $line) {
                if (stripos($line, 'arduino_daemon.py') !== false) {
                    $isRunning = true;
                    break;
                }
            }
            
            if ($isRunning) {
                $logMessage("Daemon já está em execução (PID: $pid)");
                return true;
            }
            
            // Se chegou aqui, o PID existe mas o processo não está mais ativo
            $logMessage("Removendo PID de um processo antigo: $pid");
            @unlink($pidFile);
        }
    }
    
    // Iniciar o daemon usando o script VBS
    if (file_exists($vbsScript)) {
        $logMessage("Iniciando o daemon...");
        
        // Executar o script VBS em segundo plano
        $command = 'wscript "' . $vbsScript . '"';
        $output = [];
        $returnVar = 0;
        
        exec($command, $output, $returnVar);
        
        // Verificar se o arquivo PID foi criado
        $maxAttempts = 20; // 2 segundos no total (100ms * 20)
        $attempts = 0;
        
        while ($attempts < $maxAttempts) {
            if (file_exists($pidFile) && filesize($pidFile) > 0) {
                $pid = (int)trim(file_get_contents($pidFile));
                if ($pid > 0) {
                    $logMessage("Daemon iniciado com sucesso (PID: $pid)");
                    return true;
                }
            }
            usleep(100000); // 100ms
            $attempts++;
        }
        
        $logMessage("Falha ao iniciar o daemon: tempo limite excedido");
        return false;
    }
    
    $logMessage("Erro: Script de inicialização não encontrado: $vbsScript");
    return false;
}

// Se este arquivo for executado diretamente, retornar JSON
if (basename(__FILE__) == basename($_SERVER['SCRIPT_FILENAME'])) {
    header('Content-Type: application/json');
    
    try {
        if (ensureDaemonRunning()) {
            echo json_encode([
                'success' => true, 
                'message' => 'Daemon está rodando',
                'timestamp' => date('Y-m-d H:i:s')
            ]);
        } else {
            http_response_code(500);
            echo json_encode([
                'success' => false, 
                'message' => 'Falha ao iniciar o daemon',
                'timestamp' => date('Y-m-d H:i:s')
            ]);
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false, 
            'message' => 'Erro: ' . $e->getMessage(),
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    }
}

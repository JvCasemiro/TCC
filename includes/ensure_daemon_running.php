<?php
/**
 * Garante que o daemon do Arduino está rodando
 * Inicia automaticamente se necessário
 */

function ensureDaemonRunning() {
    $baseDir = __DIR__ . '/..';
    $pidFile = $baseDir . '/arduino_daemon.pid';
    $vbsScript = $baseDir . '/start_arduino_daemon_auto.vbs';
    
    // Verificar se já está rodando
    if (file_exists($pidFile)) {
        $pid = (int)file_get_contents($pidFile);
        
        // Verificar se o processo realmente existe (Windows)
        exec("tasklist /FI \"PID eq $pid\" 2>NUL | find \"python\"", $output);
        if (!empty($output)) {
            return true; // Daemon está rodando
        }
        
        // PID existe mas processo não, limpar arquivo
        @unlink($pidFile);
    }
    
    // Daemon não está rodando, iniciar automaticamente
    if (file_exists($vbsScript)) {
        // Usar VBScript para iniciar sem janela
        exec("wscript \"$vbsScript\"", $output, $returnVar);
        
        // Aguardar daemon inicializar (máximo 5 segundos)
        $maxAttempts = 50;
        $attempts = 0;
        while ($attempts < $maxAttempts) {
            if (file_exists($pidFile)) {
                usleep(500000); // 0.5s adicional para garantir inicialização
                return true;
            }
            usleep(100000); // 0.1s
            $attempts++;
        }
        
        return false; // Falhou ao iniciar
    }
    
    return false;
}

// Se chamado diretamente, testar
if (basename(__FILE__) == basename($_SERVER['SCRIPT_FILENAME'])) {
    header('Content-Type: application/json');
    
    if (ensureDaemonRunning()) {
        echo json_encode(['success' => true, 'message' => 'Daemon está rodando']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Falha ao iniciar daemon']);
    }
}

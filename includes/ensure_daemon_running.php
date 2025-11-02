<?php
function ensureDaemonRunning() {
    $baseDir = __DIR__ . '/..';
    $pidFile = $baseDir . '/arduino_daemon.pid';
    $vbsScript = $baseDir . '/start_arduino_daemon_auto.vbs';
    
    if (file_exists($pidFile)) {
        $pid = (int)file_get_contents($pidFile);
        
        exec("tasklist /FI \"PID eq $pid\" 2>NUL | find \"python\"", $output);
        if (!empty($output)) {
            return true;
        }
        
        @unlink($pidFile);
    }
    
    if (file_exists($vbsScript)) {
        exec("wscript \"$vbsScript\"", $output, $returnVar);
        
        $maxAttempts = 50;
        $attempts = 0;
        while ($attempts < $maxAttempts) {
            if (file_exists($pidFile)) {
                usleep(500000);
                return true;
            }
            usleep(100000);
            $attempts++;
        }
        
        return false;
    }
    
    return false;
}

if (basename(__FILE__) == basename($_SERVER['SCRIPT_FILENAME'])) {
    header('Content-Type: application/json');
    
    if (ensureDaemonRunning()) {
        echo json_encode(['success' => true, 'message' => 'Daemon está rodando']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Falha ao iniciar daemon']);
    }
}

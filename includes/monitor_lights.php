<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

require_once __DIR__ . '/../config/database.php';

function getLightStatusFromFile($lightName) {
    $statusFile = __DIR__ . '/../light_status.txt';
    if (!file_exists($statusFile)) {
        return null;
    }
    
    $lines = file($statusFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, $lightName . ':') === 0) {
            $parts = explode(':', $line);
            if (count($parts) >= 3) {
                return [
                    'status' => trim($parts[1]),
                    'timestamp' => (int)trim($parts[2])
                ];
            }
        }
    }
    return null;
}

function formatUptime($seconds) {
    $hours = floor($seconds / 3600);
    $minutes = floor(($seconds % 3600) / 60);
    $seconds = $seconds % 60;
    
    $result = [];
    if ($hours > 0) $result[] = $hours . 'h';
    if ($minutes > 0 || !empty($result)) $result[] = $minutes . 'm';
    $result[] = $seconds . 's';
    
    return implode(' ', $result);
}

try {
    $conn = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8mb4", $db_user, $db_password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $stmt = $conn->query("SELECT ID_Lampada, Nome, Status FROM Lampadas");
    $lampadas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $totalLights = count($lampadas);
    $lightsOn = 0;
    $now = time();
    
    foreach ($lampadas as &$lampada) {
        $statusInfo = getLightStatusFromFile($lampada['Nome']);
        
        if ($statusInfo) {
            $lampada['last_status_change'] = date('Y-m-d H:i:s', $statusInfo['timestamp']);
            $lampada['uptime_seconds'] = $now - $statusInfo['timestamp'];
            $lampada['uptime_formatted'] = formatUptime($lampada['uptime_seconds']);
            
            if (strtolower($statusInfo['status']) === 'on') {
                $lightsOn++;
            }
        } else {
            $lampada['last_status_change'] = 'N/A';
            $lampada['uptime_seconds'] = 0;
            $lampada['uptime_formatted'] = 'N/A';
        }
    }
    
    $percentageOn = $totalLights > 0 ? round(($lightsOn / $totalLights) * 100) : 0;
    
    echo json_encode([
        'success' => true,
        'total_lights' => $totalLights,
        'lights_on' => $lightsOn,
        'percentage_on' => $percentageOn,
        'lights' => $lampadas,
        'last_updated' => date('Y-m-d H:i:s')
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao buscar informações das lâmpadas: ' . $e->getMessage()
    ]);
}
?>

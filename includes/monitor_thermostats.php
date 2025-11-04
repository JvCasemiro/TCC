<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

require_once __DIR__ . '/../config/database.php';

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
    
    $stmt = $conn->query("SELECT ID_Temperatura, Nome, Status FROM Temperaturas");
    $temperaturas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $totalThermostats = count($temperaturas);
    $thermostatsOn = 0;
    $now = time();
    
    foreach ($temperaturas as &$temperatura) {
        $temperatura['last_status_change'] = date('Y-m-d H:i:s', $temperatura['Timestamp']);
        $temperatura['uptime_seconds'] = $now - $temperatura['Timestamp'];
        $temperatura['uptime_formatted'] = formatUptime($temperatura['uptime_seconds']);
            
        if (strtolower($temperatura['Status']) === 'on') {
            $thermostatsOn++;
        } else {
            $temperatura['last_status_change'] = 'N/A';
            $temperatura['uptime_seconds'] = 0;
            $temperatura['uptime_formatted'] = 'N/A';
        }
    }
    
    $percentageOn = $totalThermostats > 0 ? round(($thermostatsOn / $totalThermostats) * 100) : 0;
    
    echo json_encode([
        'success' => true,
        'total_thermostats' => $totalThermostats,
        'thermostats_on' => $thermostatsOn,
        'percentage_on' => $percentageOn,
        'thermostats' => $temperaturas,
        'last_updated' => date('Y-m-d H:i:s')
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao buscar informações das termostatos: ' . $e->getMessage()
    ]);
}
?>

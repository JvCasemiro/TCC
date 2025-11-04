<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);

// Start output buffering to catch any unwanted output
ob_start();

// Set JSON header
header('Content-Type: application/json');

// Simple response function
function sendJsonResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    echo json_encode($data);
    exit;
}

try {
    // Check if ID is provided
    if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
        throw new Exception('ID inválido');
    }

    $zoneId = (int)$_GET['id'];
    
    // Include database connection
    $dbFile = __DIR__ . '/../config/database.php';
    if (!file_exists($dbFile)) {
        throw new Exception('Arquivo de configuração do banco de dados não encontrado: ' . $dbFile);
    }
    
    require_once $dbFile;
    
    // Check if $conn was created in database.php
    if (!isset($conn)) {
        throw new Exception('Conexão com o banco de dados não foi estabelecida');
    }
    
    // Set error mode to exception if not already set
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Prepare and execute the query using PDO
    $stmt = $conn->prepare("SELECT Nome FROM Temperaturas WHERE ID_Temperatura = :id");
    $stmt->bindParam(':id', $zoneId, PDO::PARAM_INT);
    
    $stmt->execute();
    
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($row) {
        $response = [
            'success' => true,
            'zoneName' => htmlspecialchars($row['Nome'], ENT_QUOTES, 'UTF-8')
        ];
    } else {
        throw new Exception('Zona não encontrada');
    }
    
    $stmt = null;
    
} catch (Exception $e) {
    $response = [
        'success' => false,
        'message' => $e->getMessage(),
        'debug' => [
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString()
        ]
    ];
}

// Close connection if it's still open
if (isset($conn)) {
    $conn = null;
}

// Clean any output buffers
while (ob_get_level() > 0) {
    ob_end_clean();
}

// Send the JSON response
header('Content-Type: application/json');
echo json_encode($response);
exit;
?>

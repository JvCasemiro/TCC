<?php
header('Content-Type: application/json');

if (!isset($_POST['filename']) || empty($_POST['filename'])) {
    echo json_encode(['success' => false, 'message' => 'Nome do arquivo não fornecido']);
    exit;
}
$baseDir = dirname(__DIR__);
$recordingsDir = $baseDir . '/gravacoes/';

$filename = basename($_POST['filename']);
$filepath = $recordingsDir . $filename;

if (!is_dir($recordingsDir)) {
    echo json_encode(['success' => false, 'message' => 'Diretório de gravações não encontrado']);
    exit;
}

$realFilePath = realpath($filepath);
$realRecordingsDir = realpath($recordingsDir) . DIRECTORY_SEPARATOR;

if (!$realFilePath || !file_exists($filepath) || strpos($realFilePath, $realRecordingsDir) !== 0) {
    echo json_encode([
        'success' => false, 
        'message' => 'Arquivo não encontrado ou acesso negado',
        'filepath' => $filepath,
        'realpath' => $realFilePath,
        'basedir' => $baseDir
    ]);
    exit;
}

if (@unlink($filepath)) {
    echo json_encode(['success' => true]);
} else {
    $error = error_get_last();
    echo json_encode([
        'success' => false, 
        'message' => 'Erro ao excluir o arquivo: ' . ($error['message'] ?? 'Erro desconhecido'),
        'filepath' => $filepath
    ]);
}
?>

<?php
header('Content-Type: application/json');

// Verifica se o nome do arquivo foi fornecido
if (!isset($_POST['filename']) || empty($_POST['filename'])) {
    echo json_encode(['success' => false, 'message' => 'Nome do arquivo não fornecido']);
    exit;
}

// Define o diretório base do projeto
$baseDir = dirname(__DIR__); // Volta um nível a partir do diretório atual (includes)
$recordingsDir = $baseDir . '/gravacoes/';

$filename = basename($_POST['filename']); // Previne directory traversal
$filepath = $recordingsDir . $filename;

// Verifica se o diretório de gravações existe
if (!is_dir($recordingsDir)) {
    echo json_encode(['success' => false, 'message' => 'Diretório de gravações não encontrado']);
    exit;
}

// Verifica se o arquivo existe e está dentro do diretório de gravações
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

// Tenta excluir o arquivo
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

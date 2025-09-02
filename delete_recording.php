<?php
header('Content-Type: application/json');

// Verifica se o nome do arquivo foi fornecido
if (!isset($_POST['filename']) || empty($_POST['filename'])) {
    echo json_encode(['success' => false, 'message' => 'Nome do arquivo não fornecido']);
    exit;
}

$filename = basename($_POST['filename']); // Previne directory traversal
$filepath = __DIR__ . '/gravacoes/' . $filename;

// Verifica se o arquivo existe e está dentro do diretório de gravações
if (!file_exists($filepath) || !str_starts_with(realpath($filepath), __DIR__ . '/gravacoes/')) {
    echo json_encode(['success' => false, 'message' => 'Arquivo não encontrado']);
    exit;
}

// Tenta excluir o arquivo
if (unlink($filepath)) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Erro ao excluir o arquivo']);
}
?>

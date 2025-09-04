<?php
header('Content-Type: application/json');

$baseDir = dirname(__DIR__);
$uploadDir = $baseDir . '/gravacoes/';

if (!file_exists($uploadDir)) {
    if (!mkdir($uploadDir, 0777, true)) {
        throw new Exception('Não foi possível criar o diretório de gravações.');
    }
}

try {
    if (!isset($_FILES['video'])) {
        throw new Exception('Nenhum arquivo de vídeo foi enviado.');
    }

    $file = $_FILES['video'];
    
    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('Erro no upload do arquivo: ' . $file['error']);
    }
    $fileName = uniqid('gravacao_') . '_' . date('Y-m-d_His') . '.webm';
    $filePath = $uploadDir . $fileName;

    if (!move_uploaded_file($file['tmp_name'], $filePath)) {
        throw new Exception('Falha ao salvar o arquivo. Verifique as permissões do diretório.');
    }
    chmod($filePath, 0644);

    echo json_encode([
        'success' => true,
        'message' => 'Gravação salva com sucesso!',
        'filepath' => $filePath,
        'file' => $fileName
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>

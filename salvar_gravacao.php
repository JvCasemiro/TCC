<?php
header('Content-Type: application/json');

// Verifica se o diretório de gravações existe, se não, cria
$uploadDir = __DIR__ . '/gravacoes/';
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

try {
    // Verifica se o arquivo foi enviado corretamente
    if (!isset($_FILES['video'])) {
        throw new Exception('Nenhum arquivo de vídeo foi enviado.');
    }

    $file = $_FILES['video'];
    
    // Verifica erros de upload
    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('Erro no upload do arquivo: ' . $file['error']);
    }

    // Gera um nome único para o arquivo
    $fileName = uniqid('gravacao_') . '_' . date('Y-m-d_His') . '.webm';
    $filePath = $uploadDir . $fileName;

    // Move o arquivo para o diretório de gravações
    if (!move_uploaded_file($file['tmp_name'], $filePath)) {
        throw new Exception('Falha ao salvar o arquivo.');
    }

    // Retorna sucesso
    echo json_encode([
        'success' => true,
        'message' => 'Gravação salva com sucesso!',
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

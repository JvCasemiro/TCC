<?php
header('Content-Type: application/json');

// Define o diretório base do projeto
$baseDir = dirname(__DIR__); // Volta um nível a partir do diretório atual (includes)
$uploadDir = $baseDir . '/gravacoes/';

// Garante que o diretório de gravações existe
if (!file_exists($uploadDir)) {
    if (!mkdir($uploadDir, 0777, true)) {
        throw new Exception('Não foi possível criar o diretório de gravações.');
    }
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
        throw new Exception('Falha ao salvar o arquivo. Verifique as permissões do diretório.');
    }

    // Define as permissões do arquivo (opcional, dependendo da sua configuração)
    chmod($filePath, 0644);

    // Retorna sucesso com o caminho do arquivo salvo
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

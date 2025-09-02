<?php
header('Content-Type: application/json');

// Define o diretório base do projeto
$baseDir = dirname(__DIR__); // Volta um nível a partir do diretório atual (includes)
$recordingsDir = $baseDir . '/gravacoes/';
$recordings = [];

// Verifica se o diretório existe
if (is_dir($recordingsDir)) {
    // Abre o diretório
    if ($dh = opendir($recordingsDir)) {
        // Lê os arquivos do diretório
        while (($file = readdir($dh)) !== false) {
            $filePath = $recordingsDir . $file;
            
            // Ignora diretórios e arquivos ocultos
            if ($file === '.' || $file === '..' || is_dir($filePath)) {
                continue;
            }
            
            // Obtém informações do arquivo
            $fileInfo = [
                'filename' => $file,
                'name' => $file,
                'size' => filesize($filePath),
                'modified' => filemtime($filePath),
                'path' => 'gravacoes/' . $file,
                'url' => '/TCC/gravacoes/' . $file
            ];
            
            $recordings[] = $fileInfo;
        }
        closedir($dh);
    }
} else {
    // Se o diretório não existir, retorna um array vazio
    echo json_encode([]);
    exit;
}

// Ordena as gravações por data de modificação (mais recentes primeiro)
usort($recordings, function($a, $b) {
    return $b['modified'] - $a['modified'];
});

echo json_encode($recordings);
?>

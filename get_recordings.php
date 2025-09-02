<?php
header('Content-Type: application/json');

$recordingsDir = __DIR__ . '/gravacoes/';
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
                'name' => $file,
                'size' => filesize($filePath),
                'modified' => filemtime($filePath),
                'path' => 'gravacoes/' . $file
            ];
            
            $recordings[] = $fileInfo;
        }
        closedir($dh);
    }
}

// Ordena as gravações por data de modificação (mais recentes primeiro)
usort($recordings, function($a, $b) {
    return $b['modified'] - $a['modified'];
});

echo json_encode($recordings);
?>

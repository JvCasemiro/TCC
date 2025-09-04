<?php
header('Content-Type: application/json');

$baseDir = dirname(__DIR__);
$recordingsDir = $baseDir . '/gravacoes/';
$recordings = [];
if (is_dir($recordingsDir)) {
    if ($dh = opendir($recordingsDir)) {
        while (($file = readdir($dh)) !== false) {
            $filePath = $recordingsDir . $file;
            
            if ($file === '.' || $file === '..' || is_dir($filePath)) {
                continue;
            }
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
    echo json_encode([]);
    exit;
}
usort($recordings, function($a, $b) {
    return $b['modified'] - $a['modified'];
});

echo json_encode($recordings);
?>

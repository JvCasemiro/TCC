<?php
function executarBat($arquivo) {
    $caminho = realpath(dirname(__FILE__) . '/..') . '/' . $arquivo;
    if (file_exists($caminho)) {
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            pclose(popen('start /B "" "' . $caminho . '"', 'r'));
        } else {
            exec('nohup ' . $caminho . ' > /dev/null 2>&1 &');
        }
        return true;
    }
    return false;
}

$bats = [
    'start_arduino_daemon_silent.bat',
    'start_light_controller.bat'
];
foreach ($bats as $bat) {
    executarBat($bat);
}
?>

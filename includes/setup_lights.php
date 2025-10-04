<?php
require_once __DIR__ . '/../config/database.php';

$stmt = $conn->query("SELECT COUNT(*) as total FROM Lampadas");
$result = $stmt->fetch();

if ($result['total'] == 0) {
    $lampadas = [
        ['Lâmpada 1', 'Sala'],
        ['Lâmpada 2', 'Quarto 1'],
        ['Lâmpada 3', 'Quarto 2'],
        ['Lâmpada 4', 'Cozinha'],
        ['Lâmpada 5', 'Banheiro 1'],
        ['Lâmpada 6', 'Banheiro 2'],
        ['Lâmpada 7', 'Área de Serviço'],
        ['Lâmpada 8', 'Garagem'],
        ['Lâmpada 9', 'Sala de Estar'],
        ['Lâmpada 10', 'Escritório'],
        ['Lâmpada 11', 'Varanda'],
        ['Lâmpada 12', 'Jardim']
    ];
    
    $stmt = $conn->prepare("INSERT INTO Lampadas (Nome, Comodo, Status, Brilho) VALUES (?, ?, 'off', 50)");
    
    foreach ($lampadas as $lampada) {
        $stmt->execute([$lampada[0], $lampada[1]]);
    }
    
    echo "Foram cadastradas " . count($lampadas) . " lâmpadas no banco de dados.\n";
} else {
    echo "Já existem lâmpadas cadastradas no banco de dados.\n";
}
?>

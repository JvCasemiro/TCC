<?php
header('Content-Type: application/json');

// Simulação de controle da piscina
$action = isset($_GET['action']) ? $_GET['action'] : '';
$response = ['success' => false, 'message' => ''];

// Aqui você pode adicionar a lógica real para controlar a piscina
// Por exemplo, enviar comandos para um Arduino ou outro dispositivo

if ($action === 'on') {
    // Lógica para ligar a piscina
    $response['success'] = true;
    $response['message'] = 'Piscina ligada com sucesso!';
} elseif ($action === 'off') {
    // Lógica para desligar a piscina
    $response['success'] = true;
    $response['message'] = 'Piscina desligada com sucesso!';
} else {
    $response['message'] = 'Ação inválida';
    http_response_code(400);
}

echo json_encode($response);
?>

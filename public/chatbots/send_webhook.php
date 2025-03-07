<?php
// Configurar headers para evitar problemas de CORS
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

// URL del webhook de Claris Connect
$webhook_url = 'https://jnrreb.apps.connect.claris.com/api/webhook/v1/recepciioncontacto/catch';

// Solo permitir solicitudes POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Obtener el contenido JSON enviado desde el frontend
    $json_data = file_get_contents('php://input');
    
    // Verificar que se recibieron datos
    if (!empty($json_data)) {
        // Inicializar cURL
        $ch = curl_init($webhook_url);
        
        // Configurar opciones de cURL
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json_data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        // Aumentar el timeout a 30 segundos para darle más tiempo a Claris Connect para responder
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        
        // Implementar timeout de conexión de 10 segundos
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        
        // Configurar headers
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Content-Length: ' . strlen($json_data)
        ));
        
        // Ejecutar la solicitud cURL pero no esperar por la respuesta
        // Esto permite que podamos responder al usuario inmediatamente
        curl_setopt($ch, CURLOPT_TIMEOUT, 1); // Timeout muy corto (1 segundo)
        
        // Ejecutar la solicitud cURL
        $response = curl_exec($ch);
        $curl_error = curl_error($ch);
        
        // Cerrar sesión cURL
        curl_close($ch);
        
        // Responder de inmediato al usuario sin esperar la respuesta completa
        http_response_code(200);
        echo json_encode(['success' => true, 'message' => 'El mensaje ha sido enviado correctamente']);
        
        // Registrar cualquier error en un archivo de registro (opcional)
        if (!empty($curl_error)) {
            error_log("Error al enviar al webhook: " . $curl_error);
        }
    } else {
        // No se recibieron datos
        http_response_code(400);
        echo json_encode(['error' => 'No se recibieron datos']);
    }
} else {
    // Método no permitido
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido']);
}
?>
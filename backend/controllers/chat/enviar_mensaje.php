<?php
// Inhabilita la salida de errores en pantalla
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Cabecera JSON antes de cualquier salida
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/cors.php';

// Iniciar sesión de forma segura
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Log para depuración
error_log("enviar_mensaje.php - Iniciado");
error_log("POST: " . print_r($_POST, true));

// Verificar autenticación
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'No autorizado']);
    exit;
}

// Verificar parámetros
if (!isset($_POST['conversacion_id']) || empty($_POST['conversacion_id']) || 
    !isset($_POST['mensaje']) || empty($_POST['mensaje'])) {
    echo json_encode(['success' => false, 'error' => 'Parámetros incompletos']);
    exit;
}

$usuario_id = $_SESSION['user_id'];
$conversacion_id = $_POST['conversacion_id'];
$mensaje = $_POST['mensaje'];

try {
    $db = new Database();
    $conn = $db->connect();
    
    // Verificar que el usuario pertenece a esta conversación
    $sql_check = "SELECT COUNT(*) FROM chat_participantes 
                  WHERE usuario_id = :usuario_id 
                  AND conversacion_id = :conversacion_id";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->bindParam(':usuario_id', $usuario_id, PDO::PARAM_INT);
    $stmt_check->bindParam(':conversacion_id', $conversacion_id, PDO::PARAM_INT);
    $stmt_check->execute();
    
    if ($stmt_check->fetchColumn() == 0) {
        echo json_encode(['success' => false, 'error' => 'No puedes enviar mensajes a esta conversación']);
        exit;
    }
    
    // Insertar mensaje
    $sql_insert = "INSERT INTO chat_mensajes (conversacion_id, remitente_id, mensaje) 
                   VALUES (:conversacion_id, :remitente_id, :mensaje)";
    $stmt_insert = $conn->prepare($sql_insert);
    $stmt_insert->bindParam(':conversacion_id', $conversacion_id, PDO::PARAM_INT);
    $stmt_insert->bindParam(':remitente_id', $usuario_id, PDO::PARAM_INT);
    $stmt_insert->bindParam(':mensaje', $mensaje);
    $stmt_insert->execute();
    
    echo json_encode(['success' => true, 'mensaje_id' => $conn->lastInsertId()]);
} catch (PDOException $e) {
    error_log("Error en enviar_mensaje.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Error en la base de datos']);
} catch (Exception $e) {
    error_log("Error general en enviar_mensaje.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Error del servidor']);
}
exit;
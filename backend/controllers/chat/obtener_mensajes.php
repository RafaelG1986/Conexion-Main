<?php
// Inhabilita la salida de errores en pantalla
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Cabecera JSON antes de cualquier otra salida
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/cors.php';

// Iniciar sesión de forma segura
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Log para depuración
error_log("obtener_mensajes.php - Iniciado");
error_log("GET: " . print_r($_GET, true));

// Verificar autenticación
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'No autorizado']);
    exit;
}

// Verificar parámetro conversación
if (!isset($_GET['conversacion_id']) || empty($_GET['conversacion_id'])) {
    echo json_encode(['success' => false, 'error' => 'ID de conversación requerido']);
    exit;
}

$usuario_id = $_SESSION['user_id'];
$conversacion_id = $_GET['conversacion_id'];

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
        echo json_encode(['success' => false, 'error' => 'No tienes acceso a esta conversación']);
        exit;
    }
    
    // Obtener información de la conversación
    $sql_conv = "SELECT c.id, c.titulo, c.tipo
                 FROM chat_conversaciones c
                 WHERE c.id = :conversacion_id";
    $stmt_conv = $conn->prepare($sql_conv);
    $stmt_conv->bindParam(':conversacion_id', $conversacion_id, PDO::PARAM_INT);
    $stmt_conv->execute();
    $conversacion = $stmt_conv->fetch(PDO::FETCH_ASSOC);
    
    // Obtener mensajes
    $sql_msgs = "SELECT m.id, m.conversacion_id, m.remitente_id, u.nombre as nombre_remitente, 
                        u.username, m.mensaje, m.leido, m.created_at
                 FROM chat_mensajes m
                 LEFT JOIN usuarios u ON m.remitente_id = u.id
                 WHERE m.conversacion_id = :conversacion_id
                 ORDER BY m.created_at ASC";
    $stmt_msgs = $conn->prepare($sql_msgs);
    $stmt_msgs->bindParam(':conversacion_id', $conversacion_id, PDO::PARAM_INT);
    $stmt_msgs->execute();
    $mensajes = $stmt_msgs->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true, 
        'conversacion' => $conversacion,
        'mensajes' => $mensajes
    ]);
} catch (PDOException $e) {
    error_log("Error en obtener_mensajes.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Error en la base de datos']);
} catch (Exception $e) {
    error_log("Error general en obtener_mensajes.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Error del servidor']);
}
exit;
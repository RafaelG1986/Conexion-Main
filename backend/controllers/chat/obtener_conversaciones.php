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
error_log("obtener_conversaciones.php - Iniciado");

// Verificar autenticación
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'No autorizado']);
    exit;
}

$usuario_id = $_SESSION['user_id'];

try {
    $db = new Database();
    $conn = $db->connect();
    
    // Verificar si hay conversaciones para este usuario
    $checkSql = "SELECT COUNT(*) FROM chat_participantes WHERE usuario_id = :uid";
    $checkStmt = $conn->prepare($checkSql);
    $checkStmt->bindParam(':uid', $usuario_id);
    $checkStmt->execute();
    error_log("Número de conversaciones: " . $checkStmt->fetchColumn());
    
    // Obtener conversaciones del usuario con sus datos
    $sql = "SELECT 
                c.id,
                c.titulo,
                c.tipo,
                c.created_at,
                (
                    SELECT mensaje 
                    FROM chat_mensajes 
                    WHERE conversacion_id = c.id 
                    ORDER BY created_at DESC 
                    LIMIT 1
                ) AS ultimo_mensaje,
                (
                    SELECT COUNT(*) 
                    FROM chat_mensajes 
                    WHERE conversacion_id = c.id 
                    AND remitente_id != :usuario_id
                    AND leido = 0
                ) AS mensajes_no_leidos
            FROM 
                chat_conversaciones c
            INNER JOIN 
                chat_participantes p ON p.conversacion_id = c.id
            WHERE 
                p.usuario_id = :usuario_id
            ORDER BY 
                (SELECT MAX(created_at) FROM chat_mensajes WHERE conversacion_id = c.id) DESC,
                c.created_at DESC";
                
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':usuario_id', $usuario_id, PDO::PARAM_INT);
    $stmt->execute();
    
    $conversaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode(['success' => true, 'conversaciones' => $conversaciones]);
} catch (PDOException $e) {
    error_log("Error en obtener_conversaciones.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Error en la base de datos']);
} catch (Exception $e) {
    error_log("Error general en obtener_conversaciones.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Error del servidor']);
}
exit;
<?php
// filepath: c:\xampp\htdocs\Conexion-Main\frontend\controllers\chat\marcar_leido.php

require_once __DIR__ . '/../../config/database.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'No autorizado']);
    exit;
}

$usuario_id = $_SESSION['user_id'];

// Verificar datos recibidos
if (!isset($_POST['conversacion_id'])) {
    echo json_encode(['success' => false, 'error' => 'ID de conversación no proporcionado']);
    exit;
}

$conversacion_id = $_POST['conversacion_id'];

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    // Verificar si el usuario es participante de la conversación
    $sql_check = "SELECT id FROM chat_participantes 
                  WHERE conversacion_id = :conversacion_id 
                  AND usuario_id = :usuario_id";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->bindParam(':conversacion_id', $conversacion_id);
    $stmt_check->bindParam(':usuario_id', $usuario_id);
    $stmt_check->execute();
    
    if ($stmt_check->rowCount() === 0) {
        echo json_encode(['success' => false, 'error' => 'No eres participante de esta conversación']);
        exit;
    }
    
    // Marcar como leídos todos los mensajes que no son del usuario
    $sql_update = "UPDATE chat_mensajes 
                   SET leido = 1 
                   WHERE conversacion_id = :conversacion_id 
                   AND remitente_id != :usuario_id 
                   AND leido = 0";
    $stmt_update = $conn->prepare($sql_update);
    $stmt_update->bindParam(':conversacion_id', $conversacion_id);
    $stmt_update->bindParam(':usuario_id', $usuario_id);
    $stmt_update->execute();
    
    // Actualizar último acceso del usuario
    $sql_access = "UPDATE chat_participantes 
                   SET ultimo_acceso = NOW() 
                   WHERE conversacion_id = :conversacion_id 
                   AND usuario_id = :usuario_id";
    $stmt_access = $conn->prepare($sql_access);
    $stmt_access->bindParam(':conversacion_id', $conversacion_id);
    $stmt_access->bindParam(':usuario_id', $usuario_id);
    $stmt_access->execute();
    
    echo json_encode(['success' => true, 'mensajes_actualizados' => $stmt_update->rowCount()]);
    
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Error en la base de datos: ' . $e->getMessage()]);
}
?>
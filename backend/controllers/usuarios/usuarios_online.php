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

// Verificar autenticación
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'No autorizado']);
    exit;
}

// Definir el tiempo límite (2 minutos = 120 segundos)
$tiempoLimite = time() - 120;

try {
    $db = new Database();
    $conn = $db->connect();
    
    // Actualizar timestamp del usuario actual
    $updateSql = "UPDATE usuarios SET last_activity = :time WHERE id = :id";
    $updateStmt = $conn->prepare($updateSql);
    $currentTime = time();
    $updateStmt->bindParam(':time', $currentTime, PDO::PARAM_INT);
    $updateStmt->bindParam(':id', $_SESSION['user_id'], PDO::PARAM_INT);
    $updateStmt->execute();
    
    // Limpiar usuarios inactivos automáticamente
    $limpiarSql = "UPDATE usuarios SET last_activity = 0 
                   WHERE last_activity > 0 
                   AND last_activity < :tiempoLimite";
    $limpiarStmt = $conn->prepare($limpiarSql);
    $limpiarStmt->bindParam(':tiempoLimite', $tiempoLimite, PDO::PARAM_INT);
    $limpiarStmt->execute();
    
    // Obtener usuarios conectados (activos en los últimos 2 minutos)
    // Excluir explícitamente usuarios con last_activity = 0
    $sql = "SELECT id, nombre, username, last_activity FROM usuarios 
            WHERE last_activity > :tiempoLimite 
            AND last_activity != 0
            ORDER BY nombre";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':tiempoLimite', $tiempoLimite, PDO::PARAM_INT);
    $stmt->execute();
    
    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Formatear los datos para el frontend
    foreach ($usuarios as &$usuario) {
        $usuario['online'] = true;
        $usuario['tiempo_conexion'] = time() - $usuario['last_activity'];
    }
    
    echo json_encode(['success' => true, 'usuarios' => $usuarios]);
} catch (PDOException $e) {
    error_log("Error en usuarios_online.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Error en la base de datos']);
}
exit;
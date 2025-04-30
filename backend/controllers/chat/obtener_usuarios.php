<?php
// Inhabilita la salida de errores en pantalla pero registralos en log
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
error_log("obtener_usuarios.php - Iniciado");
error_log("SESSION: " . print_r($_SESSION, true));

// Verificar autenticación
if (!isset($_SESSION['user_id'])) {
    error_log("No hay sesión de usuario");
    echo json_encode(['success' => false, 'error' => 'No autorizado']);
    exit;
}

$usuario_id = $_SESSION['user_id'];

try {
    $db = new Database();
    $conn = $db->connect();
    
    // Aquí falta el código de la consulta SQL
    // Esta es la parte que estaba incompleta y causaba el error 500
    $sql = "SELECT id, nombre, username FROM usuarios WHERE id != :usuario_id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':usuario_id', $usuario_id, PDO::PARAM_INT);
    $stmt->execute();
    
    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
    error_log("Usuarios encontrados: " . count($usuarios));
    
    echo json_encode(['success' => true, 'usuarios' => $usuarios]);
} catch (PDOException $e) {
    error_log("Error en obtener_usuarios.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Error en la base de datos: ' . $e->getMessage()]);
}
exit;
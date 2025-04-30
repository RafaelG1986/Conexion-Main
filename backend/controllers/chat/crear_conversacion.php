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
error_log("crear_conversacion.php - Iniciado");
error_log("POST: " . print_r($_POST, true));

// Verificar autenticación
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'No autorizado']);
    exit;
}

// Verificar datos
if (!isset($_POST['usuario_id']) || empty($_POST['usuario_id'])) {
    echo json_encode(['success' => false, 'error' => 'ID de usuario destino requerido']);
    exit;
}

$usuario_origen = $_SESSION['user_id'];
$usuario_destino = $_POST['usuario_id'];

try {
    $db = new Database();
    $conn = $db->connect();
    
    // Verificar si ya existe una conversación entre estos usuarios
    $sql_check = "SELECT c.id FROM chat_conversaciones c
                  INNER JOIN chat_participantes p1 ON p1.conversacion_id = c.id AND p1.usuario_id = :usuario1
                  INNER JOIN chat_participantes p2 ON p2.conversacion_id = c.id AND p2.usuario_id = :usuario2
                  WHERE c.tipo = 'individual'
                  LIMIT 1";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->bindParam(':usuario1', $usuario_origen, PDO::PARAM_INT);
    $stmt_check->bindParam(':usuario2', $usuario_destino, PDO::PARAM_INT);
    $stmt_check->execute();
    
    if ($conversacion = $stmt_check->fetch(PDO::FETCH_ASSOC)) {
        // Conversación existente
        echo json_encode(['success' => true, 'conversacion_id' => $conversacion['id']]);
        exit;
    }
    
    // Crear nueva conversación
    $conn->beginTransaction();
    
    // Obtener nombre del usuario destino para el título
    $sql_nombre = "SELECT nombre FROM usuarios WHERE id = :id";
    $stmt_nombre = $conn->prepare($sql_nombre);
    $stmt_nombre->bindParam(':id', $usuario_destino, PDO::PARAM_INT);
    $stmt_nombre->execute();
    $usuario = $stmt_nombre->fetch(PDO::FETCH_ASSOC);
    $titulo = $usuario ? $usuario['nombre'] : 'Usuario';
    
    // Insertar conversación
    $sql_conv = "INSERT INTO chat_conversaciones (titulo, tipo) VALUES (:titulo, 'individual')";
    $stmt_conv = $conn->prepare($sql_conv);
    $stmt_conv->bindParam(':titulo', $titulo);
    $stmt_conv->execute();
    
    $conversacion_id = $conn->lastInsertId();
    
    // Añadir participantes
    $sql_part = "INSERT INTO chat_participantes (conversacion_id, usuario_id) VALUES (:conv_id, :usuario_id)";
    
    // Participante 1 (usuario actual)
    $stmt_part1 = $conn->prepare($sql_part);
    $stmt_part1->bindParam(':conv_id', $conversacion_id, PDO::PARAM_INT);
    $stmt_part1->bindParam(':usuario_id', $usuario_origen, PDO::PARAM_INT);
    $stmt_part1->execute();
    
    // Participante 2 (usuario destino)
    $stmt_part2 = $conn->prepare($sql_part);
    $stmt_part2->bindParam(':conv_id', $conversacion_id, PDO::PARAM_INT);
    $stmt_part2->bindParam(':usuario_id', $usuario_destino, PDO::PARAM_INT);
    $stmt_part2->execute();
    
    $conn->commit();
    
    echo json_encode(['success' => true, 'conversacion_id' => $conversacion_id]);
} catch (PDOException $e) {
    if (isset($conn)) $conn->rollBack();
    error_log("Error en crear_conversacion.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Error en la base de datos']);
} catch (Exception $e) {
    if (isset($conn)) $conn->rollBack();
    error_log("Error general en crear_conversacion.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Error del servidor']);
}
exit;
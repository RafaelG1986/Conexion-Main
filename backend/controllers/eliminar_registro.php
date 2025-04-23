<?php
require_once __DIR__ . '/../config/cors.php';

// Iniciar sesión y conectar a la base de datos
session_start();
require_once __DIR__ . '/../config/database.php';

// Verificar si el usuario está autenticado
if (!isset($_SESSION['user'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

// Configurar encabezado JSON
header('Content-Type: application/json');

// Depuración
error_log("REQUEST_METHOD: " . $_SERVER['REQUEST_METHOD']);
error_log("POST data: " . json_encode($_POST));

// Verificar solicitud
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $id = intval($_POST['id']);
    
    // Validar ID
    if ($id <= 0) {
        error_log("ID inválido: $id");
        echo json_encode(['success' => false, 'message' => 'ID de registro inválido']);
        exit;
    }
    
    try {
        $db = new Database();
        $conn = $db->connect();
        
        // Verificar que el registro exista antes de eliminar
        $stmtCheck = $conn->prepare("SELECT id FROM registros WHERE id = :id");
        $stmtCheck->bindValue(':id', $id, PDO::PARAM_INT);
        $stmtCheck->execute();
        
        if (!$stmtCheck->fetch()) {
            error_log("Registro no encontrado: $id");
            echo json_encode(['success' => false, 'message' => 'El registro no existe']);
            exit;
        }
        
        // Eliminar el registro
        $stmt = $conn->prepare("DELETE FROM registros WHERE id = :id");
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        
        $resultado = $stmt->execute();
        $filasAfectadas = $stmt->rowCount();
        
        error_log("Resultado eliminación registro $id: " . ($resultado ? 'true' : 'false'));
        error_log("Filas afectadas: $filasAfectadas");
        
        // Responder al cliente
        echo json_encode([
            'success' => ($resultado && $filasAfectadas > 0), 
            'message' => ($resultado && $filasAfectadas > 0) ? 'Registro eliminado correctamente' : 'No se pudo eliminar el registro',
            'id' => $id,
            'filas_afectadas' => $filasAfectadas
        ]);
        
    } catch (PDOException $e) {
        error_log("Error en eliminar_registro.php: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Error en la base de datos: ' . $e->getMessage()]);
    }
} else {
    error_log("Solicitud inválida a eliminar_registro.php");
    echo json_encode(['success' => false, 'message' => 'Datos incompletos o método incorrecto']);
}
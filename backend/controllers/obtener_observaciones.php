<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/cors.php';

session_start();

// Cabecera JSON
header('Content-Type: application/json');

// Log de depuración
error_log("Iniciando obtener_observaciones.php");
error_log("Método: " . $_SERVER['REQUEST_METHOD']);
error_log("POST data: " . print_r($_POST, true));

// Verificar autenticación
if (!isset($_SESSION['user'])) {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

// Verificar método y datos
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método incorrecto. Debe ser POST']);
    exit;
}

if (!isset($_POST['id'])) {
    echo json_encode(['success' => false, 'message' => 'Falta el ID del registro']);
    exit;
}

$id = intval($_POST['id']);
if ($id <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID inválido: ' . $id]);
    exit;
}

// Añade más logs de depuración
error_log("ID recibido: " . print_r($_POST['id'], true));

try {
    $db = new Database();
    $conn = $db->connect();
    
    // Log para depuración
    error_log("Consultando observaciones para ID: " . $id);
    
    // Usar parámetros con nombre es más seguro y claro
    $stmt = $conn->prepare("SELECT observaciones FROM registros WHERE id = :id");
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    
    // Asegúrate de que la consulta SQL está obteniendo los datos correctamente
    $registro = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Log del resultado
    error_log("¿Se encontró registro? " . ($registro ? "Sí" : "No"));
    if ($registro) {
        error_log("Primeros 50 caracteres: " . substr($registro['observaciones'] ?? 'vacío', 0, 50));
    }
    
    // Log del resultado para depuración
    error_log("Resultado encontrado: " . ($registro ? "Sí" : "No"));
    
    if ($registro) {
        // Asegúrate de enviar un valor vacío en lugar de null
        $observaciones = $registro['observaciones'] ?? '';
        error_log("Observaciones encontradas: " . substr($observaciones, 0, 50) . "...");
        
        echo json_encode([
            'success' => true,
            'observaciones' => $observaciones
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Registro no encontrado']);
    }
} catch (PDOException $e) {
    error_log("PDO Error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error de base de datos']);
}


<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/cors.php';

// Iniciar sesión para obtener datos del usuario
session_start();

// Configurar cabecera JSON para la respuesta
header('Content-Type: application/json');

// Verificar que se recibieron los datos necesarios
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'], $_POST['observaciones'])) {
    
    // Obtener y validar datos
    $id = intval($_POST['id']);
    $observacion = trim($_POST['observaciones']);
    
    if (empty($observacion)) {
        echo json_encode(['success' => false, 'message' => 'La observación no puede estar vacía']);
        exit;
    }
    
    try {
        // Conectar a la base de datos
        $db = new Database();
        $conn = $db->connect();
        
        // 1. Obtener registro actual y sus observaciones previas
        $stmtGet = $conn->prepare("SELECT observaciones FROM registros WHERE id = :id");
        $stmtGet->bindValue(':id', $id, PDO::PARAM_INT);
        $stmtGet->execute();
        $registro = $stmtGet->fetch(PDO::FETCH_ASSOC);
        
        if (!$registro) {
            echo json_encode(['success' => false, 'message' => 'Registro no encontrado']);
            exit;
        }
        
        // 2. Formatear la nueva observación con fecha y usuario
        $fecha = date('d/m/Y H:i');
        $usuario = isset($_SESSION['user']['nombre']) ? $_SESSION['user']['nombre'] : 'sistema';
        $observacionFormateada = "[$fecha - $usuario]: $observacion";
        
        // 3. Combinar observaciones existentes con la nueva
        $observacionesActualizadas = $registro['observaciones'];
        if (!empty($observacionesActualizadas)) {
            $observacionesActualizadas .= "\n\n" . $observacionFormateada;
        } else {
            $observacionesActualizadas = $observacionFormateada;
        }
        
        // 4. Actualizar el registro
        $sql = "UPDATE registros SET 
                observaciones = :observaciones,
                fecha_ultimo_contacto = NOW()
                WHERE id = :id";
                
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':observaciones', $observacionesActualizadas, PDO::PARAM_STR);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        
        $resultado = $stmt->execute();
        $filasAfectadas = $stmt->rowCount();
        
        // 5. Devolver respuesta JSON
        echo json_encode([
            'success' => ($resultado && $filasAfectadas > 0),
            'message' => ($resultado && $filasAfectadas > 0) ? 'Observación guardada correctamente' : 'No se actualizó el registro',
            'observaciones' => $observacionesActualizadas
        ]);
        
    } catch (PDOException $e) {
        // Manejar errores de base de datos
        error_log("Error en agregar_observacion.php: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Error en la base de datos']);
    }
} else {
    // Si faltan datos
    echo json_encode(['success' => false, 'message' => 'Datos incompletos o método incorrecto']);
}
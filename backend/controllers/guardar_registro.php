<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
// Usar la clase Database en lugar de conexion.php
require_once __DIR__ . '/../config/database.php';

require_once __DIR__ . '/../config/cors.php';

// Solo permitir método POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../../frontend/views/admin.php?error=metodo_no_permitido');
    exit;
}

try {
    // Inicializar la conexión usando la clase Database
    $db = new Database();
    $conn = $db->connect();
    
    // Recoger variables del formulario usando el operador de fusión null
    $id = $_POST['id'] ?? null;
    $nombre_persona = $_POST['nombre_persona'] ?? '';
    $apellido_persona = $_POST['apellido_persona'] ?? '';
    $telefono = $_POST['telefono'] ?? '';
    $nombre_conector = $_POST['nombre_conector'] ?? '';
    $nombre_quien_trajo = $_POST['nombre_quien_trajo'] ?? '';
    $estado = $_POST['estado'] ?? 'Primer contacto';
    $formulario_nuevos = isset($_POST['formulario_nuevos']) ? 1 : 0;
    $formulario_llamadas = isset($_POST['formulario_llamadas']) ? 1 : 0;
    $fecha_contacto = $_POST['fecha_contacto'] ?? date('Y-m-d');
    $fecha_ultimo_contacto = $_POST['fecha_ultimo_contacto'] ?? null;
    $cumpleanos = $_POST['cumpleanos'] ?? null;
    $observaciones = $_POST['observaciones'] ?? '';
    $subido_por = $_SESSION['user']['username'] ?? 'sistema';
    
    // Verificar si es un nuevo conector
    if (isset($_POST['nombre_conector']) && $_POST['nombre_conector'] === 'otro' && !empty($_POST['otro_conector'])) {
        $nombre_conector = trim($_POST['otro_conector']);
    }
    
    // Procesar imagen si se ha enviado
    $ruta_foto = '';
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === 0) {
        // Definir directorio de uploads y asegurar que existe
        $directorio_uploads = '../../uploads/';
        if (!file_exists($directorio_uploads)) {
            mkdir($directorio_uploads, 0777, true);
        }
        
        // Crear nombre único para el archivo
        $nombre_archivo = time() . '_' . basename($_FILES['foto']['name']);
        $ruta_destino = $directorio_uploads . $nombre_archivo;
        
        // Mover archivo subido al directorio final
        if (move_uploaded_file($_FILES['foto']['tmp_name'], $ruta_destino)) {
            $ruta_foto = $nombre_archivo;
        }
    } else if (isset($_POST['id']) && isset($_POST['foto_actual'])) {
        // Si estamos actualizando y no se subió una foto nueva, mantener la actual
        $ruta_foto = $_POST['foto_actual'];
    }
    
    // Si es una actualización (existe ID)
    if ($id) {
        $sql = "UPDATE registros SET 
                nombre_persona = :nombre_persona,
                apellido_persona = :apellido_persona,
                telefono = :telefono,
                nombre_conector = :nombre_conector,
                nombre_quien_trajo = :nombre_quien_trajo,
                estado = :estado,
                formulario_nuevos = :formulario_nuevos,
                formulario_llamadas = :formulario_llamadas,
                fecha_contacto = :fecha_contacto,
                fecha_ultimo_contacto = :fecha_ultimo_contacto,
                cumpleanos = :cumpleanos,
                observaciones = :observaciones";
        
        // Solo actualizar la foto si se ha enviado una nueva
        if (!empty($ruta_foto)) {
            $sql .= ", foto = :foto";
        }
        
        $sql .= " WHERE id = :id";
        
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        
        // Si hay foto nueva, incluirla en los parámetros
        if (!empty($ruta_foto)) {
            $stmt->bindParam(':foto', $ruta_foto, PDO::PARAM_STR);
        }
    } 
    // Si es un nuevo registro
    else {
        $sql = "INSERT INTO registros (
                nombre_persona, apellido_persona, telefono, nombre_conector,
                nombre_quien_trajo, estado, formulario_nuevos, formulario_llamadas,
                fecha_contacto, fecha_ultimo_contacto, cumpleanos, observaciones, 
                foto, subido_por
            ) VALUES (
                :nombre_persona, :apellido_persona, :telefono, :nombre_conector,
                :nombre_quien_trajo, :estado, :formulario_nuevos, :formulario_llamadas,
                :fecha_contacto, :fecha_ultimo_contacto, :cumpleanos, :observaciones,
                :foto, :subido_por
            )";
            
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':foto', $ruta_foto, PDO::PARAM_STR);
        $stmt->bindParam(':subido_por', $subido_por, PDO::PARAM_STR);
    }
    
    // Parámetros comunes para ambos casos
    $stmt->bindParam(':nombre_persona', $nombre_persona, PDO::PARAM_STR);
    $stmt->bindParam(':apellido_persona', $apellido_persona, PDO::PARAM_STR);
    $stmt->bindParam(':telefono', $telefono, PDO::PARAM_STR);
    $stmt->bindParam(':nombre_conector', $nombre_conector, PDO::PARAM_STR);
    $stmt->bindParam(':nombre_quien_trajo', $nombre_quien_trajo, PDO::PARAM_STR);
    $stmt->bindParam(':estado', $estado, PDO::PARAM_STR);
    $stmt->bindParam(':formulario_nuevos', $formulario_nuevos, PDO::PARAM_INT);
    $stmt->bindParam(':formulario_llamadas', $formulario_llamadas, PDO::PARAM_INT);
    $stmt->bindParam(':fecha_contacto', $fecha_contacto, PDO::PARAM_STR);
    $stmt->bindParam(':fecha_ultimo_contacto', $fecha_ultimo_contacto, PDO::PARAM_STR);
    $stmt->bindParam(':cumpleanos', $cumpleanos, PDO::PARAM_STR);
    $stmt->bindParam(':observaciones', $observaciones, PDO::PARAM_STR);
    
    // Ejecutar la consulta
    $stmt->execute();
    
    // Redireccionar según el resultado
    if ($id) {
        header('Location: ../../frontend/views/ver_registro.php?id=' . $id . '&success=actualizado');
    } else {
        header('Location: ../../frontend/views/admin.php?success=registro_creado');
    }
    exit;
    
} catch (PDOException $e) {
    // Registrar el error para debugging
    error_log("Error en guardar_registro.php: " . $e->getMessage());
    
    // Redireccionar con mensaje de error
    if (isset($_POST['id'])) {
        header('Location: ../../frontend/views/ver_registro.php?id=' . $_POST['id'] . '&error=db_error');
    } else {
        header('Location: ../../frontend/views/admin.php?error=db_error&message=' . urlencode($e->getMessage()));
    }
    exit;
}
?>
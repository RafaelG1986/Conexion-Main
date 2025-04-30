<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/cors.php';

// Verificar si el usuario está autenticado
if (!isset($_SESSION['user'])) {
    header('Location: ../../index.html');
    exit;
}

// Verificar si se envió el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Inicializar mensaje de error
    $error = '';
    
    // Procesar la foto
    $nombre_foto = '';
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        // Verificar el tipo de archivo
        $allowed = array('jpg', 'jpeg', 'png', 'gif');
        $filename = $_FILES['foto']['name'];
        $ext = pathinfo($filename, PATHINFO_EXTENSION);
        
        if (!in_array(strtolower($ext), $allowed)) {
            $error = "Error: Formato de imagen no válido. Formatos permitidos: JPG, PNG, GIF.";
        } 
        // Verificar el tamaño (2MB máximo)
        elseif ($_FILES['foto']['size'] > 2097152) {
            $error = "Error: La imagen no debe superar los 2MB.";
        }
        else {
            // Generar nombre único para la foto
            $nombre_foto = uniqid() . '_' . $filename;
            $ruta_destino = __DIR__ . '/../../frontend/img/' . $nombre_foto;
            
            // Intentar mover el archivo subido
            if (!move_uploaded_file($_FILES['foto']['tmp_name'], $ruta_destino)) {
                $error = "Error: No se pudo guardar la imagen. Verifica los permisos de la carpeta.";
                error_log("Error moviendo archivo: " . error_get_last()['message']);
            }
        }
    }

    // Procesamiento de la imagen
    $foto = '';
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        // Código para subir la imagen
        $foto = $nombre_foto;
    }

    // Si hay errores, redirigir con mensaje de error
    if (!empty($error)) {
        $_SESSION['error_mensaje'] = $error;
        header('Location: ../../frontend/views/agregar_registro.php');
        exit;
    }

    try {
        $db = new Database();
        $conn = $db->connect();
        
        // Obtener datos del formulario con validación
        $nombre_persona = $_POST['nombre_persona'] ?? '';
        $apellido_persona = $_POST['apellido_persona'] ?? '';
        $telefono = $_POST['telefono'] ?? '';
        $nombre_conector = $_POST['nombre_conector'] ?? '';
        $nombre_quien_trajo = $_POST['nombre_quien_trajo'] ?? '';
        $estado = $_POST['estado'] ?? '';
        $fecha_contacto = $_POST['fecha_contacto'] ?? date('Y-m-d'); // Valor predeterminado hoy
        $formulario_nuevos = $_POST['formulario_nuevos'] ?? '';
        $formulario_llamadas = $_POST['formulario_llamadas'] ?? '';
        $cumpleanos = $_POST['cumpleanos'] ?? '';
        $observaciones = $_POST['observaciones'] ?? '';
        
        // Subido por el usuario actual
        $subido_por = $_SESSION['user']['nombre'] ?? '';
        
        // Construir la consulta SQL usando SOLO las columnas que existen en la tabla
        $sql = "INSERT INTO registros (
                nombre_persona, apellido_persona, telefono,
                nombre_conector, nombre_quien_trajo, estado, 
                foto, fecha_contacto, formulario_nuevos, 
                formulario_llamadas, subido_por, fecha_ultimo_contacto,
                cumpleanos, observaciones
            ) VALUES (
                :nombre, :apellido, :telefono,
                :nombre_conector, :nombre_quien_trajo, :estado,
                :foto, :fecha_contacto, :formulario_nuevos,
                :formulario_llamadas, :subido_por, NOW(),
                :cumpleanos, :observaciones
            )";
        
        $stmt = $conn->prepare($sql);
        
        // Vincular parámetros
        $stmt->bindParam(':nombre', $nombre_persona);
        $stmt->bindParam(':apellido', $apellido_persona);
        $stmt->bindParam(':telefono', $telefono);
        $stmt->bindParam(':nombre_conector', $nombre_conector);
        $stmt->bindParam(':nombre_quien_trajo', $nombre_quien_trajo);
        $stmt->bindParam(':estado', $estado);
        $stmt->bindParam(':foto', $nombre_foto);
        $stmt->bindParam(':fecha_contacto', $fecha_contacto);
        $stmt->bindParam(':formulario_nuevos', $formulario_nuevos);
        $stmt->bindParam(':formulario_llamadas', $formulario_llamadas);
        $stmt->bindParam(':subido_por', $subido_por);
        $stmt->bindParam(':cumpleanos', $cumpleanos);
        $stmt->bindParam(':observaciones', $observaciones);
        
        $stmt->execute();
        
        // Actualización en la base de datos
        $sql = "UPDATE registros SET foto = :foto, ... WHERE id = :id";
        
        // Registrar la acción
        error_log("Registro creado correctamente por usuario: {$subido_por}");
        
        // Redirigir con mensaje de éxito
        $_SESSION['exito_mensaje'] = "Registro creado correctamente.";
        header('Location: ../../frontend/views/admin.php');
        exit;
        
    } catch (PDOException $e) {
        // Si hubo error en la inserción y se subió una imagen, eliminarla
        if (!empty($nombre_foto) && file_exists($ruta_destino)) {
            unlink($ruta_destino);
        }
        
        error_log("Error en procesar_registro.php: " . $e->getMessage());
        $_SESSION['error_mensaje'] = "Error al crear el registro: " . $e->getMessage();
        header('Location: ../../frontend/views/agregar_registro.php');
        exit;
    }
    
} else {
    // Si no se envió el formulario por POST
    $_SESSION['error_mensaje'] = "Método no permitido.";
    header('Location: ../../frontend/views/admin.php');
    exit;
}
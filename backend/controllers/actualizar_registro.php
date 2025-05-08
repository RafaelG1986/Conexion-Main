<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/cors.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    // Procesar la foto si se subió una nueva
    $foto = '';
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
        $foto = uniqid('foto_') . '.' . $ext;
        move_uploaded_file($_FILES['foto']['tmp_name'], __DIR__ . '/../../frontend/img/' . $foto);
    } else {
        // Si no se subió una nueva, mantener la foto actual
        $foto = $_POST['foto_actual'] ?? '';
    }

    // Añadir al controlador de actualización
    $proximo_contacto = !empty($_POST['proximo_contacto']) ? $_POST['proximo_contacto'] : null;

    $sql = "UPDATE registros SET 
        nombre_persona = :nombre_persona,
        apellido_persona = :apellido_persona,
        telefono = :telefono,
        nombre_conector = :nombre_conector,
        nombre_quien_trajo = :nombre_quien_trajo,
        estado = :estado,
        foto = :foto,
        fecha_contacto = :fecha_contacto,
        formulario_nuevos = :formulario_nuevos,
        formulario_llamadas = :formulario_llamadas,
        subido_por = :subido_por,
        fecha_ultimo_contacto = :fecha_ultimo_contacto,
        cumpleanos = :cumpleanos,
        observaciones = :observaciones,
        proximo_contacto = :proximo_contacto
        WHERE id = :id";

    $params = [
        ':nombre_persona' => $_POST['nombre_persona'] ?? '',
        ':apellido_persona' => $_POST['apellido_persona'] ?? '',
        ':telefono' => $_POST['telefono'] ?? '',
        ':nombre_conector' => $_POST['nombre_conector'] ?? '',
        ':nombre_quien_trajo' => $_POST['nombre_quien_trajo'] ?? '',
        ':estado' => $_POST['estado'] ?? '',
        ':foto' => $foto,
        ':fecha_contacto' => $_POST['fecha_contacto'] ?? '',
        ':formulario_nuevos' => $_POST['formulario_nuevos'] ?? '',
        ':formulario_llamadas' => $_POST['formulario_llamadas'] ?? '',
        ':subido_por' => $_POST['subido_por'] ?? '',
        ':fecha_ultimo_contacto' => $_POST['fecha_ultimo_contacto'] ?? '',
        ':cumpleanos' => $_POST['cumpleanos'] ?? '',
        ':observaciones' => $_POST['observaciones'] ?? '',
        ':proximo_contacto' => $proximo_contacto,
        ':id' => $_POST['id']
    ];

    try {
        $db = new Database();
        $conn = $db->connect();
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        echo "Registro actualizado correctamente";
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
} else {
    echo "Datos incompletos.";
}
<?php
require_once __DIR__ . '/../../backend/config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = $_POST['nombre_persona'] ?? '';
    $apellido = $_POST['apellido_persona'] ?? '';
    $telefono = $_POST['telefono'] ?? '';
    $nombre_conector = $_POST['nombre_conector'] ?? '';
    $nombre_quien_trajo = $_POST['nombre_quien_trajo'] ?? '';
    $estado = $_POST['estado'] ?? '';
    $foto = ''; // Procesa la foto si es necesario
    $fecha_contacto = $_POST['fecha_contacto'] ?? '';
    $formulario_nuevos = $_POST['formulario_nuevos'] ?? '';
    $formulario_llamadas = $_POST['formulario_llamadas'] ?? '';
    $subido_por = $_POST['subido_por'] ?? '';
    $fecha_ultimo_contacto = $_POST['fecha_ultimo_contacto'] ?? '';
    $cumpleanos = $_POST['cumpleanos'] ?? '';
    $observaciones = $_POST['observaciones'] ?? '';

    try {
        $db = new Database();
        $conn = $db->connect();
        $stmt = $conn->prepare("INSERT INTO registros 
            (nombre_persona, apellido_persona, telefono, nombre_conector, nombre_quien_trajo, estado, foto, fecha_contacto, formulario_nuevos, formulario_llamadas, subido_por, fecha_ultimo_contacto, cumpleanos, observaciones)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $nombre, $apellido, $telefono, $nombre_conector, $nombre_quien_trajo, $estado, $foto, $fecha_contacto, $formulario_nuevos, $formulario_llamadas, $subido_por, $fecha_ultimo_contacto, $cumpleanos, $observaciones
        ]);
        echo "Registro agregado correctamente";
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
} else {
    echo "Método no permitido.";
}
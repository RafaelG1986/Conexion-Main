<?php
require_once __DIR__ . '/../../backend/config/database.php';
header('Content-Type: application/json; charset=utf-8');

$campo = $_GET['campo'] ?? '';
$valor = $_GET['valor'] ?? '';

if (!$campo) {
    echo json_encode([]);
    exit;
}

try {
    $db = new Database();
    $conn = $db->connect();
    
    // Lista de campos permitidos para prevenir inyección SQL
    $camposPermitidos = ['nombre_persona', 'apellido_persona', 'telefono', 'nombre_conector', 'estado', 'observaciones'];
    
    if (!in_array($campo, $camposPermitidos)) {
        echo json_encode([]);
        exit;
    }
    
    // Si se proporciona un valor específico
    if ($valor) {
        $stmt = $conn->prepare("SELECT id, nombre_persona, apellido_persona, telefono, nombre_conector, estado, observaciones FROM registros WHERE $campo = :valor ORDER BY id DESC");
        $stmt->execute([':valor' => $valor]);
    } else { 
        // Si queremos todos los registros para ese campo (que no sean nulos)
        $stmt = $conn->prepare("SELECT id, nombre_persona, apellido_persona, telefono, nombre_conector, estado, observaciones FROM registros WHERE $campo IS NOT NULL AND $campo != '' ORDER BY $campo, id DESC");
        $stmt->execute();
    }
    
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    
} catch (PDOException $e) {
    echo json_encode([]);
}
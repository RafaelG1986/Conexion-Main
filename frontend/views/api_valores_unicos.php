<?php
require_once __DIR__ . '/../../backend/config/database.php';
header('Content-Type: application/json; charset=utf-8');

$campo = $_GET['campo'] ?? '';
if (!$campo) {
    echo json_encode([]);
    exit;
}

try {
    $db = new Database();
    $conn = $db->connect();
    
    // Lista de campos permitidos para prevenir inyección SQL
    $camposPermitidos = [
        'nombre_persona', 
        'apellido_persona', 
        'telefono', 
        'nombre_conector', 
        'estado', 
        'observaciones',
        'proximo_contacto'  // Añadido nuevo campo
    ];
    
    if (!in_array($campo, $camposPermitidos)) {
        echo json_encode([]);
        exit;
    }
    
    $stmt = $conn->prepare("SELECT DISTINCT $campo FROM registros WHERE $campo IS NOT NULL AND $campo != '' ORDER BY $campo");
    $stmt->execute();
    echo json_encode($stmt->fetchAll(PDO::FETCH_COLUMN));
    
} catch (PDOException $e) {
    echo json_encode([]);
}
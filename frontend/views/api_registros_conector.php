<?php
require_once __DIR__ . '/../../backend/config/database.php';
header('Content-Type: application/json; charset=utf-8');

$conector = $_GET['conector'] ?? '';
if (!$conector) {
    echo json_encode([]);
    exit;
}

try {
    $db = new Database();
    $conn = $db->connect();
    
    $stmt = $conn->prepare("
        SELECT id, nombre_persona, apellido_persona, telefono, estado, observaciones
        FROM registros 
        WHERE nombre_conector = :conector
        ORDER BY id DESC
    ");
    $stmt->execute([':conector' => $conector]);
    
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    
} catch (PDOException $e) {
    echo json_encode([]);
}
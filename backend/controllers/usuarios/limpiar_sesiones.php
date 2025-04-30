<?php
require_once __DIR__ . '/../../config/database.php';

// Este script se puede ejecutar desde cron o desde admin

// Definir el tiempo límite (3 minutos)
$tiempoLimite = time() - (3 * 60);

try {
    $db = new Database();
    $conn = $db->connect();
    
    // Limpiar usuarios inactivos
    $sql = "UPDATE usuarios SET last_activity = 0 
            WHERE last_activity > 0 AND last_activity < :tiempoLimite";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':tiempoLimite', $tiempoLimite, PDO::PARAM_INT);
    $resultado = $stmt->execute();
    
    // Retornar resultado
    echo json_encode([
        'success' => true, 
        'limpiados' => $stmt->rowCount()
    ]);
    
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
exit;
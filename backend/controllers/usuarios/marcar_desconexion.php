<?php
header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', 0);
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false]);
    exit;
}

require_once __DIR__ . '/../../config/database.php';

try {
    $db = new Database();
    $conn = $db->connect();
    
    // Marcar timestamp de desconexión (ponemos 0 para indicar desconexión)
    $sql = "UPDATE usuarios SET last_activity = 0 WHERE id = :id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':id', $_SESSION['user_id'], PDO::PARAM_INT);
    $result = $stmt->execute();
    
    echo json_encode(['success' => $result]);
} catch (Exception $e) {
    error_log("Error al marcar desconexión: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
exit;
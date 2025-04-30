<?php
session_start();
session_unset();

// Marcar desconexión en la base de datos
if(isset($_SESSION['user_id'])) {
    try {
        require_once __DIR__ . '/../config/database.php';
        $db = new Database();
        $conn = $db->connect();
        
        $sql = "UPDATE usuarios SET last_activity = 0 WHERE id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':id', $_SESSION['user_id'], PDO::PARAM_INT);
        $stmt->execute();
    } catch (Exception $e) {
        error_log("Error al marcar desconexión en logout: " . $e->getMessage());
    }
}

session_destroy();
header('Location: ../../index.html');
exit;
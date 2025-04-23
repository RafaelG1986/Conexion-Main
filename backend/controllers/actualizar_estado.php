<?php
// filepath: c:\xampp\htdocs\conexion-main\conexion-main\backend\controllers\actualizar_estado.php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/cors.php';

file_put_contents(__DIR__ . '/debug_estado.log', "Llamada recibida: " . json_encode($_POST) . "\n", FILE_APPEND);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'], $_POST['estado'])) {
    $id = intval($_POST['id']);
    $estado = $_POST['estado'];

    try {
        $db = new Database();
        $conn = $db->connect();
        $stmt = $conn->prepare("UPDATE registros SET estado = :estado WHERE id = :id");
        $stmt->execute([
            ':estado' => $estado,
            ':id' => $id
        ]);
        // Log para depuración
        file_put_contents(__DIR__ . '/debug_estado.log', "ID: $id, Estado: $estado, Filas: " . $stmt->rowCount() . "\n", FILE_APPEND);
        echo "Estado actualizado";
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
} else {
    echo "Datos incompletos.";
}
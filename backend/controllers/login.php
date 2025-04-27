<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/cors.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($username === '' || $password === '') {
        header('Location: ../../index.html?error=campos_vacios');
        exit;
    }

    $db = new Database();
    $conn = $db->connect();

    $stmt = $conn->prepare("SELECT * FROM usuarios WHERE username = :username LIMIT 1");
    $stmt->bindParam(':username', $username, PDO::PARAM_STR);
    $stmt->execute();
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($usuario && password_verify($password, $usuario['password'])) {
        // Autenticación exitosa
        $_SESSION['user'] = [
            'id' => $usuario['id'],
            'username' => $usuario['username'],
            'nombre' => $usuario['nombre'] ?? $usuario['username']
        ];
        header('Location: ../../frontend/views/admin.php');
        exit;
    } else {
        // Fallo de autenticación
        header('Location: ../../index.html?error=credenciales');
        exit;
    }
} else {
    header('Location: ../../index.html');
    exit;
}
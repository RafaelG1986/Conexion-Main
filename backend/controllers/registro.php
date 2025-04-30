<!-- filepath: c:\xampp\htdocs\Conexion-Main\backend\controllers\registro.php -->
<?php
// Inhabilita la salida de errores en pantalla
ini_set('display_errors', 0);
error_reporting(E_ALL);

require_once __DIR__ . '/../config/database.php';

// Log para depuración
error_log("registro.php - Iniciado");
error_log("POST: " . print_r($_POST, true));

// Código de invitación válido
$CODIGO_VALIDO = "2025Conexionwow";

// Verificar si el método es POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../../frontend/views/registro.php?error=method');
    exit;
}

// Verificar que todos los campos estén completos
if (empty($_POST['nombre']) || empty($_POST['username']) || 
    empty($_POST['password']) || empty($_POST['confirm_password']) || 
    empty($_POST['codigo'])) {
    header('Location: ../../frontend/views/registro.php?error=empty');
    exit;
}

// Obtener y sanitizar datos
$nombre = filter_var($_POST['nombre'], FILTER_SANITIZE_STRING);
$username = filter_var($_POST['username'], FILTER_SANITIZE_STRING);
$password = $_POST['password'];
$confirm_password = $_POST['confirm_password'];
$codigo = $_POST['codigo'];

// Verificar que las contraseñas coincidan
if ($password !== $confirm_password) {
    header('Location: ../../frontend/views/registro.php?error=password');
    exit;
}

// Verificar el código de invitación
if ($codigo !== $CODIGO_VALIDO) {
    header('Location: ../../frontend/views/registro.php?error=codigo');
    exit;
}

try {
    $db = new Database();
    $conn = $db->connect();
    
    // Verificar si el username ya existe
    $stmt_check = $conn->prepare("SELECT COUNT(*) FROM usuarios WHERE username = :username");
    $stmt_check->bindParam(':username', $username);
    $stmt_check->execute();
    
    if ($stmt_check->fetchColumn() > 0) {
        header('Location: ../../frontend/views/registro.php?error=exists');
        exit;
    }
    
    // Encriptar contraseña
    $password_hash = password_hash($password, PASSWORD_DEFAULT);
    
    // Insertar nuevo usuario
    $stmt = $conn->prepare("INSERT INTO usuarios (username, password, nombre) VALUES (:username, :password, :nombre)");
    $stmt->bindParam(':username', $username);
    $stmt->bindParam(':password', $password_hash);
    $stmt->bindParam(':nombre', $nombre);
    
    if ($stmt->execute()) {
        // Registro exitoso
        error_log("Usuario registrado correctamente: $username");
        header('Location: ../../index.html?success=registered');
        exit;
    } else {
        // Error al insertar
        error_log("Error al registrar usuario: " . implode(", ", $stmt->errorInfo()));
        header('Location: ../../frontend/views/registro.php?error=database');
        exit;
    }
} catch (PDOException $e) {
    error_log("Error de base de datos: " . $e->getMessage());
    header('Location: ../../frontend/views/registro.php?error=database');
    exit;
} catch (Exception $e) {
    error_log("Error general: " . $e->getMessage());
    header('Location: ../../frontend/views/registro.php?error=general');
    exit;
}
?>
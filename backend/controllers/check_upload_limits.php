<?php
// filepath: c:\xampp\htdocs\conexion-main\backend\controllers\check_upload_limits.php
require_once __DIR__ . '/../config/cors.php';

header('Content-Type: application/json');

echo json_encode([
    'post_max_size' => ini_get('post_max_size'),
    'upload_max_filesize' => ini_get('upload_max_filesize'),
    'max_file_uploads' => ini_get('max_file_uploads'),
    'memory_limit' => ini_get('memory_limit'),
    'max_execution_time' => ini_get('max_execution_time')
]);
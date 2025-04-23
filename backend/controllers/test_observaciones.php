<?php
header('Content-Type: application/json');

// Siempre devuelve algunas observaciones de prueba
echo json_encode([
    'success' => true,
    'observaciones' => "[25/04/2025 10:30 - Admin] Esta es una observación de prueba.\n\n[25/04/2025 11:45 - Sistema] Segunda línea de observación para probar el formato."
]);
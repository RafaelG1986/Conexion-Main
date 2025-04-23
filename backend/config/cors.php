<?php
/**
 * Configuración global de CORS para la aplicación
 * Detecta automáticamente entornos y configura las cabeceras adecuadamente
 */

// Obtener el origen de la solicitud
$origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';
$referer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';

// Función para extraer el dominio base
function getDomainFromUrl($url) {
    $parsedUrl = parse_url($url);
    return isset($parsedUrl['host']) ? $parsedUrl['host'] : '';
}

// Detectar si estamos usando ngrok
$isNgrok = false;
$ngrokDomain = '';

if (strpos($origin, 'ngrok-free.app') !== false) {
    $isNgrok = true;
    $ngrokDomain = $origin;
} elseif (strpos($referer, 'ngrok-free.app') !== false) {
    $isNgrok = true;
    $ngrokDomain = preg_replace('/^(https?:\/\/[^\/]+).*$/', '$1', $referer);
}

// Configurar los encabezados CORS según el entorno
if ($isNgrok) {
    // Si es ngrok, permitir ese origen específico
    header("Access-Control-Allow-Origin: $ngrokDomain");
} elseif (in_array($_SERVER['SERVER_NAME'], ['localhost', '127.0.0.1'])) {
    // Entorno de desarrollo local
    header('Access-Control-Allow-Origin: *');
} else {
    // Entorno de producción - aquí puedes definir dominios específicos
    $allowedDomains = [
        'tudominio.com',
        'www.tudominio.com'
    ];
    
    $domain = getDomainFromUrl($origin);
    if (in_array($domain, $allowedDomains)) {
        header("Access-Control-Allow-Origin: $origin");
    }
}

// Configurar otros encabezados CORS
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, X-XSRF-TOKEN');
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Max-Age: 86400'); // 24 horas

// Si es una solicitud OPTIONS (preflight), terminar aquí
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}
<?php
// Script para mover imágenes de uploads/ a frontend/img/
$origen = __DIR__ . '/uploads/';
$destino = __DIR__ . '/frontend/img/';

// Verificar que las carpetas existen
if (!is_dir($origen)) {
    die("Error: Carpeta origen no existe.");
}
if (!is_dir($destino)) {
    mkdir($destino, 0755, true);
}

// Obtener todos los archivos de la carpeta origen
$archivos = glob($origen . '*.*');
$contador = 0;

// Mover cada archivo
foreach ($archivos as $archivo) {
    $nombre = basename($archivo);
    if (copy($archivo, $destino . $nombre)) {
        echo "Copiado: $nombre<br>";
        $contador++;
    } else {
        echo "Error al copiar: $nombre<br>";
    }
}

echo "Total de archivos copiados: $contador";
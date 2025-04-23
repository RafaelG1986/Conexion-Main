<?php
require_once __DIR__ . '/../../backend/config/database.php';

try {
    $db = new Database();
    $conn = $db->connect();
    
    // Obtener estadísticas generales por estado
    $datosEstados = [];
    $stmt = $conn->query("
        SELECT estado, COUNT(*) as total 
        FROM registros 
        WHERE estado IS NOT NULL AND estado != '' 
        GROUP BY estado 
        ORDER BY total DESC
    ");
    
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $datosEstados[$row['estado']] = (int)$row['total'];
    }
    
    // Obtener estadísticas por conector
    $datosConectores = [];
    $stmt = $conn->query("
        SELECT nombre_conector, COUNT(*) as total 
        FROM registros 
        WHERE nombre_conector IS NOT NULL AND nombre_conector != '' 
        GROUP BY nombre_conector 
        ORDER BY total DESC
    ");
    
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $datosConectores[$row['nombre_conector']] = (int)$row['total'];
    }
    
} catch (PDOException $e) {
    $datosEstados = [];
    $datosConectores = [];
    echo "<div class='alert alert-danger'>Error de base de datos: " . htmlspecialchars($e->getMessage()) . "</div>";
}
?>

<link rel="stylesheet" href="../css/styles_estadisticas.css">

<div class="estadisticas-container">
  <h2>Estadísticas Generales</h2>

  <div class="chart-container">
    <h3>Distribución por Estados</h3>
    <canvas id="chartEstados" 
           width="400" 
           height="300"
           data-estados='<?= json_encode($datosEstados) ?>'></canvas>
  </div>
  
  <div class="chart-container">
    <h3>Distribución por Conectores</h3>
    <canvas id="chartConectores" 
           width="400" 
           height="300"
           data-conectores='<?= json_encode($datosConectores) ?>'></canvas>
  </div>
</div>

<script>
// Script inline para asegurar que se procesen los datos
(function() {
  function inicializarEstadisticasInline() {
    // Intentar inicializar desde el JS principal si existe
    if (typeof window.inicializarEstadisticas === 'function') {
      window.inicializarEstadisticas();
    }
  }
  
  // Si el documento ya está listo, ejecutamos ahora
  if (document.readyState === 'complete' || document.readyState === 'interactive') {
    setTimeout(inicializarEstadisticasInline, 0);
  } else {
    // Si no, esperamos a que esté listo
    document.addEventListener('DOMContentLoaded', inicializarEstadisticasInline);
  }
})();
</script>
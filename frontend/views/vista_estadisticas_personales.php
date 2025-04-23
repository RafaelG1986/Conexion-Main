<?php
require_once __DIR__ . '/../../backend/config/database.php';

try {
    $db = new Database();
    $conn = $db->connect();
    
    // 1. Obtener lista de conectores
    $stmt = $conn->query("SELECT DISTINCT nombre_conector FROM registros WHERE nombre_conector IS NOT NULL AND nombre_conector != '' ORDER BY nombre_conector");
    $conectores = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    // 2. Verificar si tenemos conectores
    if (empty($conectores)) {
        echo "<div class='alert alert-warning'>No hay conectores disponibles en la base de datos.</div>";
        $conectorSeleccionado = '';
        $datosConectores = [];
    } else {
        // 3. Conector seleccionado (por GET o el primero disponible)
        $conectorSeleccionado = isset($_GET['conector']) && !empty($_GET['conector']) 
                              ? $_GET['conector'] 
                              : $conectores[0];
        
        // 4. Validamos que sea un conector válido
        if (!in_array($conectorSeleccionado, $conectores)) {
            $conectorSeleccionado = $conectores[0];
        }
        
        // 5. Obtener datos para el conector seleccionado
        $datosConectores = [];
        $stmt = $conn->prepare("
            SELECT estado, COUNT(*) as total 
            FROM registros 
            WHERE nombre_conector = :conector AND estado IS NOT NULL AND estado != ''
            GROUP BY estado 
            ORDER BY total DESC
        ");
        $stmt->execute([':conector' => $conectorSeleccionado]);
        
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $datosConectores[$row['estado']] = (int)$row['total'];
        }
        
        // 6. Si no hay datos, mostramos mensaje
        if (empty($datosConectores)) {
            echo "<div class='alert alert-info'>No hay datos de estados para el conector seleccionado.</div>";
        }
    }
    
} catch (PDOException $e) {
    echo "<div class='alert alert-danger'>Error de base de datos: " . htmlspecialchars($e->getMessage()) . "</div>";
    $conectores = [];
    $conectorSeleccionado = '';
    $datosConectores = [];
}

// Datos de prueba - DESCOMENTAR PARA FORZAR DATOS
// $conectorSeleccionado = "Prueba";
// $datosConectores = ["Contactado" => 5, "Pendiente" => 3, "Cerrado" => 2];
?>

<link rel="stylesheet" href="../css/styles_estadisticas_personales.css">

<div class="estadisticas-personales-container">
  <h2>Estadísticas Personales por Conector</h2>
  
  <form id="filtro-conector">
    <label for="conector">Conector:</label>
    <select id="conector">
      <option value="">-- Selecciona --</option>
      <?php foreach($conectores as $c): ?>
        <option value="<?= htmlspecialchars($c) ?>" 
                <?= $c === $conectorSeleccionado ? 'selected' : '' ?>>
          <?= htmlspecialchars($c) ?>
        </option>
      <?php endforeach; ?>
    </select>
  </form>
  
  <?php
  // Mostrar información de depuración
  echo "<pre style='background:#f5f5f5;padding:10px;font-size:12px'>";
  echo "Conector Seleccionado: " . htmlspecialchars($conectorSeleccionado) . "\n";
  echo "Datos: "; print_r($datosConectores);
  echo "</pre>";
  ?>
  
  <canvas id="graficoEstadosPersonal" 
        width="400" 
        height="400"
        data-conector="<?= htmlspecialchars($conectorSeleccionado, ENT_QUOTES) ?>"
        data-estados='<?= json_encode($datosConectores) ?>'></canvas>
</div>

<!-- No necesitamos definir variables globales, usamos atributos de datos -->
<script>
// Este script se ejecutará inmediatamente
(function() {
  // El inicializador puede ser llamado después, pero ya tendrá estos datos
  const inicializar = function() {
    const canvas = document.getElementById('graficoEstadosPersonal');
    if (!canvas) return;
    
    const conector = canvas.getAttribute('data-conector') || '';
    const datos = canvas.getAttribute('data-estados') || '{}';
    
    try {
      const estadosParsed = JSON.parse(datos);
      
      // Solo llamar al inicializador si no se ha ejecutado ya
      if (typeof window.inicializarEstadisticasPersonalesConDatos === 'function') {
        window.inicializarEstadisticasPersonalesConDatos(conector, estadosParsed);
      }
    } catch(e) {
      console.error("Error parseando datos de estados:", e);
    }
  };
  
  // Si el documento ya está listo, ejecutamos ahora
  if (document.readyState === 'complete' || document.readyState === 'interactive') {
    setTimeout(inicializar, 0);
  } else {
    // Si no, esperamos a que esté listo
    document.addEventListener('DOMContentLoaded', inicializar);
  }
})();
</script>
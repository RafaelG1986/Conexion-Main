<?php
// filepath: c:\xampp\htdocs\conexion-main\conexion-main\frontend\views\filtrar_base.php
require_once __DIR__ . '/../../backend/config/database.php';

// Inicializar las variables
$estados_lista = [];
$conectores_lista = [];

try {
    $db = new Database();
    $conn = $db->connect();
    
    // Para los selectores de filtros, obtener estados y conectores únicos
    $stmt_estados = $conn->prepare("SELECT DISTINCT estado FROM registros ORDER BY estado");
    $stmt_estados->execute();
    $estados_lista = $stmt_estados->fetchAll(PDO::FETCH_COLUMN);
    
    $stmt_conectores = $conn->prepare("SELECT DISTINCT nombre_conector FROM registros ORDER BY nombre_conector");
    $stmt_conectores->execute();
    $conectores_lista = $stmt_conectores->fetchAll(PDO::FETCH_COLUMN);
    
} catch (PDOException $e) {
    $error = "Error en la conexión: " . $e->getMessage();
}
?>

<link rel="stylesheet" href="../css/styles_filtrar_base.css">

<div class="filter-container">
    <div class="filter-header">
        <h2><i class="fas fa-filter"></i> Filtrar Base de Datos</h2>
        
        <!-- Botón para volver al inicio -->
        <button type="button" class="btn-home" onclick="mostrarInicio()">
            <i class="fas fa-home"></i> Volver al Inicio
        </button>
    </div>
    
    <form id="filtro-form">
        <div class="filter-form">
            <div class="filter-group">
                <label for="nombre">Nombre</label>
                <input type="text" id="nombre" name="nombre" placeholder="Buscar por nombre">
            </div>
            
            <div class="filter-group">
                <label for="apellido">Apellido</label>
                <input type="text" id="apellido" name="apellido" placeholder="Buscar por apellido">
            </div>
            
            <div class="filter-group">
                <label for="estado">Estado</label>
                <select id="estado" name="estado">
                    <option value="">Todos los estados</option>
                    <?php foreach ($estados_lista as $estado_opcion): ?>
                    <option value="<?php echo htmlspecialchars($estado_opcion); ?>">
                        <?php echo htmlspecialchars($estado_opcion); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="filter-group">
                <label for="conector">Conector</label>
                <select id="conector" name="conector">
                    <option value="">Todos los conectores</option>
                    <?php foreach ($conectores_lista as $conector_opcion): ?>
                    <option value="<?php echo htmlspecialchars($conector_opcion); ?>">
                        <?php echo htmlspecialchars($conector_opcion); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="filter-group">
                <label for="fecha_desde">Fecha Desde</label>
                <input type="date" id="fecha_desde" name="fecha_desde">
            </div>
            
            <div class="filter-group">
                <label for="fecha_hasta">Fecha Hasta</label>
                <input type="date" id="fecha_hasta" name="fecha_hasta">
            </div>
        </div>
        
        <div class="filter-actions">
            <button type="button" class="btn-reset" id="btn-limpiar"><i class="fas fa-eraser"></i> Limpiar Filtros</button>
            <button type="button" class="btn-filter" id="btn-aplicar"><i class="fas fa-search"></i> Aplicar Filtros</button>
        </div>
    </form>
    
    <!-- Contenedor para los resultados -->
    <div id="resultados-container" style="display:none;">
        <div class="results-header">
            <h3><i class="fas fa-list"></i> Resultados</h3>
            <button type="button" class="btn-back" id="btn-volver"><i class="fas fa-arrow-left"></i> Volver a Filtros</button>
        </div>
        <div id="resultados-filtro">
            <!-- Aquí se cargarán los resultados -->
        </div>
    </div>
</div>
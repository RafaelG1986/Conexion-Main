<?php
// filepath: c:\xampp\htdocs\conexion-main\conexion-main\frontend\views\resultados_filtro.php
require_once __DIR__ . '/../../backend/config/database.php';

// Inicializar variables
$filtros = [];
$parametros = [];
$consulta_base = "SELECT * FROM registros WHERE 1=1";
$query_where = "";
$total_registros = 0;
$registros = [];
$error = "";

// Capturar parámetros de la URL para mantener los filtros
$nombre = isset($_GET['nombre']) ? $_GET['nombre'] : '';
$apellido = isset($_GET['apellido']) ? $_GET['apellido'] : '';
$estado = isset($_GET['estado']) ? $_GET['estado'] : '';
$conector = isset($_GET['conector']) ? $_GET['conector'] : '';
$fecha_desde = isset($_GET['fecha_desde']) ? $_GET['fecha_desde'] : '';
$fecha_hasta = isset($_GET['fecha_hasta']) ? $_GET['fecha_hasta'] : '';
$proximo_desde = isset($_GET['proximo_desde']) ? $_GET['proximo_desde'] : '';
$proximo_hasta = isset($_GET['proximo_hasta']) ? $_GET['proximo_hasta'] : '';
$contactos_hoy = isset($_GET['contactos_hoy']) && $_GET['contactos_hoy'] == '1';
$contactos_pendientes = isset($_GET['contactos_pendientes']) && $_GET['contactos_pendientes'] == '1';

try {
    $db = new Database();
    $conn = $db->connect();
    
    // Procesar filtros
    if (!empty($_GET['nombre'])) {
        $query_where .= " AND nombre_persona LIKE :nombre";
        $parametros[':nombre'] = '%' . $_GET['nombre'] . '%';
        $filtros[] = "Nombre: " . htmlspecialchars($_GET['nombre']);
    }
    
    if (!empty($_GET['apellido'])) {
        $query_where .= " AND apellido_persona LIKE :apellido";
        $parametros[':apellido'] = '%' . $_GET['apellido'] . '%';
        $filtros[] = "Apellido: " . htmlspecialchars($_GET['apellido']);
    }
    
    if (!empty($_GET['estado'])) {
        $query_where .= " AND estado = :estado";
        $parametros[':estado'] = $_GET['estado'];
        $filtros[] = "Estado: " . htmlspecialchars($_GET['estado']);
    }
    
    if (!empty($_GET['conector'])) {
        $query_where .= " AND nombre_conector LIKE :conector";
        $parametros[':conector'] = '%' . $_GET['conector'] . '%';
        $filtros[] = "Conector: " . htmlspecialchars($_GET['conector']);
    }
    
    if (!empty($_GET['fecha_desde'])) {
        $query_where .= " AND fecha_contacto >= :fecha_desde";
        $parametros[':fecha_desde'] = $_GET['fecha_desde'];
        $filtros[] = "Desde: " . date('d/m/Y', strtotime($_GET['fecha_desde']));
    }
    
    if (!empty($_GET['fecha_hasta'])) {
        $query_where .= " AND fecha_contacto <= :fecha_hasta";
        $parametros[':fecha_hasta'] = $_GET['fecha_hasta'];
        $filtros[] = "Hasta: " . date('d/m/Y', strtotime($_GET['fecha_hasta']));
    }
    
    if (!empty($_GET['proximo_desde'])) {
        $query_where .= " AND proximo_contacto >= :proximo_desde";
        $parametros[':proximo_desde'] = $_GET['proximo_desde'];
        $filtros[] = "Próximo contacto desde: " . date('d/m/Y', strtotime($_GET['proximo_desde']));
    }

    if (!empty($_GET['proximo_hasta'])) {
        $query_where .= " AND proximo_contacto <= :proximo_hasta";
        $parametros[':proximo_hasta'] = $_GET['proximo_hasta'];
        $filtros[] = "Próximo contacto hasta: " . date('d/m/Y', strtotime($_GET['proximo_hasta']));
    }

    if ($contactos_hoy) {
        $query_where .= " AND proximo_contacto = CURRENT_DATE()";
        $filtros[] = "Contactos programados para hoy";
    }

    if ($contactos_pendientes) {
        $query_where .= " AND proximo_contacto BETWEEN CURRENT_DATE() AND DATE_ADD(CURRENT_DATE(), INTERVAL 7 DAY)";
        $filtros[] = "Contactos pendientes (próximos 7 días)";
    }
    
    // Obtener el total de registros sin filtrar para estadísticas
    $stmt_total = $conn->prepare("SELECT COUNT(*) FROM registros");
    $stmt_total->execute();
    $total_base = $stmt_total->fetchColumn();
    
    // Construir la consulta final con los filtros
    $consulta_final = $consulta_base . $query_where . " ORDER BY fecha_contacto DESC LIMIT 500";
    
    // Ejecutar la consulta
    $stmt = $conn->prepare($consulta_final);
    foreach ($parametros as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->execute();
    $registros = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Contar resultados filtrados
    $total_registros = count($registros);
    
} catch (PDOException $e) {
    $error = $e->getMessage();
}

// Estados y colores
$colores = [
    'Primer contacto' => 'background:#ffcccc; color:#a00;',
    'Conectado' => 'background:#ffd6cc; color:#b36b00;',
    'No confirmado a desayuno' => 'background:#ffe5cc; color:#b36b00;',
    'Confirmado a Desayuno' => 'background:#cce0ff; color:#00509e;',
    'Desayuno Asistido' => 'background:#cce6ff; color:#00509e;',
    'Congregado sin desayuno' => 'background:#d4edda; color:#155724;',
    'Visitante' => 'background:#fff; color:#222;',
    'No interesado' => 'background:#ffdddd; color:#a00;',
    'Por Validar Estado' => 'background:#ffe5b4; color:#b36b00;'
];
?>

<!-- Estilos para los botones de acción -->
<style>
    /* Estilos mejorados para los botones de acción */
    .btn-accion {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        border: none;
        margin: 0 4px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s ease;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        cursor: pointer;
        background-color: #f8f9fa;
        color: #495057;
    }
    
    /* Botón Ver detalles */
    .btn-accion[title="Ver detalles"] {
        background-color: #e3f2fd;
        color: #0d6efd;
    }
    
    .btn-accion[title="Ver detalles"]:hover {
        background-color: #cfe2ff;
        transform: translateY(-3px);
        box-shadow: 0 4px 8px rgba(13, 110, 253, 0.25);
    }
    
    /* Botón Editar */
    .btn-accion[title="Editar"] {
        background-color: #e7f7ee;
        color: #28a745;
    }
    
    .btn-accion[title="Editar"]:hover {
        background-color: #d4f2e2;
        transform: translateY(-3px);
        box-shadow: 0 4px 8px rgba(40, 167, 69, 0.25);
    }
    
    /* Iconos dentro de los botones */
    .btn-accion i {
        font-size: 16px;
    }
    
    /* Efecto de clic */
    .btn-accion:active {
        transform: translateY(1px);
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.2);
    }
    
    /* Celda de acciones */
    .table-responsive table td:last-child {
        text-align: center;
        white-space: nowrap;
        min-width: 100px;
    }
</style>

<?php if (!empty($filtros)): ?>
<div class="applied-filters">
    <div class="filters-title">Filtros aplicados:</div>
    <div class="filters-list">
        <?php foreach ($filtros as $filtro): ?>
            <span class="filter-tag"><?php echo $filtro; ?></span>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<div class="filter-results-info">
    <div class="results-count">
        Mostrando <?php echo $total_registros; ?> de <?php echo $total_base; ?> registros totales
        <?php if ($total_registros > 0 && $total_registros < $total_base): ?>
            (<?php echo round(($total_registros / $total_base) * 100); ?>%)
        <?php endif; ?>
    </div>
    <div class="export-actions">
        <button class="btn-export excel" id="btn-excel" title="Exportar a Excel"><i class="fas fa-file-excel"></i> Excel</button>
        <button class="btn-export pdf" id="btn-pdf" title="Exportar a PDF"><i class="fas fa-file-pdf"></i> PDF</button>
        <button class="btn-export print" id="btn-print" title="Imprimir resultados"><i class="fas fa-print"></i> Imprimir</button>
    </div>
</div>

<?php if (!empty($error)): ?>
<div class="error-message">
    <i class="fas fa-exclamation-triangle"></i> Error al procesar la consulta: <?php echo htmlspecialchars($error); ?>
</div>
<?php endif; ?>

<?php if (empty($registros) && empty($error)): ?>
<div class="no-results">
    <i class="fas fa-search"></i>
    <p>No se encontraron registros que coincidan con los criterios de búsqueda.</p>
</div>
<?php endif; ?>

<?php if (!empty($registros)): ?>
<div class="table-responsive">
    <table class="stats-table">
        <thead>
            <tr>
                <th>Nombre</th>
                <th>Apellido</th>
                <th>Teléfono</th>
                <th>Estado</th>
                <th>Conector</th>
                <th>Fecha Contacto</th>
                <th>Próximo Contacto</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($registros as $registro): ?>
            <tr>
                <td><?php echo htmlspecialchars($registro['nombre_persona']); ?></td>
                <td><?php echo htmlspecialchars($registro['apellido_persona']); ?></td>
                <td><?php echo htmlspecialchars($registro['telefono'] ?? 'No disponible'); ?></td>
                <td>
                    <span class="estado-badge" style="
                        display: inline-block;
                        padding: 5px 10px;
                        border-radius: 4px;
                        font-size: 13px;
                        font-weight: 600;
                        <?php echo $colores[$registro['estado']] ?? 'background:#f8f9fa; color:#7f8c8d;'; ?>
                    ">
                        <?php echo htmlspecialchars($registro['estado']); ?>
                    </span>
                </td>
                <td><?php echo htmlspecialchars($registro['nombre_conector']); ?></td>
                <td><?php echo date('d/m/Y', strtotime($registro['fecha_contacto'])); ?></td>
                <td>
                    <?php if (!empty($registro['proximo_contacto'])): ?>
                        <?php 
                            $fecha_programada = new DateTime($registro['proximo_contacto']);
                            $hoy = new DateTime('today');
                            
                            echo date('d/m/Y', strtotime($registro['proximo_contacto']));
                            
                            if ($fecha_programada < $hoy) {
                                echo ' <span class="badge-vencido" title="Vencido">!</span>';
                            } elseif ($hoy->diff($fecha_programada)->days <= 2) {
                                echo ' <span class="badge-urgente" title="Próximo">!</span>';
                            }
                        ?>
                    <?php else: ?>
                        <span class="text-muted">-</span>
                    <?php endif; ?>
                </td>
                <td>
                    <button type="button" class="btn-accion" title="Ver detalles" 
                            onclick="window.parent.cargarRegistro(<?php echo $registro['id']; ?>, false)">
                        <i class="fas fa-search"></i>
                    </button>
                    <button type="button" class="btn-accion" title="Editar" 
                            onclick="window.parent.cargarRegistro(<?php echo $registro['id']; ?>, true)">
                        <i class="fas fa-edit"></i>
                    </button>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>
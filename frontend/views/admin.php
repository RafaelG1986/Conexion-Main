<?php
// filepath: c:\xampp\htdocs\conexion-main\conexion-main\frontend\views\admin.php
session_start();

if (!isset($_SESSION['user'])) {
    header('Location: ../../index.html');
    exit;
}

$user = $_SESSION['user'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Usuario</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../css/styles_admin.css">
    <link rel="stylesheet" href="../css/styles_agregar_registro.css">
    <script src="../js/modal_util.js"></script>
    <script src="../js/estados.js"></script>
    <script src="../js/observaciones.js"></script>
    <script src="../js/eliminar_registro.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.2/dist/chart.umd.min.js"></script>
    <script src="../js/estadisticas.js"></script>
    <script src="../js/estadisticas_personales.js"></script>
    <script src="../js/conector.js"></script>
    <script src="../js/filtrar.js"></script>
    <script src="https://d3js.org/d3.v7.min.js"></script>
    <link rel="stylesheet" href="../css/alertas.css">
    <link rel="stylesheet" href="../css/styles_modal_observaciones.css">
    <script src="../js/ver_observaciones.js"></script>
</head>
<body>
    <div id="debug-info" style="position:fixed; bottom:0; right:0; background:rgba(0,0,0,0.7); color:white; padding:10px; z-index:9999;"></div>

    <div class="layout">
        <div class="sidebar">
            <div class="sidebar-header">
                <img src="../img/logoConexion.png" alt="Logo de la aplicación">
                <h2>Panel del Usuario</h2>
            </div>
            
            <div class="user-info">
                <p><i class="fas fa-user"></i> Bienvenido, <?php echo htmlspecialchars($user['nombre'], ENT_QUOTES, 'UTF-8'); ?></p>
            </div>
            
            <div class="sidebar-menu">
                <a href="#" onclick="mostrarInicio();return false;"><i class="fas fa-home"></i> Inicio</a>
                <a href="#" onclick="cargarVista('vista_registros.php');return false;"><i class="fas fa-database"></i> Base de Datos General</a>
                <a href="#" onclick="cargarVista('vista_estadisticas.php');return false;"><i class="fas fa-chart-bar"></i> Estadísticas Generales</a>
                <a href="#" onclick="cargarVista('agregar_registro.php');return false;"><i class="fas fa-plus-circle"></i> Crear Nuevo Registro</a>
                <a href="#" onclick="cargarVista('vista_estadisticas_personales.php');return false;"><i class="fas fa-chart-line"></i> Estadísticas Personales</a>
                <a href="#" onclick="cargarVista('base_datos_personal.php');return false;"><i class="fas fa-table"></i> Base de Datos Personal</a>
                <!-- Modificado: Cambiado para usar cargarVista como los demás enlaces -->
                <a href="#" onclick="cargarVista('filtrar_base.php');return false;"><i class="fas fa-filter"></i> Filtrar Base</a>
                <a href="#" onclick="cargarVista('vista_informes.php');return false;"><i class="fas fa-file-alt"></i> Generar Informes</a>
                <a href="../../backend/controllers/logout.php" class="logout"><i class="fas fa-sign-out-alt"></i> Cerrar Sesión</a>
            </div>
        </div>
        
        <div class="main-content centrado">
            <div id="contenido-dinamico">
                <h1>Bienvenido al Panel de Control</h1>
                <p>Selecciona una opción del menú lateral para comenzar.</p>
                
                <div class="welcome-grid">
                    <div class="welcome-card" onclick="cargarVista('vista_registros.php')">
                        <i class="fas fa-database"></i>
                        <h3>Base de Datos</h3>
                        <p>Gestiona todos los registros y contactos</p>
                    </div>
                    
                    <div class="welcome-card" onclick="cargarVista('vista_estadisticas.php')">
                        <i class="fas fa-chart-bar"></i>
                        <h3>Estadísticas</h3>
                        <p>Visualiza métricas y tendencias</p>
                    </div>
                    
                    <div class="welcome-card" onclick="cargarVista('agregar_registro.php')">
                        <i class="fas fa-plus-circle"></i>
                        <h3>Nuevo Registro</h3>
                        <p>Añade nuevos contactos al sistema</p>
                    </div>
                    
                    <div class="welcome-card" onclick="cargarVista('vista_informes.php')">
                        <i class="fas fa-file-alt"></i>
                        <h3>Informes</h3>
                        <p>Genera y exporta informes personalizados</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div id="modal-observaciones" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fas fa-comment-alt"></i> Agregar observación</h3>
                <span class="close" onclick="cerrarModal('modal-observaciones')">&times;</span>
            </div>
            <div class="modal-body">
                <input type="hidden" id="id-registro" value="">
                <div class="form-group">
                    <label for="texto-observacion">Nueva observación:</label>
                    <textarea id="texto-observacion" rows="4" class="form-control" placeholder="Escribe aquí tu observación..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button id="btn-guardar-obs" class="btn btn-primary">
                    <i class="fas fa-save"></i> Guardar
                </button>
                <button type="button" onclick="cerrarModal('modal-observaciones')" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Cancelar
                </button>
            </div>
        </div>
    </div>
    
    <!-- Modal para ver observaciones - Agregar en admin.php -->
    <div id="modal-ver-observaciones" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Historial de observaciones</h3>
                <span class="close" onclick="cerrarModalObservaciones()">&times;</span>
            </div>
            <div class="modal-body">
                <div id="observaciones-contenido" class="observaciones-historial">
                    <div class="cargando">Cargando observaciones...</div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" onclick="cerrarModalObservaciones()" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Cerrar
                </button>
            </div>
        </div>
    </div>
    
    <div id="overlay-observaciones" style="position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.7); z-index:9999; display:none;"></div>
    
    <div class="modal-overlay" id="modal-overlay"></div>
    
    <script>
    // FUNCIONES DE NAVEGACIÓN
    function cargarVista(ruta) {
        // Guardar la ruta actual para posibles recargas
        window.vistaActual = ruta;
        
        // Quitar clase active de todos los enlaces
        document.querySelectorAll('.sidebar a').forEach(link => {
            link.classList.remove('active');
        });
        
        // Añadir clase active al enlace correspondiente
        document.querySelector(`.sidebar a[onclick*="${ruta}"]`)?.classList.add('active');
        
        fetch(ruta)
            .then(res => res.text())
            .then(html => {
                document.getElementById('contenido-dinamico').innerHTML = html;
                document.querySelector('.main-content').classList.remove('centrado');
                
                // Inicializar el módulo correspondiente
                inicializarModulo(ruta);
            })
            .catch(error => {
                console.error('Error cargando vista:', error);
                document.getElementById('contenido-dinamico').innerHTML = 
                    '<div class="alert alert-danger">Error cargando la vista. Inténtalo de nuevo.</div>';
            });
    }

    // Función para inicializar los diferentes módulos según la ruta
    function inicializarModulo(ruta) {
        // Estadísticas generales
        if (ruta === 'vista_estadisticas.php') {
            if (typeof inicializarEstadisticas === 'function') {
                console.log('Inicializando estadísticas generales');
                inicializarEstadisticas();
            }
        } 
        // Estadísticas personales
        else if (ruta.includes('vista_estadisticas_personales.php')) {
            if (typeof inicializarEstadisticasPersonales === 'function') {
                console.log('Inicializando estadísticas personales');
                inicializarEstadisticasPersonales();
            }
        } 
        // Base de datos personal
        else if (ruta === 'base_datos_personal.php') {
            if (typeof inicializarConector === 'function') {
                console.log('Inicializando conector');
                inicializarConector();
            }
        } 
        // Filtrar base
        else if (ruta === 'filtrar_base.php') {
            if (typeof inicializarFiltro === 'function') {
                console.log('Inicializando filtro');
                inicializarFiltro();
            }
        }
    }

    function mostrarInicio() {
        // Quitar clase active de todos los enlaces
        document.querySelectorAll('.sidebar a').forEach(link => {
            link.classList.remove('active');
        });
        
        // Añadir clase active al enlace de inicio
        document.querySelector('.sidebar a:first-child').classList.add('active');
        
        document.querySelector('.main-content').classList.add('centrado');
        document.getElementById('contenido-dinamico').innerHTML = `
            <h1>Bienvenido al Panel de Control</h1>
            <p>Selecciona una opción del menú lateral para comenzar.</p>
            
            <div class="welcome-grid">
                <div class="welcome-card" onclick="cargarVista('vista_registros.php')">
                    <i class="fas fa-database"></i>
                    <h3>Base de Datos</h3>
                    <p>Gestiona todos los registros y contactos</p>
                </div>
                
                <div class="welcome-card" onclick="cargarVista('vista_estadisticas.php')">
                    <i class="fas fa-chart-bar"></i>
                    <h3>Estadísticas</h3>
                    <p>Visualiza métricas y tendencias</p>
                </div>
                
                <div class="welcome-card" onclick="cargarVista('agregar_registro.php')">
                    <i class="fas fa-plus-circle"></i>
                    <h3>Nuevo Registro</h3>
                    <p>Añade nuevos contactos al sistema</p>
                </div>
                
                <div class="welcome-card" onclick="cargarVista('vista_informes.php')">
                    <i class="fas fa-file-alt"></i>
                    <h3>Informes</h3>
                    <p>Genera y exporta informes personalizados</p>
                </div>
            </div>
        `;
    }
    
    // Marcar como activo el enlace de inicio al cargar la página
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelector('.sidebar a:first-child').classList.add('active');
    });

    // Función para logging
    function log(mensaje) {
        console.log(mensaje);
        const debug = document.getElementById('debug-info');
        if (debug) {
            debug.innerHTML += "<div>" + mensaje + "</div>";
            if (debug.children.length > 10) {
                debug.removeChild(debug.firstChild);
            }
        }
    }
    </script>

   
</body>
</html>
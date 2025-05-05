<?php
// filepath: c:\xampp\htdocs\conexion-main\conexion-main\frontend\views\admin.php
session_start();

if (!isset($_SESSION['user'])) {
    header('Location: ../../index.html');
    exit;
}

$user = $_SESSION['user'];
$_SESSION['user_id'] = $user['id']; // Para compatibilidad con el chat
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
    <script src="../js/informes.js"></script>
    <link rel="stylesheet" href="../css/styles_chat.css">
    <script src="../js/chat.js"></script>
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
    
    <!-- Chat sidebar -->
    <div class="chat-sidebar" id="chat-sidebar">
        <div class="chat-header">
            <h2>Mensajes</h2>
            <div class="chat-controls">
                <button id="new-chat-btn" class="btn-new-chat"><i class="fas fa-plus"></i></button>
                <button id="minimize-chat-sidebar" class="btn-control"><i class="fas fa-minus"></i></button>
                <button id="close-chat-sidebar" class="btn-control"><i class="fas fa-times"></i></button>
            </div>
        </div>
        
        <div class="search-chat">
            <input type="text" id="buscar-conversaciones" placeholder="Buscar conversaciones...">
        </div>
        
        <div class="conversations-list">
            <!-- Estado de vacío -->
            <div class="empty-state" id="empty-conversations">
                <p>No tienes conversaciones activas</p>
                <p>Haz clic en <i class="fas fa-plus"></i> para iniciar una nueva</p>
            </div>
            <!-- Aquí se cargarán las conversaciones -->
        </div>
    </div>

    <!-- Ventana de chat -->
    <div id="chat-window" class="chat-window">
        <div class="chat-window-header">
            <div class="chat-avatar-container">
                <img src="..." class="chat-avatar" alt="">
            </div>
            <div class="chat-username"></div>
            <div class="chat-controls">
                <button id="minimize-chat" class="btn-control"><i class="fas fa-minus"></i></button>
                <button id="close-chat" class="btn-control"><i class="fas fa-times"></i></button>
            </div>
        </div>
        
        <div class="chat-messages">
            <!-- Los mensajes se cargarán aquí dinámicamente -->
        </div>
        
        <div class="chat-input">
            <textarea placeholder="Escribe un mensaje..."></textarea>
            <button id="send-message"><i class="fas fa-paper-plane"></i></button>
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
        // Informes
        else if (ruta === 'vista_informes.php') {
            if (typeof inicializarInformes === 'function') {
                console.log('Inicializando módulo de informes');
                inicializarInformes();
            }
        }
    }

    // Nueva función para cargar módulo con espera
    function cargarModulo(ruta) {
        // Código existente de carga...
        
        // Añade esta parte después de cargar la vista de informes
        if (ruta === 'vista_informes.php') {
            // Esperar un momento para que el DOM se actualice
            setTimeout(function() {
                if (typeof inicializarInformes === 'function') {
                    inicializarInformes();
                } else {
                    console.error('La función inicializarInformes no está disponible');
                }
            }, 100);
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

    // Añade esta función en la sección de scripts de admin.php
    function actualizarEstado(estado, id) {
        console.log('Actualizando estado:', estado, id);
        
        const formData = new FormData();
        formData.append('id', id);
        formData.append('estado', estado);
        
        fetch('../../backend/controllers/actualizar_estado.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Estado actualizado correctamente');
            } else {
                alert('Error al actualizar: ' + (data.message || 'Error desconocido'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error de comunicación con el servidor');
        });
    }

    document.addEventListener('DOMContentLoaded', function() {
        // Seleccionar todos los selectores de estado
        document.querySelectorAll('.selector-estado').forEach(select => {
            select.addEventListener('change', function() {
                const id = this.dataset.id;  // Obtener el ID del atributo data-id
                const estado = this.value;
                
                actualizarEstado(estado, id);
            });
        });
    });

    // Agregar después del último script en admin.php
    document.addEventListener('DOMContentLoaded', function() {
        // Inicializar chat
        if (typeof inicializarChat === 'function') {
            inicializarChat();
        }
        
        // Configurar botón toggle para chat
        const chatToggleBtn = document.getElementById('chat-toggle-button');
        const chatSidebar = document.getElementById('chat-sidebar');
        
        chatToggleBtn.addEventListener('click', function() {
            chatSidebar.classList.toggle('active');
        });
    });

    // Función para cargar ver_registro.php dinámicamente
    function cargarRegistro(id, editar = false) {
        // Determinar la URL con los parámetros necesarios
        const url = `ver_registro.php?id=${id}${editar ? '&editar=1' : ''}&ajax=1`;
        
        // Guardar la vista actual para poder volver después
        window.vistaAnterior = window.vistaActual || 'vista_registros.php';
        
        // Mostrar indicador de carga
        document.getElementById('contenido-dinamico').innerHTML = 
            '<div class="cargando-contenido"><i class="fas fa-spinner fa-spin"></i> Cargando registro...</div>';
        
        // Realizar la petición fetch
        fetch(url)
            .then(res => res.text())
            .then(html => {
                // Inyectar el contenido en el contenedor dinámico
                document.getElementById('contenido-dinamico').innerHTML = html;
                
                // Añadir una barra de navegación para volver
                const navBar = document.createElement('div');
                navBar.className = 'registro-nav-bar';
                navBar.innerHTML = `
                    <button onclick="cargarVista('${window.vistaAnterior}')" class="btn-volver">
                        <i class="fas fa-arrow-left"></i> Volver al listado
                    </button>
                    <h3>${editar ? 'Editar Registro' : 'Detalles del Registro'}</h3>
                `;
                
                // Insertar la barra de navegación al inicio del contenido
                const contenido = document.getElementById('contenido-dinamico');
                contenido.insertBefore(navBar, contenido.firstChild);
                
                // Cargar el CSS específico de ver_registro si no está ya cargado
                if (!document.querySelector('link[href*="styles_ver_registro.css"]')) {
                    const link = document.createElement('link');
                    link.rel = 'stylesheet';
                    link.href = '../css/styles_ver_registro.css';
                    document.head.appendChild(link);
                }
                
                // Inicializar cualquier JS necesario para el formulario
                inicializarFormularioRegistro();
            })
            .catch(error => {
                console.error('Error cargando el registro:', error);
                document.getElementById('contenido-dinamico').innerHTML = 
                    `<div class="error-mensaje">Error al cargar el registro: ${error.message}</div>
                    <button onclick="cargarVista('${window.vistaAnterior}')" class="btn-volver">
                        <i class="fas fa-arrow-left"></i> Volver al listado
                    </button>`;
            });
    }

    // Función para inicializar el formulario del registro
    function inicializarFormularioRegistro() {
        // Capturar formularios para envío AJAX
        const formularios = document.querySelectorAll('#contenido-dinamico form');
        
        formularios.forEach(form => {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                
                // Mostrar indicador de carga
                const submitBtn = form.querySelector('button[type="submit"]');
                const originalText = submitBtn ? submitBtn.innerHTML : '';
                if (submitBtn) {
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Guardando...';
                }
                
                // Enviar formulario con AJAX
                fetch(this.action, {
                    method: 'POST',
                    body: new FormData(this),
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.text())
                .then(data => {
                    try {
                        // Intentar parsear como JSON
                        const jsonData = JSON.parse(data);
                        if (jsonData.success) {
                            alert('Registro actualizado correctamente');
                            cargarVista(window.vistaAnterior || 'vista_registros.php');
                        } else {
                            alert('Error: ' + (jsonData.message || 'No se pudo actualizar el registro'));
                        }
                    } catch (e) {
                        // Si no es JSON, mostrar el texto devuelto
                        if (data.includes('correctamente')) {
                            alert('Registro actualizado correctamente');
                            cargarVista(window.vistaAnterior || 'vista_registros.php');
                        } else {
                            alert('Error: ' + data);
                        }
                    }
                    
                    // Restaurar botón
                    if (submitBtn) {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = originalText;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error de conexión. Intenta nuevamente.');
                    
                    // Restaurar botón
                    if (submitBtn) {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = originalText;
                    }
                });
            });
        });
    }

    <!-- Añade esto al final antes de </body> -->
    document.addEventListener('DOMContentLoaded', function() {
        console.log('Verificando elementos del chat:');
        
        // Verificar botones críticos
        const btnCerrar = document.getElementById('close-chat');
        if (btnCerrar) {
            console.log('✓ Botón cerrar encontrado');
            // Asegurar que tenga evento click
            btnCerrar.addEventListener('click', function() {
                console.log('Click en botón cerrar');
                const chatWindow = document.getElementById('chat-window');
                if (chatWindow) {
                    chatWindow.style.display = 'none';
                    console.log('Chat cerrado manualmente');
                }
            });
        } else {
            console.error('✗ Botón cerrar NO encontrado');
        }
        
        // Verificar ventana de chat
        const chatWindow = document.getElementById('chat-window');
        if (chatWindow) {
            console.log('✓ Ventana chat encontrada, display:', chatWindow.style.display);
        } else {
            console.error('✗ Ventana chat NO encontrada');
        }
    });
    </script>

    <!-- Modal de usuarios conectados -->
    <div id="modal-usuarios-online" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Usuarios conectados</h2>
                <span class="close-modal">&times;</span>
            </div>
            <div class="modal-body">
                <div id="lista-usuarios-online">
                    <p class="cargando">Cargando usuarios...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button id="btn-limpiar-sesiones" class="btn-limpiar"><i class="fas fa-broom"></i> Limpiar</button>
                <button id="btn-refresh-usuarios" class="btn-refresh"><i class="fas fa-sync-alt"></i> Actualizar</button>
                <button class="btn-cerrar">Cerrar</button>
            </div>
        </div>
    </div>

    <!-- Botón para abrir el modal de usuarios conectados -->
    <button id="btn-usuarios-online" class="floating-btn">
        <i class="fas fa-users"></i>
        <span id="contador-usuarios-online">0</span>
    </button>

    <script src="/Conexion-Main/frontend/js/usuarios-online.js"></script>
</body>
</html>
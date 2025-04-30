// Sistema de monitoreo de usuarios conectados

function inicializarUsuariosOnline() {
    const modal = document.getElementById('modal-usuarios-online');
    const btnAbrir = document.getElementById('btn-usuarios-online');
    const btnCerrar = document.querySelector('.btn-cerrar');
    const closeBtn = document.querySelector('.close-modal');
    const btnRefresh = document.getElementById('btn-refresh-usuarios');
    const listaUsuarios = document.getElementById('lista-usuarios-online');
    const contadorUsuarios = document.getElementById('contador-usuarios-online');
    
    let intervaloActualizacion = null;
    
    // Abrir modal
    btnAbrir.addEventListener('click', () => {
        modal.style.display = 'block';
        cargarUsuariosOnline();
    });
    
    // Cerrar modal
    btnCerrar.addEventListener('click', () => {
        modal.style.display = 'none';
    });
    
    closeBtn.addEventListener('click', () => {
        modal.style.display = 'none';
    });
    
    window.addEventListener('click', (e) => {
        if (e.target === modal) {
            modal.style.display = 'none';
        }
    });
    
    // Actualizar manualmente
    btnRefresh.addEventListener('click', cargarUsuariosOnline);

    const btnLimpiar = document.getElementById('btn-limpiar-sesiones');
    if (btnLimpiar) {
        btnLimpiar.addEventListener('click', () => {
            btnLimpiar.disabled = true;
            btnLimpiar.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Limpiando...';
            
            fetch('/Conexion-Main/backend/controllers/usuarios/limpiar_sesiones.php')
                .then(response => response.json())
                .then(data => {
                    cargarUsuariosOnline();
                    btnLimpiar.innerHTML = `<i class="fas fa-broom"></i> Limpiar (${data.limpiados})`;
                    setTimeout(() => {
                        btnLimpiar.disabled = false;
                        btnLimpiar.innerHTML = '<i class="fas fa-broom"></i> Limpiar';
                    }, 3000);
                })
                .catch(err => {
                    btnLimpiar.disabled = false;
                    btnLimpiar.innerHTML = '<i class="fas fa-broom"></i> Limpiar';
                });
        });
    }
    
    // Cargar usuarios online
    function cargarUsuariosOnline() {
        listaUsuarios.innerHTML = '<p class="cargando">Cargando usuarios...</p>';
        
        fetch('/Conexion-Main/backend/controllers/usuarios/usuarios_online.php')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    mostrarUsuariosOnline(data.usuarios);
                    actualizarContador(data.usuarios.length);
                } else {
                    listaUsuarios.innerHTML = '<p class="error">Error: ' + (data.error || 'Desconocido') + '</p>';
                }
            })
            .catch(error => {
                console.error('Error cargando usuarios online:', error);
                listaUsuarios.innerHTML = '<p class="error">Error de conexión</p>';
            });
    }
    
    // Mostrar usuarios en el modal
    function mostrarUsuariosOnline(usuarios) {
        if (!usuarios || usuarios.length === 0) {
            listaUsuarios.innerHTML = '<p>No hay usuarios conectados</p>';
            return;
        }
        
        const html = usuarios.map(u => `
            <div class="usuario-online">
                <div class="status-indicator ${u.online ? 'online' : 'offline'}"></div>
                <div class="usuario-info">
                    <div class="usuario-nombre">${sanitizarHTML(u.nombre)}</div>
                    <div class="usuario-tiempo">${formatearTiempo(u.tiempo_conexion)}</div>
                </div>
            </div>
        `).join('');
        
        listaUsuarios.innerHTML = html;
    }
    
    // Actualizar contador flotante
    function actualizarContador(cantidad) {
        contadorUsuarios.textContent = cantidad;
    }
    
    // Formatear tiempo de conexión
    function formatearTiempo(segundos) {
        if (segundos < 60) {
            return 'Hace unos segundos';
        } else if (segundos < 3600) {
            return `Hace ${Math.floor(segundos / 60)} min`;
        } else {
            return `Hace ${Math.floor(segundos / 3600)} h`;
        }
    }
    
    // Sanitizar HTML para prevenir XSS
    function sanitizarHTML(str) {
        return str ? String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;') : '';
    }
    
    // Actualizar cada 5 segundos en lugar de 15
    intervaloActualizacion = setInterval(() => {
        // Actualizar contador incluso si modal está cerrado
        fetch('/Conexion-Main/backend/controllers/usuarios/usuarios_online.php')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    actualizarContador(data.usuarios.length);
                    
                    // Si el modal está visible, actualizar la lista completa
                    if (modal.style.display === 'block') {
                        mostrarUsuariosOnline(data.usuarios);
                    }
                }
            })
            .catch(error => {
                console.error('Error actualizando usuarios online:', error);
            });
    }, 5000);  // 5 segundos en lugar de 15
    
    // Cargar usuarios al iniciar y actualizar contador
    cargarUsuariosOnline();
    
    // Limpiar intervalo al desmontar
    return function cleanup() {
        clearInterval(intervaloActualizacion);
    };
}

// Función de heartbeat para mantener la sesión activa
function iniciarHeartbeat() {
    // Enviar señal cada 30 segundos para indicar que el usuario sigue activo
    const heartbeatInterval = setInterval(() => {
        fetch('/Conexion-Main/backend/controllers/usuarios/heartbeat.php', {
            method: 'POST'
        }).catch(err => console.error('Error en heartbeat:', err));
    }, 30000);
    
    // Limpiar intervalo cuando la ventana se cierre
    window.addEventListener('beforeunload', () => {
        clearInterval(heartbeatInterval);
    });
}

// Función para enviar la señal de desconexión
function enviarDesconexion() {
    // Usar el método navigator.sendBeacon que es más confiable que XMLHttpRequest
    if (navigator.sendBeacon) {
        navigator.sendBeacon('/Conexion-Main/backend/controllers/usuarios/marcar_desconexion.php');
    } else {
        // Fallback al método anterior
        const xhr = new XMLHttpRequest();
        xhr.open('POST', '/Conexion-Main/backend/controllers/usuarios/marcar_desconexion.php', false);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.send();
    }
}

// Eventos para detectar cierre de página
window.addEventListener('beforeunload', enviarDesconexion);
window.addEventListener('unload', enviarDesconexion);
document.addEventListener('visibilitychange', function() {
    if (document.visibilityState === 'hidden') {
        enviarDesconexion();
    }
});

// Inicializar cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', () => {
    inicializarUsuariosOnline();
    iniciarHeartbeat();
});
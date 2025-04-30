// chat.js - Sistema de chat interno para la plataforma Conexión

function inicializarChat() {
    const chatContainer = document.getElementById('chat-container');
    const usuarioActual = chatContainer.dataset.usuarioId;
    let conversacionActiva = null;
    let intervaloActualizacion = null;

    // Elementos del DOM que se repiten
    const btnNuevoChat = document.getElementById('new-chat-btn');
    const btnEnviar = document.getElementById('send-message');
    const btnMinimizar = document.getElementById('minimize-chat');
    const btnCerrar = document.getElementById('close-chat');
    const textareaInput = document.querySelector('.chat-input textarea');
    const listaConvDOM = document.querySelector('.conversations-list');
    const chatWindowDOM = document.getElementById('chat-window');
    const mensajesDOM = document.querySelector('.chat-messages');
    const headerUserDOM = document.querySelector('.chat-window-header .chat-username');
    const avatarDOM = document.querySelector('.chat-window-header .chat-avatar');

    // Cargar conversaciones iniciales
    cargarConversaciones();

    // Configurar eventos
    if (btnNuevoChat) btnNuevoChat.addEventListener('click', mostrarNuevaConversacion);
    if (btnEnviar) btnEnviar.addEventListener('click', enviarMensaje);
    if (btnMinimizar) btnMinimizar.addEventListener('click', minimizarChat);
    if (btnCerrar) btnCerrar.addEventListener('click', cerrarChat);

    textareaInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            enviarMensaje();
        }
    });

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && chatWindowDOM && chatWindowDOM.style.display !== 'none') {
            cerrarChat();
        }
    });

    // Actualizar cada 10 segundos
    intervaloActualizacion = setInterval(() => {
        if (conversacionActiva) cargarMensajes(conversacionActiva);
        cargarConversaciones();
    }, 10000);

    // Funciones principales
    function cargarConversaciones() {
        fetch('/Conexion-Main/backend/controllers/chat/obtener_conversaciones.php')
            .then(res => {
                if (!res.ok) throw new Error('Status ' + res.status);
                const ct = res.headers.get('Content-Type') || '';
                if (!ct.includes('application/json')) {
                    return res.text().then(txt => {
                        throw new Error('Respuesta no JSON:\n' + txt);
                    });
                }
                return res.json();
            })
            .then(data => {
                if (data.success) renderizarConversaciones(data.conversaciones);
                else console.error('Error en API:', data.error);
            })
            .catch(err => {
                console.error('Error cargando conversaciones:', err);
                mostrarNotificacion('Error cargando conversaciones.', 'error');
            });
    }

    function renderizarConversaciones(conversaciones) {
        if (!listaConvDOM) return;
        if (!conversaciones || conversaciones.length === 0) {
            listaConvDOM.innerHTML = `
                <div class="no-conversations">
                  <p>No tienes conversaciones activas</p>
                  <p>Haz clic en <i class="fas fa-plus"></i> para iniciar una nueva</p>
                </div>`;
            return;
        }
        listaConvDOM.innerHTML = conversaciones.map(conv => {
            const unread = conv.mensajes_no_leidos > 0;
            return `
                <div class="conversation-item ${unread ? 'unread-conversation' : ''}" data-id="${conv.id}">
                  <img src="../img/${conv.foto || 'default-user.png'}" class="chat-avatar" alt="">
                  <div class="conversation-info">
                    <div class="conversation-name">${sanitizarHTML(conv.titulo)}</div>
                    <div class="conversation-last-message">${sanitizarHTML(conv.ultimo_mensaje) || 'Sin mensajes'}</div>
                  </div>
                  ${unread ? `<span class="unread-badge">${conv.mensajes_no_leidos}</span>` : ''}
                </div>`;
        }).join('');
        document.querySelectorAll('.conversation-item').forEach(item => {
            item.addEventListener('click', () => abrirConversacion(item.dataset.id));
        });
    }

    function abrirConversacion(conversacionId) {
        if (!conversacionId) return;
        conversacionActiva = conversacionId;
        chatWindowDOM.style.display = 'flex';
        cargarMensajes(conversacionId);
        // Marcar como leída
        fetch('/Conexion-Main/backend/controllers/chat/marcar_leido.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: `conversacion_id=${encodeURIComponent(conversacionId)}`
        }).catch(err => console.error('Error marcando leído:', err));
    }

    function cargarMensajes(conversacionId) {
        if (!conversacionId) return;
        fetch(`/Conexion-Main/backend/controllers/chat/obtener_mensajes.php?conversacion_id=${encodeURIComponent(conversacionId)}`)
            .then(res => res.ok ? res.json() : Promise.reject('Status ' + res.status))
            .then(data => {
                if (data.success) {
                    renderizarMensajes(data.mensajes);
                    actualizarInfoConversacion(data.conversacion);
                } else {
                    console.error('Error mensajes:', data.error);
                }
            })
            .catch(err => {
                console.error('Error cargando mensajes:', err);
                mostrarNotificacion('Error cargando mensajes', 'error');
            });
    }

    function renderizarMensajes(mensajes) {
        if (!mensajesDOM) return;
        if (!mensajes || mensajes.length === 0) {
            mensajesDOM.innerHTML = '<div class="no-messages">No hay mensajes. ¡Sé el primero!</div>';
            return;
        }
        let html = '';
        let ultimoRemitente = null;
        let ultimaFecha = null;

        mensajes.forEach(msg => {
            const esPropio = msg.remitente_id == usuarioActual;
            const fecha = new Date(msg.created_at);
            const fechaStr = fecha.toLocaleDateString();
            if (fechaStr !== ultimaFecha) {
                html += `<div class="date-separator">${fechaStr}</div>`;
                ultimaFecha = fechaStr;
                ultimoRemitente = null;
            }
            const nuevoGrupo = ultimoRemitente !== msg.remitente_id;
            if (nuevoGrupo) {
                if (ultimoRemitente !== null) html += '</div>';
                html += `<div class="message-group ${esPropio ? 'own-messages' : 'other-messages'}">`;
                if (!esPropio) html += `<div class="sender-name">${sanitizarHTML(msg.nombre_remitente||'Usuario')}</div>`;
            }
            html += `
                <div class="message ${esPropio? 'message-sent':'message-received'}">
                  <div class="message-content">${sanitizarHTML(msg.mensaje)}</div>
                  <div class="message-time">${formatearHora(fecha)}</div>
                </div>`;
            ultimoRemitente = msg.remitente_id;
        });
        if (ultimoRemitente !== null) html += '</div>';
        mensajesDOM.innerHTML = html;
        mensajesDOM.scrollTop = mensajesDOM.scrollHeight;
    }

    function actualizarInfoConversacion(conv) {
        if (!conv) return;
        if (headerUserDOM)  headerUserDOM.textContent = conv.titulo;
        if (avatarDOM) avatarDOM.src = `../img/${conv.foto|| 'default-user.png'}`;
    }

    function enviarMensaje() {
        const mensaje = textareaInput.value.trim();
        if (!mensaje || !conversacionActiva) return;
        mostrarIndicadorEnvio(true);
        fetch('/Conexion-Main/backend/controllers/chat/enviar_mensaje.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: `conversacion_id=${encodeURIComponent(conversacionActiva)}&mensaje=${encodeURIComponent(mensaje)}`
        })
        .then(res => res.ok ? res.json() : Promise.reject('Status ' + res.status))
        .then(data => {
            if (data.success) {
                textareaInput.value = '';
                cargarMensajes(conversacionActiva);
            } else {
                console.error('Error envío:', data.error);
                mostrarNotificacion('Error al enviar mensaje', 'error');
            }
        })
        .catch(err => {
            console.error('Error enviando mensaje:', err);
            mostrarNotificacion('Error al enviar el mensaje. Intenta nuevamente.', 'error');
        })
        .finally(() => mostrarIndicadorEnvio(false));
    }

    function mostrarNuevaConversacion() {
        console.log('Abriendo modal de nueva conversación');
        // Crear modal si no existe
        let modal = document.getElementById('nuevo-chat-modal');
        if (!modal) {
            modal = document.createElement('div');
            modal.id = 'nuevo-chat-modal';
            modal.className = 'modal';
            modal.innerHTML = `
                <div class="modal-content">
                    <div class="modal-header">
                        <h3>Nueva conversación</h3>
                        <span class="close">&times;</span>
                    </div>
                    <div class="search-users">
                        <input type="text" id="buscar-usuarios" placeholder="Buscar usuarios...">
                    </div>
                    <div id="lista-usuarios" class="usuarios-lista">
                        <!-- Usuarios se cargarán aquí -->
                    </div>
                </div>
            `;
            document.body.appendChild(modal);
            
            // Configurar eventos del modal
            modal.querySelector('.close').addEventListener('click', function() {
                modal.style.display = 'none';
            });
            
            // Cerrar al hacer clic fuera del modal
            window.addEventListener('click', function(event) {
                if (event.target === modal) {
                    modal.style.display = 'none';
                }
            });
            
            // Filtrar usuarios al escribir
            modal.querySelector('#buscar-usuarios').addEventListener('input', function() {
                const busqueda = this.value.toLowerCase().trim();
                document.querySelectorAll('.usuario-item').forEach(item => {
                    const nombre = item.querySelector('.user-name').textContent.toLowerCase();
                    item.style.display = nombre.includes(busqueda) ? 'flex' : 'none';
                });
            });
        }
        
        // Mostrar modal
        modal.style.display = 'block';
        
        // Cargar usuarios
        cargarUsuarios();
    }

    function cargarUsuarios() {
        const contenedor = document.getElementById('lista-usuarios');
        if (!contenedor) {
            console.error('Error: Elemento #lista-usuarios no encontrado');
            return;
        }
        
        contenedor.innerHTML = '<div class="loading">Cargando usuarios...</div>';
        
        console.log('Realizando fetch a obtener_usuarios.php');
        fetch('/Conexion-Main/backend/controllers/chat/obtener_usuarios.php')
            .then(response => {
                console.log('Respuesta recibida:', response);
                if (!response.ok) {
                    throw new Error('Error de red: ' + response.status);
                }
                return response.json();
            })
            .then(data => {
                console.log('Datos recibidos:', data);
                if (data.success) {
                    renderizarUsuarios(data.usuarios || []);
                } else {
                    console.error('Error en respuesta:', data.error || 'Error desconocido');
                    contenedor.innerHTML = '<div class="error">Error: ' + (data.error || 'Error desconocido') + '</div>';
                }
            })
            .catch(error => {
                console.error('Error obteniendo usuarios:', error);
                contenedor.innerHTML = '<div class="error">Error al cargar usuarios: ' + error.message + '</div>';
            });
    }

    function renderizarUsuarios(usuarios) {
        const cont = document.getElementById('lista-usuarios');
        if (!usuarios || usuarios.length === 0) {
            cont.innerHTML = '<div class="no-users">No hay usuarios disponibles</div>';
            return;
        }
        cont.innerHTML = usuarios.map(u => u.id != usuarioActual ? `
            <div class="usuario-item" data-id="${u.id}">
                <img src="https://via.placeholder.com/40" class="user-avatar" alt="">
                <div class="user-name">${sanitizarHTML(u.nombre || u.username)}</div>
                <button class="btn-iniciar-chat"><i class="fas fa-comment"></i></button>
            </div>` : ''
        ).join('');
        
        document.querySelectorAll('.btn-iniciar-chat').forEach(btn => {
            btn.addEventListener('click', () => iniciarConversacion(btn.closest('.usuario-item').dataset.id));
        });
    }

    window.iniciarConversacion = function(usuarioId) {
        document.getElementById('nuevo-chat-modal').style.display='none';
        fetch('/Conexion-Main/backend/controllers/chat/crear_conversacion.php', {
            method:'POST',
            headers:{'Content-Type':'application/x-www-form-urlencoded'},
            body:`usuario_id=${encodeURIComponent(usuarioId)}`
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                abrirConversacion(data.conversacion_id);
                cargarConversaciones();
            } else {
                console.error('Error crear:', data.error);
                mostrarNotificacion('Error al crear conversacion','error');
            }
        })
        .catch(err => {
            console.error('Error crear:', err);
            mostrarNotificacion('Error al crear conversacion','error');
        });
    };

    function formatearHora(fecha) {
        return fecha.toLocaleTimeString([], {hour:'2-digit', minute:'2-digit'});
    }

    function minimizarChat() {
        chatWindowDOM.classList.toggle('minimized');
        // Forzar repintado
        chatWindowDOM.style.display='none';
        setTimeout(() => chatWindowDOM.style.display='flex', 10);
    }

    function cerrarChat() {
        console.log('Cerrando ventana de chat');
        if (chatWindowDOM) {
            chatWindowDOM.style.display = 'none';
            conversacionActiva = null;
            if (mensajesDOM) mensajesDOM.innerHTML = '';
        }
    }

    function mostrarIndicadorEnvio(mostrar) {
        btnEnviar.disabled = mostrar;
        btnEnviar.classList.toggle('sending', mostrar);
    }

    function mostrarNotificacion(msg, tipo='info') {
        let notif = document.getElementById('chat-notification');
        if (!notif) {
            notif = document.createElement('div');
            notif.id = 'chat-notification';
            document.body.appendChild(notif);
        }
        notif.textContent = msg;
        notif.className = `notification ${tipo}`;
        notif.style.display = 'block';
        setTimeout(() => notif.style.display = 'none', 3000);
    }

    function sanitizarHTML(str) {
        return str ? String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;') : '';
    }
}

function inicializarChatSidebar() {
    const sidebar = document.getElementById('chat-sidebar');
    const closeBtn = document.getElementById('close-chat-sidebar');
    const minimizeBtn = document.getElementById('minimize-chat-sidebar');
    
    if (closeBtn) {
        closeBtn.addEventListener('click', function() {
            sidebar.classList.remove('active');
            console.log('Chat sidebar cerrado');
        });
    }
    
    if (minimizeBtn) {
        minimizeBtn.addEventListener('click', function() {
            sidebar.classList.toggle('minimized');
        });
    }
}

// Inicializar cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', function() {
    inicializarChat();
    inicializarChatSidebar();
});
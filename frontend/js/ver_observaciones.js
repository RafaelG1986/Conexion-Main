// Al inicio del archivo
// Hacer la función disponible globalmente
window.verObservaciones = function(id) {
    console.log('Iniciando carga de observaciones para ID:', id);
    console.log('ID recibido:', id); // Añade esta línea para depuración
    
    if (!id || isNaN(id)) {
        alert('Error: ID de registro inválido');
        return;
    }
    
    // Verificar que el elemento existe antes de intentar acceder a él
    const modalElement = document.getElementById('modal-ver-observaciones');
    const overlayElement = document.getElementById('overlay-observaciones');
    const contenidoElement = document.getElementById('observaciones-contenido');
    
    if (!modalElement) {
        console.error('Error: No se encontró el elemento #modal-ver-observaciones');
        alert('Error: Elemento del modal no encontrado.');
        return;
    }
    
    if (!overlayElement) {
        console.error('Error: No se encontró el elemento #overlay-observaciones');
        alert('Error: Elemento del overlay no encontrado.');
        return;
    }
    
    if (!contenidoElement) {
        console.error('Error: No se encontró el elemento #observaciones-contenido');
        alert('Error: Elemento del contenido no encontrado.');
        return;
    }
    
    // Mostrar modal y overlay
    modalElement.style.display = 'flex';
    overlayElement.style.display = 'block';
    document.body.style.overflow = 'hidden';
    
    // Mostrar cargando
    contenidoElement.innerHTML = '<div class="cargando">Cargando observaciones para registro #' + id + '...</div>';
    
    // Usar XMLHttpRequest en lugar de fetch
    var xhr = new XMLHttpRequest();
    xhr.open('POST', '../../backend/controllers/obtener_observaciones.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    
    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4) {
            console.log('XHR Status:', xhr.status);
            console.log('Respuesta:', xhr.responseText);
            
            if (xhr.status === 200) {
                try {
                    var data = JSON.parse(xhr.responseText);
                    if (data.success) {
                        mostrarObservaciones(data.observaciones);
                    } else {
                        mostrarError(data.message || 'Error desconocido');
                    }
                } catch (e) {
                    console.error('Error al procesar JSON:', e);
                    mostrarError('Error al procesar la respuesta del servidor');
                }
            } else {
                mostrarError('Error en la conexión: ' + xhr.status);
            }
        }
    };
    
    // Enviar la solicitud
    xhr.send('id=' + encodeURIComponent(id));
}

// Funciones auxiliares deben estar fuera de verObservaciones
function mostrarObservaciones(texto) {
    var contenedor = document.getElementById('observaciones-contenido');
    
    if (texto && texto.trim() !== '') {
        // Formatear texto a HTML
        var html = texto
            .replace(/\n\n/g, '</p><p>')
            .replace(/\n/g, '<br>');
        
        html = '<p>' + html + '</p>';
        html = html.replace(/\[(.*?)\]/g, '<span class="observacion-meta">[$1]</span>');
        
        contenedor.innerHTML = html;
    } else {
        contenedor.innerHTML = '<p class="sin-observaciones">No hay observaciones registradas para este contacto.</p>';
    }
}

function mostrarError(mensaje) {
    document.getElementById('observaciones-contenido').innerHTML = 
        '<p class="error-mensaje">Error: ' + mensaje + '</p>';
}

function cerrarModalObservaciones() {
    document.getElementById('modal-ver-observaciones').style.display = 'none';
    document.getElementById('overlay-observaciones').style.display = 'none';
    document.body.style.overflow = 'auto'; // Restaurar scroll
}

// También agregar listener para cerrar con ESC
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        cerrarModalObservaciones();
    }
});

// Versión de prueba para verificar el funcionamiento del modal
function verObservacionesTest(id) {
    console.log('TEST - Mostrando observaciones para ID:', id);
    
    // Mostrar modal y overlay
    document.getElementById('modal-ver-observaciones').style.display = 'flex';
    document.getElementById('overlay-observaciones').style.display = 'block';
    
    // Deshabilitar scroll
    document.body.style.overflow = 'hidden';
    
    // Contenido de prueba
    setTimeout(() => {
        document.getElementById('observaciones-contenido').innerHTML = `
            <p><span class="observacion-meta">[23/04/2025 14:30 - Sistema]</span>Esta es una observación de prueba.</p>
            <p><span class="observacion-meta">[24/04/2025 09:15 - Admin]</span>Segunda observación para verificar funcionamiento.</p>
        `;
    }, 500);
}

// Agregar al final del archivo
document.addEventListener('DOMContentLoaded', function() {
    console.log('Configurando botones de observaciones');
    
    // Delegación de eventos para botones actuales y futuros
    document.body.addEventListener('click', function(e) {
        if (e.target.closest('.btn-ver-obs')) {
            const btn = e.target.closest('.btn-ver-obs');
            const id = btn.getAttribute('data-id');
            verObservaciones(id);
        }
    });
});


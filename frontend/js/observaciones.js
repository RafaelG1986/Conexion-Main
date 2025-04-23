console.log('Archivo observaciones.js cargado - v4');

// Función para abrir el modal de observaciones
function abrirObservacion(id) {
    console.log('Abriendo modal para ID:', id);
    
    try {
        // Asignar ID al campo oculto
        document.getElementById('id-registro').value = id;
        
        // Limpiar el textarea
        document.getElementById('texto-observacion').value = '';
        
        // Mostrar el modal
        const modal = document.getElementById('modal-observaciones');
        modal.style.display = 'flex';
        
        // Enfocar el textarea
        setTimeout(() => {
            document.getElementById('texto-observacion').focus();
        }, 100);
    } catch (error) {
        console.error('Error al abrir modal:', error);
        alert('Error al abrir el formulario de observaciones');
    }
}

// Función para cerrar el modal
function cerrarModal(id) {
    try {
        document.getElementById(id).style.display = 'none';
    } catch (error) {
        console.error('Error al cerrar modal:', error);
    }
}

// Función para guardar la observación
function guardarObservacion() {
    console.log('Ejecutando guardarObservacion()');
    
    // Obtener datos del formulario
    const id = document.getElementById('id-registro').value;
    const observacion = document.getElementById('texto-observacion').value;
    
    console.log('ID:', id);
    console.log('Observación:', observacion);

    // Validar
    if (!id) {
        alert('Error: No se encontró el ID del registro');
        return;
    }
    
    if (!observacion.trim()) {
        alert('Por favor escribe una observación');
        return;
    }
    
    // Mostrar indicador de carga
    const btnGuardar = document.getElementById('btn-guardar-obs');
    if (btnGuardar) {
        btnGuardar.disabled = true;
        btnGuardar.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Guardando...';
    }
    
    // Crear FormData como en actualizar_registro.php
    const formData = new FormData();
    formData.append('id', id);
    formData.append('observaciones', observacion);
    
    // Enviar solicitud al servidor
    fetch('../../backend/controllers/agregar_observacion.php', {
        method: 'POST',
        body: formData,
        credentials: 'include'
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`Error HTTP: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        console.log('Respuesta:', data);
        
        // Restaurar botón
        if (btnGuardar) {
            btnGuardar.disabled = false;
            btnGuardar.innerHTML = '<i class="fas fa-save"></i> Guardar';
        }
        
        if (data.success) {
            alert('Observación guardada correctamente');
            cerrarModal('modal-observaciones');
            
            // Recargar la vista actual si es posible
            if (typeof cargarVista === 'function' && window.vistaActual) {
                cargarVista(window.vistaActual);
            }
        } else {
            alert('Error: ' + (data.message || 'No se pudo guardar la observación'));
        }
    })
    .catch(error => {
        console.error('Error en fetch:', error);
        
        // Restaurar botón
        if (btnGuardar) {
            btnGuardar.disabled = false;
            btnGuardar.innerHTML = '<i class="fas fa-save"></i> Guardar';
        }
        
        alert('Error de conexión: ' + error.message);
    });
}

// Configurar eventos cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM cargado - configurando eventos de observaciones');
    
    // Configurar botón guardar
    const btnGuardar = document.getElementById('btn-guardar-obs');
    if (btnGuardar) {
        console.log('Botón de guardar encontrado, configurando evento');
        btnGuardar.addEventListener('click', guardarObservacion);
    } else {
        console.warn('⚠️ Botón guardar observaciones no encontrado');
    }
});
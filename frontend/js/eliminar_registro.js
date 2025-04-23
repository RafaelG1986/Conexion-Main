console.log('Archivo eliminar_registro.js cargado - v2');

// Función para eliminar un registro
function eliminarRegistro(id) {
    console.log('eliminarRegistro() llamada con ID:', id);
    
    // Confirmar antes de eliminar
    if (!confirm('¿Estás seguro de que deseas eliminar este registro? Esta acción no se puede deshacer.')) {
        return;
    }
    
    // Mostrar indicador visual
    const fila = document.querySelector(`tr[data-id="${id}"]`) || document.querySelector(`td button[onclick*="eliminarRegistro(${id})"]`)?.closest('tr');
    if (fila) {
        fila.style.backgroundColor = 'rgba(255,0,0,0.1)';
        fila.style.opacity = '0.7';
    }
    
    // Crear datos para enviar
    const formData = new FormData();
    formData.append('id', id);
    
    // URL absoluta para evitar problemas con rutas relativas
    const url = window.location.origin + '/conexion-main/backend/controllers/eliminar_registro.php';
    
    // Enviar solicitud al servidor
    fetch(url, {
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
        console.log('Respuesta del servidor:', data);
        
        if (data.success) {
            // Mensaje de éxito
            alert('Registro eliminado correctamente');
            
            // Eliminar la fila de la tabla con animación
            if (fila) {
                fila.style.transition = 'all 0.5s ease';
                fila.style.opacity = '0';
                fila.style.maxHeight = '0';
                setTimeout(() => {
                    fila.remove();
                }, 500);
            } else {
                // Si no encontramos la fila, recargar la vista
                if (typeof cargarVista === 'function' && window.vistaActual) {
                    cargarVista(window.vistaActual);
                } else {
                    // Como último recurso, recargar la página
                    location.reload();
                }
            }
        } else {
            // Restaurar apariencia normal si hay error
            if (fila) {
                fila.style.backgroundColor = '';
                fila.style.opacity = '1';
            }
            alert('Error: ' + (data.message || 'No se pudo eliminar el registro'));
        }
    })
    .catch(error => {
        console.error('Error al eliminar registro:', error);
        
        // Restaurar apariencia normal
        if (fila) {
            fila.style.backgroundColor = '';
            fila.style.opacity = '1';
        }
        
        alert('Error: ' + error.message);
    });
}

// También podemos añadir un event listener para los botones que tienen data-id
document.addEventListener('click', function(e) {
    const boton = e.target.closest('.btn-eliminar');
    if (boton) {
        const id = boton.getAttribute('data-id');
        if (id) {
            e.preventDefault();
            eliminarRegistro(id);
        }
    }
});

console.log('Event listeners de eliminar_registro.js configurados');
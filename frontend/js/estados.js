/**
 * Configuración de colores para cada estado
 */
const estadoColores = {
    'Primer contacto':          {bg:'#ffcccc', color:'#a00'},
    'Conectado':                {bg:'#ffd6cc', color:'#b36b00'},
    'No confirmado a desayuno': {bg:'#ffe5cc', color:'#b36b00'},
    'Confirmado a Desayuno':    {bg:'#cce0ff', color:'#00509e'},
    'Desayuno Asistido':        {bg:'#cce6ff', color:'#00509e'},
    'Congregado sin desayuno':  {bg:'#d4edda', color:'#155724'},
    'Visitante':                {bg:'#fff', color:'#222'},
    'No interesado':            {bg:'#ffdddd', color:'#a00'},
    'Por Validar Estado':       {bg:'#ffe5b4', color:'#b36b00'}
};

/**
 * Función principal para cambiar el estado de un registro
 * @param {number} id - ID del registro
 * @param {string} nuevoEstado - Nuevo estado a asignar
 */
function cambiarEstado(id, nuevoEstado) {
    console.log("Cambiando estado:", id, nuevoEstado);
    
    // Validación adicional
    if (!id || isNaN(parseInt(id))) {
        console.error("cambiarEstado: ID inválido:", id);
        return;
    }
    
    const data = new FormData();
    data.append('id', id);
    data.append('estado', nuevoEstado);
    
    // Mostrar indicador visual
    const elemento = document.getElementById('estado-td-' + id);
    if (elemento) {
        elemento.style.opacity = '0.5';
    }
    
    fetch('../../backend/controllers/actualizar_estado.php', {
        method: 'POST',
        body: data
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            console.log('Estado actualizado correctamente');
            // Actualizar color si existe el elemento
            setEstadoColor(id, nuevoEstado);
        } else {
            console.error('Error del servidor:', data.message || 'Error desconocido');
            alert('Error al actualizar el estado: ' + (data.message || 'Error desconocido'));
        }
    })
    .catch(error => {
        console.error('Error en la petición:', error);
        alert('Error de conexión al actualizar el estado');
    })
    .finally(() => {
        // Restaurar opacidad
        if (elemento) {
            elemento.style.opacity = '1';
        }
    });
}

/**
 * Función que valida los parámetros y llama a cambiarEstado
 * Esta es la función que debe usarse en los atributos onchange
 * @param {string} estado - Nuevo estado a asignar
 * @param {number|string} id - ID del registro (debe ser numérico)
 */
function actualizarEstado(estado, id) {
    // Validación del ID
    if (!id || isNaN(parseInt(id))) {
        console.error("ID inválido:", id);
        alert("Error: ID de registro inválido");
        return;
    }
    
    // Convertir a número para asegurar formato correcto
    const idNumerico = parseInt(id);
    console.log("Actualizando estado:", idNumerico, estado);
    
    // Usar la función principal
    cambiarEstado(idNumerico, estado);
}

/**
 * Aplica los colores correspondientes al estado
 * @param {number} id - ID del registro
 * @param {string} estado - Estado cuyo color aplicar
 */
function setEstadoColor(id, estado) {
    const td = document.getElementById('estado-td-' + id);
    if (!td) {
        console.warn(`Elemento con ID 'estado-td-${id}' no encontrado`);
        return;
    }
    
    if (!estadoColores[estado]) {
        console.warn(`No hay configuración de color para el estado '${estado}'`);
        return;
    }
    
    td.style.setProperty('background', estadoColores[estado].bg, 'important');
    td.style.setProperty('color', estadoColores[estado].color, 'important');
}

/**
 * Inicializa los colores y eventos para los selectores de estado
 */
function inicializarEstados() {
    console.log("Inicializando estados...");
    
    // Inicializar colores para selectores existentes
    document.querySelectorAll('select[name="estado"]').forEach(function(sel) {
        // Intentar obtener el ID desde data-id
        let id = sel.getAttribute('data-id');
        
        // Si no hay data-id, intentar obtenerlo del elemento contenedor
        if (!id) {
            const td = sel.closest('td[id^="estado-td-"]');
            if (td) {
                id = td.id.replace('estado-td-', '');
            }
        }
        
        if (id) {
            // Aplicar color inicial
            setEstadoColor(id, sel.value);
            
            // Añadir evento solo si no tiene ya un manejador onchange
            if (!sel.hasAttribute('onchange')) {
                sel.addEventListener('change', function() {
                    actualizarEstado(sel.value, id);
                    setEstadoColor(id, sel.value);
                });
            }
        }
    });
}

// Inicializar cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', inicializarEstados);

// Exportar funciones para uso global
window.actualizarEstado = actualizarEstado;
window.cambiarEstado = cambiarEstado;
window.setEstadoColor = setEstadoColor;
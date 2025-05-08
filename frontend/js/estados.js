/**
 * Configuración de colores para cada estado
 */
const estadoColores = {
    // Contacto Inicial
    'Primer contacto':          {bg:'#ffcccc', color:'#a00'},
    'Conectado':                {bg:'#ffd6cc', color:'#b36b00'},
    'Primer intento':           {bg:'#f5e6ff', color:'#5a00b3'},
    'Segundo Intento':          {bg:'#e6ccff', color:'#5a00b3'},
    'Tercero intento':          {bg:'#d9b3ff', color:'#5a00b3'},
    'Intento llamada telefonica': {bg:'#e1f5fe', color:'#0288d1'},   // NUEVO - Azul claro
    'Intento 2 llamada telefonica': {bg:'#b3e5fc', color:'#0277bd'}, // NUEVO - Azul medio
    'Intento 3 llamada telefonica': {bg:'#81d4fa', color:'#01579b'}, // NUEVO - Azul oscuro
    'No interesado':            {bg:'#ffdddd', color:'#a00'},
    
    // Desayunos
    'No confirma desayuno':     {bg:'#ffe5cc', color:'#b36b00'},
    'No confirmado a desayuno': {bg:'#ffe5cc', color:'#b36b00'}, // Mantener para compatibilidad
    'Confirmado a Desayuno':    {bg:'#cce0ff', color:'#00509e'},
    'Desayuno Asistido':        {bg:'#cce6ff', color:'#00509e'},
    
    // Miembros
    'Miembro activo':           {bg:'#d9f2d9', color:'#006600'},
    'Miembro inactivo':         {bg:'#ffebcc', color:'#994d00'},
    'Miembro ausente':          {bg:'#ffe6e6', color:'#cc0000'},
    'Congregado sin desayuno':  {bg:'#d4edda', color:'#155724'},
    'Visitante':                {bg:'#fff', color:'#222'},
    
    // Líderes
    'Lider Activo':             {bg:'#cce0ff', color:'#004080'},
    'Lider inactivo':           {bg:'#e6e6e6', color:'#666666'},
    'Lider ausente':            {bg:'#ffe6ea', color:'#990033'},
    
    // Reconexión
    'Reconectado':              {bg:'#c8e6c9', color:'#2e7d32'},
    'Intento de reconexión':    {bg:'#dcedc8', color:'#33691e'},
    'Etapa 1 reconexion (1 mes)': {bg:'#fff9c4', color:'#f57f17'}, // NUEVO - Amarillo
    'Etapa 2 reconexion (3 mes)': {bg:'#ffe0b2', color:'#e65100'}, // NUEVO - Naranja
    'Etapa 3 reconexion final (6 mes)': {bg:'#ffcdd2', color:'#c62828'}, // NUEVO - Rojo
    
    // Ministerios Específicos (Nuevos)
    'Vencedores Kids':         {bg:'#ffeb3b', color:'#8c6d00'}, // Amarillo
    'Legado':                  {bg:'#dcedc8', color:'#558b2f'}, // Verde claro
    'Teens Legado':            {bg:'#c8e6c9', color:'#2e7d32'}, // Verde medio
    
    // Otros
    'Por Validar Estado':       {bg:'#ffe5b4', color:'#b36b00'},
    'Nulo':                     {bg:'#e0e0e0', color:'#757575'}, // NUEVO - Gris
    'Delegado a acompañante':   {bg:'#e1bee7', color:'#6a1b9a'}, // NUEVO - Púrpura
    'Datos no autorizados':     {bg:'#ffcdd2', color:'#d32f2f'}, // NUEVO - Rojo claro
    'Datos incorrectos':        {bg:'#f8bbd0', color:'#c2185b'}  // NUEVO - Rosa
};

/**
 * Lista completa de estados agrupados por categoría
 * Útil para inicializar selectores u otras funcionalidades
 */
const gruposEstados = {
    'Contacto Inicial': [
        'Primer contacto',
        'Conectado',
        'Primer intento',
        'Segundo Intento',
        'Tercero intento',
        'Intento llamada telefonica',     // NUEVO
        'Intento 2 llamada telefonica',   // NUEVO
        'Intento 3 llamada telefonica',   // NUEVO
        'No interesado'
    ],
    'Desayunos': [
        'No confirma desayuno',
        'Confirmado a Desayuno',
        'Desayuno Asistido'
    ],
    'Miembros': [
        'Miembro activo',
        'Miembro inactivo',
        'Miembro ausente',
        'Congregado sin desayuno',
        'Visitante'
    ],
    'Líderes': [
        'Lider Activo',
        'Lider inactivo',
        'Lider ausente'
    ],
    'Reconexión': [
        'Reconectado',
        'Intento de reconexión',
        'Etapa 1 reconexion (1 mes)',
        'Etapa 2 reconexion (3 mes)',
        'Etapa 3 reconexion final (6 mes)'
    ],
    'Ministerios': [
        'Vencedores Kids',
        'Legado',
        'Teens Legado'
    ],
    'Otros': [
        'Por Validar Estado',
        'Nulo',                   // NUEVO
        'Delegado a acompañante', // NUEVO
        'Datos no autorizados',   // NUEVO
        'Datos incorrectos'       // NUEVO
    ]
};

// Crear un array plano con todos los estados para facilitar validaciones
const todosEstados = Object.values(gruposEstados).flat();

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
window.estadoColores = estadoColores;
window.gruposEstados = gruposEstados;
window.todosEstados = todos
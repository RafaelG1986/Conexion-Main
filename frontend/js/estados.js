const estadoColores = {
    'Primer contacto':    {bg:'#ffcccc', color:'#a00'},
    'Conectado':          {bg:'#ffd6cc', color:'#b36b00'},
    'No confirmado a desayuno': {bg:'#ffe5cc', color:'#b36b00'},
    'Confirmado a Desayuno':    {bg:'#cce0ff', color:'#00509e'},
    'Desayuno Asistido':        {bg:'#cce6ff', color:'#00509e'},
    'Congregado sin desayuno':  {bg:'#d4edda', color:'#155724'},
    'Visitante':                {bg:'#fff', color:'#222'},
    'No interesado':            {bg:'#ffdddd', color:'#a00'},
    'Por Validar Estado':       {bg:'#ffe5b4', color:'#b36b00'}
};

function actualizarEstado(id, nuevoEstado) {
    fetch('https://6be4-186-155-16-217.ngrok-free.app/conexion-main/conexion-main/backend/controllers/actualizar_estado.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'id=' + encodeURIComponent(id) + '&estado=' + encodeURIComponent(nuevoEstado)
    })
    .then(res => res.text())
    .then(msg => {
        // Recargar la tabla de registros automáticamente
        if (typeof cargarVista === 'function') {
            cargarVista('vista_registros.php');
        }
        // Si quieres mostrar un mensaje temporal:
        const mensajeDiv = document.getElementById('mensaje-estado');
        if (mensajeDiv) {
            mensajeDiv.textContent = 'Estado actualizado correctamente';
            setTimeout(() => {
                mensajeDiv.textContent = '';
            }, 2000);
        }
    });
}

function setEstadoColor(id, estado) {
    var td = document.getElementById('estado-td-' + id);
    if (td && estadoColores[estado]) {
        td.style.setProperty('background', estadoColores[estado].bg, 'important');
        td.style.setProperty('color', estadoColores[estado].color, 'important');
    }
}

function inicializarEstados() {
    document.querySelectorAll('select[onchange^="actualizarEstado"]').forEach(function(sel) {
        var id = sel.closest('td').id.replace('estado-td-', '');
        setEstadoColor(id, sel.value);
        sel.addEventListener('change', function() {
            setEstadoColor(id, sel.value);
        });
    });
}

// Inicializa colores al cargar la página principal
document.addEventListener('DOMContentLoaded', inicializarEstados);
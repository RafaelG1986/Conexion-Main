// Crea este nuevo archivo con las funciones de manejo de modales
function cerrarModal(id) {
    console.log('Cerrando modal:', id);
    
    // Obtener el elemento del modal
    const modal = document.getElementById(id);
    if (!modal) {
        console.error('Modal no encontrado:', id);
        return;
    }
    
    // Verificar si está visible antes de ocultarlo
    if (modal.style.display !== 'none') {
        modal.style.display = 'none';
        
        // Ocultar también el overlay
        const overlay = document.getElementById('modal-overlay');
        if (overlay) {
            overlay.style.display = 'none';
        }
        
        console.log('Modal cerrado correctamente');
    }
}

function abrirModal(id) {
    console.log('Abriendo modal:', id);
    
    // Obtener el elemento del modal
    const modal = document.getElementById(id);
    if (!modal) {
        console.error('Modal no encontrado:', id);
        return;
    }
    
    // Mostrar el modal
    modal.style.display = 'flex';
    
    // Mostrar el overlay
    const overlay = document.getElementById('modal-overlay');
    if (overlay) {
        overlay.style.display = 'block';
    }
    
    console.log('Modal abierto correctamente');
}
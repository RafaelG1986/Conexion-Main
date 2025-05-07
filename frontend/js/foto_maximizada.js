/**
 * Sistema de visualización de fotos ampliadas
 */
function mostrarFotoMaximizada(url, nombre) {
    console.log('Mostrando foto maximizada:', url);
    
    // Crear estructura del modal aprovechando clases existentes
    let modal = document.createElement('div');
    modal.id = 'modal-foto';
    modal.className = 'modal'; // Usar clase modal existente
    
    modal.innerHTML = `
        <div class="modal-content foto-modal-content">
            <div class="modal-header">
                <h3>${nombre || 'Vista ampliada'}</h3>
                <span class="close">&times;</span>
            </div>
            <div class="modal-body foto-modal-body">
                <img id="imagen-maximizada" src="${url}" alt="${nombre || 'Imagen ampliada'}">
            </div>
        </div>
    `;
    
    document.body.appendChild(modal);
    
    // Mostrar el modal
    modal.style.display = 'flex';
    modal.style.alignItems = 'center';
    modal.style.justifyContent = 'center';
    
    // Configurar los eventos de cierre
    const cerrarModal = function() {
        modal.style.display = 'none';
        setTimeout(() => {
            modal.remove();
        }, 300);
    };
    
    // Evento para cerrar con el botón X
    modal.querySelector('.close').onclick = cerrarModal;
    
    // Evento para cerrar al hacer clic fuera de la imagen
    modal.addEventListener('click', function(e) {
        if (e.target === modal) {
            cerrarModal();
        }
    });
    
    // Evento para cerrar con ESC
    document.addEventListener('keydown', function escHandler(e) {
        if (e.key === 'Escape') {
            cerrarModal();
            document.removeEventListener('keydown', escHandler);
        }
    });
}
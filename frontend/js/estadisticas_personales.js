/**
 * Módulo de Estadísticas Personales
 * Gestiona la visualización y navegación de estadísticas por conector
 */

// Variable global para el gráfico (permite destruirlo si se reinicializa)
window._chartPersonal = null;

/**
 * Inicializa el módulo de estadísticas personales
 * Esta función es llamada desde admin.php cuando se carga la vista
 */
function inicializarEstadisticasPersonales() {
    console.log('Inicializando módulo de estadísticas personales');
    
    // Configurar el selector de conector
    configurarSelectorConector();
    
    // Inicializar gráfico con datos del DOM
    inicializarGraficoDesdeDom();
}

/**
 * Configura el selector de conector evitando duplicación de event listeners
 */
function configurarSelectorConector() {
    const select = document.getElementById('conector');
    if (!select) {
        console.error('No se encontró el selector de conector');
        return;
    }
    
    // Clonar el elemento para eliminar todos los event listeners previos
    const newSelect = select.cloneNode(true);
    select.parentNode.replaceChild(newSelect, select);
    
    // Agregar el nuevo event listener
    newSelect.addEventListener('change', function() {
        const valor = this.value;
        if (!valor) return;
        
        console.log(`Cambiando a conector: ${valor}`);
        mostrarCargando();
        
        // Construir URL con timestamp para evitar caché
        const timestamp = new Date().getTime();
        const url = `vista_estadisticas_personales.php?conector=${encodeURIComponent(valor)}&t=${timestamp}`;
        
        // Detectar el contexto y usar el método apropiado para navegar
        if (window !== window.parent && typeof window.parent.cargarVista === 'function') {
            console.log('Usando navegación desde iframe (parent.cargarVista)');
            window.parent.cargarVista(url);
        } else if (typeof window.cargarVista === 'function') {
            console.log('Usando navegación con cargarVista local');
            window.cargarVista(url);
        } else {
            console.log('Usando navegación directa (window.location)');
            window.location.href = url;
        }
    });
    
    console.log('Selector de conector configurado');
}

/**
 * Muestra un indicador de carga superpuesto al contenedor principal
 */
function mostrarCargando() {
    const container = document.querySelector('.estadisticas-personales-container');
    if (!container) return;
    
    // Asegurarse de posición relativa para el contenedor
    container.style.position = 'relative';
    
    // Crear y añadir el overlay de carga
    const loadingDiv = document.createElement('div');
    loadingDiv.className = 'loading-overlay';
    loadingDiv.innerHTML = '<div class="spinner">Cargando datos...</div>';
    loadingDiv.style.cssText = 'position:absolute;top:0;left:0;width:100%;height:100%;background:rgba(255,255,255,0.8);display:flex;justify-content:center;align-items:center;z-index:1000;';
    container.appendChild(loadingDiv);
}

/**
 * Inicializa el gráfico obteniendo datos desde el DOM
 */
function inicializarGraficoDesdeDom() {
    console.log('Inicializando gráfico desde datos en DOM');
    
    const canvas = document.getElementById('graficoEstadosPersonal');
    if (!canvas) {
        console.error('No se encontró el canvas para el gráfico');
        return;
    }
    
    // Extraer datos del canvas (insertados por PHP)
    const conector = canvas.getAttribute('data-conector') || '';
    const datos = canvas.getAttribute('data-estados') || '{}';
    
    try {
        const estadosParsed = JSON.parse(datos);
        inicializarGrafico(conector, estadosParsed);
    } catch(e) {
        console.error('Error al parsear datos desde el DOM:', e);
    }
}

/**
 * Inicializa el gráfico con los datos proporcionados
 * @param {string} conector - Nombre del conector seleccionado
 * @param {Object} datosEstados - Objeto con estados como claves y cantidades como valores
 */
function inicializarGrafico(conector, datosEstados) {
    console.log(`Inicializando gráfico para: ${conector}`);
    
    // Verificar que Chart.js está disponible
    if (typeof Chart !== 'function') {
        console.error('Chart.js no está disponible. Asegúrate de incluirlo antes.');
        return;
    }
    
    // Verificar datos
    if (!datosEstados || Object.keys(datosEstados).length === 0) {
        console.warn('No hay datos para mostrar en el gráfico');
        return;
    }
    
    const canvas = document.getElementById('graficoEstadosPersonal');
    if (!canvas) return;
    
    // Preparar datos para Chart.js
    const labels = Object.keys(datosEstados);
    const data = Object.values(datosEstados);
    
    // Destruir gráfico anterior si existe
    if (window._chartPersonal) {
        console.log('Destruyendo gráfico anterior');
        try {
            window._chartPersonal.destroy();
        } catch (e) {
            console.error('Error al destruir gráfico previo:', e);
        }
    }
    
    // Crear nuevo gráfico
    const ctx = canvas.getContext('2d');
    window._chartPersonal = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: `Estados para ${conector}`,
                data: data,
                backgroundColor: [
                    '#3498db', '#2ecc71', '#e74c3c', '#f39c12', 
                    '#9b59b6', '#1abc9c', '#34495e', '#d35400'
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'top',
                },
                title: {
                    display: true,
                    text: `Estadísticas de ${conector}`,
                    font: { size: 16 }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const label = context.label || '';
                            const value = context.raw || 0;
                            const total = context.chart.data.datasets[0].data.reduce((a, b) => a + b, 0);
                            const percentage = Math.round((value / total) * 100);
                            return `${label}: ${value} (${percentage}%)`;
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Cantidad'
                    }
                }
            }
        }
    });
    
    console.log('Gráfico inicializado correctamente ✓');
}

// Exportar funciones para uso global (necesario para que admin.php pueda llamarlas)
window.inicializarEstadisticasPersonales = inicializarEstadisticasPersonales;
window.inicializarEstadisticasPersonalesConDatos = inicializarGrafico;
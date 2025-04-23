// Función para inicializar el módulo de filtrado
function inicializarFiltro() {
    console.log("Inicializando módulo de filtrado");
    
    // Usar una espera más larga para asegurar que el DOM esté listo
    setTimeout(function() {
        try {
            // Comprobar si los elementos existen antes de agregar event listeners
            const btnLimpiar = document.getElementById('btn-limpiar');
            const btnAplicar = document.getElementById('btn-aplicar');
            const btnVolver = document.getElementById('btn-volver');
            
            console.log("Elementos encontrados:", {
                btnLimpiar: btnLimpiar ? true : false,
                btnAplicar: btnAplicar ? true : false,
                btnVolver: btnVolver ? true : false
            });
            
            if (btnLimpiar) {
                btnLimpiar.addEventListener('click', function() {
                    document.getElementById('filtro-form').reset();
                });
            }
            
            if (btnAplicar) {
                btnAplicar.addEventListener('click', function() {
                    aplicarFiltros();
                });
            }
            
            if (btnVolver) {
                btnVolver.addEventListener('click', function() {
                    document.getElementById('resultados-container').style.display = 'none';
                    document.getElementById('filtro-form').style.display = 'block';
                });
            }
        } catch (error) {
            console.error("Error al inicializar filtros:", error);
        }
    }, 1000); // Aumentar a 1000ms (1 segundo) para dar más tiempo
}

// Función para aplicar los filtros y mostrar resultados
function aplicarFiltros() {
    try {
        // Comprobar si el formulario existe
        const form = document.getElementById('filtro-form');
        if (!form) {
            console.error("No se encontró el formulario de filtros");
            return;
        }
        
        // Comprobar si el contenedor de resultados existe
        const resultadosContainer = document.getElementById('resultados-container');
        const resultadosFiltro = document.getElementById('resultados-filtro');
        
        if (!resultadosContainer || !resultadosFiltro) {
            console.error("No se encontraron los contenedores de resultados");
            return;
        }
        
        // Recoger datos del formulario
        const formData = new FormData(form);
        const params = new URLSearchParams();
        
        // Añadir cada campo del formulario a los parámetros
        for (const [key, value] of formData.entries()) {
            if (value.trim() !== '') {
                params.append(key, value);
            }
        }
        
        // Mostrar indicador de carga
        resultadosFiltro.innerHTML = 
            '<div class="loading-spinner"><i class="fas fa-spinner fa-spin"></i> Cargando resultados...</div>';
        
        // Mostrar el contenedor de resultados
        resultadosContainer.style.display = 'block';
        
        // Ocultar el formulario
        form.style.display = 'none';
        
        // Hacer la petición AJAX
        fetch('resultados_filtro.php?' + params.toString())
            .then(response => {
                if (!response.ok) {
                    throw new Error('Error en la respuesta del servidor');
                }
                return response.text();
            })
            .then(html => {
                resultadosFiltro.innerHTML = html;
                
                // Configurar eventos para botones de los resultados
                setTimeout(function() {
                    configurarEventosResultados();
                }, 100);
            })
            .catch(error => {
                resultadosFiltro.innerHTML = 
                    `<div class="error-message">
                        <i class="fas fa-exclamation-triangle"></i> 
                        Error al cargar los resultados: ${error.message}
                    </div>`;
            });
    } catch (error) {
        console.error("Error al aplicar filtros:", error);
    }
}

// Configurar eventos para botones en los resultados
function configurarEventosResultados() {
    try {
        const btnExcel = document.getElementById('btn-excel');
        const btnPdf = document.getElementById('btn-pdf');
        const btnPrint = document.getElementById('btn-print');
        
        if (btnExcel) {
            btnExcel.addEventListener('click', function() {
                alert('Exportando a Excel... Esta función estará disponible próximamente.');
            });
        }
        
        if (btnPdf) {
            btnPdf.addEventListener('click', function() {
                alert('Exportando a PDF... Esta función estará disponible próximamente.');
            });
        }
        
        if (btnPrint) {
            btnPrint.addEventListener('click', function() {
                window.print();
            });
        }
    } catch (error) {
        console.error("Error al configurar eventos de resultados:", error);
    }
}
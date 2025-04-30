// Esta función será llamada desde admin.php cuando cargue la vista correspondiente
function inicializarInformes() {
    console.log("Inicializando módulo de informes");
    
    // Verificar que los elementos existan antes de añadir event listeners
    const tipoInformeSelect = document.getElementById('tipo-informe');
    const btnVistaPrevia = document.getElementById('btn-vista-previa');
    const btnGenerar = document.getElementById('btn-generar');
    
    if (!tipoInformeSelect || !btnVistaPrevia || !btnGenerar) {
        console.error("Error: No se encontraron los elementos necesarios para inicializar el módulo de informes");
        return;
    }
    
    // Mostrar campos relevantes según el tipo de informe
    tipoInformeSelect.addEventListener('change', function() {
        const tipoInforme = this.value;
        document.querySelector('.selector-conector').style.display = 
            (tipoInforme === 'personal' || tipoInforme === 'detallado') ? 'block' : 'none';
        
        document.querySelector('.selector-estado').style.display = 
            (tipoInforme === 'estados' || tipoInforme === 'detallado') ? 'block' : 'none';
    });
    
    // Vista previa del informe
    btnVistaPrevia.addEventListener('click', function() {
        generarInforme(true);
    });
    
    // Generar y descargar informe
    btnGenerar.addEventListener('click', function() {
        generarInforme(false);
    });
}

function generarInforme(esPrevista) {
    // Obtener referencia al botón usado
    const btnUsado = esPrevista ? 
        document.getElementById('btn-vista-previa') : 
        document.getElementById('btn-generar');
    
    // Guardar texto original y mostrar estado de procesamiento
    const textoOriginal = btnUsado.textContent;
    btnUsado.textContent = 'Procesando...';
    btnUsado.disabled = true;
    
    // Crear el FormData desde el formulario
    const formData = new FormData(document.getElementById('informeForm'));
    formData.append('vista_previa', esPrevista ? '1' : '0');
    
    // Mostrar datos que se envían (para depuración)
    console.log("Enviando datos al servidor:");
    for (let pair of formData.entries()) {
        console.log(pair[0] + ': ' + pair[1]);
    }
    
    // Realizar la petición AJAX
    fetch('../../backend/controllers/generar_informe.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Error en la respuesta del servidor: ' + response.status);
        }
        
        if (esPrevista) {
            return response.text();
        } else {
            return response.blob();
        }
    })
    .then(data => {
        if (esPrevista) {
            // Vista previa: mostrar en el área de resultados
            const vistaPrevia = document.getElementById('vista-previa');
            vistaPrevia.style.display = 'block';
            
            const contenidoInforme = document.getElementById('contenido-informe');
            contenidoInforme.innerHTML = '<pre>' + data + '</pre>';
        } else {
            // Descarga: generar archivo
            const url = window.URL.createObjectURL(data);
            const a = document.createElement('a');
            a.style.display = 'none';
            a.href = url;
            a.download = 'informe_conexion_' + new Date().toISOString().split('T')[0] + '.txt';
            document.body.appendChild(a);
            a.click();
            window.URL.revokeObjectURL(url);
            
            // Mostrar mensaje de éxito
            alert('Informe generado con éxito');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Ha ocurrido un error al generar el informe: ' + error.message);
    })
    .finally(() => {
        // Restaurar botón
        btnUsado.textContent = textoOriginal;
        btnUsado.disabled = false;
    });
}
<?php
require_once __DIR__ . '/../../backend/config/database.php';

try {
    $db = new Database();
    $conn = $db->connect();
    
    // Obtener lista de conectores para el filtro
    $stmt = $conn->query("SELECT DISTINCT nombre_conector FROM registros WHERE nombre_conector IS NOT NULL AND nombre_conector != '' ORDER BY nombre_conector");
    $conectores = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    // Obtener lista de estados para el filtro
    $stmt = $conn->query("SELECT DISTINCT estado FROM registros WHERE estado IS NOT NULL AND estado != '' ORDER BY estado");
    $estados = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
} catch (PDOException $e) {
    echo '<div class="alert alert-danger">Error al conectar con la base de datos: ' . $e->getMessage() . '</div>';
    $conectores = [];
    $estados = [];
}
?>

<link rel="stylesheet" href="../css/styles_informes.css">

<div class="informes-container">
    <h2>Generación de Informes</h2>
    
    <div class="alert alert-info">
        <p>Selecciona los datos que deseas incluir en tu informe y haz clic en "Generar informe".</p>
    </div>
    
    <form id="informeForm" class="informe-form">
        <div class="form-section">
            <h3>Tipo de informe</h3>
            <select id="tipo-informe" name="tipo-informe" required>
                <option value="">-- Selecciona tipo de informe --</option>
                <option value="general">Estadísticas generales</option>
                <option value="personal">Estadísticas por conector</option>
                <option value="estados">Distribución por estados</option>
                <option value="detallado">Listado detallado</option>
            </select>
        </div>
        
        <div class="form-section selector-conector" style="display: none;">
            <h3>Selecciona conector</h3>
            <select id="conector" name="conector">
                <option value="">Todos los conectores</option>
                <?php foreach ($conectores as $conector): ?>
                    <option value="<?= htmlspecialchars($conector) ?>"><?= htmlspecialchars($conector) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div class="form-section selector-estado" style="display: none;">
            <h3>Filtrar por estado</h3>
            <select id="estado" name="estado">
                <option value="">Todos los estados</option>
                <?php foreach ($estados as $estado): ?>
                    <option value="<?= htmlspecialchars($estado) ?>"><?= htmlspecialchars($estado) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div class="form-section">
            <h3>Período</h3>
            <div class="date-inputs">
                <div>
                    <label for="fecha-inicio">Desde:</label>
                    <input type="date" id="fecha-inicio" name="fecha-inicio">
                </div>
                <div>
                    <label for="fecha-fin">Hasta:</label>
                    <input type="date" id="fecha-fin" name="fecha-fin">
                </div>
            </div>
        </div>
        
        <div class="form-section">
            <h3>Opciones de formato</h3>
            <div class="checkbox-group">
                <label>
                    <input type="checkbox" id="incluir-cabecera" name="incluir-cabecera" checked>
                    Incluir cabecera con fecha de generación
                </label>
                <label>
                    <input type="checkbox" id="incluir-detalle" name="incluir-detalle" checked>
                    Incluir detalles específicos
                </label>
                <label>
                    <input type="checkbox" id="incluir-resumen" name="incluir-resumen" checked>
                    Incluir resumen al final
                </label>
            </div>
        </div>
        
        <div class="form-actions">
            <button type="button" id="btn-vista-previa" class="btn-preview">Vista previa</button>
            <button type="button" id="btn-generar" class="btn-generate">Generar y descargar</button>
        </div>
    </form>
    
    <div id="vista-previa" class="preview-section" style="display: none;">
        <h3>Vista previa del informe</h3>
        <div id="contenido-informe" class="informe-contenido">
            <p>La vista previa se mostrará aquí...</p>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Mostrar campos relevantes según el tipo de informe
    document.getElementById('tipo-informe').addEventListener('change', function() {
        const tipoInforme = this.value;
        document.querySelector('.selector-conector').style.display = 
            (tipoInforme === 'personal' || tipoInforme === 'detallado') ? 'block' : 'none';
        
        document.querySelector('.selector-estado').style.display = 
            (tipoInforme === 'estados' || tipoInforme === 'detallado') ? 'block' : 'none';
    });
    
    // Vista previa del informe
    document.getElementById('btn-vista-previa').addEventListener('click', function() {
        generarInforme(true);
    });
    
    // Generar y descargar informe
    document.getElementById('btn-generar').addEventListener('click', function() {
        generarInforme(false);
    });
});

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
</script>
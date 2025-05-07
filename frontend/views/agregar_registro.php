<?php
require_once __DIR__ . '/../../backend/config/database.php';
session_start();

// Al inicio del archivo, después de los includes
$estadosPorCategoria = [
    'Contacto Inicial' => [
        'Primer contacto' => 'background:#ffcccc; color:#a00;',
        'Conectado' => 'background:#ffd6cc; color:#b36b00;',
        'Primer intento' => 'background:#f5e6ff; color:#5a00b3;',
        'Segundo Intento' => 'background:#e6ccff; color:#5a00b3;',
        'Tercero intento' => 'background:#d9b3ff; color:#5a00b3;',
        'No interesado' => 'background:#ffdddd; color:#a00;'
    ],
    'Desayunos' => [
        'No confirma desayuno' => 'background:#ffe5cc; color:#b36b00;',
        'Confirmado a Desayuno' => 'background:#cce0ff; color:#00509e;',
        'Desayuno Asistido' => 'background:#cce6ff; color:#00509e;'
    ],
    'Miembros' => [
        'Miembro activo' => 'background:#d9f2d9; color:#006600;',
        'Miembro inactivo' => 'background:#ffebcc; color:#994d00;',
        'Miembro ausente' => 'background:#ffe6e6; color:#cc0000;',
        'Congregado sin desayuno' => 'background:#d4edda; color:#155724;',
        'Visitante' => 'background:#fff; color:#222;'
    ],
    'Líderes' => [
        'Lider Activo' => 'background:#cce0ff; color:#004080;',
        'Lider inactivo' => 'background:#e6e6e6; color:#666666;',
        'Lider ausente' => 'background:#ffe6ea; color:#990033;'
    ],
    'Reconexión' => [
        'Reconectado' => 'background:#c8e6c9; color:#2e7d32;',
        'Intento de reconexión' => 'background:#dcedc8; color:#33691e;',
        'Etapa 1 reconexion (1 mes)' => 'background:#dcedc8; color:#33691e;',
        'Etapa 2 reconexion (3 mes)' => 'background:#dcedc8; color:#33691e;',
        'Etapa 3 reconexion final (6 mes)' => 'background:#dcedc8; color:#33691e;'
    ],
    'Otros' => [
        'Por Validar Estado' => 'background:#ffe5b4; color:#b36b00;'
    ]
];

// Mostrar mensajes de error o éxito si existen
if (isset($_SESSION['error_mensaje'])) {
    echo '<div class="alert alert-danger">' . $_SESSION['error_mensaje'] . '</div>';
    unset($_SESSION['error_mensaje']);
}
if (isset($_SESSION['exito_mensaje'])) {
    echo '<div class="alert alert-success">' . $_SESSION['exito_mensaje'] . '</div>';
    unset($_SESSION['exito_mensaje']);
}

// Obtener lista de conectores para el desplegable
try {
    $db = new Database();
    $conn = $db->connect();
    $stmt = $conn->query("SELECT DISTINCT nombre_conector FROM registros WHERE nombre_conector IS NOT NULL AND nombre_conector != '' ORDER BY nombre_conector");
    $conectores = $stmt->fetchAll(PDO::FETCH_COLUMN);
} catch (PDOException $e) {
    $conectores = [];
}
?>

<link rel="stylesheet" href="../css/styles_agregar_registro.css">

<div class="registro-container">
    <div class="registro-header">
        <h2><i class="fas fa-user-plus"></i> Agregar Nuevo Registro</h2>
        <a href="#" onclick="cargarVista('vista_registros.php'); return false;" class="btn-back"><i class="fas fa-arrow-left"></i> Volver a Registros</a>
    </div>
    
    <form id="formulario-registro" method="post" action="../../backend/controllers/procesar_registro.php" enctype="multipart/form-data">
        <!-- Estructura en dos columnas principales -->
        <div class="form-columns">
            <!-- Columna izquierda con datos principales -->
            <div class="form-column">
                <!-- Sección 1: Información Principal -->
                <div class="form-section">
                    <h3><i class="fas fa-user"></i> Información Personal</h3>
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="nombre_persona">Nombre <span class="required">*</span></label>
                            <input type="text" id="nombre_persona" name="nombre_persona" required>
                        </div>
                        <div class="form-group">
                            <label for="apellido_persona">Apellido <span class="required">*</span></label>
                            <input type="text" id="apellido_persona" name="apellido_persona" required>
                        </div>
                        <div class="form-group">
                            <label for="telefono">Teléfono <span class="required">*</span></label>
                            <input type="text" id="telefono" name="telefono" required>
                        </div>
                        <div class="form-group">
                            <label for="cumpleanos">Fecha de Nacimiento</label>
                            <input type="text" id="cumpleanos" name="cumpleanos" placeholder="Ejemplo: 1 de noviembre">
                            <small class="field-hint">Ingresa solo el día y mes, sin el año</small>
                        </div>
                    </div>
                </div>
                
                <!-- Sección 2: Información de Conexión -->
                <div class="form-section">
                    <h3><i class="fas fa-link"></i> Información de Conexión</h3>
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="nombre_conector">Conector</label>
                            <select id="nombre_conector" name="nombre_conector">
                                <option value="">-- Seleccione --</option>
                                <?php foreach ($conectores as $conector): ?>
                                    <option value="<?= htmlspecialchars($conector) ?>"><?= htmlspecialchars($conector) ?></option>
                                <?php endforeach; ?>
                                <option value="otro">Otro (nuevo)</option>
                            </select>
                        </div>
                        <div class="form-group" id="otro-conector-group" style="display:none;">
                            <label for="otro_conector">Nombre del Conector</label>
                            <input type="text" id="otro_conector" name="otro_conector">
                        </div>
                        <div class="form-group">
                            <label for="nombre_quien_trajo">Invitado por</label>
                            <input type="text" id="nombre_quien_trajo" name="nombre_quien_trajo">
                        </div>
                        <div class="form-group">
                            <label for="fecha_contacto">Fecha de Contacto</label>
                            <input type="date" id="fecha_contacto" name="fecha_contacto" value="<?= date('Y-m-d') ?>">
                        </div>
                        <div class="form-group">
                            <label for="fecha_ultimo_contacto">Último Contacto</label>
                            <input type="date" id="fecha_ultimo_contacto" name="fecha_ultimo_contacto">
                        </div>
                    </div>
                </div>
                
                <!-- Sección 3: Estado y Formularios -->
                <div class="form-section">
                    <h3><i class="fas fa-clipboard-list"></i> Estado y Seguimiento</h3>
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="estado">Estado Actual</label>
                            <select id="estado" name="estado">
                                <?php foreach ($estadosPorCategoria as $categoria => $estados): ?>
                                    <optgroup label="<?php echo $categoria; ?>">
                                        <?php foreach ($estados as $estado => $estilo): ?>
                                            <option value="<?php echo $estado; ?>" style="<?php echo $estilo; ?>">
                                                <?php echo $estado; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </optgroup>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="formulario_nuevos">Formulario Nuevos</label>
                            <input type="text" id="formulario_nuevos" name="formulario_nuevos" placeholder="Estado o información del formulario">
                            <small class="field-hint">Ejemplo: Completado, Pendiente, En proceso...</small>
                        </div>
                        <div class="form-group">
                            <label for="formulario_llamadas">Formulario Llamadas</label>
                            <input type="text" id="formulario_llamadas" name="formulario_llamadas" placeholder="Estado o información del formulario">
                            <small class="field-hint">Ejemplo: Realizada, Pendiente, No contesta...</small>
                        </div>
                    </div>
                </div>
                
                <!-- Sección 4: Observaciones -->
                <div class="form-section">
                    <h3><i class="fas fa-comment-alt"></i> Observaciones</h3>
                    <div class="form-group">
                        <textarea id="observaciones" name="observaciones" rows="4" placeholder="Escribe aquí las observaciones relevantes..."></textarea>
                    </div>
                </div>
            </div>
            
            <!-- Columna derecha con foto -->
            <div class="form-column">
                <!-- Sección 5: Fotografía -->
                <div class="form-section photo-section">
                    <h3><i class="fas fa-camera"></i> Fotografía</h3>
                    <div class="form-group">
                        <label for="foto">Foto:</label>
                        <input type="file" name="foto" id="foto" class="form-control" accept="image/*">
                        <small class="form-text text-muted">Formatos permitidos: JPG, PNG, GIF. Tamaño máximo: 2MB</small>
                        
                        <!-- Vista previa de la imagen -->
                        <div id="imagen-preview" class="mt-2" style="display:none;">
                            <img id="preview" src="#" alt="Vista previa" style="max-width: 200px; max-height: 200px;">
                            <button type="button" class="btn btn-sm btn-danger mt-1" onclick="eliminarImagen()">Eliminar imagen</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Botones de acción -->
        <div class="form-actions">
            <button type="submit" class="btn-primary"><i class="fas fa-save"></i> Guardar Registro</button>
            <button type="reset" class="btn-secondary"><i class="fas fa-undo"></i> Limpiar Formulario</button>
        </div>
    </form>
</div>

<script>
// Mostrar campo de texto si selecciona "Otro" en conectores
document.getElementById('nombre_conector').addEventListener('change', function() {
    if (this.value === 'otro') {
        document.getElementById('otro-conector-group').style.display = 'block';
    } else {
        document.getElementById('otro-conector-group').style.display = 'none';
    }
});

// Vista previa de la imagen
document.getElementById('foto').addEventListener('change', function(e) {
    const file = this.files[0];
    if (file) {
        const reader = new FileReader();
        
        reader.onload = function(e) {
            document.getElementById('preview').src = e.target.result;
            document.getElementById('imagen-preview').style.display = 'block';
        }
        
        reader.readAsDataURL(file);
    }
});

// Eliminar la imagen seleccionada
function eliminarImagen() {
    document.getElementById('foto').value = '';
    document.getElementById('imagen-preview').style.display = 'none';
}

// Validación del formulario
document.getElementById('form-registro').addEventListener('submit', function(e) {
    let hasErrors = false;
    
    // Validar nombre y apellido (no vacíos)
    const nombre = document.getElementById('nombre_persona').value.trim();
    const apellido = document.getElementById('apellido_persona').value.trim();
    const telefono = document.getElementById('telefono').value.trim();
    
    if (nombre === '') {
        markInvalid('nombre_persona', 'El nombre es obligatorio');
        hasErrors = true;
    }
    
    if (apellido === '') {
        markInvalid('apellido_persona', 'El apellido es obligatorio');
        hasErrors = true;
    }
    
    // Validar teléfono (no vacío)
    if (telefono === '') {
        markInvalid('telefono', 'El teléfono es obligatorio');
        hasErrors = true;
    }
    
    // Si seleccionó "otro" en conector, validar que lo haya escrito
    if (document.getElementById('nombre_conector').value === 'otro') {
        const otroConector = document.getElementById('otro_conector').value.trim();
        if (otroConector === '') {
            markInvalid('otro_conector', 'Ingresa el nombre del conector');
            hasErrors = true;
        }
    }
    
    if (hasErrors) {
        e.preventDefault(); // Detener envío del formulario
        // Desplazar a primer error
        document.querySelector('.form-group.has-error').scrollIntoView({
            behavior: 'smooth',
            block: 'center'
        });
    }
});

// Función auxiliar para marcar campos inválidos
function markInvalid(id, message) {
    const field = document.getElementById(id);
    const formGroup = field.closest('.form-group');
    formGroup.classList.add('has-error');
    
    // Añadir mensaje de error si no existe
    if (!formGroup.querySelector('.error-message')) {
        const errorMsg = document.createElement('div');
        errorMsg.className = 'error-message';
        errorMsg.textContent = message;
        formGroup.appendChild(errorMsg);
    }
    
    // Eliminar mensaje al cambiar el valor
    field.addEventListener('input', function() {
        formGroup.classList.remove('has-error');
        const errorMsg = formGroup.querySelector('.error-message');
        if (errorMsg) {
            errorMsg.remove();
        }
    }, { once: true });
}
</script>

<!-- Añadir al final del archivo para diagnosticar límites de subida -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('Verificando límites de carga de archivos...');
    
    fetch('../../backend/controllers/check_upload_limits.php')
        .then(response => response.json())
        .then(data => {
            console.log('Límites de subida:', data);
        })
        .catch(error => {
            console.error('Error verificando límites:', error);
        });
});
</script>
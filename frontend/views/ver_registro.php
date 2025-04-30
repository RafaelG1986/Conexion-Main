<?php
require_once __DIR__ . '/../../backend/config/database.php';

// Verificar si se ha proporcionado un ID
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo "Error: ID de registro no proporcionado.";
    exit;
}

$id = $_GET['id'];
$editar = isset($_GET['editar']) && $_GET['editar'] == 1;

// Obtener información del registro
try {
    $db = new Database();
    $conn = $db->connect();
    
    $stmt = $conn->prepare("SELECT * FROM registros WHERE id = :id");
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    
    $registro = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$registro) {
        echo "Error: Registro no encontrado.";
        exit;
    }
    
} catch (PDOException $e) {
    echo "Error de base de datos: " . $e->getMessage();
    exit;
}

// Definir estados y colores
$estados = [
    'Primer contacto',
    'Conectado',
    'No confirmado a desayuno',
    'Confirmado a Desayuno',
    'Desayuno Asistido',
    'Congregado sin desayuno',
    'Visitante',
    'No interesado',
    'Por Validar Estado'
];

$colores = [
    'Primer contacto' => 'background:#ffcccc; color:#a00;',
    'Conectado' => 'background:#ffd6cc; color:#b36b00;',
    'No confirmado a desayuno' => 'background:#ffe5cc; color:#b36b00;',
    'Confirmado a Desayuno' => 'background:#cce0ff; color:#00509e;',
    'Desayuno Asistido' => 'background:#cce6ff; color:#00509e;',
    'Congregado sin desayuno' => 'background:#d4edda; color:#155724;',
    'Visitante' => 'background:#fff; color:#222;',
    'No interesado' => 'background:#ffdddd; color:#a00;',
    'Por Validar Estado' => 'background:#ffe5b4; color:#b36b00;'
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $editar ? 'Editar' : 'Ver'; ?> Registro: <?php echo htmlspecialchars($registro['nombre_persona']); ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../css/styles_ver_registro.css">
</head>
<body>
    <div class="registro-container">
        <div class="registro-header">
            <h1><?php echo $editar ? 'Editar' : 'Ver'; ?> Registro</h1>
            <span class="modo-badge <?php echo $editar ? 'modo-edicion' : ''; ?>">
                <?php echo $editar ? '<i class="fas fa-edit"></i> Modo Edición' : '<i class="fas fa-eye"></i> Modo Visualización'; ?>
            </span>
        </div>
        
        <?php if ($editar): ?>
        <form action="../../backend/controllers/actualizar_registro.php" method="post" enctype="multipart/form-data">
            <input type="hidden" name="id" value="<?php echo $id; ?>">
        <?php endif; ?>

        <!-- Sección de información personal -->
        <div class="form-section">
            <h2 class="section-title"><i class="fas fa-user"></i> Información Personal</h2>
            
            <div class="foto-section">
                <div class="foto-container">
                    <img src="<?php echo !empty($registro['foto']) ? '../img/' . $registro['foto'] : '../img/no-foto.jpg'; ?>" alt="Foto de <?php echo htmlspecialchars($registro['nombre_persona']); ?>" 
                         onclick="window.open(this.src, '_blank', 'width=900,height=900')">
                </div>
                
                <?php if ($editar): ?>
                <div class="foto-upload">
                    <label for="foto">
                        <i class="fas fa-camera"></i> <?php echo !empty($registro['foto']) ? 'Cambiar foto' : 'Subir foto'; ?>
                    </label>
                    <input type="file" id="foto" name="foto" accept="image/*">
                    <input type="hidden" name="foto_actual" value="<?php echo htmlspecialchars($registro['foto'] ?? ''); ?>">
                </div>
                <?php endif; ?>
            </div>
            
            <div class="registro-grid">
                <div class="field-group field-required">
                    <label for="nombre_persona">Nombre</label>
                    <?php if ($editar): ?>
                        <input type="text" id="nombre_persona" name="nombre_persona" value="<?php echo htmlspecialchars($registro['nombre_persona']); ?>" required>
                        <div class="validation-message">Este campo es obligatorio</div>
                    <?php else: ?>
                        <div class="field-value"><?php echo htmlspecialchars($registro['nombre_persona']); ?></div>
                    <?php endif; ?>
                </div>
                
                <div class="field-group field-required">
                    <label for="apellido_persona">Apellido</label>
                    <?php if ($editar): ?>
                        <input type="text" id="apellido_persona" name="apellido_persona" value="<?php echo htmlspecialchars($registro['apellido_persona']); ?>" required>
                        <div class="validation-message">Este campo es obligatorio</div>
                    <?php else: ?>
                        <div class="field-value"><?php echo htmlspecialchars($registro['apellido_persona']); ?></div>
                    <?php endif; ?>
                </div>
                
                <div class="field-group">
                    <label for="telefono">Teléfono</label>
                    <?php if ($editar): ?>
                        <input type="tel" id="telefono" name="telefono" value="<?php echo htmlspecialchars($registro['telefono'] ?? ''); ?>">
                        <div class="validation-message">Ingrese un número de teléfono válido</div>
                    <?php else: ?>
                        <div class="field-value"><?php echo htmlspecialchars($registro['telefono'] ?? 'No especificado'); ?></div>
                    <?php endif; ?>
                </div>
                
                <div class="field-group">
                    <label for="cumpleanos">Cumpleaños</label>
                    <?php if ($editar): ?>
                        <input type="text" id="cumpleanos" name="cumpleanos" value="<?php echo htmlspecialchars($registro['cumpleanos'] ?? ''); ?>">
                    <?php else: ?>
                        <div class="field-value"><?php echo htmlspecialchars($registro['cumpleanos'] ?? 'No especificado'); ?></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Sección de información de conexión -->
        <div class="form-section">
            <h2 class="section-title"><i class="fas fa-link"></i> Información de Conexión</h2>
            
            <div class="registro-grid">
                <div class="field-group field-required">
                    <label for="nombre_conector">Nombre del Conector</label>
                    <?php if ($editar): ?>
                        <input type="text" id="nombre_conector" name="nombre_conector" value="<?php echo htmlspecialchars($registro['nombre_conector']); ?>" required>
                        <div class="validation-message">Este campo es obligatorio</div>
                    <?php else: ?>
                        <div class="field-value"><?php echo htmlspecialchars($registro['nombre_conector']); ?></div>
                    <?php endif; ?>
                </div>
                
                <div class="field-group">
                    <label for="nombre_quien_trajo">Nombre de quien lo invitó</label>
                    <?php if ($editar): ?>
                        <input type="text" id="nombre_quien_trajo" name="nombre_quien_trajo" value="<?php echo htmlspecialchars($registro['nombre_quien_trajo'] ?? ''); ?>">
                    <?php else: ?>
                        <div class="field-value"><?php echo htmlspecialchars($registro['nombre_quien_trajo'] ?? 'No especificado'); ?></div>
                    <?php endif; ?>
                </div>
                
                <div class="field-group field-required">
                    <label for="estado">Estado</label>
                    <?php if ($editar): ?>
                        <select name="estado" id="estado" class="form-control">
                            <?php foreach ($estados as $est): ?>
                                <option value="<?php echo htmlspecialchars($est); ?>" 
                                        <?php echo ($est == $registro['estado']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($est); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    <?php else: ?>
                        <div class="field-value" style="<?php echo $colores[$registro['estado']]; ?> padding: 8px; border-radius: 4px; font-weight: bold;">
                            <?php echo htmlspecialchars($registro['estado']); ?>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="field-group">
                    <label for="fecha_contacto">Fecha de Contacto</label>
                    <?php if ($editar): ?>
                        <input type="date" id="fecha_contacto" name="fecha_contacto" value="<?php echo htmlspecialchars($registro['fecha_contacto'] ?? ''); ?>">
                    <?php else: ?>
                        <div class="field-value">
                            <?php 
                            echo !empty($registro['fecha_contacto']) 
                                ? date('d/m/Y', strtotime($registro['fecha_contacto'])) 
                                : 'No especificada';
                            ?>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="field-group">
                    <label for="fecha_ultimo_contacto">Fecha Último Contacto</label>
                    <?php if ($editar): ?>
                        <input type="date" id="fecha_ultimo_contacto" name="fecha_ultimo_contacto" value="<?php echo htmlspecialchars($registro['fecha_ultimo_contacto'] ?? ''); ?>">
                    <?php else: ?>
                        <div class="field-value">
                            <?php 
                            echo !empty($registro['fecha_ultimo_contacto']) 
                                ? date('d/m/Y', strtotime($registro['fecha_ultimo_contacto'])) 
                                : 'No especificada';
                            ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Sección de formularios -->
        <div class="form-section">
            <h2 class="section-title"><i class="fas fa-file-alt"></i> Información de Formularios</h2>
            
            <div class="registro-grid">
                <div class="field-group">
                    <label for="formulario_nuevos">Formulario de Nuevos</label>
                    <?php if ($editar): ?>
                        <input type="text" id="formulario_nuevos" name="formulario_nuevos" value="<?php echo htmlspecialchars($registro['formulario_nuevos'] ?? ''); ?>">
                    <?php else: ?>
                        <div class="field-value"><?php echo htmlspecialchars($registro['formulario_nuevos'] ?? 'No especificado'); ?></div>
                    <?php endif; ?>
                </div>
                
                <div class="field-group">
                    <label for="formulario_llamadas">Formulario de Llamadas</label>
                    <?php if ($editar): ?>
                        <input type="text" id="formulario_llamadas" name="formulario_llamadas" value="<?php echo htmlspecialchars($registro['formulario_llamadas'] ?? ''); ?>">
                    <?php else: ?>
                        <div class="field-value"><?php echo htmlspecialchars($registro['formulario_llamadas'] ?? 'No especificado'); ?></div>
                    <?php endif; ?>
                </div>
                
                <div class="field-group">
                    <label for="subido_por">Subido Por</label>
                    <?php if ($editar): ?>
                        <input type="text" id="subido_por" name="subido_por" value="<?php echo htmlspecialchars($registro['subido_por'] ?? ''); ?>">
                    <?php else: ?>
                        <div class="field-value"><?php echo htmlspecialchars($registro['subido_por'] ?? 'No especificado'); ?></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Sección de observaciones -->
        <div class="form-section">
            <h2 class="section-title"><i class="fas fa-comment-alt"></i> Observaciones</h2>
            
            <div class="field-group full-width">
                <label for="observaciones">Observaciones Generales</label>
                <?php if ($editar): ?>
                    <textarea id="observaciones" name="observaciones"><?php echo htmlspecialchars($registro['observaciones'] ?? ''); ?></textarea>
                <?php else: ?>
                    <div class="field-value" style="white-space: pre-wrap;">
                        <?php echo htmlspecialchars($registro['observaciones'] ?: 'Sin observaciones generales.'); ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="botones-container">
            <?php if ($editar): ?>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Guardar Cambios
                </button>
                <a href="ver_registro.php?id=<?php echo $id; ?>" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Cancelar
                </a>
            <?php else: ?>
                <a href="ver_registro.php?id=<?php echo $id; ?>&editar=1" class="btn btn-primary">
                    <i class="fas fa-edit"></i> Editar Registro
                </a>
                <a href="vista_registros.php" class="btn btn-secondary">
                    <i class="fas fa-list"></i> Ver Todos los Registros
                </a>
            <?php endif; ?>
            
            <!-- Nuevos botones para regresar y cerrar -->
            <a href="admin.php" class="btn btn-info">
                <i class="fas fa-home"></i> Volver al Panel
            </a>
            <button type="button" class="btn btn-dark" onclick="window.close()">
                <i class="fas fa-times-circle"></i> Cerrar Pestaña
            </button>
        </div>
        
        <?php if ($editar): ?>
        </form>
        <?php endif; ?>

        <?php if (!empty($registro['observaciones'])): ?>
        <div class="observaciones-section">
            <h2><i class="fas fa-comment-alt"></i> Observaciones</h2>
            <div class="observacion-card card-effect">
                <div class="observacion-content">
                    <?php echo nl2br(htmlspecialchars($registro['observaciones'])); ?>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        console.log('Inicializando eventos de registro...');
        
        // Obtener referencia al selector de estado
        const estadoSelect = document.getElementById('estado');
        
        if (estadoSelect) {
            console.log('Selector de estado encontrado, añadiendo event listener');
            
            // Añadir evento al cambiar el valor
            estadoSelect.addEventListener('change', function() {
                const id = <?php echo $id; ?>;
                const nuevoEstado = this.value;
                
                console.log('Estado cambiado:', id, nuevoEstado);
                
                // Crear FormData para enviar
                const formData = new FormData();
                formData.append('id', id);
                formData.append('estado', nuevoEstado);
                
                // Mostrar indicador visual
                document.body.style.cursor = 'wait';
                this.disabled = true;
                
                // Realizar petición AJAX
                fetch('../../backend/controllers/actualizar_estado.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    console.log('Respuesta recibida:', data);
                    if (data.success) {
                        // Mostrar mensaje de éxito
                        alert('Estado actualizado correctamente');
                    } else {
                        alert('Error al actualizar el estado: ' + (data.message || 'Error desconocido'));
                    }
                })
                .catch(error => {
                    console.error('Error en la petición:', error);
                    alert('Error al comunicarse con el servidor');
                })
                .finally(() => {
                    // Restaurar interfaz
                    document.body.style.cursor = 'default';
                    this.disabled = false;
                });
            });
        } else {
            console.error('No se encontró el selector de estado');
        }
    });
    </script>
</body>
</html>
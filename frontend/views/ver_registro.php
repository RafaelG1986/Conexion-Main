<?php
require_once __DIR__ . '/../../backend/config/database.php';

// Función para formatear teléfono para WhatsApp
function formatearTelefonoWhatsApp($telefono) {
    // Eliminar todos los caracteres no numéricos
    $numero = preg_replace('/[^0-9]/', '', $telefono);
    
    // Si no comienza con código de país (asumimos México como predeterminado)
    if (strlen($numero) <= 10) {
        $numero = '52' . $numero;
    }
    
    return $numero;
}

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
    // Estados existentes
    'Primer contacto',
    'Conectado',
    'No confirmado a desayuno',
    'Confirmado a Desayuno',
    'Desayuno Asistido',
    'Congregado sin desayuno',
    'Visitante',
    'No interesado',
    'Por Validar Estado',
    
    // Nuevos estados
    'Primer intento',
    'Segundo Intento',
    'Tercero intento',
    'Miembro activo',
    'Miembro inactivo',
    'Miembro ausente',
    'Lider Activo',
    'Lider inactivo',
    'Lider ausente'
];

$colores = [
    // Colores existentes
    'Primer contacto' => 'background:#ffcccc; color:#a00;',
    'Conectado' => 'background:#ffd6cc; color:#b36b00;',
    'No confirmado a desayuno' => 'background:#ffe5cc; color:#b36b00;',
    'Confirmado a Desayuno' => 'background:#cce0ff; color:#00509e;',
    'Desayuno Asistido' => 'background:#cce6ff; color:#00509e;',
    'Congregado sin desayuno' => 'background:#d4edda; color:#155724;',
    'Visitante' => 'background:#fff; color:#222;',
    'No interesado' => 'background:#ffdddd; color:#a00;',
    'Por Validar Estado' => 'background:#ffe5b4; color:#b36b00;',
    
    // Colores para los nuevos estados
    'Primer intento' => 'background:#f5e6ff; color:#5a00b3;',
    'Segundo Intento' => 'background:#e6ccff; color:#5a00b3;',
    'Tercero intento' => 'background:#d9b3ff; color:#5a00b3;',
    'Miembro activo' => 'background:#d9f2d9; color:#006600;',
    'Miembro inactivo' => 'background:#ffebcc; color:#994d00;',
    'Miembro ausente' => 'background:#ffe6e6; color:#cc0000;',
    'Lider Activo' => 'background:#cce0ff; color:#004080;',
    'Lider inactivo' => 'background:#e6e6e6; color:#666666;',
    'Lider ausente' => 'background:#ffe6ea; color:#990033;'
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
                    
                    <?php if (!$editar && !empty($registro['telefono'])): ?>
                        <div class="contact-actions" style="margin-top: 10px;">
                            <a href="https://api.whatsapp.com/send?phone=<?php echo formatearTelefonoWhatsApp($registro['telefono']); ?>&text=Hola%20<?php echo urlencode($registro['nombre_persona'] . ' ' . $registro['apellido_persona']); ?>,%20te%20contacto%20desde%20Conexi%C3%B3n" 
                               target="_blank" 
                               class="btn btn-whatsapp">
                                <i class="fab fa-whatsapp"></i> Contactar por WhatsApp
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
                
                <?php if (!empty($registro['telefono'])): ?>
                    <div class="action-buttons">
                        <a href="https://api.whatsapp.com/send?phone=<?php echo formatearTelefonoWhatsApp($registro['telefono']); ?>" 
                           class="btn btn-success btn-whatsapp" 
                           target="_blank">
                            <i class="fab fa-whatsapp"></i> Contactar por WhatsApp
                        </a>
                    </div>
                <?php endif; ?>
                
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
                        <select name="estado" id="estado" class="form-control" required>
                            <optgroup label="Contacto Inicial">
                                <option value="Primer contacto" <?php echo ($registro['estado'] == 'Primer contacto') ? 'selected' : ''; ?> style="<?php echo $colores['Primer contacto']; ?>">Primer contacto</option>
                                <option value="Primer intento" <?php echo ($registro['estado'] == 'Primer intento') ? 'selected' : ''; ?> style="<?php echo $colores['Primer intento']; ?>">Primer intento</option>
                                <option value="Segundo Intento" <?php echo ($registro['estado'] == 'Segundo Intento') ? 'selected' : ''; ?> style="<?php echo $colores['Segundo Intento']; ?>">Segundo Intento</option>
                                <option value="Tercero intento" <?php echo ($registro['estado'] == 'Tercero intento') ? 'selected' : ''; ?> style="<?php echo $colores['Tercero intento']; ?>">Tercero intento</option>
                                <option value="No interesado" <?php echo ($registro['estado'] == 'No interesado') ? 'selected' : ''; ?> style="<?php echo $colores['No interesado']; ?>">No interesado</option>
                            </optgroup>
                            
                            <optgroup label="Desayunos">
                                <option value="No confirmado a desayuno" <?php echo ($registro['estado'] == 'No confirmado a desayuno') ? 'selected' : ''; ?> style="<?php echo $colores['No confirmado a desayuno']; ?>">No confirmado a desayuno</option>
                                <option value="Confirmado a Desayuno" <?php echo ($registro['estado'] == 'Confirmado a Desayuno') ? 'selected' : ''; ?> style="<?php echo $colores['Confirmado a Desayuno']; ?>">Confirmado a Desayuno</option>
                                <option value="Desayuno Asistido" <?php echo ($registro['estado'] == 'Desayuno Asistido') ? 'selected' : ''; ?> style="<?php echo $colores['Desayuno Asistido']; ?>">Desayuno Asistido</option>
                            </optgroup>
                            
                            <optgroup label="Miembros">
                                <option value="Miembro activo" <?php echo ($registro['estado'] == 'Miembro activo') ? 'selected' : ''; ?> style="<?php echo $colores['Miembro activo']; ?>">Miembro activo</option>
                                <option value="Miembro inactivo" <?php echo ($registro['estado'] == 'Miembro inactivo') ? 'selected' : ''; ?> style="<?php echo $colores['Miembro inactivo']; ?>">Miembro inactivo</option>
                                <option value="Miembro ausente" <?php echo ($registro['estado'] == 'Miembro ausente') ? 'selected' : ''; ?> style="<?php echo $colores['Miembro ausente']; ?>">Miembro ausente</option>
                                <option value="Congregado sin desayuno" <?php echo ($registro['estado'] == 'Congregado sin desayuno') ? 'selected' : ''; ?> style="<?php echo $colores['Congregado sin desayuno']; ?>">Congregado sin desayuno</option>
                                <option value="Visitante" <?php echo ($registro['estado'] == 'Visitante') ? 'selected' : ''; ?> style="<?php echo $colores['Visitante']; ?>">Visitante</option>
                            </optgroup>
                            
                            <optgroup label="Líderes">
                                <option value="Lider Activo" <?php echo ($registro['estado'] == 'Lider Activo') ? 'selected' : ''; ?> style="<?php echo $colores['Lider Activo']; ?>">Lider Activo</option>
                                <option value="Lider inactivo" <?php echo ($registro['estado'] == 'Lider inactivo') ? 'selected' : ''; ?> style="<?php echo $colores['Lider inactivo']; ?>">Lider inactivo</option>
                                <option value="Lider ausente" <?php echo ($registro['estado'] == 'Lider ausente') ? 'selected' : ''; ?> style="<?php echo $colores['Lider ausente']; ?>">Lider ausente</option>
                            </optgroup>
                            
                            <optgroup label="Otros">
                                <option value="Conectado" <?php echo ($registro['estado'] == 'Conectado') ? 'selected' : ''; ?> style="<?php echo $colores['Conectado']; ?>">Conectado</option>
                                <option value="Por Validar Estado" <?php echo ($registro['estado'] == 'Por Validar Estado') ? 'selected' : ''; ?> style="<?php echo $colores['Por Validar Estado']; ?>">Por Validar Estado</option>
                            </optgroup>
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
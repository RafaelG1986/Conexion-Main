<?php
require_once __DIR__ . '/../../backend/config/database.php';

// Detectar si es una carga AJAX
$ajax = (isset($_GET['ajax']) && $_GET['ajax'] == '1');

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
    // Contacto Inicial
    'Primer contacto',
    'Conectado',
    'Primer intento',
    'Segundo Intento',
    'Tercero intento',
    'Intento llamada telefonica',     // NUEVO
    'Intento 2 llamada telefonica',   // NUEVO
    'Intento 3 llamada telefonica',   // NUEVO
    'No interesado',
    
    // Desayunos
    'No confirma desayuno', // Modificado (antes era "No confirmado a desayuno")
    'Confirmado a Desayuno',
    'Desayuno Asistido',
    
    // Miembros
    'Miembro activo',
    'Miembro inactivo',
    'Miembro ausente',
    'Congregado sin desayuno',
    'Visitante',
    
    // Líderes
    'Lider Activo',
    'Lider inactivo',
    'Lider ausente',
    
    // Reconexión
    'Reconectado',             // Nuevo
    'Intento de reconexión',   // Nuevo
    'Etapa 1 reconexion (1 mes)', // Nuevo
    'Etapa 2 reconexion (3 mes)', // Nuevo
    'Etapa 3 reconexion final (6 mes)', // Nuevo
    
    // Otros
    'Por Validar Estado',
    'Nulo',                      // NUEVO
    'Delegado a acompañante',    // NUEVO
    'Datos no autorizados',      // NUEVO
    'Datos incorrectos'          // NUEVO
];

$colores = [
    // Contacto Inicial
    'Primer contacto' => 'background:#ffcccc; color:#a00;',
    'Conectado' => 'background:#ffd6cc; color:#b36b00;',
    'Primer intento' => 'background:#f5e6ff; color:#5a00b3;',
    'Segundo Intento' => 'background:#e6ccff; color:#5a00b3;',
    'Tercero intento' => 'background:#d9b3ff; color:#5a00b3;',
    'Intento llamada telefonica' => 'background:#e1f5fe; color:#0288d1;',     // NUEVO
    'Intento 2 llamada telefonica' => 'background:#b3e5fc; color:#0277bd;',   // NUEVO
    'Intento 3 llamada telefonica' => 'background:#81d4fa; color:#01579b;',   // NUEVO
    'No interesado' => 'background:#ffdddd; color:#a00;',
    
    // Desayunos
    'No confirma desayuno' => 'background:#ffe5cc; color:#b36b00;', // Modificado
    'Confirmado a Desayuno' => 'background:#cce0ff; color:#00509e;',
    'Desayuno Asistido' => 'background:#cce6ff; color:#00509e;',
    
    // Miembros
    'Miembro activo' => 'background:#d9f2d9; color:#006600;',
    'Miembro inactivo' => 'background:#ffebcc; color:#994d00;',
    'Miembro ausente' => 'background:#ffe6e6; color:#cc0000;',
    'Congregado sin desayuno' => 'background:#d4edda; color:#155724;',
    'Visitante' => 'background:#fff; color:#222;',
    
    // Líderes
    'Lider Activo' => 'background:#cce0ff; color:#004080;',
    'Lider inactivo' => 'background:#e6e6e6; color:#666666;',
    'Lider ausente' => 'background:#ffe6ea; color:#990033;',
    
    // Reconexión
    'Reconectado' => 'background:#c8e6c9; color:#2e7d32;',          // Nuevo
    'Intento de reconexión' => 'background:#dcedc8; color:#33691e;', // Nuevo
    'Etapa 1 reconexion (1 mes)' => 'background:#dcedc8; color:#33691e;', // Nuevo
    'Etapa 2 reconexion (3 mes)' => 'background:#dcedc8; color:#33691e;', // Nuevo
    'Etapa 3 reconexion final (6 mes)' => 'background:#dcedc8; color:#33691e;', // Nuevo
    
    // Otros
    'Por Validar Estado' => 'background:#ffe5b4; color:#b36b00;',
    'Nulo' => 'background:#e0e0e0; color:#757575;',                   // NUEVO - Gris
    'Delegado a acompañante' => 'background:#e1bee7; color:#6a1b9a;', // NUEVO - Púrpura
    'Datos no autorizados' => 'background:#ffcdd2; color:#d32f2f;',   // NUEVO - Rojo claro
    'Datos incorrectos' => 'background:#f8bbd0; color:#c2185b;',       // NUEVO
    
    // Compatibilidad con estados antiguos
    'No confirmado a desayuno' => 'background:#ffe5cc; color:#b36b00;'
];

// Si es una carga AJAX, solo mostrar el contenido del registro
if ($ajax) {
    // Solo emitir el contenido HTML necesario
?>
<div class="registro-container">
    <!-- NUEVA SECCIÓN DE BOTONES AL INICIO -->
    <div class="registro-header">
        <h2><i class="fas fa-<?php echo $editar ? 'edit' : 'eye'; ?>"></i> 
            <?php echo $editar ? 'Editar Registro' : 'Detalles del Registro'; ?>
        </h2>
        
        <div class="actions-bar">
            <?php if ($editar): ?>
                <!-- Botones para modo edición -->
                <button type="submit" form="form-registro" class="btn-primary" title="Guardar Cambios">
                    <i class="fas fa-save"></i>
                </button>
                <button type="reset" form="form-registro" class="btn-secondary" title="Restablecer Campos">
                    <i class="fas fa-undo"></i>
                </button>
            <?php else: ?>
                <!-- Botones para modo visualización -->
                <a href="ver_registro.php?id=<?php echo $registro['id']; ?>&editar=1" class="btn-edit" title="Editar Registro">
                    <i class="fas fa-edit"></i>
                </a>
            <?php endif; ?>
            
            <!-- Botón de WhatsApp se mantiene -->
            <a href="https://wa.me/<?php echo formatearTelefonoWhatsApp($registro['telefono']); ?>" 
               class="btn-whatsapp" target="_blank" title="Contactar por WhatsApp">
                <i class="fab fa-whatsapp"></i>
            </a>
        </div>
    </div>
    
    <!-- FORMULARIO PRINCIPAL -->
    <form id="form-registro" method="post" action="../../backend/controllers/actualizar_registro.php" enctype="multipart/form-data">
        <input type="hidden" name="id" value="<?php echo $registro['id']; ?>">
        
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
                        <!-- Botón de WhatsApp eliminado para evitar duplicados -->
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
                            <optgroup label="Contacto Inicial">
                                <?php foreach (['Primer contacto', 'Conectado', 'Primer intento', 'Segundo Intento', 'Tercero intento', 'Intento llamada telefonica', 'Intento 2 llamada telefonica', 'Intento 3 llamada telefonica', 'No interesado'] as $est): ?>
                                    <option value="<?php echo htmlspecialchars($est); ?>" 
                                            <?php echo (trim($est) == trim($registro['estado'])) ? 'selected' : ''; ?>
                                            style="<?php echo $colores[$est]; ?>">
                                        <?php echo htmlspecialchars($est); ?>
                                    </option>
                                <?php endforeach; ?>
                            </optgroup>

                            <optgroup label="Desayunos">
                                <?php foreach (['No confirma desayuno', 'Confirmado a Desayuno', 'Desayuno Asistido'] as $est): ?>
                                    <option value="<?php echo htmlspecialchars($est); ?>" 
                                            <?php echo (trim($est) == trim($registro['estado']) || ($est == 'No confirma desayuno' && $registro['estado'] == 'No confirmado a desayuno')) ? 'selected' : ''; ?>
                                            style="<?php echo $colores[$est]; ?>">
                                        <?php echo htmlspecialchars($est); ?>
                                    </option>
                                <?php endforeach; ?>
                            </optgroup>

                            <optgroup label="Miembros">
                                <?php foreach (['Miembro activo', 'Miembro inactivo', 'Miembro ausente', 'Congregado sin desayuno', 'Visitante'] as $est): ?>
                                    <option value="<?php echo htmlspecialchars($est); ?>" 
                                            <?php echo (trim($est) == trim($registro['estado'])) ? 'selected' : ''; ?>
                                            style="<?php echo $colores[$est]; ?>">
                                        <?php echo htmlspecialchars($est); ?>
                                    </option>
                                <?php endforeach; ?>
                            </optgroup>

                            <optgroup label="Líderes">
                                <?php foreach (['Lider Activo', 'Lider inactivo', 'Lider ausente'] as $est): ?>
                                    <option value="<?php echo htmlspecialchars($est); ?>" 
                                            <?php echo (trim($est) == trim($registro['estado'])) ? 'selected' : ''; ?>
                                            style="<?php echo $colores[$est]; ?>">
                                        <?php echo htmlspecialchars($est); ?>
                                    </option>
                                <?php endforeach; ?>
                            </optgroup>

                            <optgroup label="Reconexión">
                                <?php foreach (['Reconectado', 'Intento de reconexión', 'Etapa 1 reconexion (1 mes)', 'Etapa 2 reconexion (3 mes)', 'Etapa 3 reconexion final (6 mes)'] as $est): ?>
                                    <option value="<?php echo htmlspecialchars($est); ?>" 
                                            <?php echo (trim($est) == trim($registro['estado'])) ? 'selected' : ''; ?>
                                            style="<?php echo $colores[$est]; ?>">
                                        <?php echo htmlspecialchars($est); ?>
                                    </option>
                                <?php endforeach; ?>
                            </optgroup>

                            <optgroup label="Otros">
                                <?php foreach (['Por Validar Estado', 'Nulo', 'Delegado a acompañante', 'Datos no autorizados', 'Datos incorrectos'] as $est): ?>
                                    <option value="<?php echo htmlspecialchars($est); ?>" 
                                            <?php echo (trim($est) == trim($registro['estado'])) ? 'selected' : ''; ?>
                                            style="<?php echo $colores[$est]; ?>">
                                        <?php echo htmlspecialchars($est); ?>
                                    </option>
                                <?php endforeach; ?>
                            </optgroup>
                        </select>
                    <?php else: ?>
                        <div class="field-value" style="<?php 
                            // Maneja el caso para estados antiguos
                            if ($registro['estado'] == 'No confirmado a desayuno') {
                                echo $colores['No confirma desayuno'];
                            } else {
                                echo isset($colores[$registro['estado']]) ? $colores[$registro['estado']] : '';
                            }
                        ?> padding: 8px; border-radius: 4px; font-weight: bold;">
                            <?php 
                            // Mostrar el nombre actualizado del estado si es el caso antiguo
                            echo ($registro['estado'] == 'No confirmado a desayuno') 
                                ? 'No confirma desayuno' 
                                : htmlspecialchars($registro['estado']); 
                            ?>
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

                <!-- Nuevo campo para Próximo Contacto -->
                <div class="field-group">
                    <label for="proximo_contacto">Próximo Contacto</label>
                    <?php if ($editar): ?>
                        <input type="date" id="proximo_contacto" name="proximo_contacto" 
                               value="<?php echo htmlspecialchars($registro['proximo_contacto'] ?? ''); ?>">
                    <?php else: ?>
                        <div class="field-value">
                            <?php 
                            if (!empty($registro['proximo_contacto'])) {
                                $fecha_programada = new DateTime($registro['proximo_contacto']);
                                $hoy = new DateTime('today');
                                $diff = $hoy->diff($fecha_programada);
                                
                                echo date('d/m/Y', strtotime($registro['proximo_contacto']));
                                
                                // Mostrar indicador si la fecha está cercana o pasada
                                if ($fecha_programada < $hoy) {
                                    echo ' <span class="badge-vencido">Vencido</span>';
                                } elseif ($diff->days <= 2) {
                                    echo ' <span class="badge-urgente">Próximo</span>';
                                }
                            } else {
                                echo 'No programado';
                            }
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
    </form>
</div>
<?php
} else {
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"width=device-width, initial-scale=1.0">
    <title><?php echo $editar ? 'Editar' : 'Ver'; ?> Registro: <?php echo htmlspecialchars($registro['nombre_persona']); ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../css/styles_ver_registro.css">
</head>
<body>
    <div class="registro-container">
        <!-- NUEVA SECCIÓN DE BOTONES AL INICIO -->
        <div class="registro-header">
            <h2><i class="fas fa-<?php echo $editar ? 'edit' : 'eye'; ?>"></i> 
                <?php echo $editar ? 'Editar Registro' : 'Detalles del Registro'; ?>
            </h2>
            
            <div class="actions-bar">
                <?php if ($editar): ?>
                    <!-- Botones para modo edición -->
                    <button type="submit" form="form-registro" class="btn-primary" title="Guardar Cambios">
                        <i class="fas fa-save"></i>
                    </button>
                    <button type="reset" form="form-registro" class="btn-secondary" title="Restablecer Campos">
                        <i class="fas fa-undo"></i>
                    </button>
                <?php else: ?>
                    <!-- Botones para modo visualización -->
                    <a href="ver_registro.php?id=<?php echo $registro['id']; ?>&editar=1" class="btn-edit" title="Editar Registro">
                        <i class="fas fa-edit"></i>
                    </a>
                <?php endif; ?>
                
                <!-- Botón de WhatsApp se mantiene -->
                <a href="https://wa.me/<?php echo formatearTelefonoWhatsApp($registro['telefono']); ?>" 
                   class="btn-whatsapp" target="_blank" title="Contactar por WhatsApp">
                    <i class="fab fa-whatsapp"></i>
                </a>
            </div>
        </div>
        
        <!-- FORMULARIO PRINCIPAL -->
        <form id="form-registro" method="post" action="../../backend/controllers/actualizar_registro.php" enctype="multipart/form-data">
            <input type="hidden" name="id" value="<?php echo $registro['id']; ?>">
            
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
                            <!-- Botón de WhatsApp eliminado para evitar duplicados -->
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
                                <optgroup label="Contacto Inicial">
                                    <?php foreach (['Primer contacto', 'Conectado', 'Primer intento', 'Segundo Intento', 'Tercero intento', 'Intento llamada telefonica', 'Intento 2 llamada telefonica', 'Intento 3 llamada telefonica', 'No interesado'] as $est): ?>
                                        <option value="<?php echo htmlspecialchars($est); ?>" 
                                                <?php echo (trim($est) == trim($registro['estado'])) ? 'selected' : ''; ?>
                                                style="<?php echo $colores[$est]; ?>">
                                            <?php echo htmlspecialchars($est); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </optgroup>

                                <optgroup label="Desayunos">
                                    <?php foreach (['No confirma desayuno', 'Confirmado a Desayuno', 'Desayuno Asistido'] as $est): ?>
                                        <option value="<?php echo htmlspecialchars($est); ?>" 
                                                <?php echo (trim($est) == trim($registro['estado']) || ($est == 'No confirma desayuno' && $registro['estado'] == 'No confirmado a desayuno')) ? 'selected' : ''; ?>
                                                style="<?php echo $colores[$est]; ?>">
                                            <?php echo htmlspecialchars($est); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </optgroup>

                                <optgroup label="Miembros">
                                    <?php foreach (['Miembro activo', 'Miembro inactivo', 'Miembro ausente', 'Congregado sin desayuno', 'Visitante'] as $est): ?>
                                        <option value="<?php echo htmlspecialchars($est); ?>" 
                                                <?php echo (trim($est) == trim($registro['estado'])) ? 'selected' : ''; ?>
                                                style="<?php echo $colores[$est]; ?>">
                                            <?php echo htmlspecialchars($est); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </optgroup>

                                <optgroup label="Líderes">
                                    <?php foreach (['Lider Activo', 'Lider inactivo', 'Lider ausente'] as $est): ?>
                                        <option value="<?php echo htmlspecialchars($est); ?>" 
                                                <?php echo (trim($est) == trim($registro['estado'])) ? 'selected' : ''; ?>
                                                style="<?php echo $colores[$est]; ?>">
                                            <?php echo htmlspecialchars($est); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </optgroup>

                                <optgroup label="Reconexión">
                                    <?php foreach (['Reconectado', 'Intento de reconexión', 'Etapa 1 reconexion (1 mes)', 'Etapa 2 reconexion (3 mes)', 'Etapa 3 reconexion final (6 mes)'] as $est): ?>
                                        <option value="<?php echo htmlspecialchars($est); ?>" 
                                                <?php echo (trim($est) == trim($registro['estado'])) ? 'selected' : ''; ?>
                                                style="<?php echo $colores[$est]; ?>">
                                            <?php echo htmlspecialchars($est); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </optgroup>

                                <optgroup label="Otros">
                                    <?php foreach (['Por Validar Estado', 'Nulo', 'Delegado a acompañante', 'Datos no autorizados', 'Datos incorrectos'] as $est): ?>
                                        <option value="<?php echo htmlspecialchars($est); ?>" 
                                                <?php echo (trim($est) == trim($registro['estado'])) ? 'selected' : ''; ?>
                                                style="<?php echo $colores[$est]; ?>">
                                            <?php echo htmlspecialchars($est); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </optgroup>
                            </select>
                        <?php else: ?>
                            <div class="field-value" style="<?php 
                                // Maneja el caso para estados antiguos
                                if ($registro['estado'] == 'No confirmado a desayuno') {
                                    echo $colores['No confirma desayuno'];
                                } else {
                                    echo isset($colores[$registro['estado']]) ? $colores[$registro['estado']] : '';
                                }
                            ?> padding: 8px; border-radius: 4px; font-weight: bold;">
                                <?php 
                                // Mostrar el nombre actualizado del estado si es el caso antiguo
                                echo ($registro['estado'] == 'No confirmado a desayuno') 
                                    ? 'No confirma desayuno' 
                                    : htmlspecialchars($registro['estado']); 
                                ?>
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

                    <!-- Nuevo campo para Próximo Contacto -->
                    <div class="field-group">
                        <label for="proximo_contacto">Próximo Contacto</label>
                        <?php if ($editar): ?>
                            <input type="date" id="proximo_contacto" name="proximo_contacto" 
                                   value="<?php echo htmlspecialchars($registro['proximo_contacto'] ?? ''); ?>">
                        <?php else: ?>
                            <div class="field-value">
                                <?php 
                                if (!empty($registro['proximo_contacto'])) {
                                    $fecha_programada = new DateTime($registro['proximo_contacto']);
                                    $hoy = new DateTime('today');
                                    $diff = $hoy->diff($fecha_programada);
                                    
                                    echo date('d/m/Y', strtotime($registro['proximo_contacto']));
                                    
                                    // Mostrar indicador si la fecha está cercana o pasada
                                    if ($fecha_programada < $hoy) {
                                        echo ' <span class="badge-vencido">Vencido</span>';
                                    } elseif ($diff->days <= 2) {
                                        echo ' <span class="badge-urgente">Próximo</span>';
                                    }
                                } else {
                                    echo 'No programado';
                                }
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
        </form>
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
<?php
}
?>
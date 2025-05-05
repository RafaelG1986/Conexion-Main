<?php
require_once __DIR__ . '/../../backend/config/database.php';

// Función para formatear teléfono para WhatsApp
function formatearTelefonoWhatsApp($telefono) {
    // Eliminar todos los caracteres no numéricos
    $numero = preg_replace('/[^0-9]/', '', $telefono);
    
    // Si no comienza con código de país (asumimos México como predeterminado)
    if (strlen($numero) <= 10) {
        $numero = '57' . $numero;
    }
    
    return $numero;
}

/**
 * Obtiene el mensaje predefinido para WhatsApp según el estado del registro
 */
function obtenerMensajeWhatsApp($estado, $nombre, $apellido, $conector = 'Conexión') {
    $nombreCompleto = trim($nombre . ' ' . $apellido);
    
    // Mensajes predefinidos por estado
    $mensajes = [
        // Contacto Inicial
        'Primer contacto' => "Hola {$nombreCompleto}, te contactamos de Iglesia en Casa. Gracias por compartir tus datos con nosotros. ¿Te gustaría recibir más información sobre nuestras actividades?",
        
        'Conectado' => "Hola {$nombreCompleto}, te saludamos de Iglesia en Casa. Nos alegra haber conectado contigo. ¿Podemos ayudarte con algo específico?",
        
        'Primer intento' => "Buen día {$nombreCompleto}, Te Habla {$conector} de parte de los Pastores Javier Aponte y Paola Palacios; Queremos reconfirmar una invitación a un desayuno para comentarte a cerca de nuestra vision, y propósito; comentarte de nuestras reuniones semanales: Domingo 8:00am y 10:30am, Miércoles 6:45pm. Tambien tenemos reuniones de jóvenes los sábados a las 2:00pm y gozamos de nuestro estudio Bíblico llamado Crecer. Adicional estamos conectados con Rhema, Institución Bíblica mundial, donde podrás conocer mas la palabra, con los cursos de CEBCO. Queremos lo mejor para ti. Un abrazo, buen dia.",
        
        'Segundo Intento' => "Hola {$nombreCompleto}, ¿cómo estás? Te escribo nuevamente desde Iglesia en Casa. Nos gustaría saber si te interesa conocer más sobre nuestras actividades.",
        
        'Tercero intento' => "Hola {$nombreCompleto}, ¿cómo has estado? Te contactamos nuevamente desde Iglesia en Casa. Nos encantaría contar contigo en nuestro próximo evento.",
        
        'No interesado' => "Hola {$nombreCompleto}, respetamos tu decisión de no participar por ahora en Iglesia en Casa. Si en algún momento deseas volver a conectar, estaremos aquí.",
        
        // Desayunos
        'No confirma desayuno' => "Hola {$nombreCompleto}, te escribimos de Iglesia en Casa. Queremos invitarte personalmente a nuestro próximo desayuno de conexión. ¿Te gustaría asistir?",
        
        'Confirmado a Desayuno' => "Hola {$nombreCompleto}, gracias por confirmar tu asistencia al desayuno de Iglesia en Casa. Te esperamos este domingo a las 9:00 AM. ¿Necesitas indicaciones para llegar?",
        
        'Desayuno Asistido' => "Hola {$nombreCompleto}, fue un gusto tenerte en nuestro desayuno. Nos encantaría seguir viéndote en Iglesia en Casa. ¿Te gustaría conocer nuestras próximas actividades?",
        
        // Miembros
        'Miembro activo' => "Hola {$nombreCompleto}, te saludamos desde Iglesia en Casa. Queremos recordarte sobre nuestra próxima actividad este domingo. ¡Contamos contigo!",
        
        'Miembro inactivo' => "Hola {$nombreCompleto}, hace tiempo que no te vemos en Iglesia en Casa. Nos gustaría saber cómo estás y si podemos apoyarte en algo.",
        
        'Miembro ausente' => "Hola {$nombreCompleto}, te extrañamos en Iglesia en Casa. ¿Cómo has estado? Nos gustaría reconectar contigo y saber cómo podemos servirte.",
        
        'Congregado sin desayuno' => "Hola {$nombreCompleto}, nos da gusto verte en los servicios de Iglesia en Casa. Te invitamos a nuestro próximo desayuno de conexión. ¿Te gustaría participar?",
        
        'Visitante' => "Hola {$nombreCompleto}, gracias por visitar Iglesia en Casa. Fue un gusto conocerte. ¿Te gustaría recibir información sobre nuestras próximas actividades?",
        
        // Líderes
        'Lider Activo' => "Hola {$nombreCompleto}, te escribo para coordinar los detalles de nuestro próximo evento en Iglesia en Casa. ¿Podemos agendar una llamada para discutir los preparativos?",
        
        'Lider inactivo' => "Hola {$nombreCompleto}, te escribo desde Iglesia en Casa. Extrañamos tu liderazgo y participación. ¿Podemos conversar sobre tu regreso al equipo?",
        
        'Lider ausente' => "Hola {$nombreCompleto}, desde Iglesia en Casa nos gustaría reconectar contigo. Tu experiencia y liderazgo son valiosos para nosotros. ¿Cómo has estado?",
        
        // Reconexión
        'Reconectado' => "Hola {$nombreCompleto}, qué alegría reconectar contigo en Iglesia en Casa. ¿Te gustaría participar en nuestras próximas actividades?",
        
        'Intento de reconexión' => "Hola {$nombreCompleto}, te escribimos desde Iglesia en Casa. Nos gustaría reconectar contigo y saber cómo has estado últimamente.",
        
        // Otros
        'Por Validar Estado' => "Hola {$nombreCompleto}, te contactamos desde Iglesia en Casa. Nos gustaría conocerte mejor y entender cómo podemos servirte."
    ];
    
    // Retornar el mensaje correspondiente al estado o un mensaje genérico si no está definido
    return isset($mensajes[$estado]) 
        ? $mensajes[$estado] 
        : "Hola {$nombreCompleto}, te contacto desde Iglesia en Casa. ¿Cómo estás?";
}
?>

<link rel="stylesheet" href="../css/styles_vista_registros.css">

<?php
// Lista completa de estados organizada por categorías
$estados = [
    // Contacto Inicial
    'Primer contacto',
    'Conectado',
    'Primer intento',
    'Segundo Intento',
    'Tercero intento',
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
    
    // Otros
    'Por Validar Estado'
];

// Colores para cada estado
$colores = [
    // Contacto Inicial
    'Primer contacto' => 'background:#ffcccc; color:#a00;',
    'Conectado' => 'background:#ffd6cc; color:#b36b00;',
    'Primer intento' => 'background:#f5e6ff; color:#5a00b3;',
    'Segundo Intento' => 'background:#e6ccff; color:#5a00b3;',
    'Tercero intento' => 'background:#d9b3ff; color:#5a00b3;',
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
    
    // Otros
    'Por Validar Estado' => 'background:#ffe5b4; color:#b36b00;'
];

try {
    $db = new Database();
    $conn = $db->connect();
    $stmt = $conn->query("SELECT foto, nombre_persona, apellido_persona, telefono, nombre_conector, nombre_quien_trajo, estado, id, observaciones, fecha_contacto FROM registros ORDER BY CASE WHEN fecha_contacto IS NULL THEN 1 ELSE 0 END, fecha_contacto DESC");
    $registros = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo '<p>Error al conectar con la base de datos: ' . $e->getMessage() . '</p>';
    exit;
}
?>

<div class="registros-container" style="max-width: 100%; padding: 0;">
    <h2>Registros</h2>
    <div id="mensaje-estado"></div>
    <div class="table-responsive" style="overflow-x: auto; width: 100%;">
        <table border="1" cellpadding="6" cellspacing="0">
            <thead>
                <tr>
                    <th>Foto</th>
                    <th>Nombre</th>
                    <th>Apellido</th>
                    <th>Teléfono</th>
                    <th>Nombre del Conector</th>
                    <th>Quién lo invitó</th>
                    <th>Estado</th>
                    <th>Fecha de ingreso</th><!-- Agregar esta línea -->
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($registros)): ?>
                    <tr><td colspan="9" style="text-align:center;">No hay registros.</td></tr>
                <?php endif; ?>
                <?php foreach ($registros as $registro): ?>
                <tr data-id="<?php echo $registro['id']; ?>">
                    <td>
                        <?php if (!empty($registro['foto'])): ?>
                            <img src="../img/<?php echo htmlspecialchars($registro['foto']); ?>" alt="Foto">
                        <?php else: ?>
                            Sin foto
                        <?php endif; ?>
                    </td>
                    <td><?php echo htmlspecialchars($registro['nombre_persona']); ?></td>
                    <td><?php echo htmlspecialchars($registro['apellido_persona']); ?></td>
                    <td><?php echo htmlspecialchars($registro['telefono']); ?></td>
                    <td><?php echo htmlspecialchars($registro['nombre_conector']); ?></td>
                    <td><?php echo htmlspecialchars($registro['nombre_quien_trajo']); ?></td>
                    <td id="estado-td-<?php echo $registro['id']; ?>" class="celda-estado" 
                        <?php 
                        // Aplicar el color directamente a la celda según el estado actual
                        if (isset($colores[trim($registro['estado'])])) {
                            echo 'style="' . $colores[trim($registro['estado'])] . '"';
                        }
                        ?>>
                        <select onchange="actualizarEstado(this.value, <?php echo $registro['id']; ?>)">
                            <optgroup label="Contacto Inicial">
                                <?php foreach (['Primer contacto', 'Conectado', 'Primer intento', 'Segundo Intento', 'Tercero intento', 'No interesado'] as $estadoOpcion): ?>
                                    <?php $estadoClase = str_replace(' ', '', $estadoOpcion); ?>
                                    <option value="<?php echo htmlspecialchars($estadoOpcion); ?>"
                                        class="estado-<?php echo $estadoClase; ?>"
                                        style="<?php echo $colores[$estadoOpcion]; ?>"
                                        <?php if (trim($registro['estado']) == trim($estadoOpcion)) echo 'selected'; ?>>
                                        <?php echo htmlspecialchars($estadoOpcion); ?>
                                    </option>
                                <?php endforeach; ?>
                            </optgroup>
                            
                            <optgroup label="Desayunos">
                                <?php foreach (['No confirma desayuno', 'Confirmado a Desayuno', 'Desayuno Asistido'] as $estadoOpcion): ?>
                                    <?php $estadoClase = str_replace(' ', '', $estadoOpcion); ?>
                                    <option value="<?php echo htmlspecialchars($estadoOpcion); ?>"
                                        class="estado-<?php echo $estadoClase; ?>"
                                        style="<?php echo $colores[$estadoOpcion]; ?>"
                                        <?php if (trim($registro['estado']) == trim($estadoOpcion)) echo 'selected'; ?>>
                                        <?php echo htmlspecialchars($estadoOpcion); ?>
                                    </option>
                                <?php endforeach; ?>
                            </optgroup>
                            
                            <optgroup label="Miembros">
                                <?php foreach (['Miembro activo', 'Miembro inactivo', 'Miembro ausente', 'Congregado sin desayuno', 'Visitante'] as $estadoOpcion): ?>
                                    <?php $estadoClase = str_replace(' ', '', $estadoOpcion); ?>
                                    <option value="<?php echo htmlspecialchars($estadoOpcion); ?>"
                                        class="estado-<?php echo $estadoClase; ?>"
                                        style="<?php echo $colores[$estadoOpcion]; ?>"
                                        <?php if (trim($registro['estado']) == trim($estadoOpcion)) echo 'selected'; ?>>
                                        <?php echo htmlspecialchars($estadoOpcion); ?>
                                    </option>
                                <?php endforeach; ?>
                            </optgroup>
                            
                            <optgroup label="Líderes">
                                <?php foreach (['Lider Activo', 'Lider inactivo', 'Lider ausente'] as $estadoOpcion): ?>
                                    <?php $estadoClase = str_replace(' ', '', $estadoOpcion); ?>
                                    <option value="<?php echo htmlspecialchars($estadoOpcion); ?>"
                                        class="estado-<?php echo $estadoClase; ?>"
                                        style="<?php echo $colores[$estadoOpcion]; ?>"
                                        <?php if (trim($registro['estado']) == trim($estadoOpcion)) echo 'selected'; ?>>
                                        <?php echo htmlspecialchars($estadoOpcion); ?>
                                    </option>
                                <?php endforeach; ?>
                            </optgroup>
                            
                            <optgroup label="Reconexión">
                                <?php foreach (['Reconectado', 'Intento de reconexión'] as $estadoOpcion): ?>
                                    <?php $estadoClase = str_replace(' ', '', $estadoOpcion); ?>
                                    <option value="<?php echo htmlspecialchars($estadoOpcion); ?>"
                                        class="estado-<?php echo $estadoClase; ?>"
                                        style="<?php echo $colores[$estadoOpcion]; ?>"
                                        <?php if (trim($registro['estado']) == trim($estadoOpcion)) echo 'selected'; ?>>
                                        <?php echo htmlspecialchars($estadoOpcion); ?>
                                    </option>
                                <?php endforeach; ?>
                            </optgroup>
                            
                            <optgroup label="Otros">
                                <?php foreach (['Por Validar Estado'] as $estadoOpcion): ?>
                                    <?php $estadoClase = str_replace(' ', '', $estadoOpcion); ?>
                                    <option value="<?php echo htmlspecialchars($estadoOpcion); ?>"
                                        class="estado-<?php echo $estadoClase; ?>"
                                        style="<?php echo $colores[$estadoOpcion]; ?>"
                                        <?php if (trim($registro['estado']) == trim($estadoOpcion)) echo 'selected'; ?>>
                                        <?php echo htmlspecialchars($estadoOpcion); ?>
                                    </option>
                                <?php endforeach; ?>
                            </optgroup>
                        </select>
                    </td>                   
                    <td>
                        <?php 
                        if (!empty($registro['fecha_contacto'])) {
                            echo date('d/m/Y', strtotime($registro['fecha_contacto'])); 
                        } else {
                            echo "Sin fecha";
                        }
                        ?>
                    </td>
                    <td class="acciones-td">
                        <div class="botones-accion">
                            <button type="button" class="btn-accion" title="Editar" 
                                    onclick="cargarRegistro(<?php echo $registro['id']; ?>, true)">
                                <i class="fas fa-edit"></i>
                            </button>
                            
                            <button type="button" class="btn-accion" title="Ver detalles" 
                                    onclick="cargarRegistro(<?php echo $registro['id']; ?>)">
                                <i class="fas fa-search"></i>
                            </button>
                            
                            <button type="button" class="btn-accion" title="Agregar observación" 
                                    onclick="abrirObservacion(<?php echo $registro['id']; ?>)">
                                <i class="fas fa-comment"></i>
                            </button>
                            
                            <button type="button" class="btn-accion" title="Ver observaciones" 
                                    onclick="verObservaciones(<?php echo $registro['id']; ?>)">
                                <i class="fas fa-eye"></i>
                            </button>
                            
                            <button type="button" class="btn-accion" title="Eliminar" 
                                    onclick="eliminarRegistro(<?php echo $registro['id']; ?>)">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                            
                            <!-- Añadir botón de WhatsApp si existe teléfono -->
                            <?php if (!empty($registro['telefono'])): ?>
                                <?php 
                                // Obtener el mensaje personalizado según el estado
                                $mensaje = obtenerMensajeWhatsApp(
                                    trim($registro['estado']),
                                    $registro['nombre_persona'],
                                    $registro['apellido_persona'],
                                    $registro['nombre_conector'] ?: 'Conexión' // Pasar el nombre del conector o 'Conexión' si está vacío
                                );
                                ?>
                                <a href="https://api.whatsapp.com/send?phone=<?php echo formatearTelefonoWhatsApp($registro['telefono']); ?>&text=<?php echo urlencode($mensaje); ?>" 
                                   target="_blank" 
                                   class="btn-accion btn-whatsapp" 
                                   title="Contactar por WhatsApp"
                                   data-mensaje="<?php echo htmlspecialchars($mensaje); ?>">
                                    <i class="fab fa-whatsapp"></i>
                                </a>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<div id="modal-observaciones" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Agregar observación</h3>
            <span class="close" onclick="cerrarModal('modal-observaciones')">&times;</span>
        </div>
        <div class="modal-body">
            <input type="hidden" id="id-registro" value="">
            <div class="form-group">
                <label for="texto-observacion">Observación:</label>
                <textarea id="texto-observacion" rows="4" class="form-control"></textarea>
            </div>
        </div>
        <div class="modal-footer">
            <button id="btn-guardar-obs" class="btn btn-primary" onclick="guardarObservacion()">
                <i class="fas fa-save"></i> Guardar
            </button>
            <button type="button" onclick="cerrarModal('modal-observaciones')" class="btn btn-secondary">
                <i class="fas fa-times"></i> Cancelar
            </button>
        </div>
    </div>
</div>

<script>
// Mejorar la función para abrir y cerrar el modal
window.abrirModal = function() {
    document.getElementById('modal-observaciones').style.display = 'block';
    document.getElementById('modal-overlay').style.display = 'block';
}

window.cerrarModal = function() {
    document.getElementById('modal-observaciones').style.display = 'none';
    document.getElementById('modal-overlay').style.display = 'none';
}

// Reemplazar la función original si es necesario
if (typeof verObservaciones === 'function') {
    const originalVerObservaciones = verObservaciones;
    window.verObservaciones = function(id) {
        originalVerObservaciones(id);
        abrirModal();
    }
}

// Script para mejorar la interacción con los selectores de estado
document.addEventListener('DOMContentLoaded', function() {
    // Seleccionar todos los selectores de estado
    const selects = document.querySelectorAll('select[onchange^="actualizarEstado"]');
    
    // Para cada select, añadir listeners para resaltar la celda cuando se enfoca
    selects.forEach(select => {
        // Añadir clase al inicio para destacar el estado seleccionado
        const selectedOption = select.options[select.selectedIndex];
        if (selectedOption) {
            selectedOption.classList.add('estado-selected');
        }
        
        // Cuando el usuario hace foco en el selector
        select.addEventListener('focus', function() {
            this.closest('td').classList.add('estado-activo');
        });
        
        // Cuando el usuario quita el foco
        select.addEventListener('blur', function() {
            this.closest('td').classList.remove('estado-activo');
        });
        
        // Cuando cambia la selección, actualizar la clase de la opción seleccionada
        select.addEventListener('change', function() {
            // Quitar la clase de la opción previamente seleccionada
            Array.from(this.options).forEach(option => {
                option.classList.remove('estado-selected');
            });
            
            // Añadir la clase a la nueva opción seleccionada
            const selectedOption = this.options[this.selectedIndex];
            if (selectedOption) {
                selectedOption.classList.add('estado-selected');
                
                // Actualizar el color de fondo de la celda para que coincida con la opción seleccionada
                const celda = this.closest('td');
                if (celda && selectedOption.style.background) {
                    celda.style.background = selectedOption.style.background;
                    celda.style.color = selectedOption.style.color;
                }
            }
        });
    });
});

// Modificar la función actualizarEstado existente o crearla si no existe
window.actualizarEstado = function(id, estado) {
    const originalActualizarEstado = window.actualziarEstadoOriginal || function(id, estado) {
        fetch('../../backend/controllers/actualizar_estado.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `id=${id}&estado=${encodeURIComponent(estado)}`
        })
        .then(res => res.json())
        .then(data => {
            const mensaje = document.getElementById('mensaje-estado');
            mensaje.textContent = data.mensaje;
            mensaje.className = data.exito ? 'exito' : 'error';
            mensaje.style.display = 'block';
            
            setTimeout(() => {
                mensaje.style.display = 'none';
            }, 3000);
        })
        .catch(error => {
            console.error('Error:', error);
        });
    };
    
    // Guardar la función original si no se ha hecho ya
    if (!window.actualziarEstadoOriginal) {
        window.actualziarEstadoOriginal = actualizarEstado;
    }
    
    // Llamar a la función original
    originalActualizarEstado(id, estado);
    
    // Buscar el color correspondiente al estado seleccionado
    const celda = document.getElementById(`estado-td-${id}`);
    if (celda) {
        const select = celda.querySelector('select');
        if (select) {
            const selectedOption = Array.from(select.options).find(opt => opt.value === estado);
            if (selectedOption) {
                // Aplicar el color de la opción a la celda
                celda.style.background = selectedOption.style.background;
                celda.style.color = selectedOption.style.color;
            }
        }
    }
};
</script>

<script>
// Esta función reconfigurará los eventos después de cargar la vista
document.addEventListener('DOMContentLoaded', function() {
    console.log('Vista de registros cargada, configurando botones de observaciones');
    
    // Configurar el botón de guardar observación
    if (typeof configurarBotonObservacion === 'function') {
        configurarBotonObservacion();
    }
});

// Asegurar que el modal de observaciones funcione correctamente
document.querySelectorAll('button[onclick^="abrirObservacion"]').forEach(btn => {
    btn.addEventListener('click', function(e) {
        const idMatch = this.getAttribute('onclick').match(/abrirObservacion\((\d+)\)/);
        if (idMatch && idMatch[1]) {
            const id = idMatch[1];
            console.log('Click en botón observación para ID:', id);
        }
    });
});
</script>

<script>
// Función para guardar observaciones
function guardarObservacion() {
    console.log('Ejecutando guardarObservacion() desde vista_registros.php');
    
    // Obtener datos del formulario
    const id = document.getElementById('id-registro').value;
    const observacion = document.getElementById('texto-observacion').value;
    
    console.log('ID:', id);
    console.log('Observación:', observacion);

    // Validar
    if (!id) {
        alert('Error: No se encontró el ID del registro');
        return;
    }
    
    if (!observacion.trim()) {
        alert('Por favor escribe una observación');
        return;
    }
    
    // Mostrar indicador de carga
    const btnGuardar = document.getElementById('btn-guardar-obs');
    btnGuardar.disabled = true;
    btnGuardar.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Guardando...';
    
    // Crear datos para enviar
    const formData = new FormData();
    formData.append('id', id);
    formData.append('observaciones', observacion);
    
    // URL absoluta para evitar problemas con rutas relativas
    const url = '../../backend/controllers/agregar_observacion.php';
    
    // Enviar solicitud al servidor
    fetch(url, {
        method: 'POST',
        body: formData,
        credentials: 'include'
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`Error HTTP: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        console.log('Respuesta:', data);
        
        // Restaurar botón
        btnGuardar.disabled = false;
        btnGuardar.innerHTML = '<i class="fas fa-save"></i> Guardar';
        
        if (data.success) {
            // Mostrar mensaje de éxito
            alert('Observación guardada correctamente');
            
            // Cerrar el modal
            cerrarModal('modal-observaciones');
            
            // Recargar la página para mostrar la nueva observación
            location.reload();
        } else {
            alert('Error: ' + (data.message || 'No se pudo guardar la observación'));
        }
    })
    .catch(error => {
        console.error('Error en fetch:', error);
        
        // Restaurar botón
        btnGuardar.disabled = false;
        btnGuardar.innerHTML = '<i class="fas fa-save"></i> Guardar';
        
        // Mostrar mensaje de error
        alert('Error de conexión: ' + error.message);
    });
}
</script>

<script>
// Mejora para los tooltips en dispositivos móviles
document.addEventListener('DOMContentLoaded', function() {
    // Para cada botón de acción
    document.querySelectorAll('.btn-accion').forEach(button => {
        // Al mantener presionado en móvil (para emular hover)
        button.addEventListener('touchstart', function() {
            const title = this.getAttribute('title');
            if (title) {
                alert(title);
            }
        });
    });
});
</script>

<script>
// Vista previa de mensaje WhatsApp
document.addEventListener('DOMContentLoaded', function() {
    // Crear elemento para mostrar la vista previa
    const previewElement = document.createElement('div');
    previewElement.className = 'whatsapp-preview';
    previewElement.style.display = 'none';
    previewElement.style.position = 'absolute';
    previewElement.style.background = '#DCF8C6';
    previewElement.style.border = '1px solid #4CAF50';
    previewElement.style.borderRadius = '8px';
    previewElement.style.padding = '10px 15px';
    previewElement.style.maxWidth = '300px';
    previewElement.style.boxShadow = '0 3px 10px rgba(0,0,0,0.2)';
    previewElement.style.zIndex = '1000';
    previewElement.style.fontSize = '14px';
    previewElement.style.color = '#303030';
    document.body.appendChild(previewElement);
    
    // Para cada botón de WhatsApp
    document.querySelectorAll('.btn-whatsapp').forEach(btn => {
        // Mostrar vista previa al pasar el ratón
        btn.addEventListener('mouseenter', function(e) {
            const mensaje = this.getAttribute('data-mensaje');
            if (mensaje) {
                previewElement.textContent = mensaje;
                
                // Posicionar cerca del botón
                const rect = this.getBoundingClientRect();
                previewElement.style.left = rect.right + 10 + 'px';
                previewElement.style.top = rect.top + window.scrollY + 'px';
                
                // Mostrar
                previewElement.style.display = 'block';
            }
        });
        
        // Ocultar al quitar el ratón
        btn.addEventListener('mouseleave', function() {
            previewElement.style.display = 'none';
        });
    });
});
</script>

<style>
/* Estilos para el historial de observaciones */
.observaciones-historial {
    max-height: 400px;
    overflow-y: auto;
    padding: 15px;
    background-color: #f9f9f9;
    border-radius: 4px;
    box-shadow: inset 0 0 5px rgba(0,0,0,0.1);
}

.observaciones-historial p {
    margin-bottom: 15px;
    padding-bottom: 15px;
    border-bottom: 1px solid #e0e0e0;
}

.observaciones-historial p:last-child {
    border-bottom: none;
}

.observacion-meta {
    color: #2980b9;
    font-weight: bold;
    display: block;
    margin-bottom: 5px;
}

.sin-observaciones {
    color: #7f8c8d;
    font-style: italic;
    text-align: center;
    padding: 20px;
}

.error-mensaje {
    color: #e74c3c;
    text-align: center;
    padding: 20px;
}

.cargando {
    text-align: center;
    padding: 20px;
    color: #7f8c8d;
}

/* Estilos para los botones de acción */
.botones-accion {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
    justify-content: center;
}

.btn-accion {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 36px;
    height: 36px;
    border-radius: 50%;
    border: none;
    background-color: #f1f1f1;
    color: #333;
    font-size: 14px;
    cursor: pointer;
    transition: all 0.2s ease;
    padding: 0;
}

.btn-accion:hover {
    transform: translateY(-2px);
    box-shadow: 0 3px 5px rgba(0,0,0,0.1);
}

/* Colores específicos para cada tipo de acción */
.btn-accion:nth-child(1) { /* Editar */
    background-color: #e3f2fd;
    color: #0d47a1;
}

.btn-accion:nth-child(2) { /* Ver detalles */
    background-color: #e8f5e9;
    color: #1b5e20;
}

.btn-accion:nth-child(3) { /* Agregar observación */
    background-color: #fff3e0;
    color: #e65100;
}

.btn-accion:nth-child(4) { /* Ver observaciones */
    background-color: #ede7f6;
    color: #4527a0;
}

.btn-accion:nth-child(5) { /* Eliminar */
    background-color: #ffebee;
    color: #b71c1c;
}

.btn-whatsapp { /* WhatsApp */
    background-color: #e0f7fa;
    color: #00796b;
}

/* Tamaño fijo para la columna de acciones */
.acciones-td {
    min-width: 200px;
}

/* Responsive para dispositivos pequeños */
@media (max-width: 768px) {
    .botones-accion {
        flex-direction: column;
        align-items: center;
    }
    
    .btn-accion {
        margin-bottom: 5px;
    }
}
</style>
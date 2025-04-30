<?php
require_once __DIR__ . '/../../backend/config/database.php';
?>

<link rel="stylesheet" href="../css/styles_vista_registros.css">

<?php
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

try {
    $db = new Database();
    $conn = $db->connect();
    $stmt = $conn->query("SELECT foto, nombre_persona, apellido_persona, telefono, nombre_conector, nombre_quien_trajo, estado, id, observaciones FROM registros ORDER BY id DESC");
    $registros = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo '<p>Error al conectar con la base de datos: ' . $e->getMessage() . '</p>';
    exit;
}
?>

<div class="registros-container">
    <h2>Registros</h2>
    <div id="mensaje-estado"></div>
    <div class="table-responsive">
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
                    <th>Observaciones</th>
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
                            <?php
                            foreach ($estados as $estadoOpcion):
                                $estadoClase = str_replace(' ', '', $estadoOpcion);
                            ?>
                                <option value="<?php echo htmlspecialchars($estadoOpcion); ?>"
                                    class="estado-<?php echo $estadoClase; ?>"
                                    style="<?php echo $colores[$estadoOpcion]; ?>"
                                    <?php if (trim($registro['estado']) == trim($estadoOpcion)) echo 'selected'; ?>>
                                    <?php echo htmlspecialchars($estadoOpcion); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                    <td><?php echo htmlspecialchars($registro['observaciones']); ?></td>
                    <td class="acciones-td">
                        <div class="botones-accion">
                            <button type="button" class="btn-accion" title="Editar" 
                                    onclick="window.open('ver_registro.php?id=<?php echo $registro['id']; ?>&editar=1', '_blank')">
                                <i class="fas fa-edit"></i>
                            </button>
                            
                            <button type="button" class="btn-accion" title="Ver detalles" 
                                    onclick="window.open('ver_registro.php?id=<?php echo $registro['id']; ?>', '_blank')">
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
<?php
require_once __DIR__ . '/../config/database.php';

// Verificar sesión (añadir esta parte si es necesario)
session_start();
if (!isset($_SESSION['user'])) {
    header('Content-Type: text/plain');
    echo "Error: No hay sesión activa.";
    exit;
}

// Obtener parámetros del formulario
$tipoInforme = isset($_POST['tipo-informe']) ? $_POST['tipo-informe'] : '';
$conector = isset($_POST['conector']) ? $_POST['conector'] : '';
$estado = isset($_POST['estado']) ? $_POST['estado'] : '';
$fechaInicio = isset($_POST['fecha-inicio']) ? $_POST['fecha-inicio'] : '';
$fechaFin = isset($_POST['fecha-fin']) ? $_POST['fecha-fin'] : '';
$incluirCabecera = isset($_POST['incluir-cabecera']) ? true : false;
$incluirDetalle = isset($_POST['incluir-detalle']) ? true : false;
$incluirResumen = isset($_POST['incluir-resumen']) ? true : false;
$vistaPrevista = isset($_POST['vista_previa']) && $_POST['vista_previa'] === '1';

// Crear el informe
$informe = generarInforme(
    $tipoInforme, 
    $conector, 
    $estado, 
    $fechaInicio, 
    $fechaFin, 
    $incluirCabecera, 
    $incluirDetalle, 
    $incluirResumen
);

// Si es vista previa, devolver HTML, si no, enviar para descarga
if ($vistaPrevista) {
    header('Content-Type: text/plain; charset=utf-8');
    echo $informe;
} else {
    $fecha = date('Y-m-d');
    $nombreArchivo = "informe_{$fecha}.txt";
    
    // Encabezados para forzar la descarga
    header('Content-Type: text/plain; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $nombreArchivo . '"');
    header('Content-Length: ' . strlen($informe));
    header('Cache-Control: private, max-age=0, must-revalidate');
    header('Pragma: public');
    
    // Asegurarse de que no haya salida antes de los encabezados
    if (ob_get_level()) ob_end_clean();
    
    echo $informe;
}
exit;

/**
 * Genera el contenido del informe según los parámetros
 */
function generarInforme($tipoInforme, $conector, $estado, $fechaInicio, $fechaFin, $incluirCabecera, $incluirDetalle, $incluirResumen) {
    $db = new Database();
    $conn = $db->connect();
    $resultado = "";
    
    // Añadir cabecera si está seleccionada
    if ($incluirCabecera) {
        $resultado .= "=======================================================\n";
        $resultado .= "                INFORME DE CONEXIÓN                    \n";
        $resultado .= "=======================================================\n";
        $resultado .= "Fecha de generación: " . date('d/m/Y H:i:s') . "\n";
        $resultado .= "Tipo de informe: " . obtenerNombreTipoInforme($tipoInforme) . "\n";
        
        if ($conector) {
            $resultado .= "Conector: " . $conector . "\n";
        }
        
        if ($estado) {
            $resultado .= "Estado: " . $estado . "\n";
        }
        
        if ($fechaInicio && $fechaFin) {
            $resultado .= "Período: Del " . date('d/m/Y', strtotime($fechaInicio)) . " al " . date('d/m/Y', strtotime($fechaFin)) . "\n";
        } elseif ($fechaInicio) {
            $resultado .= "Período: Desde " . date('d/m/Y', strtotime($fechaInicio)) . "\n";
        } elseif ($fechaFin) {
            $resultado .= "Período: Hasta " . date('d/m/Y', strtotime($fechaFin)) . "\n";
        }
        
        $resultado .= "=======================================================\n\n";
    }
    
    // Generar el contenido según el tipo de informe
    switch ($tipoInforme) {
        case 'general':
            $resultado .= generarInformeGeneral($conn, $fechaInicio, $fechaFin, $incluirDetalle);
            break;
            
        case 'personal':
            $resultado .= generarInformePersonal($conn, $conector, $fechaInicio, $fechaFin, $incluirDetalle);
            break;
            
        case 'estados':
            $resultado .= generarInformeEstados($conn, $estado, $fechaInicio, $fechaFin, $incluirDetalle);
            break;
            
        case 'detallado':
            $resultado .= generarInformeDetallado($conn, $conector, $estado, $fechaInicio, $fechaFin, $incluirDetalle);
            break;
            
        default:
            $resultado .= "No se ha seleccionado un tipo de informe válido.\n";
    }
    
    // Añadir resumen si está seleccionado
    if ($incluirResumen) {
        $resultado .= "\n=======================================================\n";
        $resultado .= "                     RESUMEN                           \n";
        $resultado .= "=======================================================\n";
        
        // Obtener totales generales
        $sql = "SELECT COUNT(*) as total FROM registros WHERE 1=1";
        
        if ($conector) {
            $sql .= " AND nombre_conector = :conector";
        }
        
        if ($estado) {
            $sql .= " AND estado = :estado";
        }
        
        if ($fechaInicio) {
            $sql .= " AND fecha_creacion >= :fecha_inicio";
        }
        
        if ($fechaFin) {
            $sql .= " AND fecha_creacion <= :fecha_fin";
        }
        
        $stmt = $conn->prepare($sql);
        
        if ($conector) {
            $stmt->bindParam(':conector', $conector);
        }
        
        if ($estado) {
            $stmt->bindParam(':estado', $estado);
        }
        
        if ($fechaInicio) {
            $fechaInicioDb = $fechaInicio . ' 00:00:00';
            $stmt->bindParam(':fecha_inicio', $fechaInicioDb);
        }
        
        if ($fechaFin) {
            $fechaFinDb = $fechaFin . ' 23:59:59';
            $stmt->bindParam(':fecha_fin', $fechaFinDb);
        }
        
        $stmt->execute();
        $totalRegistros = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        $resultado .= "Total de registros: " . $totalRegistros . "\n";
        
        // Añadir más estadísticas al resumen según el tipo de informe
        switch ($tipoInforme) {
            case 'general':
                $resultado .= generarResumenGeneral($conn, $fechaInicio, $fechaFin);
                break;
                
            case 'personal':
                $resultado .= generarResumenPersonal($conn, $conector, $fechaInicio, $fechaFin);
                break;
                
            case 'estados':
                $resultado .= generarResumenEstados($conn, $estado, $fechaInicio, $fechaFin);
                break;
        }
    }
    
    return $resultado;
}

/**
 * Obtiene el nombre descriptivo del tipo de informe
 */
function obtenerNombreTipoInforme($tipo) {
    switch ($tipo) {
        case 'general': return 'Estadísticas Generales';
        case 'personal': return 'Estadísticas por Conector';
        case 'estados': return 'Distribución por Estados';
        case 'detallado': return 'Listado Detallado';
        default: return 'Desconocido';
    }
}

/**
 * Genera informe de estadísticas generales
 */
function generarInformeGeneral($conn, $fechaInicio, $fechaFin, $incluirDetalle) {
    $resultado = "ESTADÍSTICAS GENERALES\n";
    $resultado .= "---------------------\n\n";
    
    // Consulta para obtener conteo por conector
    $sql = "SELECT nombre_conector, COUNT(*) as total FROM registros WHERE nombre_conector IS NOT NULL AND nombre_conector != ''";
    
    if ($fechaInicio) {
        $sql .= " AND fecha_creacion >= :fecha_inicio";
    }
    
    if ($fechaFin) {
        $sql .= " AND fecha_creacion <= :fecha_fin";
    }
    
    $sql .= " GROUP BY nombre_conector ORDER BY total DESC";
    
    $stmt = $conn->prepare($sql);
    
    if ($fechaInicio) {
        $fechaInicioDb = $fechaInicio . ' 00:00:00';
        $stmt->bindParam(':fecha_inicio', $fechaInicioDb);
    }
    
    if ($fechaFin) {
        $fechaFinDb = $fechaFin . ' 23:59:59';
        $stmt->bindParam(':fecha_fin', $fechaFinDb);
    }
    
    $stmt->execute();
    $conectores = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $resultado .= "DISTRIBUCIÓN POR CONECTORES:\n";
    foreach ($conectores as $row) {
        $resultado .= str_pad($row['nombre_conector'], 30) . ": " . $row['total'] . " registros\n";
    }
    
    // Consulta para obtener conteo por estado
    $sql = "SELECT estado, COUNT(*) as total FROM registros WHERE estado IS NOT NULL AND estado != ''";
    
    if ($fechaInicio) {
        $sql .= " AND fecha_creacion >= :fecha_inicio";
    }
    
    if ($fechaFin) {
        $sql .= " AND fecha_creacion <= :fecha_fin";
    }
    
    $sql .= " GROUP BY estado ORDER BY total DESC";
    
    $stmt = $conn->prepare($sql);
    
    if ($fechaInicio) {
        $fechaInicioDb = $fechaInicio . ' 00:00:00';
        $stmt->bindParam(':fecha_inicio', $fechaInicioDb);
    }
    
    if ($fechaFin) {
        $fechaFinDb = $fechaFin . ' 23:59:59';
        $stmt->bindParam(':fecha_fin', $fechaFinDb);
    }
    
    $stmt->execute();
    $estados = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $resultado .= "\nDISTRIBUCIÓN POR ESTADOS:\n";
    foreach ($estados as $row) {
        $resultado .= str_pad($row['estado'], 30) . ": " . $row['total'] . " registros\n";
    }
    
    return $resultado;
}

/**
 * Genera informe de estadísticas por conector
 */
function generarInformePersonal($conn, $conector, $fechaInicio, $fechaFin, $incluirDetalle) {
    if (empty($conector)) {
        return "ESTADÍSTICAS POR CONECTOR\n----------------------\n\nSelecciona un conector específico para ver sus estadísticas.\n";
    }
    
    $resultado = "ESTADÍSTICAS DEL CONECTOR: " . strtoupper($conector) . "\n";
    $resultado .= str_repeat("-", 40) . "\n\n";
    
    // Consulta para obtener conteo por estado para este conector
    $sql = "SELECT estado, COUNT(*) as total FROM registros WHERE nombre_conector = :conector";
    
    if ($fechaInicio) {
        $sql .= " AND fecha_creacion >= :fecha_inicio";
    }
    
    if ($fechaFin) {
        $sql .= " AND fecha_creacion <= :fecha_fin";
    }
    
    $sql .= " GROUP BY estado ORDER BY total DESC";
    
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':conector', $conector);
    
    if ($fechaInicio) {
        $fechaInicioDb = $fechaInicio . ' 00:00:00';
        $stmt->bindParam(':fecha_inicio', $fechaInicioDb);
    }
    
    if ($fechaFin) {
        $fechaFinDb = $fechaFin . ' 23:59:59';
        $stmt->bindParam(':fecha_fin', $fechaFinDb);
    }
    
    $stmt->execute();
    $estados = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $resultado .= "DISTRIBUCIÓN POR ESTADOS:\n";
    foreach ($estados as $row) {
        $resultado .= str_pad($row['estado'], 30) . ": " . $row['total'] . " registros\n";
    }
    
    if ($incluirDetalle) {
        // Consulta para obtener los últimos 10 registros de este conector
        $sql = "SELECT nombre_persona, apellido_persona, telefono, estado, fecha_creacion 
                FROM registros 
                WHERE nombre_conector = :conector";
        
        if ($fechaInicio) {
            $sql .= " AND fecha_creacion >= :fecha_inicio";
        }
        
        if ($fechaFin) {
            $sql .= " AND fecha_creacion <= :fecha_fin";
        }
        
        $sql .= " ORDER BY fecha_creacion DESC LIMIT 10";
        
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':conector', $conector);
        
        if ($fechaInicio) {
            $fechaInicioDb = $fechaInicio . ' 00:00:00';
            $stmt->bindParam(':fecha_inicio', $fechaInicioDb);
        }
        
        if ($fechaFin) {
            $fechaFinDb = $fechaFin . ' 23:59:59';
            $stmt->bindParam(':fecha_fin', $fechaFinDb);
        }
        
        $stmt->execute();
        $registros = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $resultado .= "\nÚLTIMOS REGISTROS:\n";
        foreach ($registros as $reg) {
            $resultado .= "- " . $reg['nombre_persona'] . " " . $reg['apellido_persona'];
            $resultado .= " | Tel: " . $reg['telefono'];
            $resultado .= " | Estado: " . $reg['estado'];
            $resultado .= " | Fecha: " . date('d/m/Y', strtotime($reg['fecha_creacion'])) . "\n";
        }
    }
    
    return $resultado;
}

/**
 * Genera informe por estados
 */
function generarInformeEstados($conn, $estado, $fechaInicio, $fechaFin, $incluirDetalle) {
    $resultado = "INFORME DE ESTADOS\n";
    $resultado .= "------------------\n\n";
    
    if (empty($estado)) {
        // Mostrar distribución general de estados
        $sql = "SELECT estado, COUNT(*) as total FROM registros WHERE estado IS NOT NULL AND estado != ''";
        
        if ($fechaInicio) {
            $sql .= " AND fecha_creacion >= :fecha_inicio";
        }
        
        if ($fechaFin) {
            $sql .= " AND fecha_creacion <= :fecha_fin";
        }
        
        $sql .= " GROUP BY estado ORDER BY total DESC";
        
        $stmt = $conn->prepare($sql);
        
        if ($fechaInicio) {
            $fechaInicioDb = $fechaInicio . ' 00:00:00';
            $stmt->bindParam(':fecha_inicio', $fechaInicioDb);
        }
        
        if ($fechaFin) {
            $fechaFinDb = $fechaFin . ' 23:59:59';
            $stmt->bindParam(':fecha_fin', $fechaFinDb);
        }
        
        $stmt->execute();
        $estados = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $resultado .= "DISTRIBUCIÓN GENERAL POR ESTADOS:\n";
        foreach ($estados as $row) {
            $resultado .= str_pad($row['estado'], 30) . ": " . $row['total'] . " registros\n";
        }
    } else {
        // Mostrar información detallada del estado seleccionado
        $resultado .= "ESTADO SELECCIONADO: " . strtoupper($estado) . "\n";
        $resultado .= str_repeat("-", 40) . "\n\n";
        
        // Contar registros con este estado por conector
        $sql = "SELECT nombre_conector, COUNT(*) as total 
                FROM registros 
                WHERE estado = :estado AND nombre_conector IS NOT NULL AND nombre_conector != ''";
        
        if ($fechaInicio) {
            $sql .= " AND fecha_creacion >= :fecha_inicio";
        }
        
        if ($fechaFin) {
            $sql .= " AND fecha_creacion <= :fecha_fin";
        }
        
        $sql .= " GROUP BY nombre_conector ORDER BY total DESC";
        
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':estado', $estado);
        
        if ($fechaInicio) {
            $fechaInicioDb = $fechaInicio . ' 00:00:00';
            $stmt->bindParam(':fecha_inicio', $fechaInicioDb);
        }
        
        if ($fechaFin) {
            $fechaFinDb = $fechaFin . ' 23:59:59';
            $stmt->bindParam(':fecha_fin', $fechaFinDb);
        }
        
        $stmt->execute();
        $conectores = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $resultado .= "DISTRIBUCIÓN POR CONECTORES:\n";
        foreach ($conectores as $row) {
            $resultado .= str_pad($row['nombre_conector'], 30) . ": " . $row['total'] . " registros\n";
        }
        
        if ($incluirDetalle) {
            // Listar últimos 10 registros con este estado
            $sql = "SELECT nombre_persona, apellido_persona, telefono, nombre_conector, fecha_creacion 
                    FROM registros 
                    WHERE estado = :estado";
            
            if ($fechaInicio) {
                $sql .= " AND fecha_creacion >= :fecha_inicio";
            }
            
            if ($fechaFin) {
                $sql .= " AND fecha_creacion <= :fecha_fin";
            }
            
            $sql .= " ORDER BY fecha_creacion DESC LIMIT 10";
            
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':estado', $estado);
            
            if ($fechaInicio) {
                $fechaInicioDb = $fechaInicio . ' 00:00:00';
                $stmt->bindParam(':fecha_inicio', $fechaInicioDb);
            }
            
            if ($fechaFin) {
                $fechaFinDb = $fechaFin . ' 23:59:59';
                $stmt->bindParam(':fecha_fin', $fechaFinDb);
            }
            
            $stmt->execute();
            $registros = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $resultado .= "\nÚLTIMOS REGISTROS CON ESTADO '" . $estado . "':\n";
            foreach ($registros as $reg) {
                $resultado .= "- " . $reg['nombre_persona'] . " " . $reg['apellido_persona'];
                $resultado .= " | Conector: " . $reg['nombre_conector'];
                $resultado .= " | Tel: " . $reg['telefono'];
                $resultado .= " | Fecha: " . date('d/m/Y', strtotime($reg['fecha_creacion'])) . "\n";
            }
        }
    }
    
    return $resultado;
}

/**
 * Genera informe detallado
 */
function generarInformeDetallado($conn, $conector, $estado, $fechaInicio, $fechaFin, $incluirDetalle) {
    $resultado = "INFORME DETALLADO\n";
    $resultado .= "-----------------\n\n";
    
    // Construir consulta con filtros
    $sql = "SELECT id, nombre_persona, apellido_persona, telefono, nombre_conector, estado, fecha_creacion FROM registros WHERE 1=1";
    $params = [];
    
    if ($conector) {
        $sql .= " AND nombre_conector = :conector";
        $params[':conector'] = $conector;
    }
    
    if ($estado) {
        $sql .= " AND estado = :estado";
        $params[':estado'] = $estado;
    }
    
    if ($fechaInicio) {
        $sql .= " AND fecha_creacion >= :fecha_inicio";
        $params[':fecha_inicio'] = $fechaInicio . ' 00:00:00';
    }
    
    if ($fechaFin) {
        $sql .= " AND fecha_creacion <= :fecha_fin";
        $params[':fecha_fin'] = $fechaFin . ' 23:59:59';
    }
    
    // Ordenar por fecha más reciente
    $sql .= " ORDER BY fecha_creacion DESC";
    
    // Limitar registros si no se piden detalles
    if (!$incluirDetalle) {
        $sql .= " LIMIT 20";
    }
    
    $stmt = $conn->prepare($sql);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    
    $stmt->execute();
    $registros = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($registros) === 0) {
        $resultado .= "No se encontraron registros con los criterios seleccionados.\n";
        return $resultado;
    }
    
    $resultado .= "REGISTROS ENCONTRADOS: " . count($registros) . "\n\n";
    
    // Cabecera de la tabla
    $resultado .= str_pad("ID", 5) . " | ";
    $resultado .= str_pad("NOMBRE", 20) . " | ";
    $resultado .= str_pad("APELLIDO", 20) . " | ";
    $resultado .= str_pad("TELÉFONO", 15) . " | ";
    $resultado .= str_pad("CONECTOR", 20) . " | ";
    $resultado .= str_pad("ESTADO", 25) . " | ";
    $resultado .= "FECHA\n";
    
    $resultado .= str_repeat("-", 120) . "\n";
    
    // Datos
    foreach ($registros as $reg) {
        $resultado .= str_pad($reg['id'], 5) . " | ";
        $resultado .= str_pad(substr($reg['nombre_persona'], 0, 18), 20) . " | ";
        $resultado .= str_pad(substr($reg['apellido_persona'], 0, 18), 20) . " | ";
        $resultado .= str_pad($reg['telefono'], 15) . " | ";
        $resultado .= str_pad(substr($reg['nombre_conector'], 0, 18), 20) . " | ";
        $resultado .= str_pad(substr($reg['estado'], 0, 23), 25) . " | ";
        $resultado .= date('d/m/Y', strtotime($reg['fecha_creacion'])) . "\n";
    }
    
    return $resultado;
}

/**
 * Genera resumen para informe general
 */
function generarResumenGeneral($conn, $fechaInicio, $fechaFin) {
    $resultado = "";
    
    // Total de conectores activos
    $sql = "SELECT COUNT(DISTINCT nombre_conector) as total FROM registros 
            WHERE nombre_conector IS NOT NULL AND nombre_conector != ''";
    
    if ($fechaInicio) {
        $sql .= " AND fecha_creacion >= :fecha_inicio";
    }
    
    if ($fechaFin) {
        $sql .= " AND fecha_creacion <= :fecha_fin";
    }
    
    $stmt = $conn->prepare($sql);
    
    if ($fechaInicio) {
        $fechaInicioDb = $fechaInicio . ' 00:00:00';
        $stmt->bindParam(':fecha_inicio', $fechaInicioDb);
    }
    
    if ($fechaFin) {
        $fechaFinDb = $fechaFin . ' 23:59:59';
        $stmt->bindParam(':fecha_fin', $fechaFinDb);
    }
    
    $stmt->execute();
    $totalConectores = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    $resultado .= "Total de conectores activos: " . $totalConectores . "\n";
    
    // Estados más comunes
    $sql = "SELECT estado, COUNT(*) as total FROM registros 
            WHERE estado IS NOT NULL AND estado != ''";
    
    if ($fechaInicio) {
        $sql .= " AND fecha_creacion >= :fecha_inicio";
    }
    
    if ($fechaFin) {
        $sql .= " AND fecha_creacion <= :fecha_fin";
    }
    
    $sql .= " GROUP BY estado ORDER BY total DESC LIMIT 3";
    
    $stmt = $conn->prepare($sql);
    
    if ($fechaInicio) {
        $fechaInicioDb = $fechaInicio . ' 00:00:00';
        $stmt->bindParam(':fecha_inicio', $fechaInicioDb);
    }
    
    if ($fechaFin) {
        $fechaFinDb = $fechaFin . ' 23:59:59';
        $stmt->bindParam(':fecha_fin', $fechaFinDb);
    }
    
    $stmt->execute();
    $estadosComunes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $resultado .= "Estados más comunes:\n";
    foreach ($estadosComunes as $i => $estado) {
        $resultado .= "  " . ($i + 1) . ". " . $estado['estado'] . " (" . $estado['total'] . " registros)\n";
    }
    
    return $resultado;
}

/**
 * Genera resumen para informe personal
 */
function generarResumenPersonal($conn, $conector, $fechaInicio, $fechaFin) {
    if (empty($conector)) return "";
    
    $resultado = "";
    
    // Total de registros para este conector
    $sql = "SELECT COUNT(*) as total FROM registros WHERE nombre_conector = :conector";
    
    if ($fechaInicio) {
        $sql .= " AND fecha_creacion >= :fecha_inicio";
    }
    
    if ($fechaFin) {
        $sql .= " AND fecha_creacion <= :fecha_fin";
    }
    
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':conector', $conector);
    
    if ($fechaInicio) {
        $fechaInicioDb = $fechaInicio . ' 00:00:00';
        $stmt->bindParam(':fecha_inicio', $fechaInicioDb);
    }
    
    if ($fechaFin) {
        $fechaFinDb = $fechaFin . ' 23:59:59';
        $stmt->bindParam(':fecha_fin', $fechaFinDb);
    }
    
    $stmt->execute();
    $totalRegistros = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Estado más común para este conector
    $sql = "SELECT estado, COUNT(*) as total FROM registros 
            WHERE nombre_conector = :conector AND estado IS NOT NULL AND estado != ''";
    
    if ($fechaInicio) {
        $sql .= " AND fecha_creacion >= :fecha_inicio";
    }
    
    if ($fechaFin) {
        $sql .= " AND fecha_creacion <= :fecha_fin";
    }
    
    $sql .= " GROUP BY estado ORDER BY total DESC LIMIT 1";
    
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':conector', $conector);
    
    if ($fechaInicio) {
        $fechaInicioDb = $fechaInicio . ' 00:00:00';
        $stmt->bindParam(':fecha_inicio', $fechaInicioDb);
    }
    
    if ($fechaFin) {
        $fechaFinDb = $fechaFin . ' 23:59:59';
        $stmt->bindParam(':fecha_fin', $fechaFinDb);
    }
    
    $stmt->execute();
    $estadoComun = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $resultado .= "Total de registros del conector: " . $totalRegistros . "\n";
    
    if ($estadoComun) {
        $resultado .= "Estado más común: " . $estadoComun['estado'] . " (" . $estadoComun['total'] . " registros)\n";
        $porcentaje = round(($estadoComun['total'] / $totalRegistros) * 100, 2);
        $resultado .= "Porcentaje: " . $porcentaje . "%\n";
    }
    
    return $resultado;
}

/**
 * Genera resumen para informe de estados
 */
function generarResumenEstados($conn, $estado, $fechaInicio, $fechaFin) {
    if (empty($estado)) return "";
    
    $resultado = "";
    
    // Total de registros con este estado
    $sql = "SELECT COUNT(*) as total FROM registros WHERE estado = :estado";
    
    if ($fechaInicio) {
        $sql .= " AND fecha_creacion >= :fecha_inicio";
    }
    
    if ($fechaFin) {
        $sql .= " AND fecha_creacion <= :fecha_fin";
    }
    
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':estado', $estado);
    
    if ($fechaInicio) {
        $fechaInicioDb = $fechaInicio . ' 00:00:00';
        $stmt->bindParam(':fecha_inicio', $fechaInicioDb);
    }
    
    if ($fechaFin) {
        $fechaFinDb = $fechaFin . ' 23:59:59';
        $stmt->bindParam(':fecha_fin', $fechaFinDb);
    }
    
    $stmt->execute();
    $totalRegistros = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Conector con más registros en este estado
    $sql = "SELECT nombre_conector, COUNT(*) as total FROM registros 
            WHERE estado = :estado AND nombre_conector IS NOT NULL AND nombre_conector != ''";
    
    if ($fechaInicio) {
        $sql .= " AND fecha_creacion >= :fecha_inicio";
    }
    
    if ($fechaFin) {
        $sql .= " AND fecha_creacion <= :fecha_fin";
    }
    
    $sql .= " GROUP BY nombre_conector ORDER BY total DESC LIMIT 1";
    
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':estado', $estado);
    
    if ($fechaInicio) {
        $fechaInicioDb = $fechaInicio . ' 00:00:00';
        $stmt->bindParam(':fecha_inicio', $fechaInicioDb);
    }
    
    if ($fechaFin) {
        $fechaFinDb = $fechaFin . ' 23:59:59';
        $stmt->bindParam(':fecha_fin', $fechaFinDb);
    }
    
    $stmt->execute();
    $conectorPrincipal = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $resultado .= "Total de registros con estado '" . $estado . "': " . $totalRegistros . "\n";
    
    if ($conectorPrincipal) {
        $resultado .= "Conector con más registros en este estado: " . $conectorPrincipal['nombre_conector'] . " (" . $conectorPrincipal['total'] . " registros)\n";
        $porcentaje = round(($conectorPrincipal['total'] / $totalRegistros) * 100, 2);
        $resultado .= "Porcentaje: " . $porcentaje . "%\n";
    }
    
    return $resultado;
}
?>
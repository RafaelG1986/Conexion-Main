window.inicializarConector = function() {
  // CAMBIADO: Usa ID específico para esta vista
  const select = document.getElementById('conector-personal'); 
  const tbody = document.querySelector('#tabla-registros-conector tbody');
  
  if (!select || !tbody) {
    console.log('No se encontraron elementos necesarios para Base de Datos Personal');
    return;
  }

  // evitar listeners duplicados - IMPORTANTE
  select.replaceWith(select.cloneNode(true));
  const nuevoSelect = document.getElementById('conector-personal');

  nuevoSelect.addEventListener('change', (e) => {
    // Prevenir cualquier comportamiento por defecto
    e.preventDefault();
    
    const conector = nuevoSelect.value;
    if (!conector) {
      tbody.innerHTML = '<tr><td colspan="6" style="text-align:center;">Selecciona un conector.</td></tr>';
      return;
    }
    
    console.log("Cargando datos para conector:", conector);
    
    // CORRECCIÓN: Usar la ruta relativa correcta
    fetch(`api_registros_conector.php?conector=${encodeURIComponent(conector)}`)
      .then(r => {
        if (!r.ok) throw new Error(`Error HTTP: ${r.status}`);
        return r.json();
      })
      .then(data => {
        console.log("Datos recibidos:", data.length, "registros");
        if (!data.length) {
          tbody.innerHTML = '<tr><td colspan="6" style="text-align:center;">No hay registros.</td></tr>';
        } else {
          tbody.innerHTML = data.map(r => `
            <tr>
              <td>${r.id || ''}</td>
              <td>${r.nombre_persona || ''}</td>
              <td>${r.apellido_persona || ''}</td>
              <td>${r.telefono || ''}</td>
              <td>${r.estado || ''}</td>
              <td>${r.observaciones || ''}</td>
            </tr>
          `).join('');
        }
      })
      .catch((error) => {
        console.error('Error cargando registros:', error);
        tbody.innerHTML = '<tr><td colspan="6" style="text-align:center;">Error al cargar datos.</td></tr>';
      });
  });
};
// Esta función será llamada por admin.php cuando se cargue la vista
window.inicializarEstadisticasPersonales = function() {
  const canvas = document.getElementById('graficoEstadosPersonal');
  if (!canvas) return;
  
  const conector = canvas.getAttribute('data-conector') || '';
  const datos = canvas.getAttribute('data-estados') || '{}';
  
  try {
    const estadosParsed = JSON.parse(datos);
    window.inicializarEstadisticasPersonalesConDatos(conector, estadosParsed);
  } catch(e) {
    console.error("Error parseando datos de estados:", e);
  }
};

// Esta función recibe los datos directamente
window.inicializarEstadisticasPersonalesConDatos = function(conector, datosBD) {
  const canvas = document.getElementById('graficoEstadosPersonal');
  if (!canvas) return;
  
  // Si Chart.js no está disponible, salimos
  if (typeof Chart === 'undefined') return;
  
  const labels = Object.keys(datosBD);
  const data = Object.values(datosBD);
  
  // Si no hay datos, no dibujamos nada
  if (labels.length === 0) return;
  
  // Destruir gráfico anterior si existe
  if (window._chartPersonal) {
    window._chartPersonal.destroy();
  }
  
  // Crear nuevo gráfico
  window._chartPersonal = new Chart(canvas, {
    type: 'bar',
    data: {
      labels: labels,
      datasets: [{
        label: `Estados para: ${conector}`,
        data: data,
        backgroundColor: [
          '#3498db', '#2ecc71', '#e74c3c', '#f39c12', 
          '#9b59b6', '#1abc9c', '#34495e', '#d35400'
        ],
        borderWidth: 1
      }]
    },
    options: {
      responsive: true,
      scales: {
        y: { 
          beginAtZero: true,
          title: {
            display: true,
            text: 'Cantidad'
          }
        },
        x: {
          title: {
            display: true,
            text: 'Estados'
          }
        }
      },
      plugins: {
        title: {
          display: true,
          text: `Estadísticas de ${conector}`,
          font: {
            size: 16
          }
        }
      }
    }
  });
  
  // Manejar cambio de selector
  const select = document.getElementById('conector');
  if (select) {
    select.addEventListener('change', function() {
      const valor = this.value;
      if (!valor) return;
      cargarVista(`vista_estadisticas_personales.php?conector=${encodeURIComponent(valor)}`);
    });
  }
};
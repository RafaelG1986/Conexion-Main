window.inicializarEstadisticas = function() {
  // 1. GRÁFICO DE ESTADOS
  const canvasEstados = document.getElementById('chartEstados');
  if (!canvasEstados) return;
  
  let datosEstadosStr = canvasEstados.getAttribute('data-estados') || '{}';
  let datosEstados;
  
  try {
    datosEstados = JSON.parse(datosEstadosStr);
  } catch (e) {
    console.error("Error parseando datos estados:", e);
    return;
  }
  
  // Verificar Chart.js
  if (typeof Chart === 'undefined') return;
  
  // Crear gráfico de estados
  try {
    if (window._chartEstados) {
      window._chartEstados.destroy();
    }
    
    window._chartEstados = new Chart(canvasEstados, {
      type: 'pie',
      data: {
        labels: Object.keys(datosEstados),
        datasets: [{
          data: Object.values(datosEstados),
          backgroundColor: [
            '#3498db', '#2ecc71', '#e74c3c', '#f39c12', 
            '#9b59b6', '#1abc9c', '#34495e', '#d35400'
          ]
        }]
      },
      options: {
        responsive: true,
        plugins: {
          legend: {
            position: 'right',
          },
          title: {
            display: true,
            text: 'Distribución por Estados'
          }
        }
      }
    });
  } catch (e) {
    console.error("Error creando gráfico de estados:", e);
  }
  
  // 2. GRÁFICO DE CONECTORES
  const canvasConectores = document.getElementById('chartConectores');
  if (!canvasConectores) return;
  
  let datosConectoresStr = canvasConectores.getAttribute('data-conectores') || '{}';
  let datosConectores;
  
  try {
    datosConectores = JSON.parse(datosConectoresStr);
  } catch (e) {
    console.error("Error parseando datos conectores:", e);
    return;
  }
  
  // Crear gráfico de conectores
  try {
    if (window._chartConectores) {
      window._chartConectores.destroy();
    }
    
    window._chartConectores = new Chart(canvasConectores, {
      type: 'bar',
      data: {
        labels: Object.keys(datosConectores),
        datasets: [{
          label: 'Registros por conector',
          data: Object.values(datosConectores),
          backgroundColor: '#3498db'
        }]
      },
      options: {
        responsive: true,
        plugins: {
          legend: {
            display: false
          },
          title: {
            display: true,
            text: 'Distribución por Conectores'
          }
        },
        scales: {
          y: {
            beginAtZero: true
          }
        }
      }
    });
  } catch (e) {
    console.error("Error creando gráfico de conectores:", e);
  }
};
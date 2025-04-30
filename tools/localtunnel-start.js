// localtunnel-start.js - Versión mejorada con reintentos automáticos
const localtunnel = require('localtunnel');

// Configuración
const port = 80; // Puerto que deseas exponer (XAMPP)
const subdomain = 'conexion-main-xyz123'; // Subdominio base
const maxRetries = 20; // Máximo número de reintentos
let retryCount = 0;
let tunnel = null;

// Función principal con reintentos automáticos
async function startTunnel() {
  // Generar un subdominio único si hay reintentos
  const currentSubdomain = retryCount > 0 
    ? `${subdomain}-${retryCount}` 
    : subdomain;
  
  try {
    console.log(`Intento ${retryCount + 1}: Iniciando túnel en ${currentSubdomain}.loca.lt...`);
    
    tunnel = await localtunnel({ 
      port: port,
      subdomain: currentSubdomain
    });
    
    // Reiniciar contador de reintentos cuando hay éxito
    retryCount = 0;
    console.log(`✅ Túnel iniciado exitosamente en: ${tunnel.url}`);
    console.log(`Fecha y hora: ${new Date().toLocaleString()}`);
    
    // Manejar evento de cierre
    tunnel.on('close', () => {
      console.log('⚠️ Túnel cerrado inesperadamente');
      handleReconnect();
    });
    
    // Manejar errores en el túnel establecido
    tunnel.on('error', (err) => {
      console.error('❌ Error en el túnel:', err);
      handleReconnect();
    });
    
  } catch (error) {
    console.error(`❌ Error al iniciar túnel ${currentSubdomain}:`, error.message);
    handleReconnect();
  }
}

// Manejar reconexiones con retroceso exponencial
function handleReconnect() {
  if (tunnel) {
    try { tunnel.close(); } catch (e) { /* Ignorar errores al cerrar */ }
    tunnel = null;
  }
  
  retryCount++;
  const delay = Math.min(30000, 1000 * Math.pow(1.5, retryCount)); // Retroceso exponencial
  
  if (retryCount <= maxRetries) {
    console.log(`🔄 Reintento ${retryCount}/${maxRetries} en ${delay/1000} segundos...`);
    setTimeout(startTunnel, delay);
  } else {
    console.error(`❌ Se alcanzó el máximo de reintentos (${maxRetries}). Reiniciando proceso...`);
    setTimeout(() => process.exit(1), 1000); // Forzar a PM2 a reiniciar completamente
  }
}

// Manejo de señales del sistema
process.on('SIGINT', () => {
  console.log('Cerrando túnel por señal SIGINT...');
  if (tunnel) tunnel.close();
  process.exit(0);
});

process.on('SIGTERM', () => {
  console.log('Cerrando túnel por señal SIGTERM...');
  if (tunnel) tunnel.close();
  process.exit(0);
});

// Iniciar el túnel
console.log(`🚀 Iniciando servicio de túnel para puerto ${port}...`);
startTunnel();
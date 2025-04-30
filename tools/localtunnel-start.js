// localtunnel-start.js
const localtunnel = require('localtunnel');

// Configuración
const port = 80; // Puerto que deseas exponer (XAMPP)
const subdomain = 'conexion-main-xyz123'; // Subdominio deseado (opcional)

async function startTunnel() {
  try {
    const tunnel = await localtunnel({ 
      port: port,
      subdomain: subdomain 
    });
    
    console.log(`Túnel iniciado en: ${tunnel.url}`);
    
    tunnel.on('close', () => {
      console.log('Túnel cerrado, reiniciando...');
      // PM2 detectará esta salida y reiniciará el proceso
      process.exit(1);
    });
    
    // Mantener el proceso vivo
    process.on('SIGINT', () => {
      tunnel.close();
      process.exit(0);
    });
    
  } catch (error) {
    console.error('Error al iniciar el túnel:', error);
    process.exit(1);
  }
}

startTunnel();
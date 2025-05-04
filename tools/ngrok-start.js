const ngrok = require('ngrok');
const fs = require('fs');
const path = require('path');

// Configuración
const PORT = 80;
let url = null;
let connectAttempts = 0;
const MAX_ATTEMPTS = 5;

async function startNgrok() {
  try {
    console.log(`🚀 Iniciando ngrok en puerto ${PORT}...`);
    
    // Intentar conectar
    url = await ngrok.connect({
      addr: PORT,
      onStatusChange: (status) => {
        console.log(`Estado del túnel: ${status}`);
      },
      onLogEvent: (data) => {
        if (data.includes('error') || data.includes('failed')) {
          console.error(`❌ Error ngrok: ${data}`);
        }
      }
    });
    
    console.log(`✅ Túnel ngrok iniciado exitosamente`);
    console.log(`📌 URL pública: ${url}`);
    console.log(`📅 Fecha y hora: ${new Date().toLocaleString()}`);
    
    // Guardar la URL para uso futuro
    const configData = {
      url: url,
      timestamp: new Date().toISOString(),
      port: PORT
    };
    
    fs.writeFileSync(
      path.join(__dirname, 'ngrok-url.json'),
      JSON.stringify(configData, null, 2)
    );
    
    // Reiniciar contador de intentos
    connectAttempts = 0;
    
  } catch (error) {
    console.error(`❌ Error al iniciar ngrok: ${error.message}`);
    
    // Manejar reintentos
    connectAttempts++;
    if (connectAttempts < MAX_ATTEMPTS) {
      const delay = Math.min(30000, 1000 * Math.pow(2, connectAttempts));
      console.log(`🔄 Reintento ${connectAttempts}/${MAX_ATTEMPTS} en ${delay/1000} segundos...`);
      setTimeout(startNgrok, delay);
    } else {
      console.error(`❌ Se alcanzó el máximo de reintentos. Reinicia manualmente.`);
      process.exit(1); // Para que PM2 lo reinicie
    }
  }
}

// Manejo de señales del sistema
process.on('SIGINT', cleanup);
process.on('SIGTERM', cleanup);

async function cleanup() {
  console.log('Cerrando túnel ngrok...');
  if (url) {
    try {
      await ngrok.disconnect(url);
      console.log('Túnel desconectado correctamente');
    } catch (e) {
      console.error('Error al desconectar túnel:', e);
    }
  }
  
  try {
    await ngrok.kill();
    console.log('Proceso ngrok terminado');
  } catch (e) {
    console.error('Error al terminar ngrok:', e);
  }
  
  process.exit(0);
}

// Iniciar ngrok
startNgrok();
const express = require('express');
const path = require('path');
const app = express();
const PORT = process.env.PORT || 3000;

// Configurar para servir archivos estáticos
app.use(express.static(path.join(__dirname, 'frontend')));

// Para APIs REST
app.use('/api', require('./backend/api'));

// Para cualquier otra ruta, servir el index.html
app.get('*', (req, res) => {
  res.sendFile(path.join(__dirname, 'index.html'));
});

app.listen(PORT, () => {
  console.log(`Servidor Conexion-Main_xyz123 ejecutándose en puerto ${PORT}`);
});
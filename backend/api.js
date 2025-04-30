const express = require('express');
const router = express.Router();

// Rutas de API básicas
router.get('/status', (req, res) => {
    res.json({ status: 'online', name: 'Conexion-Main_xyz123' });
});

// Ejemplo de ruta para usuarios
router.get('/usuarios', (req, res) => {
    // Aquí implementarías la lógica para obtener usuarios
    res.json({ success: true, message: 'API de usuarios en construcción' });
});

module.exports = router;
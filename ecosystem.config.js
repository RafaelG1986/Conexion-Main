module.exports = {
  apps: [{
    name: "Conexion-Main_xyz123",
    script: "./server.js",  // O la ruta a tu archivo principal si usas Node.js
    watch: true,
    env: {
      NODE_ENV: "development",
      PORT: 3000
    },
    env_production: {
      NODE_ENV: "production",
      PORT: 80
    }
  }]
};
# Guardar la configuración actual de procesos
pm2 save

# Configurar inicio automático tras reinicio del sistema
pm2 startup

# Si necesitas especificar un usuario (como administrador)
pm2 startup windows --user NOMBRE_USUARIO

# Iniciar un proceso
pm2 start tools/localtunnel-start.js --name "conexion-tunnel"

# Reiniciar un proceso
pm2 restart conexion-tunnel

# Detener un proceso
pm2 stop conexion-tunnel

# Eliminar un proceso de la gestión de PM2
pm2 delete conexion-tunnel

# Iniciar varios procesos según archivo de configuración
pm2 start ecosystem.config.js

# Listar todos los procesos
pm2 list

# Ver dashboard de monitoreo
pm2 monit

# Ver estadísticas detalladas
pm2 show conexion-tunnel

# Ver uso de recursos
pm2 status

# Ver logs de un proceso específico
pm2 logs conexion-tunnel

# Ver logs con límite de líneas
pm2 logs conexion-tunnel --lines 100

# Vaciar logs
pm2 flush

# Ver logs en tiempo real
pm2 logs --raw

# Iniciar túnel para Conexión-Main
pm2 start tools/localtunnel-start.js --name "conexion-tunnel"

# Verificar URL del túnel
pm2 logs conexion-tunnel --lines 5

# Reiniciar túnel si hay problemas de conexión
pm2 restart conexion-tunnel

# Reiniciar automáticamente si se supera un umbral de memoria (300MB)
pm2 start tools/localtunnel-start.js --max-memory-restart 300M

# Limitar intentos de reinicio por errores (3 reintentos)
pm2 start tools/localtunnel-start.js --max-restarts 3

# Iniciar con nueva configuración de reinicio
pm2 start tools/localtunnel-start.js --name "conexion-tunnel" --max-memory-restart 100M --restart-delay 10000 --max-restarts 10

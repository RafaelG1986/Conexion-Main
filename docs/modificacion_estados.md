Archivos a modificar para cambiar los estados del sistema
Para mantener una correcta sincronización de estados en todo el sistema, es necesario modificar los siguientes archivos cuando se agregan, modifican o eliminan estados:

1. Archivos principales de configuración de estados
frontend/js/estados.js - Archivo JavaScript principal que define:
Colores de todos los estados (estadoColores)
Agrupación por categorías (gruposEstados)
Funciones de utilidad relacionadas con estados

2. Archivos de vistas que contienen listas de estados
frontend/views/vista_registros.php - Vista principal de registros:

Define el array $estados con todos los estados disponibles
Define el array $colores con los estilos CSS para cada estado
Contiene mensajes para WhatsApp por cada estado (obtenerMensajeWhatsApp())
Filtros y selectores de estados
frontend/views/ver_registro.php - Vista detallada de un registro:

Define $estados con lista completa de estados
Define $colores para aplicar estilos a cada estado
Contiene el selector para cambiar el estado de un registro
frontend/views/agregar_registro.php - Formulario para nuevos registros:

Define $estadosPorCategoria con estados agrupados y sus estilos
Contiene el selector de estado inicial

3. Posibles controladores o scripts adicionales
backend/controllers/actualizar_estado.php - Si existe alguna lógica especial basada en el nombre del estado
backend/controllers/procesar_registro.php - Si hay validaciones o lógica específicas para ciertos estados
Consideraciones al modificar estados
Mantener los mismos nombres de estados en todos los archivos
Usar los mismos códigos de colores para consistencia visual
Mantener la organización por categorías
Verificar si hay lógica de negocio que dependa de nombres específicos de estados
Actualizar la documentación o manuales de usuario si es necesario
Con estas modificaciones, los nuevos estados estarán disponibles en todo el sistema de forma coherente y totalmente funcional.
<!-- filepath: c:\xampp\htdocs\Conexion-Main\frontend\views\registro.php -->
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Conexión - Registro</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../css/styles_index.css">
    <link rel="icon" href="../img/favicon.ico" type="image/x-icon">
    <style>
        .registration-container {
            max-width: 500px;
            padding: 30px;
        }
        .input-group {
            margin-bottom: 20px;
        }
        .back-link {
            margin-top: 20px;
            display: block;
            text-align: center;
            color: #6c757d;
        }
        .info-text {
            font-size: 12px;
            color: #6c757d;
            margin-top: 5px;
        }
    </style>
</head>
<body>
    <div class="login-container registration-container">
        <img src="../img/logoConexion.png" alt="Logo Conexión" class="logo">
        <h2>Crear Cuenta</h2>
        
        <div id="error-message" class="error-message"></div>
        <div id="success-message" class="success-message"></div>
        
        <form id="registro-form" action="../../backend/controllers/registro.php" method="post">
            <div class="input-group">
                <i class="fas fa-user"></i>
                <input type="text" id="nombre" name="nombre" placeholder="Nombre completo" required>
            </div>
            
            <div class="input-group">
                <i class="fas fa-at"></i>
                <input type="text" id="username" name="username" placeholder="Nombre de usuario" required>
                <p class="info-text">Este será su identificador para iniciar sesión</p>
            </div>
            
            <div class="input-group">
                <i class="fas fa-lock"></i>
                <input type="password" id="password" name="password" placeholder="Contraseña" required>
            </div>
            
            <div class="input-group">
                <i class="fas fa-lock"></i>
                <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirmar contraseña" required>
            </div>
            
            <div class="input-group">
                <i class="fas fa-key"></i>
                <input type="text" id="codigo" name="codigo" placeholder="Código de invitación" required>
                <p class="info-text">Se requiere un código válido para registrarse</p>
            </div>
            
            <button type="submit" class="login-btn">Registrarse <i class="fas fa-user-plus"></i></button>
        </form>
        
        <a href="../../index.html" class="back-link"><i class="fas fa-arrow-left"></i> Volver al inicio de sesión</a>
    </div>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const registroForm = document.getElementById('registro-form');
        const errorMessage = document.getElementById('error-message');
        const successMessage = document.getElementById('success-message');
        
        // Verificar si hay mensajes en la URL (por ejemplo, después de una redirección)
        const urlParams = new URLSearchParams(window.location.search);
        const error = urlParams.get('error');
        const success = urlParams.get('success');
        
        if (error) {
            switch(error) {
                case 'empty':
                    mostrarError('Por favor completa todos los campos');
                    break;
                case 'password':
                    mostrarError('Las contraseñas no coinciden');
                    break;
                case 'codigo':
                    mostrarError('El código de invitación no es válido');
                    break;
                case 'exists':
                    mostrarError('El nombre de usuario ya está en uso');
                    break;
                default:
                    mostrarError('Ha ocurrido un error en el registro');
            }
        }
        
        if (success) {
            mostrarExito('¡Registro exitoso! Ya puedes iniciar sesión.');
        }
        
        registroForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const nombre = document.getElementById('nombre').value.trim();
            const username = document.getElementById('username').value.trim();
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            const codigo = document.getElementById('codigo').value.trim();
            
            // Validaciones básicas
            if (!nombre || !username || !password || !confirmPassword || !codigo) {
                mostrarError('Por favor completa todos los campos');
                return;
            }
            
            if (password !== confirmPassword) {
                mostrarError('Las contraseñas no coinciden');
                return;
            }
            
            // Validar código de invitación
            if (codigo !== '2025Conexionwow') {
                mostrarError('El código de invitación no es válido');
                return;
            }
            
            // Si pasa las validaciones, enviar el formulario
            this.submit();
        });
        
        function mostrarError(mensaje) {
            errorMessage.textContent = mensaje;
            errorMessage.classList.add('show');
            successMessage.classList.remove('show');
            
            setTimeout(() => {
                errorMessage.classList.remove('show');
            }, 5000);
        }
        
        function mostrarExito(mensaje) {
            successMessage.textContent = mensaje;
            successMessage.classList.add('show');
            errorMessage.classList.remove('show');
        }
        
        // Animación en campos
        const inputs = document.querySelectorAll('input');
        inputs.forEach(input => {
            input.addEventListener('focus', function() {
                this.parentElement.style.transform = 'translateY(-2px)';
                this.parentElement.style.transition = 'transform 0.3s';
            });
            
            input.addEventListener('blur', function() {
                this.parentElement.style.transform = 'translateY(0)';
            });
        });
    });
    </script>
</body>
</html>
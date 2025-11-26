<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Iniciar Sesión' ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="<?= base_url('assets/css/login.css') ?>" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <h1>Iniciar Sesión</h1>
            <p>Ingresa tus credenciales para acceder</p>
        </div>

        <form action="<?= base_url('auth/authenticate') ?>" method="POST" id="loginForm">
            <?= csrf_field() ?>
            
            <div class="form-group">
                <input type="text" class="form-control" id="login" name="login" 
                    placeholder="Usuario o Email" value="<?= old('login') ?>" required>
                <i class="fas fa-user input-icon"></i>
            </div>

            <div class="form-group">
                <input type="password" class="form-control" id="password" name="password" 
                    placeholder="Contraseña" required>
                <i class="fas fa-lock input-icon"></i>
            </div>

            <button type="submit" class="btn-login">
                Iniciar Sesión
            </button>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Configuración del Toast de SweetAlert2
        const Toast = Swal.mixin({
            toast: true,
            position: "top-end",
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
            didOpen: (toast) => {
                toast.onmouseenter = Swal.stopTimer;
                toast.onmouseleave = Swal.resumeTimer;
            }
        });

        // Mostrar notificaciones específicas según el tipo de error
        <?php 
        $errorType = session()->getFlashdata('error_type');
        $successType = session()->getFlashdata('success_type');
        ?>

        // Error: Usuario no encontrado
        <?php if ($errorType === 'user_not_found'): ?>
            Toast.fire({
                icon: "error",
                title: "Usuario no encontrado"
            });
        <?php endif; ?>

        // Error: Contraseña incorrecta
        <?php if ($errorType === 'wrong_password'): ?>
            Toast.fire({
                icon: "error",
                title: "Contraseña incorrecta"
            });
        <?php endif; ?>

        // Error: Validación de campos
        <?php if ($errorType === 'validation'): ?>
            Toast.fire({
                icon: "warning",
                title: "Campos incompletos"
            });
        <?php endif; ?>

        // Éxito: Login exitoso
        <?php if ($successType === 'login_success'): ?>
            Swal.mixin({
                toast: true,
                position: "top-end",
                showConfirmButton: false,
                timer: 1000,
                timerProgressBar: true,
                didOpen: (toast) => {
                    toast.onmouseenter = Swal.stopTimer;
                    toast.onmouseleave = Swal.resumeTimer;
                }
            }).fire({
                icon: "success",
                title: "Inicio de sesión exitoso"
            }).then(() => {
                window.location.href = "<?= session()->getFlashdata('redirect_to') ?? base_url('/welcome') ?>";
            });
        <?php endif; ?>

        // Info: Sesión cerrada
        <?php if (session()->getFlashdata('logout_success')): ?>
            Toast.fire({
                icon: "info",
                title: "Sesión cerrada"
            });
        <?php endif; ?>

       // Validación del formulario antes de enviar
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            const login = document.getElementById('login').value.trim();
            const password = document.getElementById('password').value;
            if (!login || !password) {
                e.preventDefault();
                Toast.fire({
                    icon: "warning",
                    title: "Por favor, completa todos los campos"
                });
                return false;
            }
            Toast.fire({
                icon: "info",
                title: "Verificando credenciales..."
            });
        });
    </script>
</body>
</html>
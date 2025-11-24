<?php
/**
 * Login Admin - Portal de Trabajo UTP
 * Autenticación con correo y contraseña para administradores
 */
session_start();
$page_title = 'Ingresar - Administrador';

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/session.php';

// Si ya está autenticado, redirigir
if (estaAutenticado()) {
    redirigirSegunRol();
}

include __DIR__ . '/../layout/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4 p-lg-5">
                    <!-- Icono/Logo -->
                    <div class="text-center mb-4">
                        <div class="bg-primary bg-opacity-10 d-inline-flex rounded-circle p-3 mb-3">
                            <i class="bi bi-shield-lock text-primary" style="font-size: 48px;"></i>
                        </div>
                        <h2 style="color: #2557F5; font-weight: 700;">
                            Acceso Administrativo
                        </h2>
                        <p class="text-muted">Panel de administración del portal</p>
                    </div>
                    
                    <form method="POST" action="<?php echo BASE_URL; ?>/controllers/AuthController.php?action=loginAdmin">
                        <input type="hidden" name="csrf_token" value="<?php echo generarTokenCSRF(); ?>">
                        
                        <!-- Correo -->
                        <div class="mb-3">
                            <label for="correo" class="form-label fw-semibold">
                                Correo Electrónico <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="bi bi-envelope"></i>
                                </span>
                                <input type="email" 
                                       class="form-control" 
                                       id="correo" 
                                       name="correo" 
                                       placeholder="admin@utp.ac.pa" 
                                       required
                                       autocomplete="username">
                            </div>
                        </div>
                        
                        <!-- Contraseña -->
                        <div class="mb-3">
                            <label for="password" class="form-label fw-semibold">
                                Contraseña <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="bi bi-lock"></i>
                                </span>
                                <input type="password" 
                                       class="form-control" 
                                       id="password" 
                                       name="password" 
                                       placeholder="••••••••" 
                                       required
                                       autocomplete="current-password">
                                <button class="btn btn-outline-secondary" 
                                        type="button" 
                                        onclick="togglePassword()">
                                    <i class="bi bi-eye" id="toggleIcon"></i>
                                </button>
                            </div>
                        </div>
                        
                        <!-- Información de prueba -->
                        <div class="alert alert-warning border-0 mb-4">
                            <small>
                                <strong>Credenciales de prueba:</strong><br>
                                Usuario: <code>denis.cedeno@utp.ac.pa</code><br>
                                Contraseña: <code>Admin123!</code>
                            </small>
                        </div>
                        
                        <!-- Botón -->
                        <button type="submit" class="btn btn-primary w-100 btn-lg mb-3">
                            <i class="bi bi-box-arrow-in-right me-2"></i>Ingresar al Panel
                        </button>
                    </form>
                    
                    <hr class="my-4">
                    
                    <!-- Link a estudiante -->
                    <div class="text-center">
                        <a href="<?php echo BASE_URL; ?>/views/auth/login_estudiante.php" class="text-muted text-decoration-none small">
                            <i class="bi bi-arrow-left me-1"></i>Volver a login de estudiantes
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Info adicional -->
            <div class="text-center mt-4">
                <p class="text-muted small mb-2">
                    <i class="bi bi-shield-check text-primary me-1"></i>
                    Acceso restringido solo para personal autorizado
                </p>
                <p class="text-muted small">
                    ¿Olvidaste tu contraseña? <a href="<?php echo BASE_URL; ?>/views/public/contacto.php">Contacta al administrador</a>
                </p>
            </div>
        </div>
    </div>
</div>

<script>
// Toggle password visibility
function togglePassword() {
    const passwordInput = document.getElementById('password');
    const toggleIcon = document.getElementById('toggleIcon');
    
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        toggleIcon.classList.remove('bi-eye');
        toggleIcon.classList.add('bi-eye-slash');
    } else {
        passwordInput.type = 'password';
        toggleIcon.classList.remove('bi-eye-slash');
        toggleIcon.classList.add('bi-eye');
    }
}
</script>

<?php include __DIR__ . '/../layout/footer.php'; ?>
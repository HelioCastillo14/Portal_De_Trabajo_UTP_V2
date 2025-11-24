<?php
/**
 * Login Estudiante - Portal de Trabajo UTP
 * Autenticación sin contraseña, solo con correo @utp.ac.pa
 */
session_start();
$page_title = 'Ingresar - Estudiante';

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
                        <div class="bg-success-subtle d-inline-flex rounded-circle p-3 mb-3">
                            <i class="bi bi-person-circle text-success" style="font-size: 48px;"></i>
                        </div>
                        <h2 style="color: var(--verde-principal); font-weight: 700;">
                            Ingresar como Estudiante
                        </h2>
                        <p class="text-muted">Accede con tu correo institucional UTP</p>
                    </div>
                    
                    <form method="POST" action="<?php echo BASE_URL; ?>/controllers/AuthController.php?action=loginEstudiante" id="loginForm">
                        <input type="hidden" name="csrf_token" value="<?php echo generarTokenCSRF(); ?>">
                        
                        <!-- Correo institucional -->
                        <div class="mb-3">
                            <label for="correo" class="form-label fw-semibold">
                                Correo Institucional <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="bi bi-envelope"></i>
                                </span>
                                <input type="email" 
                                       class="form-control" 
                                       id="correo" 
                                       name="correo" 
                                       placeholder="tu.nombre@utp.ac.pa" 
                                       required
                                       pattern="[a-zA-Z0-9._%+-]+@utp\.ac\.pa$">
                            </div>
                            <small class="text-muted">Solo se permiten correos @utp.ac.pa</small>
                        </div>
                        
                        <!-- Nombres (solo si es primer login) -->
                        <div class="mb-3">
                            <label for="nombres" class="form-label fw-semibold">
                                Nombres
                            </label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="bi bi-person"></i>
                                </span>
                                <input type="text" 
                                       class="form-control" 
                                       id="nombres" 
                                       name="nombres" 
                                       placeholder="Juan Carlos">
                            </div>
                            <small class="text-muted">Solo si es tu primera vez ingresando</small>
                        </div>
                        
                        <!-- Apellidos (solo si es primer login) -->
                        <div class="mb-3">
                            <label for="apellidos" class="form-label fw-semibold">
                                Apellidos
                            </label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="bi bi-person"></i>
                                </span>
                                <input type="text" 
                                       class="form-control" 
                                       id="apellidos" 
                                       name="apellidos" 
                                       placeholder="Pérez González">
                            </div>
                        </div>
                        
                        <!-- Información -->
                        <div class="alert alert-info border-0 mb-4">
                            <i class="bi bi-info-circle me-2"></i>
                            <small>
                                <strong>Nota:</strong> Si es tu primera vez, completa nombres y apellidos. 
                                Si ya tienes cuenta, solo ingresa tu correo.
                            </small>
                        </div>
                        
                        <!-- Botón -->
                        <button type="submit" class="btn btn-success w-100 btn-lg mb-3">
                            <i class="bi bi-box-arrow-in-right me-2"></i>Ingresar
                        </button>
                        
                        <!-- Link a ofertas públicas -->
                        <div class="text-center mb-3">
                            <a href="<?php echo BASE_URL; ?>/views/public/ofertas_publicas.php" class="text-muted text-decoration-none small">
                                <i class="bi bi-eye me-1"></i>Ver ofertas sin ingresar
                            </a>
                        </div>
                    </form>
                    
                    <hr class="my-4">
                    
                    <!-- Link a admin -->
                    <div class="text-center">
                        <a href="<?php echo BASE_URL; ?>/views/auth/login_admin.php" class="text-muted text-decoration-none small">
                            <i class="bi bi-shield-lock me-1"></i>¿Eres administrador?
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Info adicional -->
            <div class="text-center mt-4">
                <p class="text-muted small mb-2">
                    <i class="bi bi-shield-check text-success me-1"></i>
                    Conexión segura y protegida
                </p>
                <p class="text-muted small">
                    ¿Problemas para ingresar? <a href="<?php echo BASE_URL; ?>/views/public/contacto.php">Contáctanos</a>
                </p>
            </div>
        </div>
    </div>
</div>

<script>
// Validación de correo UTP
document.getElementById('loginForm').addEventListener('submit', function(e) {
    const correo = document.getElementById('correo').value;
    
    if (!correo.endsWith('@utp.ac.pa')) {
        e.preventDefault();
        alert('Solo se permiten correos institucionales @utp.ac.pa');
        return false;
    }
});
</script>

<?php include __DIR__ . '/../layout/footer.php'; ?>
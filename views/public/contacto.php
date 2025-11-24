<?php
/**
 * Contacto - Portal de Trabajo UTP
 * Formulario de contacto público
 */
session_start();
$page_title = 'Contacto - Portal UTP';

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/session.php';

include __DIR__ . '/../layout/header.php';
?>

<div class="container py-5">
    <div class="row">
        <!-- Información de contacto -->
        <div class="col-lg-5 mb-4 mb-lg-0">
            <h1 class="mb-4" style="font-size: 32px; font-weight: 700; color: var(--gris-oscuro);">
                Contáctanos
            </h1>
            <p class="text-muted mb-4">
                ¿Tienes preguntas sobre el Portal de Trabajo UTP? Estamos aquí para ayudarte. 
                Completa el formulario y nos pondremos en contacto contigo pronto.
            </p>
            
            <!-- Info boxes -->
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="bg-success-subtle rounded p-3">
                        <i class="bi bi-geo-alt-fill text-success fs-4"></i>
                    </div>
                    <div>
                        <h6 class="mb-1 fw-bold">Ubicación</h6>
                        <p class="text-muted mb-0 small">
                            Avenida Manuel Espinosa Batista<br>
                            Campus Víctor Levi Sasso<br>
                            Panamá, República de Panamá
                        </p>
                    </div>
                </div>
            </div>
            
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="bg-success-subtle rounded p-3">
                        <i class="bi bi-telephone-fill text-success fs-4"></i>
                    </div>
                    <div>
                        <h6 class="mb-1 fw-bold">Teléfono</h6>
                        <p class="text-muted mb-0">+507 560-3000</p>
                    </div>
                </div>
            </div>
            
            <div class="card border-0 shadow-sm">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="bg-success-subtle rounded p-3">
                        <i class="bi bi-envelope-fill text-success fs-4"></i>
                    </div>
                    <div>
                        <h6 class="mb-1 fw-bold">Correo Electrónico</h6>
                        <p class="text-muted mb-0">info@utp.ac.pa</p>
                    </div>
                </div>
            </div>
            
            <div class="mt-4">
                <h6 class="fw-bold mb-3">Síguenos</h6>
                <div class="d-flex gap-2">
                    <a href="#" class="btn btn-outline-success btn-sm">
                        <i class="bi bi-facebook"></i>
                    </a>
                    <a href="#" class="btn btn-outline-success btn-sm">
                        <i class="bi bi-twitter"></i>
                    </a>
                    <a href="#" class="btn btn-outline-success btn-sm">
                        <i class="bi bi-instagram"></i>
                    </a>
                    <a href="#" class="btn btn-outline-success btn-sm">
                        <i class="bi bi-linkedin"></i>
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Formulario -->
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4 p-lg-5">
                    <h3 class="mb-4">Envíanos un Mensaje</h3>
                    
                    <form method="POST" action="<?php echo BASE_URL; ?>/controllers/ContactoController.php?action=guardar" id="contactoForm">
                        <input type="hidden" name="csrf_token" value="<?php echo generarTokenCSRF(); ?>">
                        
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="nombre" class="form-label">
                                    Nombre Completo <span class="text-danger">*</span>
                                </label>
                                <input type="text" 
                                       class="form-control" 
                                       id="nombre" 
                                       name="nombre" 
                                       placeholder="Tu nombre" 
                                       required
                                       minlength="3">
                            </div>
                            
                            <div class="col-md-6">
                                <label for="correo" class="form-label">
                                    Correo Electrónico <span class="text-danger">*</span>
                                </label>
                                <input type="email" 
                                       class="form-control" 
                                       id="correo" 
                                       name="correo" 
                                       placeholder="tu.correo@ejemplo.com" 
                                       required>
                            </div>
                            
                            <div class="col-12">
                                <label for="asunto" class="form-label">
                                    Asunto <span class="text-danger">*</span>
                                </label>
                                <input type="text" 
                                       class="form-control" 
                                       id="asunto" 
                                       name="asunto" 
                                       placeholder="¿Sobre qué quieres escribir?" 
                                       required>
                            </div>
                            
                            <div class="col-12">
                                <label for="mensaje" class="form-label">
                                    Mensaje <span class="text-danger">*</span>
                                </label>
                                <textarea class="form-control" 
                                          id="mensaje" 
                                          name="mensaje" 
                                          rows="5" 
                                          placeholder="Escribe tu mensaje aquí..." 
                                          required
                                          minlength="10"
                                          maxlength="1000"></textarea>
                                <small class="text-muted">Mínimo 10 caracteres, máximo 1000</small>
                            </div>
                            
                            <!-- Captcha simple -->
                            <div class="col-12">
                                <label class="form-label">
                                    Verificación <span class="text-danger">*</span>
                                </label>
                                <div class="d-flex align-items-center gap-3">
                                    <div class="bg-light p-3 rounded border">
                                        <strong>5 + 3 = ?</strong>
                                    </div>
                                    <input type="number" 
                                           class="form-control" 
                                           name="captcha" 
                                           placeholder="Respuesta" 
                                           required
                                           style="max-width: 100px;">
                                </div>
                                <small class="text-muted">Por favor resuelve la operación matemática</small>
                            </div>
                            
                            <div class="col-12">
                                <button type="submit" class="btn btn-success btn-lg w-100">
                                    <i class="bi bi-send me-2"></i>Enviar Mensaje
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Validación adicional del formulario
document.getElementById('contactoForm').addEventListener('submit', function(e) {
    const captcha = parseInt(this.querySelector('[name="captcha"]').value);
    
    if (captcha !== 8) {
        e.preventDefault();
        alert('Por favor verifica que la respuesta del captcha sea correcta (5 + 3 = 8)');
        return false;
    }
});
</script>

<?php include __DIR__ . '/../layout/footer.php'; ?>
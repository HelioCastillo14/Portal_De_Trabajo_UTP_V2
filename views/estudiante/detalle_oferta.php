<?php
/**
 * Detalle de Oferta - Área Estudiante
 * Vista detallada de oferta con opción de postular (autenticado)
 */
session_start();
require_once __DIR__ . '/../../config/session.php';
verificarSesion('estudiante');
$page_title = 'Detalle de Oferta';

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../models/Oferta.php';
require_once __DIR__ . '/../../models/Postulacion.php';

$db = Database::getInstance()->getConnection();
$ofertaModel = new Oferta($db);
$postulacionModel = new Postulacion($db);

$id_oferta = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id_oferta <= 0) {
    header('Location: ' . BASE_URL . '/views/estudiante/ofertas.php');
    exit();
}

$oferta = $ofertaModel->findById($id_oferta);

if (!$oferta || $oferta['estado'] !== 'activa') {
    $_SESSION['error'] = 'Oferta no disponible';
    header('Location: ' . BASE_URL . '/views/estudiante/ofertas.php');
    exit();
}

$habilidades = $ofertaModel->getHabilidades($id_oferta);
$ya_postulo = $ofertaModel->yaSePostulo($id_oferta, $_SESSION['id_usuario']);

include __DIR__ . '/../layout/header_estudiante.php';
?>

<div class="container py-5">
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <!-- Botón volver -->
            <a href="<?php echo BASE_URL; ?>/views/estudiante/ofertas.php" class="btn btn-link text-decoration-none mb-4">
                <i class="bi bi-arrow-left me-2"></i>Volver a búsqueda
            </a>
            
            <!-- Card principal -->
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4 p-lg-5">
                    <!-- Header -->
                    <div class="d-flex align-items-start gap-3 mb-4">
                        <div class="company-logo">
                            <?php if ($oferta['logo'] && $oferta['logo'] !== 'placeholder-logo.png'): ?>
                                <img src="<?php echo BASE_URL; ?>/assets/images/logos/<?php echo htmlspecialchars($oferta['logo']); ?>" 
                                     alt="<?php echo htmlspecialchars($oferta['empresa']); ?>">
                            <?php else: ?>
                                <i class="bi bi-building"></i>
                            <?php endif; ?>
                        </div>
                        
                        <div class="flex-grow-1">
                            <p class="text-uppercase mb-1" style="font-size: 14px; font-weight: 600; color: var(--gris-claro); letter-spacing: 0.5px;">
                                <?php echo htmlspecialchars($oferta['empresa']); ?>
                            </p>
                            <h1 class="job-title-detail mb-2">
                                <?php echo htmlspecialchars($oferta['titulo']); ?>
                            </h1>
                            
                            <!-- Badges -->
                            <div class="d-flex flex-wrap gap-2 mb-3">
                                <?php
                                $tipo_badges = [
                                    'tiempo_completo' => 'badge-fulltime',
                                    'medio_tiempo' => 'badge-parttime',
                                    'pasantia' => 'badge-parttime'
                                ];
                                $modalidad_badges = [
                                    'remoto' => 'badge-remote',
                                    'presencial' => 'badge-presencial',
                                    'hibrido' => 'badge-hibrido'
                                ];
                                $nivel_badges = [
                                    'junior' => 'badge-junior',
                                    'mid' => 'badge-mid',
                                    'senior' => 'badge-senior'
                                ];
                                ?>
                                <span class="badge-custom <?php echo $tipo_badges[$oferta['tipo_empleo']] ?? 'badge-fulltime'; ?>">
                                    <?php echo ucfirst(str_replace('_', ' ', $oferta['tipo_empleo'])); ?>
                                </span>
                                <span class="badge-custom <?php echo $modalidad_badges[$oferta['modalidad']] ?? 'badge-remote'; ?>">
                                    <?php echo ucfirst($oferta['modalidad']); ?>
                                </span>
                                <span class="badge-custom <?php echo $nivel_badges[$oferta['nivel_experiencia']] ?? 'badge-mid'; ?>">
                                    <?php echo ucfirst($oferta['nivel_experiencia']); ?>
                                </span>
                            </div>
                            
                            <!-- Meta info -->
                            <div class="d-flex flex-wrap gap-3 text-muted">
                                <span><i class="bi bi-geo-alt me-1"></i><?php echo htmlspecialchars($oferta['ubicacion']); ?></span>
                                <span><i class="bi bi-calendar-event me-1"></i>Publicado <?php echo tiempoTranscurrido($oferta['fecha_publicacion']); ?></span>
                                <?php if ($oferta['fecha_limite']): ?>
                                    <span><i class="bi bi-clock me-1"></i>Expira el <?php echo formatearFecha($oferta['fecha_limite']); ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Salario -->
                    <?php if ($oferta['salario_min']): ?>
                        <div class="alert bg-success-subtle border-0 mb-4">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-cash-stack fs-4 text-success me-3"></i>
                                <div>
                                    <strong class="text-success">Rango Salarial</strong>
                                    <div class="fs-5 fw-bold text-success">
                                        <?php echo formatearSalario($oferta['salario_min'], $oferta['salario_max']); ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <hr>
                    
                    <!-- Descripción -->
                    <div class="job-description mb-4">
                        <h5 class="mb-3" style="font-size: 18px; font-weight: 700;">Descripción del Puesto</h5>
                        <p style="font-size: 15px; line-height: 1.7; color: var(--gris-medio);">
                            <?php echo nl2br(htmlspecialchars($oferta['descripcion'])); ?>
                        </p>
                    </div>
                    
                    <!-- Requisitos -->
                    <?php if ($oferta['requisitos']): ?>
                        <div class="job-description mb-4">
                            <h5 class="mb-3" style="font-size: 18px; font-weight: 700;">Requisitos</h5>
                            <p style="font-size: 15px; line-height: 1.7; color: var(--gris-medio);">
                                <?php echo nl2br(htmlspecialchars($oferta['requisitos'])); ?>
                            </p>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Responsabilidades -->
                    <?php if ($oferta['responsabilidades']): ?>
                        <div class="job-description mb-4">
                            <h5 class="mb-3" style="font-size: 18px; font-weight: 700;">Responsabilidades</h5>
                            <p style="font-size: 15px; line-height: 1.7; color: var(--gris-medio);">
                                <?php echo nl2br(htmlspecialchars($oferta['responsabilidades'])); ?>
                            </p>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Beneficios -->
                    <?php if ($oferta['beneficios']): ?>
                        <div class="job-description mb-4">
                            <h5 class="mb-3" style="font-size: 18px; font-weight: 700;">Beneficios</h5>
                            <p style="font-size: 15px; line-height: 1.7; color: var(--gris-medio);">
                                <?php echo nl2br(htmlspecialchars($oferta['beneficios'])); ?>
                            </p>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Habilidades -->
                    <?php if (!empty($habilidades)): ?>
                        <div class="mb-4">
                            <h5 class="mb-3" style="font-size: 18px; font-weight: 700;">Habilidades Requeridas</h5>
                            <div class="d-flex flex-wrap gap-2">
                                <?php foreach ($habilidades as $hab): ?>
                                    <span class="badge bg-secondary" style="font-size: 13px; padding: 8px 16px; font-weight: 500;">
                                        <?php echo htmlspecialchars($hab['nombre']); ?>
                                    </span>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <hr>
                    
                    <!-- Información de la Empresa -->
                    <div class="mb-4">
                        <h5 class="mb-3" style="font-size: 18px; font-weight: 700;">Sobre la Empresa</h5>
                        <div class="d-flex flex-wrap gap-4">
                            <?php if ($oferta['sitio_web']): ?>
                                <div>
                                    <i class="bi bi-globe text-muted"></i>
                                    <a href="<?php echo htmlspecialchars($oferta['sitio_web']); ?>" 
                                       target="_blank" 
                                       rel="noopener noreferrer"
                                       class="ms-2">
                                        Visitar sitio web
                                    </a>
                                </div>
                            <?php endif; ?>
                            <?php if ($oferta['sector']): ?>
                                <div class="text-muted">
                                    <i class="bi bi-building"></i>
                                    <span class="ms-2"><?php echo htmlspecialchars($oferta['sector']); ?></span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <hr>
                    
                    <!-- Botón de postulación -->
                    <?php if ($ya_postulo): ?>
                        <div class="alert alert-info border-0">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-check-circle-fill fs-3 me-3"></i>
                                <div>
                                    <h6 class="alert-heading mb-1">Ya te has postulado a esta oferta</h6>
                                    <p class="mb-0 small">Puedes revisar el estado de tu postulación en <a href="<?php echo BASE_URL; ?>/views/estudiante/mis_postulaciones.php">Mis Postulaciones</a></p>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="card bg-light border-0">
                            <div class="card-body p-4">
                                <h5 class="mb-3">¿Te interesa esta oportunidad?</h5>
                                <p class="text-muted mb-3">
                                    Al postularte, tu perfil y CV serán enviados a <?php echo htmlspecialchars($oferta['empresa']); ?> 
                                    para su revisión.
                                </p>
                                <form method="POST" action="<?php echo BASE_URL; ?>/controllers/PostulacionController.php?action=postular" id="formPostular">
                                    <input type="hidden" name="csrf_token" value="<?php echo generarTokenCSRF(); ?>">
                                    <input type="hidden" name="id_oferta" value="<?php echo $oferta['id_oferta']; ?>">
                                    
                                    <div class="d-flex gap-2">
                                        <button type="submit" class="btn btn-success btn-lg">
                                            <i class="bi bi-send me-2"></i>Postular a esta Oferta
                                        </button>
                                        <a href="<?php echo BASE_URL; ?>/views/estudiante/ofertas.php" class="btn btn-outline-secondary btn-lg">
                                            Seguir Buscando
                                        </a>
                                    </div>
                                </form>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Ofertas relacionadas (opcional) -->
            <div class="mt-4">
                <a href="<?php echo BASE_URL; ?>/views/estudiante/ofertas.php" class="btn btn-link">
                    <i class="bi bi-arrow-left me-2"></i>Ver más ofertas disponibles
                </a>
            </div>
        </div>
    </div>
</div>

<script>
// Confirmación antes de postular
document.getElementById('formPostular')?.addEventListener('submit', function(e) {
    if (!confirm('¿Estás seguro que deseas postularte a esta oferta?')) {
        e.preventDefault();
    }
});
</script>

<?php include __DIR__ . '/../layout/footer.php'; ?>
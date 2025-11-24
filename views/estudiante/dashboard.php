<?php
/**
 * Dashboard Estudiante - Portal de Trabajo UTP
 * Panel principal con estadísticas y accesos rápidos
 */
session_start();
require_once __DIR__ . '/../../config/session.php';
verificarSesion('estudiante');

$page_title = 'Dashboard - Estudiante';

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../models/Estudiante.php';
require_once __DIR__ . '/../../models/Postulacion.php';
require_once __DIR__ . '/../../models/Oferta.php';

$db = Database::getInstance()->getConnection();
$estudianteModel = new Estudiante($db);
$postulacionModel = new Postulacion($db);
$ofertaModel = new Oferta($db);

$id_estudiante = $_SESSION['id_usuario'];

// Obtener datos del estudiante con estadísticas
$estudiante = $estudianteModel->getConPostulaciones($id_estudiante);

// Obtener postulaciones recientes
$postulaciones_recientes = $postulacionModel->getPostulacionesEstudiante($id_estudiante, 5, 0);

// Obtener ofertas recomendadas
$ofertas_recomendadas = $ofertaModel->getOfertasRecientes(6);

include __DIR__ . '/../layout/header_estudiante.php';
?>

<div class="container py-5">
    <!-- Bienvenida -->
    <div class="row mb-4">
        <div class="col-12">
            <h1 style="font-size: 32px; font-weight: 700; color: var(--gris-oscuro);">
                ¡Bienvenido, <?php echo htmlspecialchars($estudiante['nombres']); ?>!
            </h1>
            <p class="text-muted">Este es tu panel de control. Aquí puedes ver tus postulaciones y buscar nuevas oportunidades.</p>
        </div>
    </div>
    
    <!-- Cards de Estadísticas -->
    <div class="row g-4 mb-5">
        <!-- Total Postulaciones -->
        <div class="col-md-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center gap-3">
                        <div class="bg-primary bg-opacity-10 rounded p-3">
                            <i class="bi bi-file-earmark-text text-primary" style="font-size: 32px;"></i>
                        </div>
                        <div>
                            <h3 class="mb-0" style="font-weight: 700;"><?php echo $estudiante['total_postulaciones'] ?? 0; ?></h3>
                            <p class="text-muted mb-0 small">Total Postulaciones</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- En Revisión -->
        <div class="col-md-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center gap-3">
                        <div class="bg-warning bg-opacity-10 rounded p-3">
                            <i class="bi bi-clock-history text-warning" style="font-size: 32px;"></i>
                        </div>
                        <div>
                            <h3 class="mb-0" style="font-weight: 700;"><?php echo $estudiante['postulaciones_en_revision'] ?? 0; ?></h3>
                            <p class="text-muted mb-0 small">En Revisión</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Aceptadas -->
        <div class="col-md-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center gap-3">
                        <div class="bg-success bg-opacity-10 rounded p-3">
                            <i class="bi bi-check-circle text-success" style="font-size: 32px;"></i>
                        </div>
                        <div>
                            <h3 class="mb-0" style="font-weight: 700;"><?php echo $estudiante['postulaciones_aceptadas'] ?? 0; ?></h3>
                            <p class="text-muted mb-0 small">Aceptadas</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Estado CV -->
        <div class="col-md-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center gap-3">
                        <div class="<?php echo $estudiante['estado_cv'] === 'activo' ? 'bg-success' : 'bg-danger'; ?> bg-opacity-10 rounded p-3">
                            <i class="bi bi-file-pdf <?php echo $estudiante['estado_cv'] === 'activo' ? 'text-success' : 'text-danger'; ?>" style="font-size: 32px;"></i>
                        </div>
                        <div>
                            <h6 class="mb-0 fw-bold"><?php echo $estudiante['estado_cv'] === 'activo' ? 'CV Activo' : 'Sin CV'; ?></h6>
                            <p class="text-muted mb-0 small">
                                <?php echo $estudiante['estado_cv'] === 'activo' ? 'Actualizado' : 'Sube tu CV'; ?>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row g-4">
        <!-- Columna Izquierda: Postulaciones Recientes -->
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="mb-0" style="font-weight: 700;">Postulaciones Recientes</h5>
                        <a href="<?php echo BASE_URL; ?>/views/estudiante/mis_postulaciones.php" class="btn btn-sm btn-outline-success">
                            Ver Todas
                        </a>
                    </div>
                    
                    <?php if (!empty($postulaciones_recientes)): ?>
                        <div class="list-group list-group-flush">
                            <?php foreach ($postulaciones_recientes as $post): ?>
                                <div class="list-group-item px-0">
                                    <div class="d-flex gap-3">
                                        <div class="company-logo-small">
                                            <?php if ($post['logo'] && $post['logo'] !== 'placeholder-logo.png'): ?>
                                                <img src="<?php echo BASE_URL; ?>/assets/images/logos/<?php echo htmlspecialchars($post['logo']); ?>" alt="">
                                            <?php else: ?>
                                                <i class="bi bi-building"></i>
                                            <?php endif; ?>
                                        </div>
                                        
                                        <div class="flex-grow-1">
                                            <h6 class="mb-1"><?php echo htmlspecialchars($post['titulo_oferta']); ?></h6>
                                            <p class="text-muted small mb-2"><?php echo htmlspecialchars($post['nombre_empresa']); ?></p>
                                            
                                            <div class="d-flex align-items-center gap-3">
                                                <?php
                                                $badge_class = [
                                                    'en_revision' => 'bg-warning',
                                                    'aceptada' => 'bg-success',
                                                    'rechazada' => 'bg-danger'
                                                ];
                                                $badge_text = [
                                                    'en_revision' => 'En Revisión',
                                                    'aceptada' => 'Aceptada',
                                                    'rechazada' => 'Rechazada'
                                                ];
                                                ?>
                                                <span class="badge <?php echo $badge_class[$post['estado']]; ?>">
                                                    <?php echo $badge_text[$post['estado']]; ?>
                                                </span>
                                                <small class="text-muted">
                                                    <?php echo tiempoTranscurrido($post['fecha_postulacion']); ?>
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-5">
                            <i class="bi bi-inbox" style="font-size: 64px; color: var(--gris-claro);"></i>
                            <p class="text-muted mt-3">No tienes postulaciones aún</p>
                            <a href="<?php echo BASE_URL; ?>/views/estudiante/ofertas.php" class="btn btn-success">
                                Buscar Empleos
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Columna Derecha: Acciones Rápidas y Estado del Perfil -->
        <div class="col-lg-5">
            <!-- Estado del Perfil -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-4">
                    <h5 class="mb-4" style="font-weight: 700;">Estado del Perfil</h5>
                    
                    <!-- Completitud del perfil -->
                    <?php
                    $completitud = 0;
                    if (!empty($estudiante['nombres'])) $completitud += 25;
                    if (!empty($estudiante['carrera'])) $completitud += 25;
                    if (!empty($estudiante['descripcion_perfil'])) $completitud += 25;
                    if ($estudiante['estado_cv'] === 'activo') $completitud += 25;
                    ?>
                    
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="small fw-semibold">Completitud</span>
                            <span class="small fw-semibold"><?php echo $completitud; ?>%</span>
                        </div>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar bg-success" style="width: <?php echo $completitud; ?>%"></div>
                        </div>
                    </div>
                    
                    <!-- Checklist -->
                    <div class="list-group list-group-flush">
                        <div class="list-group-item px-0 d-flex align-items-center gap-2">
                            <i class="bi bi-check-circle-fill text-success"></i>
                            <span class="small">Información básica</span>
                        </div>
                        <div class="list-group-item px-0 d-flex align-items-center gap-2">
                            <i class="bi bi-<?php echo !empty($estudiante['carrera']) ? 'check-circle-fill text-success' : 'circle text-muted'; ?>"></i>
                            <span class="small">Carrera seleccionada</span>
                        </div>
                        <div class="list-group-item px-0 d-flex align-items-center gap-2">
                            <i class="bi bi-<?php echo !empty($estudiante['descripcion_perfil']) ? 'check-circle-fill text-success' : 'circle text-muted'; ?>"></i>
                            <span class="small">Descripción del perfil</span>
                        </div>
                        <div class="list-group-item px-0 d-flex align-items-center gap-2">
                            <i class="bi bi-<?php echo $estudiante['estado_cv'] === 'activo' ? 'check-circle-fill text-success' : 'circle text-muted'; ?>"></i>
                            <span class="small">CV subido</span>
                        </div>
                    </div>
                    
                    <?php if ($completitud < 100): ?>
                        <a href="<?php echo BASE_URL; ?>/views/estudiante/perfil.php" class="btn btn-success w-100 mt-3">
                            Completar Perfil
                        </a>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Acciones Rápidas -->
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <h5 class="mb-4" style="font-weight: 700;">Acciones Rápidas</h5>
                    
                    <div class="d-grid gap-2">
                        <a href="<?php echo BASE_URL; ?>/views/estudiante/ofertas.php" class="btn btn-success">
                            <i class="bi bi-search me-2"></i>Buscar Empleos
                        </a>
                        <a href="<?php echo BASE_URL; ?>/views/estudiante/perfil.php" class="btn btn-outline-success">
                            <i class="bi bi-person-circle me-2"></i>Editar Perfil
                        </a>
                        <a href="<?php echo BASE_URL; ?>/views/estudiante/mis_postulaciones.php" class="btn btn-outline-success">
                            <i class="bi bi-file-earmark-text me-2"></i>Ver Postulaciones
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../layout/footer.php'; ?>
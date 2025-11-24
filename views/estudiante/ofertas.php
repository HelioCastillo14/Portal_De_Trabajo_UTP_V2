<?php
session_start();
require_once __DIR__ . '/../../config/session.php';
verificarSesion('estudiante');
$page_title = 'Buscar Empleos';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../models/Oferta.php';
require_once __DIR__ . '/../../models/Empresa.php';

$db = Database::getInstance()->getConnection();
$ofertaModel = new Oferta($db);
$empresaModel = new Empresa($db);

$filtros = [
    'busqueda' => $_GET['q'] ?? '',
    'modalidad' => $_GET['modalidad'] ?? '',
    'tipo_empleo' => $_GET['tipo'] ?? '',
    'nivel' => $_GET['nivel'] ?? '',
    'empresa' => $_GET['empresa'] ?? ''
];

$ofertas = $ofertaModel->getOfertasActivas($filtros, 1, 50);
$empresas_activas = $empresaModel->listarActivas();

$id_seleccionado = isset($_GET['id']) ? (int)$_GET['id'] : (!empty($ofertas) ? $ofertas[0]['id_oferta'] : 0);
$oferta_seleccionada = null;
$habilidades = [];

if ($id_seleccionado > 0) {
    $oferta_seleccionada = $ofertaModel->findById($id_seleccionado);
    if ($oferta_seleccionada) {
        $habilidades = $ofertaModel->getHabilidades($id_seleccionado);
        $ya_postulo = $ofertaModel->yaSePostulo($id_seleccionado, $_SESSION['id_usuario']);
    }
}

include __DIR__ . '/../layout/header_estudiante.php';
?>

<style>
.jobs-list-container {
    height: calc(100vh - 300px);
    overflow-y: auto;
    padding-right: 8px;
}

.jobs-list-container::-webkit-scrollbar,
.job-detail-panel::-webkit-scrollbar {
    width: 6px;
}

.job-list-card {
    cursor: pointer;
    transition: all 0.2s ease;
    border: 2px solid #E5E7EB;
    border-radius: 12px;
    background: white;
    animation: slideIn 0.3s ease forwards;
}

.job-list-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(30,132,73,0.1);
    border-color: var(--verde-principal);
}

.job-list-card.selected {
    border-color: var(--verde-principal);
    background-color: #f8fdf9;
}

.job-title-small {
    font-size: 16px;
    font-weight: 700;
    line-height: 1.3;
    color: var(--gris-oscuro);
}

.company-name-small {
    font-size: 14px;
    color: var(--gris-claro);
}

.job-detail-panel {
    position: sticky;
    top: 100px;
    max-height: calc(100vh - 120px);
    overflow-y: auto;
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
}

.job-title-detail {
    font-size: 32px;
    font-weight: 700;
    color: var(--gris-oscuro);
    line-height: 1.2;
    margin-bottom: 12px;
}

.job-description h5 {
    font-size: 18px;
    font-weight: 700;
    color: var(--gris-oscuro);
    margin-bottom: 12px;
}

.job-description p,
.job-description li {
    font-size: 15px;
    line-height: 1.7;
    color: var(--gris-medio);
}

@media (max-width: 991px) {
    .jobs-list-container {
        height: auto !important;
        margin-bottom: 24px;
    }
    
    .job-detail-panel {
        position: relative;
        top: 0;
        max-height: none;
    }
}
</style>

<!-- Hero Empleos -->
<section class="hero-empleos">
    <div class="container position-relative" style="z-index: 1;">
        <h1 class="hero-title-empleos">Encuentra tu Empleo Ideal</h1>
        <p class="hero-subtitle-empleos">
            <?php echo count($ofertas); ?> oportunidades esperando por ti
        </p>
        
        <div class="search-box-empleos">
            <form method="GET" class="d-flex gap-2">
                <input type="text" name="q" class="form-control" placeholder="Buscar por puesto, empresa o habilidad..." value="<?php echo htmlspecialchars($filtros['busqueda']); ?>">
                <button class="btn btn-success" type="submit"><i class="bi bi-search"></i> Buscar</button>
            </form>
        </div>
    </div>
</section>

<!-- Filtros Sticky -->
<div class="filters py-3 bg-white">
    <div class="container">
        <div class="d-flex gap-2 flex-wrap">
            <div class="dropdown">
                <button class="btn dropdown-toggle" data-bs-toggle="dropdown">
                    <i class="bi bi-briefcase me-2"></i><?php echo $filtros['tipo_empleo'] ? ucfirst(str_replace('_', ' ', $filtros['tipo_empleo'])) : 'Tipo de Empleo'; ?>
                </button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="?<?php echo http_build_query(array_merge($filtros, ['tipo' => ''])); ?>">Todos</a></li>
                    <li><a class="dropdown-item" href="?<?php echo http_build_query(array_merge($filtros, ['tipo' => 'tiempo_completo'])); ?>">Tiempo Completo</a></li>
                    <li><a class="dropdown-item" href="?<?php echo http_build_query(array_merge($filtros, ['tipo' => 'medio_tiempo'])); ?>">Medio Tiempo</a></li>
                    <li><a class="dropdown-item" href="?<?php echo http_build_query(array_merge($filtros, ['tipo' => 'pasantia'])); ?>">Pasantía</a></li>
                </ul>
            </div>
            
            <div class="dropdown">
                <button class="btn dropdown-toggle" data-bs-toggle="dropdown">
                    <i class="bi bi-laptop me-2"></i><?php echo $filtros['modalidad'] ? ucfirst($filtros['modalidad']) : 'Modalidad'; ?>
                </button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="?<?php echo http_build_query(array_merge($filtros, ['modalidad' => ''])); ?>">Todas</a></li>
                    <li><a class="dropdown-item" href="?<?php echo http_build_query(array_merge($filtros, ['modalidad' => 'remoto'])); ?>">Remoto</a></li>
                    <li><a class="dropdown-item" href="?<?php echo http_build_query(array_merge($filtros, ['modalidad' => 'presencial'])); ?>">Presencial</a></li>
                    <li><a class="dropdown-item" href="?<?php echo http_build_query(array_merge($filtros, ['modalidad' => 'hibrido'])); ?>">Híbrido</a></li>
                </ul>
            </div>
            
            <div class="dropdown">
                <button class="btn dropdown-toggle" data-bs-toggle="dropdown">
                    <i class="bi bi-graph-up me-2"></i><?php echo $filtros['nivel'] ? ucfirst($filtros['nivel']) : 'Nivel'; ?>
                </button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="?<?php echo http_build_query(array_merge($filtros, ['nivel' => ''])); ?>">Todos</a></li>
                    <li><a class="dropdown-item" href="?<?php echo http_build_query(array_merge($filtros, ['nivel' => 'junior'])); ?>">Junior</a></li>
                    <li><a class="dropdown-item" href="?<?php echo http_build_query(array_merge($filtros, ['nivel' => 'mid'])); ?>">Mid-Level</a></li>
                    <li><a class="dropdown-item" href="?<?php echo http_build_query(array_merge($filtros, ['nivel' => 'senior'])); ?>">Senior</a></li>
                </ul>
            </div>
            
            <?php if (array_filter($filtros)): ?>
                <a href="?" class="btn btn-outline-secondary"><i class="bi bi-x-circle me-1"></i>Limpiar</a>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Layout 2 Columnas -->
<div class="container py-4">
    <div class="row">
        <!-- COLUMNA IZQUIERDA: Lista scrolleable -->
        <div class="col-lg-5 col-xl-4">
            <div class="jobs-list-container">
                <?php if (!empty($ofertas)): ?>
                    <?php foreach ($ofertas as $oferta): ?>
                        <div class="card job-list-card mb-3 <?php echo ($oferta['id_oferta'] == $id_seleccionado) ? 'selected' : ''; ?>" 
                             onclick="window.location.href='?id=<?php echo $oferta['id_oferta']; ?><?php echo $filtros['busqueda'] ? '&q=' . urlencode($filtros['busqueda']) : ''; ?>'">
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-auto">
                                        <div class="company-logo-small">
                                            <?php if ($oferta['logo'] && $oferta['logo'] !== 'placeholder-logo.png'): ?>
                                                <img src="<?php echo BASE_URL; ?>/assets/images/logos/<?php echo htmlspecialchars($oferta['logo']); ?>" alt="">
                                            <?php else: ?>
                                                <i class="bi bi-building"></i>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    
                                    <div class="col">
                                        <h6 class="job-title-small mb-1"><?php echo htmlspecialchars($oferta['titulo']); ?></h6>
                                        <p class="company-name-small mb-2"><?php echo htmlspecialchars($oferta['empresa']); ?></p>
                                        
                                        <div class="d-flex align-items-center gap-2 mb-2">
                                            <small class="text-muted"><?php echo tiempoTranscurrido($oferta['fecha_publicacion']); ?></small>
                                        </div>
                                        
                                        <div class="d-flex flex-wrap gap-2">
                                            <?php
                                            $tipo_badges = ['tiempo_completo' => 'badge-fulltime', 'medio_tiempo' => 'badge-parttime', 'pasantia' => 'badge-parttime'];
                                            $modalidad_badges = ['remoto' => 'badge-remote', 'presencial' => 'badge-presencial', 'hibrido' => 'badge-hibrido'];
                                            ?>
                                            <span class="badge-custom <?php echo $tipo_badges[$oferta['tipo_empleo']] ?? 'badge-fulltime'; ?>">
                                                <?php echo ucfirst(str_replace('_', ' ', $oferta['tipo_empleo'])); ?>
                                            </span>
                                            <span class="badge-custom <?php echo $modalidad_badges[$oferta['modalidad']] ?? 'badge-remote'; ?>">
                                                <?php echo ucfirst($oferta['modalidad']); ?>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="text-center py-5">
                        <i class="bi bi-search" style="font-size: 64px; color: var(--gris-claro);"></i>
                        <p class="text-muted mt-3">No se encontraron ofertas</p>
                        <a href="?" class="btn btn-success">Ver Todas</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- COLUMNA DERECHA: Detalle sticky -->
        <div class="col-lg-7 col-xl-8">
            <?php if ($oferta_seleccionada): ?>
                <div class="job-detail-panel p-4">
                    <div class="d-flex align-items-start gap-3 mb-4">
                        <div class="company-logo">
                            <?php if ($oferta_seleccionada['logo'] && $oferta_seleccionada['logo'] !== 'placeholder-logo.png'): ?>
                                <img src="<?php echo BASE_URL; ?>/assets/images/logos/<?php echo htmlspecialchars($oferta_seleccionada['logo']); ?>" alt="">
                            <?php else: ?>
                                <i class="bi bi-building"></i>
                            <?php endif; ?>
                        </div>
                        
                        <div class="flex-grow-1">
                            <p class="text-uppercase mb-1" style="font-size: 14px; font-weight: 600; color: var(--gris-claro);">
                                <?php echo htmlspecialchars($oferta_seleccionada['empresa']); ?>
                            </p>
                            <h1 class="job-title-detail"><?php echo htmlspecialchars($oferta_seleccionada['titulo']); ?></h1>
                            
                            <div class="d-flex flex-wrap gap-3 text-muted mb-3">
                                <span><i class="bi bi-geo-alt me-1"></i><?php echo htmlspecialchars($oferta_seleccionada['ubicacion']); ?></span>
                                <span><i class="bi bi-calendar-event me-1"></i><?php echo tiempoTranscurrido($oferta_seleccionada['fecha_publicacion']); ?></span>
                            </div>
                        </div>
                    </div>
                    
                    <?php if ($oferta_seleccionada['salario_min']): ?>
                        <div class="alert bg-success-subtle border-0 mb-4">
                            <strong class="text-success">Salario:</strong>
                            <span class="fs-5 fw-bold text-success ms-2"><?php echo formatearSalario($oferta_seleccionada['salario_min'], $oferta_seleccionada['salario_max']); ?></span>
                        </div>
                    <?php endif; ?>
                    
                    <div class="job-description mb-4">
                        <h5>Descripción</h5>
                        <p><?php echo nl2br(htmlspecialchars($oferta_seleccionada['descripcion'])); ?></p>
                    </div>
                    
                    <?php if ($oferta_seleccionada['requisitos']): ?>
                        <div class="job-description mb-4">
                            <h5>Requisitos</h5>
                            <p><?php echo nl2br(htmlspecialchars($oferta_seleccionada['requisitos'])); ?></p>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($habilidades)): ?>
                        <div class="mb-4">
                            <h5>Habilidades Requeridas</h5>
                            <div class="d-flex flex-wrap gap-2">
                                <?php foreach ($habilidades as $hab): ?>
                                    <span class="badge bg-secondary"><?php echo htmlspecialchars($hab['nombre']); ?></span>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <hr>
                    
                    <?php if (isset($ya_postulo) && $ya_postulo): ?>
                        <div class="alert alert-info">
                            <i class="bi bi-check-circle me-2"></i>Ya te has postulado a esta oferta
                        </div>
                    <?php else: ?>
                        <form method="POST" action="<?php echo BASE_URL; ?>/controllers/PostulacionController.php?action=postular">
                            <input type="hidden" name="csrf_token" value="<?php echo generarTokenCSRF(); ?>">
                            <input type="hidden" name="id_oferta" value="<?php echo $oferta_seleccionada['id_oferta']; ?>">
                            <button type="submit" class="btn btn-success w-100 btn-lg">
                                <i class="bi bi-send me-2"></i>Postular Ahora
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div class="text-center py-5">
                    <i class="bi bi-briefcase" style="font-size: 64px; color: var(--gris-claro);"></i>
                    <p class="text-muted mt-3">Selecciona una oferta para ver los detalles</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../layout/footer.php'; ?>
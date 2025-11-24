<?php
/**
 * HOME.PHP - Homepage alternativa (si la usas en vez de index.php en raíz)
 * Portal de Trabajo UTP
 */
session_start();
$page_title = 'Portal de Trabajo UTP';

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../models/Oferta.php';
require_once __DIR__ . '/../../models/Empresa.php';

$db = Database::getInstance()->getConnection();
$ofertaModel = new Oferta($db);
$empresaModel = new Empresa($db);

// Obtener ofertas recientes
$ofertas_recientes = $ofertaModel->getOfertasRecientes(6);

// Obtener estadísticas
$stats = [
    'ofertas' => $ofertaModel->contarActivas(),
    'empresas' => $empresaModel->contarActivas(),
    'nuevas_hoy' => $ofertaModel->contarNuevasHoy()
];

include __DIR__ . '/../layout/header.php';
?>

<!-- Link al CSS específico -->
<link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/home.css">

<!-- Hero Section -->
<section class="hero-home">
    <div class="container position-relative" style="z-index: 1;">
        <div class="row align-items-center">
            <div class="col-lg-8 mx-auto text-center">
                <h1 class="hero-title-home">
                    Encuentra tu Próxima<br>
                    <span style="color: var(--verde-principal);">Oportunidad Profesional</span>
                </h1>
                <p class="hero-subtitle-home">
                    Conectamos estudiantes UTP con las mejores empresas de Panamá
                </p>
                
                <!-- Search Box -->
                <div class="search-box-home">
                    <form method="GET" action="<?php echo BASE_URL; ?>/views/public/ofertas_publicas.php" class="d-flex gap-2">
                        <input type="text" 
                               name="q" 
                               class="form-control border-0" 
                               placeholder="Buscar por puesto, empresa o habilidad..." 
                               autocomplete="off">
                        <button class="btn btn-success" type="submit">
                            <i class="bi bi-search me-2"></i>Buscar Empleos
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Estadísticas -->
<section class="container my-5">
    <div class="stats-section">
        <div class="row">
            <div class="col-md-4">
                <div class="stat-item">
                    <div class="stat-number"><?php echo $stats['ofertas']; ?></div>
                    <div class="stat-label">Ofertas Activas</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-item">
                    <div class="stat-number"><?php echo $stats['empresas']; ?></div>
                    <div class="stat-label">Empresas Aliadas</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-item">
                    <div class="stat-number"><?php echo $stats['nuevas_hoy']; ?></div>
                    <div class="stat-label">Nuevas Hoy</div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Ofertas Recientes -->
<section class="ofertas-recientes">
    <div class="container">
        <h2 class="section-title">Últimas Oportunidades</h2>
        
        <?php if (!empty($ofertas_recientes)): ?>
            <div class="row g-4 mb-5">
                <?php foreach ($ofertas_recientes as $oferta): ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="job-card-home">
                            <!-- Logo y estado -->
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div class="company-logo">
                                    <?php if ($oferta['logo'] && $oferta['logo'] !== 'placeholder-logo.png'): ?>
                                        <img src="<?php echo BASE_URL; ?>/assets/images/logos/<?php echo htmlspecialchars($oferta['logo']); ?>" 
                                             alt="<?php echo htmlspecialchars($oferta['empresa']); ?>">
                                    <?php else: ?>
                                        <i class="bi bi-building"></i>
                                    <?php endif; ?>
                                </div>
                                <span class="badge-dot bg-success"></span>
                            </div>
                            
                            <!-- Empresa -->
                            <p class="company-name">
                                <?php echo htmlspecialchars($oferta['empresa']); ?>
                            </p>
                            
                            <!-- Título -->
                            <h3 class="job-title">
                                <?php echo htmlspecialchars($oferta['titulo']); ?>
                            </h3>
                            
                            <!-- Descripción -->
                            <p class="job-description">
                                <?php echo acortarTexto($oferta['descripcion'], 100); ?>
                            </p>
                            
                            <!-- Meta info -->
                            <div class="job-meta">
                                <i class="bi bi-geo-alt"></i><?php echo htmlspecialchars($oferta['ubicacion']); ?>
                                <span class="mx-2">•</span>
                                <i class="bi bi-laptop"></i><?php echo ucfirst($oferta['modalidad']); ?>
                            </div>
                            
                            <!-- Salario -->
                            <?php if ($oferta['salario_min']): ?>
                                <div class="salary">
                                    <?php echo formatearSalario($oferta['salario_min'], $oferta['salario_max']); ?>
                                </div>
                            <?php endif; ?>
                            
                            <!-- Botón -->
                            <a href="<?php echo BASE_URL; ?>/views/public/detalle_oferta_publica.php?id=<?php echo $oferta['id_oferta']; ?>" 
                               class="btn btn-success btn-details">
                                Ver Detalles
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <!-- Ver todas -->
            <div class="text-center">
                <a href="<?php echo BASE_URL; ?>/views/public/ofertas_publicas.php" class="btn btn-outline-success btn-lg">
                    Ver Todas las Ofertas
                    <i class="bi bi-arrow-right ms-2"></i>
                </a>
            </div>
        <?php else: ?>
            <div class="text-center py-5">
                <i class="bi bi-inbox" style="font-size: 64px; color: var(--gris-claro);"></i>
                <h3 class="mt-3">No hay ofertas disponibles en este momento</h3>
                <p class="text-muted">Vuelve pronto para ver nuevas oportunidades</p>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- Beneficios -->
<section class="beneficios-section">
    <div class="container">
        <h2 class="section-title">¿Por qué usar nuestro portal?</h2>
        
        <div class="row g-4">
            <div class="col-md-4">
                <div class="beneficio-card">
                    <div class="beneficio-icon">
                        <i class="bi bi-shield-check"></i>
                    </div>
                    <h5>Ofertas Verificadas</h5>
                    <p>Todas las empresas y ofertas están verificadas por la UTP para garantizar oportunidades reales y confiables.</p>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="beneficio-card">
                    <div class="beneficio-icon">
                        <i class="bi bi-calendar-check"></i>
                    </div>
                    <h5>Actualizaciones Diarias</h5>
                    <p>Nuevas oportunidades laborales publicadas todos los días de empresas líderes en diferentes sectores.</p>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="beneficio-card">
                    <div class="beneficio-icon">
                        <i class="bi bi-rocket-takeoff"></i>
                    </div>
                    <h5>Impulsa tu Carrera</h5>
                    <p>Accede a pasantías, medio tiempo y tiempo completo diseñados específicamente para estudiantes UTP.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="cta-section">
    <div class="container">
        <h2>¿Listo para comenzar?</h2>
        <p>Crea tu perfil y empieza a aplicar a ofertas que impulsen tu carrera profesional</p>
        <a href="<?php echo BASE_URL; ?>/views/auth/login_estudiante.php" class="btn btn-light btn-lg">
            <i class="bi bi-person-plus me-2"></i>Ingresar como Estudiante
        </a>
    </div>
</section>

<?php include __DIR__ . '/../layout/footer.php'; ?>
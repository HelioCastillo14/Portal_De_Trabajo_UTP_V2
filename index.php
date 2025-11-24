<?php
/**
 * Homepage - Portal de Trabajo UTP
 * Página principal pública con hero, búsqueda y últimas ofertas
 */
session_start();
$page_title = 'Portal de Trabajo UTP - Empleos para Estudiantes';

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/models/Oferta.php';

$db = Database::getInstance()->getConnection();
$ofertaModel = new Oferta($db);

// Obtener últimas 6 ofertas
$ofertas_recientes = $ofertaModel->getOfertasRecientes(6);

// Obtener estadísticas para el hero
$stats = $ofertaModel->obtenerEstadisticas();

include __DIR__ . '/views/layout/header.php';
?>

<!-- HERO SECTION -->
<section class="hero-section">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-7">
                <h1 class="hero-title">
                    Encuentra tu Próxima<br>
                    Oportunidad Profesional
                </h1>
                <p class="hero-subtitle">
                    Conectamos estudiantes de la UTP con las mejores empresas de Panamá
                </p>
                
                <!-- CAJA DE BÚSQUEDA -->
                <div class="search-box mb-4">
                    <form action="<?php echo BASE_URL; ?>/views/public/ofertas_publicas.php" method="GET">
                        <div class="input-group">
                            <input type="text" 
                                   name="q" 
                                   class="form-control" 
                                   placeholder="Buscar por puesto o habilidad (ej: PHP, Desarrollador, Analista...)"
                                   value="<?php echo isset($_GET['q']) ? htmlspecialchars($_GET['q']) : ''; ?>">
                            <button class="btn btn-success" type="submit">
                                <i class="bi bi-search me-2"></i>Buscar Empleos
                            </button>
                        </div>
                    </form>
                </div>
                
                <!-- ESTADÍSTICAS -->
                <div class="d-flex gap-4 text-muted">
                    <div>
                        <i class="bi bi-briefcase me-2"></i>
                        <strong class="text-dark"><?php echo $stats['ofertas_activas']; ?></strong> empleos activos
                    </div>
                    <div>
                        <i class="bi bi-building me-2"></i>
                        <strong class="text-dark">10+</strong> empresas
                    </div>
                </div>
            </div>
            
            <div class="col-lg-5 d-none d-lg-block text-center">
                <img src="<?php echo BASE_URL; ?>/assets/images/hero-illustration.png" 
                     alt="Estudiantes UTP" 
                     class="img-fluid"
                     style="max-height: 400px;"
                     onerror="this.style.display='none'">
            </div>
        </div>
    </div>
</section>

<!-- ÚLTIMAS OFERTAS -->
<section class="py-5">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 style="font-size: 32px; font-weight: 700; color: var(--gris-oscuro);">
                Últimas Oportunidades
            </h2>
            <a href="<?php echo BASE_URL; ?>/views/public/ofertas_publicas.php" class="view-all">
                Ver todas <i class="bi bi-arrow-right ms-1"></i>
            </a>
        </div>
        
        <div class="row g-4">
            <?php if (!empty($ofertas_recientes)): ?>
                <?php foreach ($ofertas_recientes as $oferta): ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="card job-card h-100 border-0 shadow-sm">
                            <div class="card-body">
                                <!-- Logo y estado -->
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <div class="company-logo">
                                        <?php $logo = $oferta['logo'] ?? $oferta['empresa_logo'] ?? null; if ($logo && $logo !== 'placeholder-logo.png'): ?>
                                            <img src="<?php echo BASE_URL; ?>/assets/images/logos/<?php echo htmlspecialchars($oferta['logo']); ?>" 
                                                 alt="<?php echo htmlspecialchars($oferta['empresa']); ?>">
                                        <?php else: ?>
                                            <i class="bi bi-building"></i>
                                        <?php endif; ?>
                                    </div>
                                    <span class="badge-dot bg-success"></span>
                                </div>
                                
                                <!-- Nombre empresa -->
                                <p class="company-name text-uppercase mb-2" style="font-size: 14px; font-weight: 600; color: var(--gris-claro); letter-spacing: 0.5px;">
                                    <?php echo htmlspecialchars($oferta['empresa']); ?>
                                </p>
                                
                                <!-- Título -->
                                <h5 class="card-title mb-3" style="font-size: 20px; font-weight: 700; line-height: 1.3; color: var(--gris-oscuro);">
                                    <?php echo htmlspecialchars($oferta['titulo']); ?>
                                </h5>
                                
                                <!-- Descripción -->
                                <p class="card-text text-muted mb-3" style="font-size: 14px; line-height: 1.6;">
                                    <?php echo acortarTexto($oferta['descripcion'], 100); ?>
                                </p>
                                
                                <!-- Meta info -->
                                <div class="d-flex align-items-center gap-3 mb-3 text-muted small">
                                    <span><i class="bi bi-geo-alt me-1"></i><?php echo htmlspecialchars($oferta['ubicacion']); ?></span>
                                    <span>|</span>
                                    <span><i class="bi bi-laptop me-1"></i><?php echo ucfirst($oferta['modalidad']); ?></span>
                                </div>
                                
                                <!-- Salario -->
                                <?php if ($oferta['salario_min']): ?>
                                    <p class="salary mb-3" style="font-size: 18px; font-weight: 700; color: var(--verde-principal);">
                                        <?php echo formatearSalario($oferta['salario_min'], $oferta['salario_max']); ?>
                                    </p>
                                <?php endif; ?>
                                
                                <!-- Botón -->
                                <a href="<?php echo BASE_URL; ?>/views/public/detalle_oferta_publica.php?id=<?php echo $oferta['id_oferta']; ?>" 
                                   class="btn btn-success w-100">
                                    Ver Detalles
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12 text-center py-5">
                    <i class="bi bi-briefcase" style="font-size: 64px; color: var(--gris-claro);"></i>
                    <p class="text-muted mt-3">No hay ofertas disponibles en este momento</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- SECCIÓN DE BENEFICIOS -->
<section class="py-5 bg-white">
    <div class="container">
        <h2 class="text-center mb-5" style="font-size: 32px; font-weight: 700; color: var(--gris-oscuro);">
            ¿Por qué usar nuestro portal?
        </h2>
        
        <div class="row g-4">
            <div class="col-md-4 text-center">
                <div class="mb-3">
                    <i class="bi bi-shield-check" style="font-size: 48px; color: var(--verde-principal);"></i>
                </div>
                <h5 style="font-weight: 700; color: var(--gris-oscuro);">Ofertas Verificadas</h5>
                <p class="text-muted">Todas las empresas son verificadas por la UTP</p>
            </div>
            
            <div class="col-md-4 text-center">
                <div class="mb-3">
                    <i class="bi bi-clock-history" style="font-size: 48px; color: var(--verde-principal);"></i>
                </div>
                <h5 style="font-weight: 700; color: var(--gris-oscuro);">Actualizaciones Diarias</h5>
                <p class="text-muted">Nuevas oportunidades cada día</p>
            </div>
            
            <div class="col-md-4 text-center">
                <div class="mb-3">
                    <i class="bi bi-graph-up-arrow" style="font-size: 48px; color: var(--verde-principal);"></i>
                </div>
                <h5 style="font-weight: 700; color: var(--gris-oscuro);">Impulsa tu Carrera</h5>
                <p class="text-muted">Conecta con las mejores empresas de Panamá</p>
            </div>
        </div>
    </div>
</section>

<?php include __DIR__ . '/views/layout/footer.php'; ?>
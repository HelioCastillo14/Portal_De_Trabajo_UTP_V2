<?php
/**
 * Ofertas Públicas - Portal de Trabajo UTP
 * Lista de ofertas para usuarios no autenticados
 */
session_start();
$page_title = 'Empleos Disponibles - Portal UTP';

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../models/Oferta.php';
require_once __DIR__ . '/../../models/Empresa.php';

$db = Database::getInstance()->getConnection();
$ofertaModel = new Oferta($db);
$empresaModel = new Empresa($db);

// Filtros
// Filtros
$filtros = [
    'busqueda' => $_GET['q'] ?? '',
    'modalidad' => $_GET['modalidad'] ?? '',
    'tipo_empleo' => $_GET['tipo'] ?? '',
    'empresa' => $_GET['empresa'] ?? ''
];

$pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$limite = 12;

// Si hay término de búsqueda, usar procedimiento almacenado
if (!empty($filtros['busqueda'])) {
    // Usar STORED PROCEDURE para búsqueda avanzada
    $ofertas_sp = $ofertaModel->buscarPorRolSP($filtros['busqueda']);
    
    // Aplicar filtros adicionales en PHP si es necesario
    $ofertas = array_filter($ofertas_sp, function($oferta) use ($filtros) {
        $cumple = true;
        
        if (!empty($filtros['modalidad']) && $oferta['modalidad'] !== $filtros['modalidad']) {
            $cumple = false;
        }
        
        if (!empty($filtros['tipo_empleo']) && $oferta['tipo_empleo'] !== $filtros['tipo_empleo']) {
            $cumple = false;
        }
        
        return $cumple;
    });
    
    // Paginación manual
    $total_resultados = count($ofertas);
    $total_paginas = ceil($total_resultados / $limite);
    $offset = ($pagina - 1) * $limite;
    $ofertas = array_slice($ofertas, $offset, $limite);
    
} else {
    // Sin búsqueda, usar método tradicional
    $ofertas = $ofertaModel->getOfertasActivas($filtros, $pagina, $limite);
    $total_paginas = $ofertaModel->getTotalPaginas($limite, $filtros);
}

$empresas_activas = $empresaModel->listarActivas();

include __DIR__ . '/../layout/header.php';
?>

<!-- Hero Empleos -->
<section class="hero-empleos">
    <div class="container position-relative" style="z-index: 1;">
        <h1 class="hero-title-empleos">Explora Oportunidades</h1>
        <p class="hero-subtitle-empleos">
            <?php echo count($ofertas); ?> empleos disponibles para estudiantes UTP
            <?php if (!empty($filtros['busqueda'])): ?>
                <span class="badge bg-info ms-2">
                    <i class="bi bi-search"></i> Búsqueda Avanzada con Stored Procedure
                </span>
            <?php endif; ?>
        </p>
        
        <!-- Búsqueda -->
        <div class="search-box-empleos">
            <form method="GET" class="d-flex gap-2">
                <input type="text" 
                       name="q" 
                       class="form-control" 
                       placeholder="Buscar por puesto, empresa o habilidad..." 
                       value="<?php echo htmlspecialchars($filtros['busqueda']); ?>">
                <button class="btn btn-success" type="submit">
                    <i class="bi bi-search"></i> Buscar
                </button>
            </form>
        </div>
    </div>
</section>

<!-- Filtros -->
<div class="filters py-3 bg-white">
    <div class="container">
        <div class="d-flex gap-2 flex-wrap">
            <!-- Tipo de Empleo -->
            <div class="dropdown">
                <button class="btn dropdown-toggle" data-bs-toggle="dropdown">
                    <i class="bi bi-briefcase me-2"></i>
                    <?php echo $filtros['tipo_empleo'] ? ucfirst(str_replace('_', ' ', $filtros['tipo_empleo'])) : 'Tipo de Empleo'; ?>
                </button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="?<?php echo http_build_query(array_merge($filtros, ['tipo' => ''])); ?>">Todos</a></li>
                    <li><a class="dropdown-item" href="?<?php echo http_build_query(array_merge($filtros, ['tipo' => 'tiempo_completo'])); ?>">Tiempo Completo</a></li>
                    <li><a class="dropdown-item" href="?<?php echo http_build_query(array_merge($filtros, ['tipo' => 'medio_tiempo'])); ?>">Medio Tiempo</a></li>
                    <li><a class="dropdown-item" href="?<?php echo http_build_query(array_merge($filtros, ['tipo' => 'pasantia'])); ?>">Pasantía</a></li>
                </ul>
            </div>
            
            <!-- Modalidad -->
            <div class="dropdown">
                <button class="btn dropdown-toggle" data-bs-toggle="dropdown">
                    <i class="bi bi-laptop me-2"></i>
                    <?php echo $filtros['modalidad'] ? ucfirst($filtros['modalidad']) : 'Modalidad'; ?>
                </button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="?<?php echo http_build_query(array_merge($filtros, ['modalidad' => ''])); ?>">Todas</a></li>
                    <li><a class="dropdown-item" href="?<?php echo http_build_query(array_merge($filtros, ['modalidad' => 'remoto'])); ?>">Remoto</a></li>
                    <li><a class="dropdown-item" href="?<?php echo http_build_query(array_merge($filtros, ['modalidad' => 'presencial'])); ?>">Presencial</a></li>
                    <li><a class="dropdown-item" href="?<?php echo http_build_query(array_merge($filtros, ['modalidad' => 'hibrido'])); ?>">Híbrido</a></li>
                </ul>
            </div>
            
            <!-- Empresa -->
            <div class="dropdown">
                <button class="btn dropdown-toggle" data-bs-toggle="dropdown">
                    <i class="bi bi-building me-2"></i>Empresa
                </button>
                <ul class="dropdown-menu" style="max-height: 300px; overflow-y: auto;">
                    <li><a class="dropdown-item" href="?<?php echo http_build_query(array_merge($filtros, ['empresa' => ''])); ?>">Todas</a></li>
                    <?php foreach ($empresas_activas as $empresa): ?>
                        <li><a class="dropdown-item" href="?<?php echo http_build_query(array_merge($filtros, ['empresa' => $empresa['id_empresa']])); ?>">
                            <?php echo htmlspecialchars($empresa['nombre_comercial']); ?>
                        </a></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            
            <!-- Limpiar Filtros -->
            <?php if (array_filter($filtros)): ?>
                <a href="?" class="btn btn-outline-secondary">
                    <i class="bi bi-x-circle me-1"></i>Limpiar
                </a>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Grid de Ofertas -->
<section class="py-5">
    <div class="container">
        <?php if (!empty($ofertas)): ?>
            <div class="row g-4">
                <?php foreach ($ofertas as $oferta): ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="card job-card h-100 border-0 shadow-sm">
                            <div class="card-body">
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
            </div>
            
            <!-- Paginación -->
            <?php if ($total_paginas > 1): ?>
                <nav class="mt-5">
                    <ul class="pagination justify-content-center">
                        <!-- Primera -->
                        <li class="page-item <?php echo $pagina <= 1 ? 'disabled' : ''; ?>">
                            <a class="page-link" href="?<?php echo http_build_query(array_merge($filtros, ['pagina' => 1])); ?>">
                                <i class="bi bi-chevron-double-left"></i>
                            </a>
                        </li>
                        
                        <!-- Anterior -->
                        <li class="page-item <?php echo $pagina <= 1 ? 'disabled' : ''; ?>">
                            <a class="page-link" href="?<?php echo http_build_query(array_merge($filtros, ['pagina' => $pagina - 1])); ?>">
                                <i class="bi bi-chevron-left"></i>
                            </a>
                        </li>
                        
                        <!-- Páginas -->
                        <?php for ($i = max(1, $pagina - 2); $i <= min($total_paginas, $pagina + 2); $i++): ?>
                            <li class="page-item <?php echo $i == $pagina ? 'active' : ''; ?>">
                                <a class="page-link" href="?<?php echo http_build_query(array_merge($filtros, ['pagina' => $i])); ?>">
                                    <?php echo $i; ?>
                                </a>
                            </li>
                        <?php endfor; ?>
                        
                        <!-- Siguiente -->
                        <li class="page-item <?php echo $pagina >= $total_paginas ? 'disabled' : ''; ?>">
                            <a class="page-link" href="?<?php echo http_build_query(array_merge($filtros, ['pagina' => $pagina + 1])); ?>">
                                <i class="bi bi-chevron-right"></i>
                            </a>
                        </li>
                        
                        <!-- Última -->
                        <li class="page-item <?php echo $pagina >= $total_paginas ? 'disabled' : ''; ?>">
                            <a class="page-link" href="?<?php echo http_build_query(array_merge($filtros, ['pagina' => $total_paginas])); ?>">
                                <i class="bi bi-chevron-double-right"></i>
                            </a>
                        </li>
                    </ul>
                </nav>
            <?php endif; ?>
            
        <?php else: ?>
            <div class="text-center py-5">
                <i class="bi bi-search" style="font-size: 64px; color: var(--gris-claro);"></i>
                <h3 class="mt-3">No se encontraron ofertas</h3>
                <p class="text-muted">Intenta ajustar tus filtros de búsqueda</p>
                <a href="?" class="btn btn-success mt-3">Ver Todas las Ofertas</a>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php include __DIR__ . '/../layout/footer.php'; ?>
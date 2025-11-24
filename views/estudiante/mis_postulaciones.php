<?php
session_start();
require_once __DIR__ . '/../../config/session.php';
verificarSesion('estudiante');
$page_title = 'Mis Postulaciones';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../models/Postulacion.php';
$db = Database::getInstance()->getConnection();
$postulacionModel = new Postulacion($db);
$id_estudiante = $_SESSION['id_usuario'];
$pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$limite = 10;
$offset = ($pagina - 1) * $limite;
$postulaciones = $postulacionModel->getPostulacionesEstudiante($id_estudiante, $limite, $offset);
$total = $postulacionModel->contarPorEstudiante($id_estudiante);
$total_paginas = ceil($total / $limite);
include __DIR__ . '/../layout/header_estudiante.php';
?>
<div class="container py-5">
    <h1 class="mb-4" style="font-size: 32px; font-weight: 700;">Mis Postulaciones</h1>
    <?php if (!empty($postulaciones)): ?>
        <div class="row g-4">
            <?php foreach ($postulaciones as $post): ?>
                <div class="col-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body p-4">
                            <div class="row align-items-center">
                                <div class="col-auto">
                                    <div class="company-logo-small">
                                        <?php if ($post['logo'] && $post['logo'] !== 'placeholder-logo.png'): ?>
                                            <img src="<?php echo BASE_URL; ?>/assets/images/logos/<?php echo htmlspecialchars($post['logo']); ?>" alt="">
                                        <?php else: ?>
                                            <i class="bi bi-building"></i>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="col">
                                    <h5 class="mb-1"><?php echo htmlspecialchars($post['titulo_oferta']); ?></h5>
                                    <p class="text-muted mb-2"><?php echo htmlspecialchars($post['nombre_empresa']); ?></p>
                                    <div class="d-flex flex-wrap gap-3">
                                        <?php
                                        $badges = ['en_revision' => 'bg-warning', 'aceptada' => 'bg-success', 'rechazada' => 'bg-danger'];
                                        $textos = ['en_revision' => 'En Revisión', 'aceptada' => 'Aceptada', 'rechazada' => 'Rechazada'];
                                        ?>
                                        <span class="badge <?php echo $badges[$post['estado']]; ?>"><?php echo $textos[$post['estado']]; ?></span>
                                        <small class="text-muted">Postulado <?php echo tiempoTranscurrido($post['fecha_postulacion']); ?></small>
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <?php if ($post['estado'] === 'en_revision'): ?>
                                        <form method="POST" action="<?php echo BASE_URL; ?>/controllers/PostulacionController.php?action=cancelar" onsubmit="return confirm('¿Seguro que deseas cancelar esta postulación?');">
                                            <input type="hidden" name="csrf_token" value="<?php echo generarTokenCSRF(); ?>">
                                            <input type="hidden" name="id_postulacion" value="<?php echo $post['id_postulacion']; ?>">
                                            <button type="submit" class="btn btn-outline-danger btn-sm">Cancelar</button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <?php if ($total_paginas > 1): ?>
            <nav class="mt-4">
                <ul class="pagination justify-content-center">
                    <?php for ($i = 1; $i <= $total_paginas; $i++): ?>
                        <li class="page-item <?php echo $i == $pagina ? 'active' : ''; ?>">
                            <a class="page-link" href="?pagina=<?php echo $i; ?>"><?php echo $i; ?></a>
                        </li>
                    <?php endfor; ?>
                </ul>
            </nav>
        <?php endif; ?>
    <?php else: ?>
        <div class="text-center py-5">
            <i class="bi bi-inbox" style="font-size: 64px; color: var(--gris-claro);"></i>
            <h3 class="mt-3">No tienes postulaciones</h3>
            <p class="text-muted">Comienza a buscar empleos y postúlate a las ofertas que te interesen</p>
            <a href="<?php echo BASE_URL; ?>/views/estudiante/ofertas.php" class="btn btn-success mt-3">Buscar Empleos</a>
        </div>
    <?php endif; ?>
</div>
<?php include __DIR__ . '/../layout/footer.php'; ?>
<?php
session_start();
require_once __DIR__ . '/../../../config/session.php';
verificarSesion('admin');
$page_title = 'Detalle de Oferta';
require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../../models/Oferta.php';
require_once __DIR__ . '/../../../models/Postulacion.php';
$db = Database::getInstance()->getConnection();
$ofertaModel = new Oferta($db);
$postulacionModel = new Postulacion($db);
$id_oferta = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id_oferta <= 0) { header('Location: ' . BASE_URL . '/views/admin/ofertas/index.php'); exit(); }
$oferta = $ofertaModel->findById($id_oferta);
if (!$oferta) { header('Location: ' . BASE_URL . '/views/admin/ofertas/index.php'); exit(); }
$postulaciones = $postulacionModel->getPostulacionesPorOferta($id_oferta);
$habilidades = $ofertaModel->getHabilidades($id_oferta);
include __DIR__ . '/../../layout/header_admin.php';
?>
<div class="container-fluid px-4 py-4">
    <div class="d-flex align-items-center mb-4">
        <a href="<?php echo BASE_URL; ?>/views/admin/ofertas/index.php" class="btn btn-outline-secondary me-3"><i class="bi bi-arrow-left"></i></a>
        <h1 class="mb-0"><?php echo htmlspecialchars($oferta['titulo']); ?></h1>
    </div>
    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-4">
                    <h5 class="mb-3">Información de la Oferta</h5>
                    <div class="row g-3">
                        <div class="col-md-6"><strong>Empresa:</strong> <?php echo htmlspecialchars($oferta['empresa']); ?></div>
                        <div class="col-md-6"><strong>Estado:</strong> <span class="badge bg-<?php echo $oferta['estado'] === 'activa' ? 'success' : 'secondary'; ?>"><?php echo ucfirst($oferta['estado']); ?></span></div>
                        <div class="col-md-6"><strong>Modalidad:</strong> <?php echo ucfirst($oferta['modalidad']); ?></div>
                        <div class="col-md-6"><strong>Tipo:</strong> <?php echo ucfirst(str_replace('_', ' ', $oferta['tipo_empleo'])); ?></div>
                        <div class="col-md-6"><strong>Nivel:</strong> <?php echo ucfirst($oferta['nivel_experiencia']); ?></div>
                        <div class="col-md-6"><strong>Ubicación:</strong> <?php echo htmlspecialchars($oferta['ubicacion']); ?></div>
                        <div class="col-md-6"><strong>Publicada:</strong> <?php echo formatearFecha($oferta['fecha_publicacion']); ?></div>
                        <div class="col-md-6"><strong>Fecha límite:</strong> <?php echo formatearFecha($oferta['fecha_limite']); ?></div>
                        <?php if ($oferta['salario_min']): ?>
                            <div class="col-12"><strong>Salario:</strong> <?php echo formatearSalario($oferta['salario_min'], $oferta['salario_max']); ?></div>
                        <?php endif; ?>
                        <div class="col-12"><hr><h6>Descripción</h6><p><?php echo nl2br(htmlspecialchars($oferta['descripcion'])); ?></p></div>
                        <?php if (!empty($habilidades)): ?>
                            <div class="col-12"><h6>Habilidades</h6><div class="d-flex flex-wrap gap-2"><?php foreach ($habilidades as $hab): ?><span class="badge bg-secondary"><?php echo htmlspecialchars($hab['nombre']); ?></span><?php endforeach; ?></div></div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <h5 class="mb-4">Postulaciones (<?php echo count($postulaciones); ?>)</h5>
                    <?php if (!empty($postulaciones)): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Estudiante</th>
                                        <th>Correo</th>
                                        <th>Carrera</th>
                                        <th>Estado</th>
                                        <th>Fecha</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($postulaciones as $post): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($post['nombres_estudiante'] . ' ' . $post['apellidos_estudiante']); ?></td>
                                            <td><?php echo htmlspecialchars($post['correo_estudiante']); ?></td>
                                            <td><?php echo htmlspecialchars($post['carrera']); ?></td>
                                            <td>
                                                <?php
                                                $badges = ['en_revision' => 'bg-warning', 'aceptada' => 'bg-success', 'rechazada' => 'bg-danger'];
                                                echo '<span class="badge ' . $badges[$post['estado']] . '">' . ucfirst(str_replace('_', ' ', $post['estado'])) . '</span>';
                                                ?>
                                            </td>
                                            <td><?php echo tiempoTranscurrido($post['fecha_postulacion']); ?></td>
                                            <td>
                                                <?php if ($post['cv_ruta']): ?>
                                                    <a href="<?php echo BASE_URL; ?>/assets/uploads/cvs/<?php echo htmlspecialchars($post['cv_ruta']); ?>" target="_blank" class="btn btn-sm btn-info"><i class="bi bi-file-pdf"></i></a>
                                                <?php endif; ?>
                                                <form method="POST" action="<?php echo BASE_URL; ?>/controllers/PostulacionController.php?action=cambiarEstado" style="display:inline;">
                                                    <input type="hidden" name="csrf_token" value="<?php echo generarTokenCSRF(); ?>">
                                                    <input type="hidden" name="id_postulacion" value="<?php echo $post['id_postulacion']; ?>">
                                                    <input type="hidden" name="redirect" value="detalle_oferta">
                                                    <select name="estado" class="form-select form-select-sm d-inline-block" style="width:auto;" onchange="this.form.submit()">
                                                        <option value="en_revision" <?php echo $post['estado'] === 'en_revision' ? 'selected' : ''; ?>>En Revisión</option>
                                                        <option value="aceptada" <?php echo $post['estado'] === 'aceptada' ? 'selected' : ''; ?>>Aceptada</option>
                                                        <option value="rechazada" <?php echo $post['estado'] === 'rechazada' ? 'selected' : ''; ?>>Rechazada</option>
                                                    </select>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="text-muted text-center py-4">No hay postulaciones aún</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <h5 class="mb-4">Acciones</h5>
                    <div class="d-grid gap-2">
                        <a href="<?php echo BASE_URL; ?>/views/admin/ofertas/editar.php?id=<?php echo $oferta['id_oferta']; ?>" class="btn btn-warning"><i class="bi bi-pencil me-2"></i>Editar Oferta</a>
                        <form method="POST" action="<?php echo BASE_URL; ?>/controllers/OfertaController.php?action=cambiarEstado">
                            <input type="hidden" name="csrf_token" value="<?php echo generarTokenCSRF(); ?>">
                            <input type="hidden" name="id_oferta" value="<?php echo $oferta['id_oferta']; ?>">
                            <select name="estado" class="form-select mb-2">
                                <option value="activa" <?php echo $oferta['estado'] === 'activa' ? 'selected' : ''; ?>>Activa</option>
                                <option value="cerrada" <?php echo $oferta['estado'] === 'cerrada' ? 'selected' : ''; ?>>Cerrada</option>
                                <option value="tomada" <?php echo $oferta['estado'] === 'tomada' ? 'selected' : ''; ?>>Tomada</option>
                            </select>
                            <button type="submit" class="btn btn-primary w-100">Cambiar Estado</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include __DIR__ . '/../../layout/footer.php'; ?>
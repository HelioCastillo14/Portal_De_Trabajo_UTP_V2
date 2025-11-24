<?php
session_start();
require_once __DIR__ . '/../../../config/session.php';
verificarSesion('admin');
$page_title = 'Gestionar Postulaciones';
require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../../models/Postulacion.php';
$db = Database::getInstance()->getConnection();
$postulacionModel = new Postulacion($db);
$filtros = ['estado' => $_GET['estado'] ?? '', 'fecha_inicio' => $_GET['fecha_inicio'] ?? ''];
$postulaciones = $postulacionModel->listarTodas($filtros, 100, 0);
include __DIR__ . '/../../layout/header_admin.php';
?>
<div class="container-fluid px-4 py-4">
    <h1 class="mb-4">Gestionar Postulaciones</h1>
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-4">
                    <select name="estado" class="form-select">
                        <option value="">Todos los estados</option>
                        <option value="en_revision" <?php echo $filtros['estado'] === 'en_revision' ? 'selected' : ''; ?>>En Revisión</option>
                        <option value="aceptada" <?php echo $filtros['estado'] === 'aceptada' ? 'selected' : ''; ?>>Aceptada</option>
                        <option value="rechazada" <?php echo $filtros['estado'] === 'rechazada' ? 'selected' : ''; ?>>Rechazada</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <input type="date" name="fecha_inicio" class="form-control" value="<?php echo htmlspecialchars($filtros['fecha_inicio']); ?>">
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-primary w-100"><i class="bi bi-search"></i> Filtrar</button>
                </div>
            </form>
        </div>
    </div>
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr><th>ID</th><th>Estudiante</th><th>Oferta</th><th>Empresa</th><th>Estado</th><th>Fecha</th><th>Acciones</th></tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($postulaciones)): ?>
                            <?php foreach ($postulaciones as $post): ?>
                                <tr>
                                    <td><?php echo $post['id_postulacion']; ?></td>
                                    <td><?php echo htmlspecialchars($post['nombres_estudiante'] . ' ' . $post['apellidos_estudiante']); ?></td>
                                    <td><?php echo htmlspecialchars($post['titulo_oferta']); ?></td>
                                    <td><?php echo htmlspecialchars($post['nombre_empresa']); ?></td>
                                    <td><?php $badges = ['en_revision' => 'bg-warning', 'aceptada' => 'bg-success', 'rechazada' => 'bg-danger']; echo '<span class="badge ' . $badges[$post['estado']] . '">' . ucfirst(str_replace('_', ' ', $post['estado'])) . '</span>'; ?></td>
                                    <td><?php echo tiempoTranscurrido($post['fecha_postulacion']); ?></td>
                                    <td>
                                        <form method="POST" action="<?php echo BASE_URL; ?>/controllers/PostulacionController.php?action=cambiarEstado" style="display:inline;">
                                            <input type="hidden" name="csrf_token" value="<?php echo generarTokenCSRF(); ?>">
                                            <input type="hidden" name="id_postulacion" value="<?php echo $post['id_postulacion']; ?>">
                                            <select name="estado" class="form-select form-select-sm d-inline-block" style="width:auto;" onchange="if(confirm('¿Cambiar estado?')) this.form.submit()">
                                                <option value="en_revision" <?php echo $post['estado'] === 'en_revision' ? 'selected' : ''; ?>>En Revisión</option>
                                                <option value="aceptada" <?php echo $post['estado'] === 'aceptada' ? 'selected' : ''; ?>>Aceptada</option>
                                                <option value="rechazada" <?php echo $post['estado'] === 'rechazada' ? 'selected' : ''; ?>>Rechazada</option>
                                            </select>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="7" class="text-center text-muted py-4">No hay postulaciones</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php include __DIR__ . '/../../layout/footer.php'; ?>
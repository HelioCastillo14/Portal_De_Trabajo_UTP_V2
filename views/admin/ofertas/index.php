<?php
session_start();
require_once __DIR__ . '/../../../config/session.php';
verificarSesion('admin');
$page_title = 'Gestionar Ofertas';
require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../../models/Oferta.php';
require_once __DIR__ . '/../../../models/Empresa.php';
$db = Database::getInstance()->getConnection();
$ofertaModel = new Oferta($db);
$empresaModel = new Empresa($db);
$filtros = ['busqueda' => $_GET['q'] ?? '', 'empresa' => $_GET['empresa'] ?? '', 'estado' => $_GET['estado'] ?? ''];
$ofertas = $ofertaModel->getOfertasAdmin($filtros, 100, 0);
$empresas = $empresaModel->listarActivas();
include __DIR__ . '/../../layout/header_admin.php';
?>
<div class="container-fluid px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Gestionar Ofertas</h1>
        <a href="<?php echo BASE_URL; ?>/views/admin/ofertas/crear.php" class="btn btn-success">
            <i class="bi bi-plus-circle me-2"></i>Nueva Oferta
        </a>
    </div>
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-4">
                    <input type="text" name="q" class="form-control" placeholder="Buscar por título..." value="<?php echo htmlspecialchars($filtros['busqueda']); ?>">
                </div>
                <div class="col-md-3">
                    <select name="empresa" class="form-select">
                        <option value="">Todas las empresas</option>
                        <?php foreach ($empresas as $emp): ?>
                            <option value="<?php echo $emp['id_empresa']; ?>" <?php echo $filtros['empresa'] == $emp['id_empresa'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($emp['nombre_comercial']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="estado" class="form-select">
                        <option value="">Todos los estados</option>
                        <option value="activa" <?php echo $filtros['estado'] === 'activa' ? 'selected' : ''; ?>>Activa</option>
                        <option value="cerrada" <?php echo $filtros['estado'] === 'cerrada' ? 'selected' : ''; ?>>Cerrada</option>
                        <option value="tomada" <?php echo $filtros['estado'] === 'tomada' ? 'selected' : ''; ?>>Tomada</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100"><i class="bi bi-search"></i> Buscar</button>
                </div>
            </form>
        </div>
    </div>
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Título</th>
                            <th>Empresa</th>
                            <th>Modalidad</th>
                            <th>Tipo</th>
                            <th>Estado</th>
                            <th>Postulaciones</th>
                            <th>Fecha Límite</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($ofertas)): ?>
                            <?php foreach ($ofertas as $oferta): ?>
                                <tr>
                                    <td><?php echo $oferta['id_oferta']; ?></td>
                                    <td><strong><?php echo htmlspecialchars($oferta['titulo']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($oferta['empresa']); ?></td>
                                    <td><?php echo ucfirst($oferta['modalidad']); ?></td>
                                    <td><?php echo ucfirst(str_replace('_', ' ', $oferta['tipo_empleo'])); ?></td>
                                    <td>
                                        <span class="badge bg-<?php echo $oferta['estado'] === 'activa' ? 'success' : ($oferta['estado'] === 'cerrada' ? 'secondary' : 'info'); ?>">
                                            <?php echo ucfirst($oferta['estado']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-primary"><?php echo $oferta['total_postulaciones'] ?? 0; ?></span>
                                    </td>
                                    <td><?php echo formatearFecha($oferta['fecha_limite']); ?></td>
                                    <td>
                                        <a href="<?php echo BASE_URL; ?>/views/admin/ofertas/detalle.php?id=<?php echo $oferta['id_oferta']; ?>" class="btn btn-sm btn-info" title="Ver detalles">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="<?php echo BASE_URL; ?>/views/admin/ofertas/editar.php?id=<?php echo $oferta['id_oferta']; ?>" class="btn btn-sm btn-warning" title="Editar">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="9" class="text-center text-muted py-4">No se encontraron ofertas</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php include __DIR__ . '/../../layout/footer.php'; ?>
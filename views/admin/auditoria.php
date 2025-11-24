<?php
session_start();
require_once __DIR__ . '/../../config/session.php';
verificarSesion('admin');
$page_title = 'Bitácora de Auditoría';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../models/Auditoria.php';

$db = Database::getInstance()->getConnection();
$auditoriaModel = new Auditoria($db);

// Filtros
$filtros = [
    'tipo_usuario' => $_GET['tipo_usuario'] ?? '',
    'accion' => $_GET['accion'] ?? '',
    'fecha_inicio' => $_GET['fecha_inicio'] ?? date('Y-m-01'),
    'fecha_fin' => $_GET['fecha_fin'] ?? date('Y-m-d')
];

// Obtener registros
$registros = $auditoriaModel->obtenerRegistros($filtros, 50, 0);

include __DIR__ . '/../layout/header_admin.php';
?>

<div class="container-fluid px-4 py-4">
    <h1 class="mb-4">Bitácora de Auditoría</h1>
    
    <!-- Filtros -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Tipo de Usuario</label>
                    <select name="tipo_usuario" class="form-select">
                        <option value="">Todos</option>
                        <option value="estudiante" <?php echo $filtros['tipo_usuario'] === 'estudiante' ? 'selected' : ''; ?>>Estudiante</option>
                        <option value="admin" <?php echo $filtros['tipo_usuario'] === 'admin' ? 'selected' : ''; ?>>Admin</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Acción</label>
                    <select name="accion" class="form-select">
                        <option value="">Todas</option>
                        <option value="login" <?php echo $filtros['accion'] === 'login' ? 'selected' : ''; ?>>Login</option>
                        <option value="login_admin" <?php echo $filtros['accion'] === 'login_admin' ? 'selected' : ''; ?>>Login Admin</option>
                        <option value="logout" <?php echo $filtros['accion'] === 'logout' ? 'selected' : ''; ?>>Logout</option>
                        <option value="crear_oferta" <?php echo $filtros['accion'] === 'crear_oferta' ? 'selected' : ''; ?>>Crear Oferta</option>
                        <option value="actualizar_oferta" <?php echo $filtros['accion'] === 'actualizar_oferta' ? 'selected' : ''; ?>>Actualizar Oferta</option>
                        <option value="crear_postulacion" <?php echo $filtros['accion'] === 'crear_postulacion' ? 'selected' : ''; ?>>Crear Postulación</option>
                        <option value="cambiar_estado_postulacion" <?php echo $filtros['accion'] === 'cambiar_estado_postulacion' ? 'selected' : ''; ?>>Cambiar Estado</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Fecha Inicio</label>
                    <input type="date" name="fecha_inicio" class="form-control" value="<?php echo htmlspecialchars($filtros['fecha_inicio']); ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Fecha Fin</label>
                    <input type="date" name="fecha_fin" class="form-control" value="<?php echo htmlspecialchars($filtros['fecha_fin']); ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label">&nbsp;</label>
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-search"></i> Filtrar
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Tabla de registros -->
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Usuario</th>
                            <th>Acción</th>
                            <th>Tabla</th>
                            <th>IP</th>
                            <th>Fecha/Hora</th>
                            <th>Detalles</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($registros)): ?>
                            <?php foreach ($registros as $reg): ?>
                                <tr>
                                    <td><?php echo $reg['id_auditoria']; ?></td>
                                    <td>
                                        <span class="badge bg-<?php echo $reg['tipo_usuario'] === 'admin' ? 'danger' : 'primary'; ?>">
                                            <?php echo ucfirst($reg['tipo_usuario']); ?>
                                        </span>
                                        #<?php echo $reg['id_usuario']; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($reg['accion']); ?></td>
                                    <td><?php echo htmlspecialchars($reg['tabla_afectada'] ?? '-'); ?></td>
                                    <td><small><?php echo htmlspecialchars($reg['ip_address'] ?? '-'); ?></small></td>
                                    <td><?php echo date('d/m/Y H:i:s', strtotime($reg['fecha_hora'])); ?></td>
                                    <td>
                                        <?php if ($reg['datos_anteriores'] || $reg['datos_nuevos']): ?>
                                            <button class="btn btn-sm btn-outline-info" 
                                                    onclick="verDetalles(<?php echo htmlspecialchars(json_encode($reg)); ?>)">
                                                <i class="bi bi-info-circle"></i>
                                            </button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">
                                    No se encontraron registros de auditoría
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal para detalles -->
<div class="modal fade" id="detallesModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detalles de Auditoría</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="detallesContenido"></div>
            </div>
        </div>
    </div>
</div>

<script>
function verDetalles(registro) {
    let html = '<h6>Información General</h6>';
    html += '<ul>';
    html += '<li><strong>Acción:</strong> ' + registro.accion + '</li>';
    html += '<li><strong>Tabla:</strong> ' + (registro.tabla_afectada || 'N/A') + '</li>';
    html += '<li><strong>Registro ID:</strong> ' + (registro.id_registro_afectado || 'N/A') + '</li>';
    html += '<li><strong>IP:</strong> ' + (registro.ip_address || 'N/A') + '</li>';
    html += '<li><strong>User Agent:</strong> ' + (registro.user_agent || 'N/A') + '</li>';
    html += '</ul>';
    
    if (registro.datos_anteriores) {
        html += '<h6 class="mt-3">Datos Anteriores</h6>';
        html += '<pre class="bg-light p-3 rounded">' + JSON.stringify(JSON.parse(registro.datos_anteriores), null, 2) + '</pre>';
    }
    
    if (registro.datos_nuevos) {
        html += '<h6 class="mt-3">Datos Nuevos</h6>';
        html += '<pre class="bg-light p-3 rounded">' + JSON.stringify(JSON.parse(registro.datos_nuevos), null, 2) + '</pre>';
    }
    
    document.getElementById('detallesContenido').innerHTML = html;
    new bootstrap.Modal(document.getElementById('detallesModal')).show();
}
</script>

<?php include __DIR__ . '/../layout/footer.php'; ?>
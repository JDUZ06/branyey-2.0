<?php
include '../includes/conexion.php';
include '../includes/header.php';

// Para marcar la página activa en el sidebar
$current_page = basename($_SERVER['PHP_SELF']);

// --- FILTROS ---
$tipo = $_GET['tipo'] ?? ''; // 'usuario' o 'invitado'
$cliente = trim($_GET['cliente'] ?? '');
$orden = $_GET['orden'] ?? 'fecha_desc'; // fecha_desc, fecha_asc, total_desc, total_asc

$where = [];
$params = [];

if ($tipo === 'usuario') {
    $where[] = 'v.id_usuario IS NOT NULL';
} elseif ($tipo === 'invitado') {
    $where[] = 'v.id_invitado IS NOT NULL';
}

if ($cliente) {
    $where[] = '(u.username LIKE ? OR i.nombre LIKE ?)';
    $params[] = "%$cliente%";
    $params[] = "%$cliente%";
}

$whereSql = '';
if ($where) {
    $whereSql = 'WHERE ' . implode(' AND ', $where);
}

switch ($orden) {
    case 'fecha_asc': $orderSql = 'v.fecha ASC'; break;
    case 'total_desc': $orderSql = 'v.total DESC'; break;
    case 'total_asc': $orderSql = 'v.total ASC'; break;
    default: $orderSql = 'v.fecha DESC';
}

$sql = "
    SELECT v.*, 
           u.username AS usuario, 
           i.nombre AS invitado
    FROM ventas v
    LEFT JOIN usuarios u ON v.id_usuario = u.id
    LEFT JOIN invitados i ON v.id_invitado = i.id
    $whereSql
    ORDER BY $orderSql
";
$stmt = $conexion->prepare($sql);
$stmt->execute($params);
$ventas = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="d-flex">

    <!-- Sidebar oscuro -->
    <nav id="sidebar" class="d-flex flex-column p-3 bg-dark text-white" style="min-width:220px; min-height:100vh;">
        <a href="dashboard.php" class="d-flex align-items-center mb-3 mb-md-0 me-md-auto text-white text-decoration-none">
            <span class="fs-4">Branyey Admin</span>
        </a>
        <hr class="border-secondary">
        <ul class="nav nav-pills flex-column mb-auto">
            <li class="nav-item">
                <a href="dashboard.php" class="nav-link text-white <?= $current_page=='dashboard.php'?'active':'' ?>">
                    <i class="bi bi-speedometer2 me-2"></i> Dashboard
                </a>
            </li>
            <li>
                <a href="productos.php" class="nav-link text-white <?= $current_page=='productos.php'?'active':'' ?>">
                    <i class="bi bi-box-seam me-2"></i> Gestion Productos
                </a>
            </li>
            <li>
                <a href="ventas.php" class="nav-link text-white <?= $current_page=='ventas.php'?'active':'' ?>">
                    <i class="bi bi-cart-check me-2"></i> Ventas
                </a>
            </li>
            <li>
                <a href="gestion_usuarios.php" class="nav-link text-white <?= $current_page=='gestion_usuarios.php'?'active':'' ?>">
                    <i class="bi bi-people me-2"></i> Usuarios
                </a>
            </li>
            <li>
                <a href="reportes.php" class="nav-link text-white <?= $current_page=='reportes.php'?'active':'' ?>">
                    <i class="bi bi-file-earmark-pdf me-2"></i> Reportes
                </a>
            </li>
        </ul>
        <hr class="border-secondary">
        <div class="text-center small text-white">© 2025 Branyey</div>
    </nav>

    <!-- Contenido principal -->
    <div id="content" class="flex-fill p-4 bg-light">
        <h2>Listado de Ventas</h2>

        <!-- FORMULARIO DE FILTRO -->
        <form method="get" class="mb-3 row g-2">
            <div class="col-auto">
                <input type="text" name="cliente" class="form-control" placeholder="Nombre de cliente" value="<?= htmlspecialchars($cliente) ?>">
            </div>
            <div class="col-auto">
                <select name="tipo" class="form-select">
                    <option value="">Todos los clientes</option>
                    <option value="usuario" <?= $tipo==='usuario'?'selected':'' ?>>Usuario registrado</option>
                    <option value="invitado" <?= $tipo==='invitado'?'selected':'' ?>>Invitado</option>
                </select>
            </div>
            <div class="col-auto">
                <select name="orden" class="form-select">
                    <option value="fecha_desc" <?= $orden==='fecha_desc'?'selected':'' ?>>Fecha descendente</option>
                    <option value="fecha_asc" <?= $orden==='fecha_asc'?'selected':'' ?>>Fecha ascendente</option>
                    <option value="total_desc" <?= $orden==='total_desc'?'selected':'' ?>>Total descendente</option>
                    <option value="total_asc" <?= $orden==='total_asc'?'selected':'' ?>>Total ascendente</option>
                </select>
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-primary">Filtrar</button>
            </div>
        </form>

        <!-- BOTÓN PDF -->
        <a href="ventas_pdf.php?cliente=<?= urlencode($cliente) ?>&tipo=<?= $tipo ?>&orden=<?= $orden ?>" target="_blank" class="btn btn-success mb-3">
            Descargar PDF de Ventas
        </a>

        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Cliente</th>
                    <th>Tipo</th>
                    <th>Fecha</th>
                    <th>Total</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($ventas as $v): ?>
                <tr>
                    <td><?= $v['id'] ?></td>
                    <td><?= htmlspecialchars($v['usuario'] ?? $v['invitado'] ?? 'Desconocido') ?></td>
                    <td><?= $v['usuario'] ? 'Usuario registrado' : 'Invitado' ?></td>
                    <td><?= $v['fecha'] ?></td>
                    <td>$<?= number_format($v['total'],2) ?></td>
                    <td>
                        <a href="detalle_venta.php?id=<?= $v['id'] ?>" class="btn btn-info btn-sm">Detalle</a>
                        <a href="venta_pdf.php?id=<?= $v['id'] ?>" target="_blank" class="btn btn-secondary btn-sm">PDF</a>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if(empty($ventas)): ?>
                <tr>
                    <td colspan="6" class="text-center">No se encontraron ventas</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include '../includes/footer.php'; ?>

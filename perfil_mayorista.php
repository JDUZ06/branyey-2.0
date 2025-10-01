<?php
session_start();
include 'includes/conexion.php';
include 'includes/header.php';

// Validar rol comprador
if(!isset($_SESSION['rol']) || $_SESSION['rol'] != 'comprador'){
    header('Location: index.php');
    exit;
}

$id_usuario = $_SESSION['usuario_id'] ?? null;

// --- FILTROS ---
$fecha_inicio = $_GET['fecha_inicio'] ?? '';
$fecha_fin = $_GET['fecha_fin'] ?? '';
$orden = $_GET['orden'] ?? 'fecha_desc';

$where = ['v.id_usuario = ?'];
$params = [$id_usuario];

if($fecha_inicio) {
    $where[] = 'v.fecha >= ?';
    $params[] = $fecha_inicio.' 00:00:00';
}

if($fecha_fin) {
    $where[] = 'v.fecha <= ?';
    $params[] = $fecha_fin.' 23:59:59';
}

$whereSql = 'WHERE '.implode(' AND ', $where);

// Mapear orden de forma segura
$ordenes_validos = [
    'fecha_desc' => 'v.fecha DESC',
    'fecha_asc'  => 'v.fecha ASC',
    'total_desc' => 'v.total DESC',
    'total_asc'  => 'v.total ASC'
];
$orderSql = $ordenes_validos[$orden] ?? 'v.fecha DESC';

// Obtener ventas del usuario
$sql = "
    SELECT v.*, 
           GROUP_CONCAT(CONCAT(d.cantidad,'x ',p.estilo,' (',v2.color,'-',v2.talla,')') SEPARATOR ', ') AS productos
    FROM ventas v
    INNER JOIN detalle_ventas d ON v.id = d.id_venta
    INNER JOIN variantes v2 ON d.id_variante = v2.id
    INNER JOIN productos p ON v2.id_producto = p.id
    $whereSql
    GROUP BY v.id
    ORDER BY $orderSql
";
$stmt = $conexion->prepare($sql);
$stmt->execute($params);
$ventas = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calcular total general
$total_general = array_sum(array_column($ventas, 'total'));
?>

<div class="container">
    <h2>Mi Perfil - Historial de Compras</h2>

    <!-- FILTROS -->
    <form method="get" class="row g-2 mb-3">
        <div class="col-auto">
            <label>Desde:</label>
            <input type="date" name="fecha_inicio" class="form-control" value="<?= htmlspecialchars($fecha_inicio) ?>">
        </div>
        <div class="col-auto">
            <label>Hasta:</label>
            <input type="date" name="fecha_fin" class="form-control" value="<?= htmlspecialchars($fecha_fin) ?>">
        </div>
        <div class="col-auto">
            <label>Ordenar por:</label>
            <select name="orden" class="form-select">
                <option value="fecha_desc" <?= $orden==='fecha_desc'?'selected':'' ?>>Fecha descendente</option>
                <option value="fecha_asc" <?= $orden==='fecha_asc'?'selected':'' ?>>Fecha ascendente</option>
                <option value="total_desc" <?= $orden==='total_desc'?'selected':'' ?>>Total descendente</option>
                <option value="total_asc" <?= $orden==='total_asc'?'selected':'' ?>>Total ascendente</option>
            </select>
        </div>
        <div class="col-auto align-self-end">
            <button type="submit" class="btn btn-primary">Filtrar</button>
        </div>
    </form>

    <p><strong>Total de Compras:</strong> $<?= number_format($total_general,2) ?></p>

    <table class="table table-bordered table-striped">
        <thead class="table-dark">
            <tr>
                <th>ID Venta</th>
                <th>Fecha</th>
                <th>Productos</th>
                <th>Total</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php if(!empty($ventas)): ?>
                <?php foreach($ventas as $v): ?>
                    <tr>
                        <td><?= $v['id'] ?></td>
                        <td><?= $v['fecha'] ?></td>
                        <td><?= htmlspecialchars($v['productos']) ?></td>
                        <td>$<?= number_format($v['total'],2) ?></td>
                        <td>
                            <button class="btn btn-info btn-sm" onclick="verDetalle(<?= $v['id'] ?>)">Detalle</button>
                            <a href="factura_cliente.php?id=<?= $v['id'] ?>" target="_blank" class="btn btn-secondary btn-sm">PDF</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="5" class="text-center">No se encontraron compras</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Modal para mostrar detalle -->
<div class="modal fade" id="detalleModal" tabindex="-1" aria-labelledby="detalleModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="detalleModalLabel">Detalle de la Compra</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Producto</th>
                    <th>Color</th>
                    <th>Talla</th>
                    <th>Cantidad</th>
                    <th>Precio Mayor</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody id="detalleBody">
                <!-- Contenido cargado por AJAX -->
            </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<script>
function verDetalle(idVenta) {
    fetch('perfil_mayorista_detalle.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'id_venta=' + idVenta
    })
    .then(res => res.json())
    .then(data => {
        const tbody = document.getElementById('detalleBody');
        tbody.innerHTML = '';

        if(data.success) {
            data.productos.forEach(p => {
                const row = `<tr>
                    <td>${p.estilo}</td>
                    <td>${p.color}</td>
                    <td>${p.talla}</td>
                    <td>${p.cantidad}</td>
                    <td>$${parseFloat(p.precio_unitario).toFixed(2)}</td>
                    <td>$${parseFloat(p.total).toFixed(2)}</td>
                </tr>`;
                tbody.innerHTML += row;
            });
            var detalleModal = new bootstrap.Modal(document.getElementById('detalleModal'));
            detalleModal.show();
        } else {
            alert(data.message);
        }
    });
}
</script>

<?php include 'includes/footer.php'; ?>
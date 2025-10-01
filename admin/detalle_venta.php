<?php
include '../includes/conexion.php';
include '../includes/header.php';

// Para marcar la página activa en el sidebar
$current_page = 'ventas.php';

$id = intval($_GET['id']);

// Traer información de la venta y cliente
$stmt = $conexion->prepare("
    SELECT v.*, u.username AS usuario, i.nombre AS invitado
    FROM ventas v
    LEFT JOIN usuarios u ON v.id_usuario = u.id
    LEFT JOIN invitados i ON v.id_invitado = i.id
    WHERE v.id=?
");
$stmt->execute([$id]);
$venta = $stmt->fetch(PDO::FETCH_ASSOC);

if(!$venta){
    echo "<p>Venta no encontrada.</p>";
    include '../includes/footer.php';
    exit;
}

// Traer detalles de la venta
$stmt = $conexion->prepare("
    SELECT dv.*, va.color, va.talla, p.estilo
    FROM detalle_ventas dv
    JOIN variantes va ON dv.id_variante=va.id
    JOIN productos p ON va.id_producto=p.id
    WHERE dv.id_venta=?
");
$stmt->execute([$id]);
$detalles = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Determinar tipo de cliente
$tipo_cliente = $venta['usuario'] ? 'Mayorista / Registrado' : 'Invitado';
$nombre_cliente = $venta['usuario'] ?? $venta['invitado'] ?? 'Desconocido';
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
        <h2>Detalle de Venta #<?= $venta['id'] ?></h2>

        <p><strong>Cliente:</strong> <?= htmlspecialchars($nombre_cliente) ?> (<?= $tipo_cliente ?>)</p>
        <?php if(!empty($venta['direccion_cliente'])): ?>
            <p><strong>Dirección de envío:</strong> <?= htmlspecialchars($venta['direccion_cliente']) ?></p>
        <?php endif; ?>

        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Producto</th>
                    <th>Color</th>
                    <th>Talla</th>
                    <th>Cantidad</th>
                    <th>Precio Unitario</th>
                    <th>Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $total = 0;
                foreach($detalles as $d):
                    $subtotal = $d['cantidad'] * $d['precio_unitario'];
                    $total += $subtotal;
                ?>
                <tr>
                    <td><?= htmlspecialchars($d['estilo']) ?></td>
                    <td><?= htmlspecialchars($d['color']) ?></td>
                    <td><?= htmlspecialchars($d['talla']) ?></td>
                    <td><?= $d['cantidad'] ?></td>
                    <td>$<?= number_format($d['precio_unitario'],2) ?></td>
                    <td>$<?= number_format($subtotal,2) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="5" style="text-align:right;"><strong>Total:</strong></td>
                    <td><strong>$<?= number_format($total,2) ?></strong></td>
                </tr>
            </tfoot>
        </table>

        <!-- Botón para descargar factura PDF -->
        <a href="../factura_cliente.php?id=<?= $venta['id'] ?>" target="_blank" class="btn btn-success mb-3">
            Descargar Factura
        </a>
        <a href="ventas.php"   class="btn btn-success mb-3">volver</a>
    </div>
</div>

<?php include '../includes/footer.php'; ?>

<?php
include '../includes/conexion.php';
include '../includes/header.php';

// Eliminar producto si se pasa ?eliminar=id
if(isset($_GET['eliminar'])){
    $id_eliminar = intval($_GET['eliminar']);

    // Verificar si existen movimientos de stock para este producto (todas sus variantes)
    $stmtVar = $conexion->prepare("SELECT id FROM variantes WHERE id_producto = ?");
    $stmtVar->execute([$id_eliminar]);
    $variantes = $stmtVar->fetchAll(PDO::FETCH_COLUMN);

    $tieneMovimientos = false;
    if(!empty($variantes)){
        $inQuery = implode(',', array_fill(0, count($variantes), '?'));
        $stmtMov = $conexion->prepare("SELECT COUNT(*) FROM movimientos_stock WHERE id_variante IN ($inQuery)");
        $stmtMov->execute($variantes);
        if($stmtMov->fetchColumn() > 0){
            $tieneMovimientos = true;
        }
    }

    if($tieneMovimientos){
        echo "<p class='text-danger'>No se puede eliminar este producto porque hay movimientos registrados en stock.</p>";
    } else {
        // Primero eliminar variantes
        $stmt = $conexion->prepare("DELETE FROM variantes WHERE id_producto=?");
        $stmt->execute([$id_eliminar]);

        // Luego eliminar el producto
        $stmt = $conexion->prepare("DELETE FROM productos WHERE id=?");
        $stmt->execute([$id_eliminar]);

        echo "<p class='text-success'>Producto eliminado correctamente.</p>";
    }
}

// Obtener todos los productos
$stmt = $conexion->prepare("SELECT * FROM productos ORDER BY created_at DESC");
$stmt->execute();
$productos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Para marcar la página activa en el sidebar
$current_page = basename($_SERVER['PHP_SELF']);
?>

<div class="d-flex">

    <!-- Sidebar -->
    <nav id="sidebar" class="d-flex flex-column p-3 bg-dark text-white" style="min-width: 220px; min-height: 100vh;">
        <a href="dashboard.php" class="d-flex align-items-center mb-3 mb-md-0 me-md-auto text-white text-decoration-none">
            <span class="fs-4">Branyey Admin</span>
        </a>
        <hr>
        <ul class="nav nav-pills flex-column mb-auto">
            <li class="nav-item">
                <a href="dashboard.php" class="nav-link text-white <?= $current_page=='dashboard.php' ? 'active' : '' ?>">
                    <i class="bi bi-speedometer2 me-2"></i> Dashboard
                </a>
            </li>
            <li>
                <a href="productos.php" class="nav-link text-white <?= $current_page=='productos.php' ? 'active' : '' ?>">
                    <i class="bi bi-box-seam me-2"></i> Gestion Productos
                </a>
            </li>
            <li>
                <a href="ventas.php" class="nav-link text-white <?= $current_page=='ventas.php' ? 'active' : '' ?>">
                    <i class="bi bi-cart-check me-2"></i> Ventas
                </a>
            </li>
            <li>
                <a href="gestion_usuarios.php" class="nav-link text-white <?= $current_page=='gestion_usuarios.php' ? 'active' : '' ?>">
                    <i class="bi bi-people me-2"></i> Usuarios
                </a>
            </li>
            <li>
                <a href="reportes.php" class="nav-link text-white <?= $current_page=='reportes.php' ? 'active' : '' ?>">
                    <i class="bi bi-file-earmark-pdf me-2"></i> Reportes
                </a>
            </li>
        </ul>
        <hr>
        <div class="text-center small text-white">© 2025 Branyey</div>
    </nav>

    <!-- Main content -->
    <div id="content" class="flex-fill p-4">
        <h2>Gestión de Productos</h2>
        <a href="productos/ingresar_producto.php" class="btn btn-success mb-3">Agregar Producto</a>

        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Estilo</th>
                    <th>Descripción</th>
                    <th>Precio unidad</th>
                    <th>Precio mayor</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($productos as $p): ?>
                <tr>
                    <td><?= $p['id'] ?></td>
                    <td><?= htmlspecialchars($p['estilo']) ?></td>
                    <td><?= htmlspecialchars($p['descripcion']) ?></td>
                    <td>$<?= $p['precio_unidad'] ?></td>
                    <td>$<?= $p['precio_mayor'] ?></td>
                    <td>
                        <a href="productos/editar_producto.php?id=<?= $p['id'] ?>" class="btn btn-warning btn-sm">Editar</a>
                        <a href="?eliminar=<?= $p['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('¿Seguro que quieres eliminar este producto?')">Eliminar</a>
                        <a href="productos/catalogo.php" class="btn btn-info btn-sm">Ver catálogo</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include '../includes/footer.php'; ?>

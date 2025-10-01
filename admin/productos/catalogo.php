<?php
include '../../includes/conexion.php';
include '../../includes/header.php';

// Detectar la página actual para marcar sidebar activo
$current_page = basename($_SERVER['PHP_SELF']);

// Filtros
$condiciones = [];
$params = [];

if(!empty($_GET['color'])){
    $condiciones[] = "v.color=?";
    $params[] = $_GET['color'];
}
if(!empty($_GET['talla'])){
    $condiciones[] = "v.talla=?";
    $params[] = $_GET['talla'];
}

$where = '';
if($condiciones){
    $where = 'WHERE ' . implode(' AND ', $condiciones);
}

$query = $conexion->prepare("
    SELECT p.*, v.color, v.talla, v.stock, ip.url 
    FROM productos p 
    JOIN variantes v ON v.id_producto=p.id
    LEFT JOIN imagenes_producto ip ON ip.id_producto=p.id
    $where
    GROUP BY p.id
");
$query->execute($params);
$productos = $query->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="d-flex">

    <!-- Sidebar oscuro -->
    <nav id="sidebar" class="d-flex flex-column p-3 bg-dark text-white" style="min-width: 220px; min-height: 100vh;">
        <a href="../dashboard.php" class="d-flex align-items-center mb-3 mb-md-0 me-md-auto text-white text-decoration-none">
            <span class="fs-4">Branyey Admin</span>
        </a>
        <hr class="border-secondary">
        <ul class="nav nav-pills flex-column mb-auto">
            <li class="nav-item">
                <a href="../dashboard.php" class="nav-link text-white <?= $current_page=='dashboard.php' ? 'active' : '' ?>">
                    <i class="bi bi-speedometer2 me-2"></i> Dashboard
                </a>
            </li>
            <li>
                <a href="../productos.php" class="nav-link text-white <?= in_array($current_page, ['catalogo.php']) ? 'active' : '' ?>">
                    <i class="bi bi-box-seam me-2"></i>Gestion Productos
                </a>
            </li>
            <li>
                <a href="../ventas.php" class="nav-link text-white <?= $current_page=='ventas.php' ? 'active' : '' ?>">
                    <i class="bi bi-cart-check me-2"></i> Ventas
                </a>
            </li>
            <li>
                <a href="../gestion_usuarios.php" class="nav-link text-white <?= $current_page=='gestion_usuarios.php' ? 'active' : '' ?>">
                    <i class="bi bi-people me-2"></i> Usuarios
                </a>
            </li>
            <li>
                <a href="../reportes.php" class="nav-link text-white <?= $current_page=='reportes.php' ? 'active' : '' ?>">
                    <i class="bi bi-file-earmark-pdf me-2"></i> Reportes
                </a>
            </li>
        </ul>
        <hr class="border-secondary">
        <div class="text-center small text-white">© 2025 Branyey</div>
    </nav>

    <!-- Main content -->
    <div id="content" class="flex-fill p-4 bg-light">
        <h2>Catálogo de Productos</h2>

        <form method="get" class="mb-3">
            <input type="text" name="color" placeholder="Color" class="form-control mb-2" value="<?= $_GET['color'] ?? '' ?>">
            <input type="text" name="talla" placeholder="Talla" class="form-control mb-2" value="<?= $_GET['talla'] ?? '' ?>">
            <button class="btn btn-primary">Filtrar</button>
            <a href="catalogo.php" class="btn btn-secondary">Limpiar</a>
             <a href="../productos.php"   class="btn btn-success ">volver</a>

        </form>

        <div class="row">
            <?php foreach($productos as $p): ?>
            <div class="col-md-4">
                <div class="card mb-3">
                    <img src="../../uploads/<?= $p['url'] ?? 'default.png' ?>" class="card-img-top" alt="Producto">
                    <div class="card-body">
                        <h5 class="card-title"><?= $p['estilo'] ?></h5>
                        <p class="card-text"><?= $p['descripcion'] ?></p>
                        <p>Precio unidad: $<?= $p['precio_unidad'] ?></p>
                        <p>Precio mayorista: $<?= $p['precio_mayor'] ?></p>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

</div>

<?php include '../../includes/footer.php'; ?>

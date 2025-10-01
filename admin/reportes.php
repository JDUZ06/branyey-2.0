<?php
include '../includes/conexion.php';
include '../includes/header.php';
require '../vendor/autoload.php';
use Dompdf\Dompdf;

// Para marcar la página activa en el sidebar
$current_page = 'reportes.php';

// Traer productos con variantes
$stmt = $conexion->prepare("
    SELECT p.id, p.estilo, p.descripcion, v.color, v.talla, v.stock
    FROM productos p
    LEFT JOIN variantes v ON p.id = v.id_producto
    ORDER BY p.id
");
$stmt->execute();
$productos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calcular stock total por producto
$totales = [];
foreach($productos as $p){
    $totales[$p['id']] = ($totales[$p['id']] ?? 0) + $p['stock'];
}

// Generar HTML para tabla
function generarTablaHTML($productos, $totales){
    $html = '<h2>Reporte de Inventario</h2>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Producto</th>
                <th>Descripción</th>
                <th>Color</th>
                <th>Talla</th>
                <th>Stock</th>
            </tr>
        </thead>
        <tbody>';
    foreach($productos as $p){
        $html .= '<tr>
            <td>'.htmlspecialchars($p['estilo']).'</td>
            <td>'.htmlspecialchars($p['descripcion']).'</td>
            <td>'.htmlspecialchars($p['color']).'</td>
            <td>'.htmlspecialchars($p['talla']).'</td>
            <td>'.$p['stock'].'</td>
        </tr>';
    }
    $html .= '</tbody></table>';

    $html .= '<h4>Stock total por producto</h4>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Producto</th>
                <th>Total Stock</th>
            </tr>
        </thead>
        <tbody>';
    foreach($totales as $id => $total){
        $html .= '<tr>
            <td>'.htmlspecialchars($productos[array_search($id, array_column($productos, 'id'))]['estilo']).'</td>
            <td>'.$total.'</td>
        </tr>';
    }
    $html .= '</tbody></table>';
    return $html;
}
?>

<div class="d-flex">

    <!-- Sidebar oscuro -->
    <nav id="sidebar" class="d-flex flex-column p-3 bg-dark text-white" style="min-width:220px; min-height:100vh;">
        <a href="../dashboard.php" class="d-flex align-items-center mb-3 mb-md-0 me-md-auto text-white text-decoration-none">
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
                <a href="productos.php" class="nav-link text-white">
                    <i class="bi bi-box-seam me-2"></i> Gestion Productos
                </a>
            </li>
            <li>
                <a href="ventas.php" class="nav-link text-white">
                    <i class="bi bi-cart-check me-2"></i> Ventas
                </a>
            </li>
            <li>
                <a href="gestion_usuarios.php" class="nav-link text-white">
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
        <?php echo generarTablaHTML($productos, $totales); ?>

        <a href="descargar_reporte.php" class="btn btn-primary mt-3">Exportar a PDF</a>
    </div>
</div>

<?php include '../includes/footer.php'; ?>

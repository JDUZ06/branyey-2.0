<?php
include '../includes/conexion.php';
include '../includes/header.php';

/* Solo acceso para administrador */
if(!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'administrador'){
    header("Location: ../index.php");
    exit;
}

// Para marcar la página activa en el sidebar
$current_page = basename($_SERVER['PHP_SELF']);
?>

<div class="d-flex dashboard-container">

    <!-- Sidebar oscuro -->
    <nav id="sidebar" class="d-flex flex-column p-3 bg-dark text-white dashboard-sidebar" style="min-width: 220px; min-height: 100vh;">
        <a href="dashboard.php" class="d-flex align-items-center mb-3 mb-md-0 me-md-auto text-white text-decoration-none">
            <span class="fs-4">Branyey Admin</span>
        </a>
        <hr class="border-secondary">
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
        <hr class="border-secondary">
        <div class="text-center small text-white">© 2025 Branyey</div>
    </nav>

    <!-- Main content -->
    <div id="content" class="flex-fill p-4 bg-light dashboard-content">
        <h2 class="mb-4 dashboard-heading">Dashboard</h2>

<!-- Estadísticas principales + Stock Crítico -->
<div class="row mb-4">
    <div class="col-md-3 mb-3">
        <div class="card dashboard-card shadow-sm border-secondary text-center stats-card">
            <div class="card-body d-flex flex-column justify-content-center">
                <h6 class="dashboard-title text-muted">Total Productos</h6>
                <?php
                $stmt = $conexion->query("SELECT COUNT(*) as total FROM productos");
                $totalProductos = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
                ?>
                <p class="dashboard-value text-dark"><?= $totalProductos ?></p>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card dashboard-card shadow-sm border-secondary text-center stats-card">
            <div class="card-body d-flex flex-column justify-content-center">
                <h6 class="dashboard-title text-muted">Total Ventas</h6>
                <?php
                $stmt = $conexion->query("SELECT COUNT(*) as total FROM ventas");
                $totalVentas = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
                ?>
                <p class="dashboard-value text-dark"><?= $totalVentas ?></p>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card dashboard-card shadow-sm border-secondary text-center stats-card">
            <div class="card-body d-flex flex-column justify-content-center">
                <h6 class="dashboard-title text-muted">Clientes Registrados</h6>
                <?php
                $stmt = $conexion->query("SELECT COUNT(*) as total FROM usuarios");
                $totalUsuarios = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
                ?>
                <p class="dashboard-value text-dark"><?= $totalUsuarios ?></p>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card dashboard-card shadow-sm border-danger text-center stats-card">
            <div class="card-body d-flex flex-column justify-content-center">
                <h6 class="dashboard-title text-muted">Stock Crítico (≤5)</h6>
                <?php
                $stmt = $conexion->query("SELECT COUNT(*) as total FROM variantes WHERE stock <= 5");
                $stockCritico = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
                ?>
                <p class="dashboard-value text-danger"><?= $stockCritico ?></p>
            </div>
        </div>
    </div>
</div>

        <!-- Charts principales -->
        <div class="row mb-4">
            <div class="col-lg-6 col-md-12 mb-3">
                <div class="card dashboard-card shadow-sm border-secondary">
                    <div class="card-header bg-light text-dark dashboard-chart-header">Ventas por Mes</div>
                    <div class="card-body">
                        <canvas id="ventasMes" height="180"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 col-md-12 mb-3">
                <div class="card dashboard-card shadow-sm border-secondary">
                    <div class="card-header bg-light text-dark dashboard-chart-header">Productos Más Vendidos</div>
                    <div class="card-body">
                        <canvas id="productosTop" height="180"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-lg-4 col-md-6 mb-3">
                <div class="card dashboard-card shadow-sm border-secondary">
                    <div class="card-header bg-light text-dark dashboard-chart-header">Productos con Menor Stock</div>
                    <div class="card-body">
                        <canvas id="productosMenosStock" height="150"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6 mb-3">
                <div class="card dashboard-card shadow-sm border-secondary">
                    <div class="card-header bg-light text-dark dashboard-chart-header">Productos con Mayor Stock</div>
                    <div class="card-body">
                        <canvas id="productosMasStock" height="150"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-12 mb-3">
                <div class="card dashboard-card shadow-sm border-secondary">
                    <div class="card-header bg-light text-dark dashboard-chart-header">Clientes por Tipo</div>
                    <div class="card-body">
                        <canvas id="clientesTipo" height="150"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener("DOMContentLoaded", () => {
    // Ventas por mes
    fetch('../estadisticas/ventas_por_mes.php').then(res => res.json()).then(data => {
        new Chart(document.getElementById('ventasMes'), {
            type: 'bar',
            data: { labels: data.meses, datasets: [{ label: 'Ventas', data: data.cantidades, backgroundColor: '#0d3b66' }] },
            options: { responsive:true, plugins:{legend:{display:false}}, scales:{y:{beginAtZero:true}} }
        });
    });

    // Productos top
    fetch('../estadisticas/productos_top.php').then(res => res.json()).then(data => {
        new Chart(document.getElementById('productosTop'), {
            type: 'bar',
            data: { labels: data.productos, datasets: [{ label: 'Unidades vendidas', data: data.cantidades, backgroundColor: '#3f72af' }] },
            options: { responsive:true, plugins:{legend:{display:false}}, scales:{y:{beginAtZero:true}} }
        });
    });

    // Stock
    fetch('../estadisticas/productos_stock.php').then(res => res.json()).then(data => {
        new Chart(document.getElementById('productosMenosStock'), {
            type: 'bar',
            data: { labels: data.productos_menos, datasets: [{ label: 'Stock', data: data.stock_menos, backgroundColor: '#9d0208' }] },
            options: { responsive:true, plugins:{legend:{display:false}}, scales:{y:{beginAtZero:true}} }
        });

        new Chart(document.getElementById('productosMasStock'), {
            type: 'bar',
            data: { labels: data.productos_mas, datasets: [{ label: 'Stock', data: data.stock_mas, backgroundColor: '#3f72af' }] },
            options: { responsive:true, plugins:{legend:{display:false}}, scales:{y:{beginAtZero:true}} }
        });
    });

    // Clientes por tipo
    fetch('../estadisticas/clientes_tipo.php').then(res => res.json()).then(data => {
        new Chart(document.getElementById('clientesTipo'), {
            type: 'pie',
            data: { labels: data.tipos, datasets: [{ data: data.cantidades, backgroundColor: ['#0d3b66','#666666'] }] },
            options: { responsive:true }
        });
    });
});
</script>

<?php include '../includes/footer.php'; ?>


<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener("DOMContentLoaded", () => {
    // Ventas por mes
    fetch('../estadisticas/ventas_por_mes.php').then(res => res.json()).then(data => {
        new Chart(document.getElementById('ventasMes'), {
            type: 'bar',
            data: { labels: data.meses, datasets: [{ label: 'Ventas', data: data.cantidades, backgroundColor: '#0d3b66' }] },
            options: { responsive:true, plugins:{legend:{display:false}}, scales:{y:{beginAtZero:true}} }
        });
    });

    // Productos top
    fetch('../estadisticas/productos_top.php').then(res => res.json()).then(data => {
        new Chart(document.getElementById('productosTop'), {
            type: 'bar',
            data: { labels: data.productos, datasets: [{ label: 'Unidades vendidas', data: data.cantidades, backgroundColor: '#3f72af' }] },
            options: { responsive:true, plugins:{legend:{display:false}}, scales:{y:{beginAtZero:true}} }
        });
    });

    // Stock
    fetch('../estadisticas/productos_stock.php').then(res => res.json()).then(data => {
        new Chart(document.getElementById('productosMenosStock'), {
            type: 'bar',
            data: { labels: data.productos_menos, datasets: [{ label: 'Stock', data: data.stock_menos, backgroundColor: '#9d0208' }] },
            options: { responsive:true, plugins:{legend:{display:false}}, scales:{y:{beginAtZero:true}} }
        });

        new Chart(document.getElementById('productosMasStock'), {
            type: 'bar',
            data: { labels: data.productos_mas, datasets: [{ label: 'Stock', data: data.stock_mas, backgroundColor: '#3f72af' }] },
            options: { responsive:true, plugins:{legend:{display:false}}, scales:{y:{beginAtZero:true}} }
        });
    });

    // Clientes por tipo
    fetch('../estadisticas/clientes_tipo.php').then(res => res.json()).then(data => {
        new Chart(document.getElementById('clientesTipo'), {
            type: 'pie',
            data: { labels: data.tipos, datasets: [{ data: data.cantidades, backgroundColor: ['#0d3b66','#666666'] }] },
            options: { responsive:true }
        });
    });
});
</script>

<?php include '../includes/footer.php'; ?>

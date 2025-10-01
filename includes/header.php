<?php
if(session_status() === PHP_SESSION_NONE) session_start();

// Obtener la ruta del script actual
$scriptPath = $_SERVER['PHP_SELF'];

// Calcular basePath dinámico según ubicación
$basePath = '';
if(str_contains($scriptPath, '/admin/')) {
    $basePath = str_repeat('../', substr_count($scriptPath, '/') - 2);
} elseif(str_contains($scriptPath, '/usuarios/')) {
    $basePath = '../';
} elseif(str_contains($scriptPath, '/productos/')) {
    $basePath = '../../';
} else {
    $basePath = '';
}

// Enlace del logo según rol
if(!isset($_SESSION['rol'])){
    $logo_href = $basePath . 'index.php';
} elseif($_SESSION['rol'] === 'administrador'){
    $logo_href = $basePath . 'admin/dashboard.php';
} elseif($_SESSION['rol'] === 'comprador'){
    $logo_href = $basePath . 'catalogo_mayor.php';
}

// Ruta de logout
$logout_path = $basePath . 'usuarios/logout.php';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Branyey</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= $basePath ?>assets/css/estilos.css?v=<?= time() ?>">
    <style>
        .carrito-icon { position: relative; display: inline-block; }
        .carrito-icon span { position: absolute; top: -5px; right: -10px; background: red; color: #fff; font-size: 12px; padding: 2px 6px; border-radius: 50%; }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-light bg-light mb-4">
    <div class="container">
        <a class="navbar-brand" href="<?= $logo_href ?>">Branyey</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <?php if(isset($_SESSION['username']) && isset($_SESSION['rol'])): ?>
                    <li class="nav-item me-2">
                        <span class="nav-link">Hola, <?= htmlspecialchars($_SESSION['username']) ?></span>
                    </li>

                    <?php if($_SESSION['rol'] === 'administrador'): ?>
                        <li class="nav-item"><a class="nav-link" href="<?= $basePath ?>admin/dashboard.php">Inicio</a></li>
                    <?php elseif($_SESSION['rol'] === 'comprador'): ?>
                        <li class="nav-item"><a class="nav-link" href="<?= $basePath ?>catalogo_mayor.php">Catálogo </a></li>
                        <!-- Botón perfil mayorista -->
                        <li class="nav-item"><a class="nav-link" href="<?= $basePath ?>perfil_mayorista.php">Mi Perfil</a></li>
                    <?php endif; ?>

                    <li class="nav-item me-2">
                        <a class="nav-link btn btn-outline-danger btn-sm text-dark" href="<?= $logout_path ?>">Cerrar Sesión</a>
                    </li>

                    <?php if($_SESSION['rol'] !== 'administrador'): ?>
                        <li class="nav-item carrito-icon">
                            <a class="nav-link" href="<?= $basePath ?>carrito.php">
                                🛒 Carrito <span id="contador-carrito">0</span>
                            </a>
                        </li>
                    <?php endif; ?>

                <?php else: ?>
                    <li class="nav-item"><a class="nav-link" href="<?= $basePath ?>usuarios/login.php">Iniciar Sesión</a></li>
                    <li class="nav-item carrito-icon">
                        <a class="nav-link" href="<?= $basePath ?>carrito.php">
                            🛒 Carrito <span id="contador-carrito">0</span>
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Contador de carrito desde sesión/localStorage
function actualizarContador() {
    const carrito = JSON.parse(localStorage.getItem('carrito')) || [];
    const contador = document.getElementById('contador-carrito');
    if(contador) contador.textContent = carrito.length;
}
document.addEventListener('DOMContentLoaded', actualizarContador);
</script>

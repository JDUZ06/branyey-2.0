<?php
session_start();
include '../includes/conexion.php';
include '../includes/header.php';

// Para marcar la página activa en el sidebar
$current_page = 'gestion_usuarios.php';

/*
if(!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'administrador'){
    header("Location: ../index.php");
    exit;
}
*/
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
        <h2>Gestión de Usuarios</h2>

        <!-- Mostrar mensajes -->
        <?php if(isset($_SESSION['mensaje'])): ?>
            <div class="alert alert-<?= $_SESSION['mensaje_tipo'] ?>">
                <?= htmlspecialchars($_SESSION['mensaje']) ?>
            </div>
            <?php
            // Limpiar mensaje después de mostrarlo
            unset($_SESSION['mensaje']);
            unset($_SESSION['mensaje_tipo']);
            ?>
        <?php endif; ?>

        <h4 class="mt-3">Crear Usuario</h4>
        <form method="post" action="../usuarios/registrar.php" class="mb-4">
            <div class="mb-2">
                <input type="text" name="username" class="form-control" placeholder="Nombre de usuario" required>
            </div>
            <div class="mb-2">
                <input type="password" name="password" class="form-control" placeholder="Contraseña" required>
            </div>
            <div class="mb-2">
                <select name="rol" class="form-select" required>
                    <option value="">Selecciona rol</option>
                    <?php
                    $stmt_roles = $conexion->query("SELECT * FROM roles");
                    $roles = $stmt_roles->fetchAll(PDO::FETCH_ASSOC);
                    foreach($roles as $r):
                    ?>
                        <option value="<?= $r['id'] ?>"><?= htmlspecialchars($r['nombre']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" name="crear" class="btn btn-success">Crear Usuario</button>
        </form>

        <h4>Usuarios existentes</h4>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Usuario</th>
                    <th>Rol</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $stmt = $conexion->query("SELECT u.id, u.username, r.nombre AS rol FROM usuarios u JOIN roles r ON u.rol_id=r.id");
                $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
                foreach($usuarios as $u):
                ?>
                <tr>
                    <td><?= $u['id'] ?></td>
                    <td><?= htmlspecialchars($u['username']) ?></td>
                    <td><?= htmlspecialchars($u['rol']) ?></td>
                    <td>
                        <a href="../usuarios/editar.php?id=<?= $u['id'] ?>" class="btn btn-sm btn-primary">Editar</a>
                        <a href="../usuarios/eliminar.php?id=<?= $u['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Eliminar usuario?')">Eliminar</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include '../includes/footer.php'; ?>

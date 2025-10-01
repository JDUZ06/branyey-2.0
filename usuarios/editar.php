<?php
include '../includes/conexion.php';
include '../includes/header.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if($id == 0) die("ID de usuario no válido");

// Para marcar la página activa en el sidebar
$current_page = 'gestion_usuarios.php';

// Traer información del usuario
$stmt = $conexion->prepare("SELECT * FROM usuarios WHERE id=?");
$stmt->execute([$id]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);
if(!$usuario) die("Usuario no encontrado");

// Traer roles
$stmt_roles = $conexion->query("SELECT * FROM roles");
$roles = $stmt_roles->fetchAll(PDO::FETCH_ASSOC);

if($_POST){
    $username = $_POST['username'];
    $rol_id = $_POST['rol'];

    // Actualizar contraseña solo si se ingresó
    if(!empty($_POST['password'])){
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $stmt = $conexion->prepare("UPDATE usuarios SET username=?, password=?, rol_id=? WHERE id=?");
        $stmt->execute([$username, $password, $rol_id, $id]);
    } else {
        $stmt = $conexion->prepare("UPDATE usuarios SET username=?, rol_id=? WHERE id=?");
        $stmt->execute([$username, $rol_id, $id]);
    }

    $_SESSION['mensaje'] = "Usuario actualizado correctamente";
    $_SESSION['mensaje_tipo'] = "success";
    header("Location: ../admin/gestion_usuarios.php");
    exit;
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
                <a href="../dashboard.php" class="nav-link text-white <?= $current_page=='dashboard.php'?'active':'' ?>">
                    <i class="bi bi-speedometer2 me-2"></i> Dashboard
                </a>
            </li>
            <li>
                <a href="../productos.php" class="nav-link text-white">
                    <i class="bi bi-box-seam me-2"></i> Gestion Productos
                </a>
            </li>
            <li>
                <a href="../ventas.php" class="nav-link text-white">
                    <i class="bi bi-cart-check me-2"></i> Ventas
                </a>
            </li>
            <li>
                <a href="../gestion_usuarios.php" class="nav-link text-white <?= $current_page=='gestion_usuarios.php'?'active':'' ?>">
                    <i class="bi bi-people me-2"></i> Usuarios
                </a>
            </li>
            <li>
                <a href="../reportes.php" class="nav-link text-white">
                    <i class="bi bi-file-earmark-pdf me-2"></i> Reportes
                </a>
            </li>
        </ul>
        <hr class="border-secondary">
        <div class="text-center small text-white">© 2025 Branyey</div>
    </nav>

    <!-- Contenido principal -->
    <div id="content" class="flex-fill p-4 bg-light">
        <h2 class="mb-4">Editar Usuario</h2>

        <form method="post">
            <div class="mb-2">
                <label>Nombre de usuario</label>
                <input type="text" name="username" value="<?= htmlspecialchars($usuario['username']) ?>" class="form-control" required>
            </div>
            <div class="mb-2">
                <label>Contraseña (dejar en blanco para mantener)</label>
                <input type="password" name="password" class="form-control">
            </div>
            <div class="mb-2">
                <label>Rol</label>
                <select name="rol" class="form-select" required>
                    <?php foreach($roles as $r): ?>
                        <option value="<?= $r['id'] ?>" <?= $usuario['rol_id']==$r['id']?'selected':'' ?>>
                            <?= htmlspecialchars($r['nombre']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <button type="submit" class="btn btn-success">Actualizar Usuario</button>
            <a href="../admin/gestion_usuarios.php" class="btn btn-secondary">Volver</a>
        </form>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>

<?php
session_start();
include '../includes/conexion.php';

$mensaje = '';

if($_POST){
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $conexion->prepare("SELECT u.*, r.nombre as rol FROM usuarios u 
                                JOIN roles r ON u.rol_id = r.id 
                                WHERE username=?");
    $stmt->execute([$username]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if($usuario && password_verify($password, $usuario['password'])){
        $_SESSION['usuario_id'] = $usuario['id'];
        $_SESSION['rol'] = $usuario['rol'];
        $_SESSION['username'] = $usuario['username'];

        // Redirección según rol
        if($usuario['rol']=='administrador'){
            header('Location: ../admin/dashboard.php');
            exit;
        } elseif($usuario['rol']=='comprador'){
            header('Location: ../catalogo_mayor.php');
            exit;
        }
    } else {
        $mensaje = 'Usuario o contraseña incorrectos';
    }
}
?>

<?php include '../includes/header.php'; ?>

<div class="login-page">
    <div class="login-container">
        <h2 class="text-center mb-4">Iniciar Sesión</h2>
        <?php if($mensaje) echo "<div class='alert alert-danger'>$mensaje</div>"; ?>
        <form method="post">
            <input type="text" name="username" placeholder="Usuario" class="form-control mb-3" required>
            <input type="password" name="password" placeholder="Contraseña" class="form-control mb-3" required>
            <button type="submit" class="btn btn-primary w-100">Entrar</button>
        </form>
    </div>
</div>

<?php include '../includes/footer.php'; ?>

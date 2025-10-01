<?php
session_start();

// Guardar rol antes de destruir sesión
$rol = $_SESSION['rol'] ?? null;

// Destruir sesión
session_unset();
session_destroy();

// Redirigir según rol anterior
if($rol === 'administrador'){
    header('Location: ../index.php'); // vuelve a la carpeta padre del admin
    exit;
} elseif($rol === 'comprador'){
    header('Location: ../index.php'); // comprador vuelve al index normal
    exit;
}
?>

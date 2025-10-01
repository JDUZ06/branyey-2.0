<?php
include '../includes/conexion.php';
include '../includes/header.php';

// Solo acceso administrador
if(!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'administrador'){
    header("Location: ../index.php");
    exit;
}

// Obtener ID del usuario a eliminar
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if($id === 0) die("ID de usuario no válido");

// Evitar que el admin se elimine a sí mismo
if($id == $_SESSION['id_usuario']){
    die("No puedes eliminar tu propio usuario mientras estés conectado.");
}

// Eliminar usuario
$stmt = $conexion->prepare("DELETE FROM usuarios WHERE id=?");
$stmt->execute([$id]);

// Redirigir al listado
header("Location: ../admin/gestion_usuarios.php");
exit;

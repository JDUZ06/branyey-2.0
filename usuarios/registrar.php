<?php
session_start(); // ✅ Obligatorio para usar $_SESSION
include '../includes/conexion.php';

if($_POST){
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $rol_id = intval($_POST['rol']);

    // Validación de contraseña
    if(!preg_match('/^(?=.*[a-zA-Z])(?=.*\d)[A-Za-z\d]{8,}$/', $password)){
        $_SESSION['mensaje'] = 'La contraseña debe tener al menos 8 caracteres, incluyendo letras y números';
        $_SESSION['mensaje_tipo'] = 'danger';
    } else {
        $password_hash = password_hash($password, PASSWORD_DEFAULT);

        try {
            $stmt = $conexion->prepare("INSERT INTO usuarios (username,password,rol_id) VALUES (?,?,?)");
            $stmt->execute([$username,$password_hash,$rol_id]);

            $_SESSION['mensaje'] = 'Usuario creado correctamente!';
            $_SESSION['mensaje_tipo'] = 'success';
        } catch(PDOException $e) {
            // Código 23000 indica violación de restricción (clave única)
            if($e->getCode() == 23000){
                $_SESSION['mensaje'] = 'El nombre de usuario ya existe, elige otro';
                $_SESSION['mensaje_tipo'] = 'danger';
            } else {
                $_SESSION['mensaje'] = 'Error al crear el usuario';
                $_SESSION['mensaje_tipo'] = 'danger';
            }
        }
    }

    header('Location: ../admin/gestion_usuarios.php');
    exit;
}

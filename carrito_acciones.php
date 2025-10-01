<?php
session_start();
header('Content-Type: application/json');

// Detectar si hay usuario logueado
$id_usuario = $_SESSION['usuario_id'] ?? null;

// Nombre de la clave de carrito en sesión
$claveCarrito = $id_usuario ? "carrito_usuario_" . $id_usuario : "carrito_invitado";

// Inicializar carrito si no existe
if (!isset($_SESSION[$claveCarrito])) {
    $_SESSION[$claveCarrito] = [];
}

$accion = $_POST['accion'] ?? '';

switch ($accion) {
    case 'agregar':
        $producto = [
            'id_variante' => $_POST['id_variante'],
            'id_producto' => $_POST['id_producto'],
            'estilo'      => $_POST['estilo'],
            'color'       => $_POST['color'],
            'talla'       => $_POST['talla'],
            'cantidad'    => intval($_POST['cantidad']),
            'precio'      => floatval($_POST['precio'])
        ];

        $encontrado = false;
        foreach ($_SESSION[$claveCarrito] as &$item) {
            if ($item['id_variante'] == $producto['id_variante']) {
                $item['cantidad'] += $producto['cantidad'];
                $encontrado = true;
                break;
            }
        }
        if (!$encontrado) {
            $_SESSION[$claveCarrito][] = $producto;
        }
        break;

    case 'eliminar':
        $id_variante = $_POST['id_variante'] ?? null;
        if ($id_variante !== null) {
            $_SESSION[$claveCarrito] = array_filter($_SESSION[$claveCarrito], function ($item) use ($id_variante) {
                return $item['id_variante'] != $id_variante;
            });
            $_SESSION[$claveCarrito] = array_values($_SESSION[$claveCarrito]); // reindexar
        }
        break;

    case 'vaciar':
        $_SESSION[$claveCarrito] = [];
        break;

    case 'actualizar':
        $id_variante = $_POST['id_variante'] ?? null;
        $cantidad = intval($_POST['cantidad'] ?? 1);
        foreach($_SESSION[$claveCarrito] as &$item){
            if($item['id_variante'] == $id_variante){
                $item['cantidad'] = $cantidad;
                break;
            }
        }
        break;

    case 'obtener':
    default:
        // simplemente devuelve el carrito
        break;
}

// Responder con el carrito actualizado
echo json_encode([
    'success' => true,
    'carrito' => $_SESSION[$claveCarrito]
]);

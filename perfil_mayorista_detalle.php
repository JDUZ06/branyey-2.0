<?php
session_start();
header('Content-Type: application/json');
include 'includes/conexion.php';

// Validar usuario logueado
$id_usuario = $_SESSION['usuario_id'] ?? null;
if(!$id_usuario){
    echo json_encode(['success'=>false,'message'=>'Usuario no autenticado']);
    exit;
}

// Obtener id_venta
$id_venta = $_POST['id_venta'] ?? null;
if(!$id_venta){
    echo json_encode(['success'=>false,'message'=>'Parámetros inválidos']);
    exit;
}

// Traer detalle de la venta solo si pertenece al usuario
$sql = "
    SELECT dv.id_variante, dv.cantidad, p.estilo, p.precio_mayor, var.color, var.talla, var.stock
    FROM detalle_ventas dv
    JOIN ventas v ON dv.id_venta = v.id
    JOIN variantes var ON dv.id_variante = var.id
    JOIN productos p ON var.id_producto = p.id
    WHERE dv.id_venta = ? AND v.id_usuario = ?
";
$stmt = $conexion->prepare($sql);
$stmt->execute([$id_venta, $id_usuario]);
$productos = $stmt->fetchAll(PDO::FETCH_ASSOC);

if(!$productos){
    echo json_encode(['success'=>false,'message'=>'Venta no encontrada']);
    exit;
}

// Agregar total por producto usando precio_mayor
foreach($productos as &$p){
    $p['precio_unitario'] = $p['precio_mayor'];
    $p['total'] = $p['cantidad'] * $p['precio_unitario'];
}

echo json_encode(['success'=>true,'productos'=>$productos]);
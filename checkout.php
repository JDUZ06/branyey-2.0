<?php
require 'vendor/autoload.php';
use Dompdf\Dompdf;

include 'includes/conexion.php';
session_start();
header('Content-Type: application/json');

// Identificar si es usuario registrado
$id_usuario = $_SESSION['usuario_id'] ?? null;
$claveCarrito = $id_usuario ? "carrito_usuario_" . $id_usuario : "carrito_invitado";
$carrito = $_SESSION[$claveCarrito] ?? [];

if(empty($carrito)){
    echo json_encode(['success'=>false,'message'=>'El carrito está vacío']);
    exit;
}

// Datos de invitado si no hay usuario
if(!$id_usuario){
    $nombre = $_POST['nombre'] ?? '';
    $telefono = $_POST['telefono'] ?? '';
    $direccion = $_POST['direccion'] ?? '';
    $cedula = $_POST['cedula'] ?? '';

    if(!$nombre || !$telefono || !$direccion || !$cedula){
        echo json_encode(['success'=>false,'message'=>'Complete todos los datos para finalizar la compra']);
        exit;
    }

    $stmt = $conexion->prepare("INSERT INTO invitados (nombre, telefono) VALUES (?, ?)");
    $stmt->execute([$nombre, $telefono]);
    $id_invitado = $conexion->lastInsertId();
}else{
    $id_invitado = null;
    $nombre = $telefono = $direccion = $cedula = null;
}

try{
    $conexion->beginTransaction();
    $total = 0;

    // Verificar stock y calcular total
    foreach($carrito as $item){
        $stmt = $conexion->prepare("SELECT stock FROM variantes WHERE id=? FOR UPDATE");
        $stmt->execute([$item['id_variante']]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if(!$row) throw new Exception("Variante no encontrada");
        if($item['cantidad'] > $row['stock']) throw new Exception("Cantidad supera stock disponible");
        $total += $item['precio'] * $item['cantidad'];
    }

    // Insertar venta
    $stmt = $conexion->prepare("
        INSERT INTO ventas
        (id_usuario, id_invitado, nombre_cliente, telefono_cliente, direccion_cliente, total)
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([$id_usuario, $id_invitado, $nombre, $telefono, $direccion, $total]);
    $id_venta = $conexion->lastInsertId();

    // Insertar detalle y actualizar stock
    foreach($carrito as $item){
        $stmt = $conexion->prepare("INSERT INTO detalle_ventas (id_venta, id_variante, cantidad, precio_unitario) VALUES (?, ?, ?, ?)");
        $stmt->execute([$id_venta, $item['id_variante'], $item['cantidad'], $item['precio']]);

        $stmt = $conexion->prepare("UPDATE variantes SET stock = stock - ? WHERE id=?");
        $stmt->execute([$item['cantidad'], $item['id_variante']]);

        $stmt = $conexion->prepare("INSERT INTO movimientos_stock (id_variante, tipo, cantidad, referencia) VALUES (?, 'salida', ?, ?)");
        $stmt->execute([$item['id_variante'], $item['cantidad'], 'Venta ID '.$id_venta]);
    }

    $conexion->commit();
    $_SESSION[$claveCarrito] = [];

    // Nombre del PDF
    $nombre_pdf = $id_usuario 
        ? "factura_usuario_".$id_usuario."_venta_".$id_venta.".pdf"
        : "factura_invitado_".$id_invitado."_venta_".$id_venta.".pdf";

    echo json_encode([
        'success' => true,
        'message' => 'Compra realizada con éxito',
        'id_venta' => $id_venta,
        'nombre_pdf' => $nombre_pdf,
        'total' => $total,
        'productos' => $carrito
    ]);

}catch(Exception $e){
    $conexion->rollBack();
    echo json_encode(['success'=>false, 'message'=>$e->getMessage()]);
}

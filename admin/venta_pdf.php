<?php
require '../vendor/autoload.php';
use Dompdf\Dompdf;

include '../includes/conexion.php';

$id_venta = $_GET['id'] ?? null;
if (!$id_venta) die("ID de venta no especificado.");

// Traer venta con cliente
$stmt = $conexion->prepare("
    SELECT v.*, 
           u.username AS usuario, 
           i.nombre AS invitado,
           i.email AS email_invitado,
           i.telefono AS telefono_invitado
    FROM ventas v
    LEFT JOIN usuarios u ON v.id_usuario = u.id
    LEFT JOIN invitados i ON v.id_invitado = i.id
    WHERE v.id=?
");
$stmt->execute([$id_venta]);
$venta = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$venta) die("Venta no encontrada.");

// Traer detalle de venta
$stmt = $conexion->prepare("
    SELECT dv.*, var.color, var.talla, p.estilo 
    FROM detalle_ventas dv
    JOIN variantes var ON dv.id_variante = var.id
    JOIN productos p ON var.id_producto = p.id
    WHERE dv.id_venta=?
");
$stmt->execute([$id_venta]);
$detalle = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Preparar HTML
$tipo = $venta['usuario'] ? 'Usuario registrado' : 'Invitado';
$cliente = $venta['usuario'] ?? $venta['invitado'];
$email = $venta['email_cliente'] ?? $venta['email_invitado'] ?? '';
$telefono = $venta['telefono_cliente'] ?? $venta['telefono_invitado'] ?? '';
$direccion = $venta['direccion_cliente'] ?? '';

$html = '
<html>
<head>
<style>
body { font-family: Arial, sans-serif; font-size: 12px; }
h2 { text-align: center; }
table { width: 100%; border-collapse: collapse; margin-top: 10px; }
table, th, td { border: 1px solid black; }
th, td { padding: 6px; text-align: left; }
th { background-color: #f2f2f2; }
</style>
</head>
<body>
<h2>Detalle de Venta</h2>
<p><strong>Venta ID:</strong> '.$venta['id'].'<br>
<strong>Fecha:</strong> '.$venta['fecha'].'<br>
<strong>Cliente:</strong> '.$cliente.' ('.$tipo.')<br>';
if($email) $html .= '<strong>Email:</strong> '.$email.'<br>';
if($telefono) $html .= '<strong>Teléfono:</strong> '.$telefono.'<br>';
if($direccion) $html .= '<strong>Dirección:</strong> '.$direccion.'<br>';
$html .= '</p>
<table>
<thead>
<tr>
<th>Producto</th>
<th>Color</th>
<th>Talla</th>
<th>Cantidad</th>
<th>Precio Unitario</th>
<th>Subtotal</th>
</tr>
</thead>
<tbody>';

$total = 0;
foreach($detalle as $d){
    $subtotal = $d['precio_unitario'] * $d['cantidad'];
    $total += $subtotal;
    $html .= '<tr>
<td>'.htmlspecialchars($d['estilo']).'</td>
<td>'.htmlspecialchars($d['color']).'</td>
<td>'.htmlspecialchars($d['talla']).'</td>
<td>'.$d['cantidad'].'</td>
<td>$'.number_format($d['precio_unitario'],2).'</td>
<td>$'.number_format($subtotal,2).'</td>
</tr>';
}

$html .= '</tbody>
<tfoot>
<tr>
<td colspan="5" style="text-align:right;"><strong>Total</strong></td>
<td><strong>$'.number_format($total,2).'</strong></td>
</tr>
</tfoot>
</table>
</body>
</html>';

$dompdf = new Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper('A4','portrait');
$dompdf->render();
$dompdf->stream("venta_".$venta['id'].".pdf", ["Attachment" => false]);

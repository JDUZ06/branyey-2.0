<?php
require 'vendor/autoload.php'; // Asegúrate de tener dompdf instalado
use Dompdf\Dompdf;

include 'includes/conexion.php';
session_start();

$id_venta = $_GET['id'] ?? null;
if(!$id_venta){
    die("ID de venta no proporcionado.");
}

// Traer la venta
$stmt = $conexion->prepare("
    SELECT v.*, u.username AS usuario, i.nombre AS invitado
    FROM ventas v
    LEFT JOIN usuarios u ON v.id_usuario = u.id
    LEFT JOIN invitados i ON v.id_invitado = i.id
    WHERE v.id=?
");
$stmt->execute([$id_venta]);
$venta = $stmt->fetch(PDO::FETCH_ASSOC);

if(!$venta){
    die("Venta no encontrada.");
}

// Traer detalles de la venta
$stmt = $conexion->prepare("
    SELECT dv.*, va.color, va.talla, p.estilo
    FROM detalle_ventas dv
    INNER JOIN variantes va ON dv.id_variante = va.id
    INNER JOIN productos p ON va.id_producto = p.id
    WHERE dv.id_venta=?
");
$stmt->execute([$id_venta]);
$detalles = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Crear HTML de factura
$html = '<h2 style="text-align:center;">Factura de Compra</h2>';
$html .= '<p><strong>Venta ID:</strong> '.$venta['id'].'</p>';
$html .= '<p><strong>Cliente:</strong> '.htmlspecialchars($venta['usuario'] ?? $venta['invitado'] ?? 'Invitado').'</p>';
if($venta['nombre_cliente']) $html .= '<p><strong>Nombre:</strong> '.htmlspecialchars($venta['nombre_cliente']).'</p>';
if($venta['telefono_cliente']) $html .= '<p><strong>Teléfono:</strong> '.htmlspecialchars($venta['telefono_cliente']).'</p>';
if($venta['direccion_cliente']) $html .= '<p><strong>Dirección:</strong> '.htmlspecialchars($venta['direccion_cliente']).'</p>';
$html .= '<p><strong>Fecha:</strong> '.$venta['fecha'].'</p>';

$html .= '<table border="1" cellspacing="0" cellpadding="5" width="100%">
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

foreach($detalles as $d){
    $subtotal = $d['cantidad'] * $d['precio_unitario'];
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
<td><strong>$'.number_format($venta['total'],2).'</strong></td>
</tr>
</tfoot>
</table>';

// Inicializar Dompdf
$dompdf = new Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

// Nombre de archivo personalizado
$cliente = $venta['usuario'] ?? $venta['invitado'] ?? 'invitado';
$filename = "Factura_Venta_{$id_venta}_{$cliente}.pdf";

// Descargar automáticamente
$dompdf->stream($filename, ["Attachment" => true]);
exit;

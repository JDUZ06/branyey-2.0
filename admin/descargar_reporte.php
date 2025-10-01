<?php
include '../includes/conexion.php';
require '../vendor/autoload.php';
use Dompdf\Dompdf;

// Traer productos con variantes
$stmt = $conexion->prepare("
    SELECT p.id, p.estilo, p.descripcion, v.color, v.talla, v.stock
    FROM productos p
    LEFT JOIN variantes v ON p.id = v.id_producto
    ORDER BY p.id
");
$stmt->execute();
$productos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calcular stock total por producto
$totales = [];
foreach($productos as $p){
    $totales[$p['id']] = ($totales[$p['id']] ?? 0) + $p['stock'];
}

// HTML del PDF
$html = '<h2>Reporte de Inventario</h2>
<table border="1" cellpadding="5" cellspacing="0">
    <thead>
        <tr>
            <th>Producto</th>
            <th>Descripción</th>
            <th>Color</th>
            <th>Talla</th>
            <th>Stock</th>
        </tr>
    </thead>
    <tbody>';
foreach($productos as $p){
    $html .= '<tr>
        <td>'.htmlspecialchars($p['estilo']).'</td>
        <td>'.htmlspecialchars($p['descripcion']).'</td>
        <td>'.htmlspecialchars($p['color']).'</td>
        <td>'.htmlspecialchars($p['talla']).'</td>
        <td>'.$p['stock'].'</td>
    </tr>';
}
$html .= '</tbody></table>';

$html .= '<h4>Stock total por producto</h4>
<table border="1" cellpadding="5" cellspacing="0">
    <thead>
        <tr>
            <th>Producto</th>
            <th>Total Stock</th>
        </tr>
    </thead>
    <tbody>';
foreach($totales as $id => $total){
    $html .= '<tr>
        <td>'.htmlspecialchars($productos[array_search($id, array_column($productos, 'id'))]['estilo']).'</td>
        <td>'.$total.'</td>
    </tr>';
}
$html .= '</tbody></table>';

// Generar PDF
$dompdf = new Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

// Forzar descarga
header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="reporte_inventario.pdf"');
echo $dompdf->output();
exit;

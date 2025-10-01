<?php
require '../vendor/autoload.php';
use Dompdf\Dompdf;

include '../includes/conexion.php';

// --- FILTROS ---
$tipo = $_GET['tipo'] ?? '';
$cliente = trim($_GET['cliente'] ?? '');
$orden = $_GET['orden'] ?? 'fecha_desc';

$where = [];
$params = [];

if ($tipo === 'usuario') {
    $where[] = 'v.id_usuario IS NOT NULL';
} elseif ($tipo === 'invitado') {
    $where[] = 'v.id_invitado IS NOT NULL';
}

if ($cliente) {
    $where[] = '(u.username LIKE ? OR i.nombre LIKE ?)';
    $params[] = "%$cliente%";
    $params[] = "%$cliente%";
}

$whereSql = '';
if ($where) $whereSql = 'WHERE ' . implode(' AND ', $where);

switch ($orden) {
    case 'fecha_asc': $orderSql = 'v.fecha ASC'; break;
    case 'total_desc': $orderSql = 'v.total DESC'; break;
    case 'total_asc': $orderSql = 'v.total ASC'; break;
    default: $orderSql = 'v.fecha DESC';
}

$sql = "
SELECT v.*, u.username AS usuario, i.nombre AS invitado
FROM ventas v
LEFT JOIN usuarios u ON v.id_usuario = u.id
LEFT JOIN invitados i ON v.id_invitado = i.id
$whereSql
ORDER BY $orderSql
";

$stmt = $conexion->prepare($sql);
$stmt->execute($params);
$ventas = $stmt->fetchAll(PDO::FETCH_ASSOC);

// HTML para Dompdf
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
<h2>Listado de Ventas</h2>
<table>
<thead>
<tr>
<th>ID</th>
<th>Cliente</th>
<th>Tipo</th>
<th>Fecha</th>
<th>Total</th>
</tr>
</thead>
<tbody>';

foreach($ventas as $v){
    $tipo_venta = $v['usuario'] ? 'Usuario registrado' : 'Invitado';
    $cliente = $v['usuario'] ?? $v['invitado'] ?? 'Desconocido';
    $html .= '<tr>
<td>'.$v['id'].'</td>
<td>'.htmlspecialchars($cliente).'</td>
<td>'.$tipo_venta.'</td>
<td>'.$v['fecha'].'</td>
<td>$'.number_format($v['total'],2).'</td>
</tr>';
}

$html .= '</tbody></table></body></html>';

$dompdf = new Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper('A4','portrait');
$dompdf->render();
$dompdf->stream("ventas_".date('Ymd_His').".pdf", ["Attachment" => false]);

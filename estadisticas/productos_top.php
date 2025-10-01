<?php
include '../includes/conexion.php';
$data = ['productos' => [], 'cantidades' => []];

$stmt = $conexion->query("
    SELECT p.estilo, SUM(dv.cantidad) as total_vendido
    FROM detalle_ventas dv
    JOIN variantes v ON dv.id_variante = v.id
    JOIN productos p ON v.id_producto = p.id
    GROUP BY p.id
    ORDER BY total_vendido DESC
    LIMIT 10
");

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $data['productos'][] = $row['estilo'];
    $data['cantidades'][] = (int)$row['total_vendido'];
}

header('Content-Type: application/json');
echo json_encode($data);

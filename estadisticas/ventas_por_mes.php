<?php
include '../includes/conexion.php';

$data = ['meses' => [], 'cantidades' => []];

$stmt = $conexion->query("
    SELECT DATE_FORMAT(fecha, '%Y-%m') as mes, COUNT(*) as total
    FROM ventas
    GROUP BY mes
    ORDER BY mes ASC
");

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $data['meses'][] = $row['mes'];
    $data['cantidades'][] = (int)$row['total'];
}

header('Content-Type: application/json');
echo json_encode($data);

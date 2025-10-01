<?php
include '../includes/conexion.php';
$data = ['productos_mas' => [], 'stock_mas' => [], 'productos_menos' => [], 'stock_menos' => []];

// Mayor stock
$stmt = $conexion->query("
    SELECT p.estilo, SUM(v.stock) as total_stock
    FROM variantes v
    JOIN productos p ON v.id_producto = p.id
    GROUP BY p.id
    ORDER BY total_stock DESC
    LIMIT 5
");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $data['productos_mas'][] = $row['estilo'];
    $data['stock_mas'][] = (int)$row['total_stock'];
}

// Menor stock
$stmt = $conexion->query("
    SELECT p.estilo, SUM(v.stock) as total_stock
    FROM variantes v
    JOIN productos p ON v.id_producto = p.id
    GROUP BY p.id
    ORDER BY total_stock ASC
    LIMIT 5
");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $data['productos_menos'][] = $row['estilo'];
    $data['stock_menos'][] = (int)$row['total_stock'];
}

header('Content-Type: application/json');
echo json_encode($data);

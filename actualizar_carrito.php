<?php
session_start();
require_once "conexion.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_variante = intval($_POST['id_variante']);
    $cantidad = intval($_POST['cantidad']);

    # Verificar stock
    $stmt = $conn->prepare("SELECT stock FROM variantes_productos WHERE id = ?");
    $stmt->bind_param("i", $id_variante);
    $stmt->execute();
    $res = $stmt->get_result();
    $row = $res->fetch_assoc();

    if ($row && $cantidad <= $row['stock']) {
        foreach ($_SESSION['carrito'] as &$item) {
            if ($item['id_variante'] == $id_variante) {
                $item['cantidad'] = $cantidad;
            }
        }
        echo json_encode(["success" => true]);
    } else {
        echo json_encode(["success" => false]);
    }
}

<?php
include '../includes/conexion.php';

$data = ['tipos' => ['Usuarios','Invitados'], 'cantidades' => []];

$stmtUsuarios = $conexion->query("SELECT COUNT(*) as total FROM usuarios");
$stmtInvitados = $conexion->query("SELECT COUNT(*) as total FROM invitados");

$data['cantidades'][] = (int)$stmtUsuarios->fetch(PDO::FETCH_ASSOC)['total'];
$data['cantidades'][] = (int)$stmtInvitados->fetch(PDO::FETCH_ASSOC)['total'];

header('Content-Type: application/json');
echo json_encode($data);

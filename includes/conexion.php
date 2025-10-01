<?php
// includes/conexion.php
$host = '127.0.0.1';
$db   = 'branyey_bd';
$user = 'root';
$pass = ''; // tu password si aplica
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // importante
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $conexion = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    error_log('DB ERROR: '.$e->getMessage());
    die('Error de conexión a la base de datos.');
}

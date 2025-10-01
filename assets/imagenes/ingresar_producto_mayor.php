<?php
include 'conexion.php'; // Conectar a la base de datos

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $estilo = $_POST['estilo'];
    $precio = $_POST['precio'];
    $stock = $_POST['stock'];
    $imagen = $_POST['imagen'];

    $sql = "INSERT INTO PRODUCTOS_POR_MAYOR (ESTILO, PRECIO, STOCK, IMAGEN) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sdss", $estilo, $precio, $stock, $imagen);

    if ($stmt->execute()) {
        echo "Producto por mayor ingresado exitosamente.";
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
}
$conn->close();
?>

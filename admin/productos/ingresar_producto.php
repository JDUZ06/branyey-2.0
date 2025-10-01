<?php
include '../../includes/conexion.php';
include '../../includes/header.php';

// Asegurarse de que la carpeta uploads exista
$uploadDir = '../../uploads/';
if(!is_dir($uploadDir)){
    mkdir($uploadDir, 0777, true); // Crea carpeta con permisos si no existe
}

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $estilo = $_POST['estilo'];
    $descripcion = $_POST['descripcion'];
    $precio_unidad = $_POST['precio_unidad'];
    $precio_mayor = $_POST['precio_mayor'];

    // Insertar producto
    $stmt = $conexion->prepare("INSERT INTO productos (estilo, descripcion, precio_unidad, precio_mayor) VALUES (:estilo, :descripcion, :precio_unidad, :precio_mayor)");
    $stmt->execute([
        ':estilo' => $estilo,
        ':descripcion' => $descripcion,
        ':precio_unidad' => $precio_unidad,
        ':precio_mayor' => $precio_mayor
    ]);
    $id_producto = $conexion->lastInsertId();

    // Variantes
    $colores = $_POST['color'] ?? [];
    $tallas = $_POST['talla'] ?? [];
    $stocks = $_POST['stock'] ?? [];

    foreach($colores as $i => $color){
        $talla = $tallas[$i] ?? null;
        $stock = $stocks[$i] ?? 0;

        if(!empty($color) && !empty($talla)){
            $stmt = $conexion->prepare("INSERT INTO variantes (id_producto, color, talla, stock) VALUES (:id_producto, :color, :talla, :stock)");
            $stmt->execute([
                ':id_producto' => $id_producto,
                ':color' => $color,
                ':talla' => $talla,
                ':stock' => $stock
            ]);

            // Registrar entrada en movimientos_stock
            $id_variante = $conexion->lastInsertId();
            if($stock > 0){
                $stmtMovimiento = $conexion->prepare("
                    INSERT INTO movimientos_stock (id_variante, tipo, cantidad, referencia)
                    VALUES (:id_variante, 'entrada', :cantidad, :referencia)
                ");
                $stmtMovimiento->execute([
                    ':id_variante' => $id_variante,
                    ':cantidad' => $stock,
                    ':referencia' => 'Ingreso inicial producto'
                ]);
            }
        }
    }

    // Imágenes
    if(isset($_FILES['imagenes'])){
        foreach($_FILES['imagenes']['tmp_name'] as $i => $tmpName){
            if(!empty($tmpName)){
                $nombreArchivo = time() . '_' . basename($_FILES['imagenes']['name'][$i]);
                if(move_uploaded_file($tmpName, $uploadDir . $nombreArchivo)){
                    $colorImagen = $_POST['color_imagen'][$i] ?? null;

                    $stmt = $conexion->prepare("INSERT INTO imagenes_producto (id_producto, url, color) VALUES (:id_producto, :url, :color)");
                    $stmt->execute([
                        ':id_producto' => $id_producto,
                        ':url' => $nombreArchivo,
                        ':color' => $colorImagen
                    ]);
                } else {
                    echo "<div class='alert alert-danger'>Error al subir la imagen: {$nombreArchivo}</div>";
                }
            }
        }
    }

    echo "<div class='alert alert-success'>Producto agregado correctamente.</div>";
}
?>

<div class="d-flex">
    <!-- Sidebar oscuro -->
    <nav id="sidebar" class="d-flex flex-column p-3 bg-dark text-white" style="min-width:220px; min-height:100vh;">
        <a href="../dashboard.php" class="d-flex align-items-center mb-3 mb-md-0 me-md-auto text-white text-decoration-none">
            <span class="fs-4">Branyey Admin</span>
        </a>
        <hr class="border-secondary">
        <ul class="nav nav-pills flex-column mb-auto">
            <li class="nav-item">
                <a href="../dashboard.php" class="nav-link text-white">
                    <i class="bi bi-speedometer2 me-2"></i> Dashboard
                </a>
            </li>
            <li>
                <a href="../productos.php" class="nav-link text-white active">
                    <i class="bi bi-box-seam me-2"></i> Gestion Productos
                </a>
            </li>
            <li>
                <a href="ventas.php" class="nav-link text-white">
                    <i class="bi bi-cart-check me-2"></i> Ventas
                </a>
            </li>
            <li>
                <a href="gestion_usuarios.php" class="nav-link text-white">
                    <i class="bi bi-people me-2"></i> Usuarios
                </a>
            </li>
            <li>
                <a href="reportes.php" class="nav-link text-white">
                    <i class="bi bi-file-earmark-pdf me-2"></i> Reportes
                </a>
            </li>
        </ul>
        <hr class="border-secondary">
        <div class="text-center small text-white">© 2025 Branyey</div>
    </nav>

    <!-- Contenido principal -->
    <div id="content" class="flex-fill p-4 bg-light">
        <h2>Ingresar Producto</h2>
        <form method="POST" enctype="multipart/form-data">
            <div class="mb-3">
                <label>Estilo:</label>
                <input type="text" name="estilo" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>Descripción:</label>
                <textarea name="descripcion" class="form-control"></textarea>
            </div>
            <div class="mb-3">
                <label>Precio unidad:</label>
                <input type="number" step="0.01" name="precio_unidad" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>Precio mayor:</label>
                <input type="number" step="0.01" name="precio_mayor" class="form-control" required>
            </div>

            <h4>Variantes</h4>
            <div id="variantes-container">
                <div class="variant-row mb-2">
                    <input type="text" name="color[]" placeholder="Color" class="form-control mb-1" required>
                    <input type="text" name="talla[]" placeholder="Talla" class="form-control mb-1" required>
                    <input type="number" name="stock[]" placeholder="Stock" class="form-control mb-1" required>
                </div>
            </div>
            <button type="button" class="btn btn-secondary mb-3" id="add-variant">Agregar Variante</button>

            <h4>Imágenes</h4>
            <div id="imagenes-container">
                <input type="file" name="imagenes[]" class="form-control mb-2" required>
                <input type="text" name="color_imagen[]" placeholder="Color de la imagen (opcional)" class="form-control mb-2">
            </div>
            <button type="button" class="btn btn-secondary mb-3" id="add-image">Agregar Imagen</button>

            <button type="submit" class="btn btn-primary">Guardar Producto</button>
            <a href="../productos.php" class="btn btn-danger">Volver</a>

        </form>
    </div>
</div>

<script>
// Añadir variantes dinámicamente
document.getElementById('add-variant').addEventListener('click', function(){
    const container = document.getElementById('variantes-container');
    const div = document.createElement('div');
    div.classList.add('variant-row','mb-2');
    div.innerHTML = `
        <input type="text" name="color[]" placeholder="Color" class="form-control mb-1" required>
        <input type="text" name="talla[]" placeholder="Talla" class="form-control mb-1" required>
        <input type="number" name="stock[]" placeholder="Stock" class="form-control mb-1" required>
    `;
    container.appendChild(div);
});

// Añadir imágenes dinámicamente
document.getElementById('add-image').addEventListener('click', function(){
    const container = document.getElementById('imagenes-container');
    const inputFile = document.createElement('input');
    inputFile.type = 'file';
    inputFile.name = 'imagenes[]';
    inputFile.classList.add('form-control','mb-2');

    const inputColor = document.createElement('input');
    inputColor.type = 'text';
    inputColor.name = 'color_imagen[]';
    inputColor.placeholder = 'Color de la imagen (opcional)';
    inputColor.classList.add('form-control','mb-2');

    container.appendChild(inputFile);
    container.appendChild(inputColor);
});
</script>

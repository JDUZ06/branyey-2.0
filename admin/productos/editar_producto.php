<?php
include '../../includes/conexion.php';
include '../../includes/header.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if($id == 0) die("ID de producto no válido");

// Para marcar la página activa en el sidebar
$current_page = basename($_SERVER['PHP_SELF']);

// Eliminar imagen
if(isset($_GET['eliminar_img'])){
    $id_img = intval($_GET['eliminar_img']);
    $stmt = $conexion->prepare("SELECT url FROM imagenes_producto WHERE id=?");
    $stmt->execute([$id_img]);
    $img = $stmt->fetch(PDO::FETCH_ASSOC);

    if($img){
        $ruta = "../../uploads/" . $img['url']; 
        if(file_exists($ruta)) unlink($ruta);

        $stmt = $conexion->prepare("DELETE FROM imagenes_producto WHERE id=?");
        $stmt->execute([$id_img]);

        header("Location: editar_producto.php?id=".$id);
        exit;
    }
}

// Producto
$stmt = $conexion->prepare("SELECT * FROM productos WHERE id=?");
$stmt->execute([$id]);
$producto = $stmt->fetch(PDO::FETCH_ASSOC);
if(!$producto) die("Producto no encontrado");

// Variantes
$stmt = $conexion->prepare("SELECT * FROM variantes WHERE id_producto=?");
$stmt->execute([$id]);
$variantes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Imágenes
$stmt = $conexion->prepare("SELECT * FROM imagenes_producto WHERE id_producto=?");
$stmt->execute([$id]);
$imagenes = $stmt->fetchAll(PDO::FETCH_ASSOC);

if($_POST){
    $estilo = $_POST['estilo'];
    $descripcion = $_POST['descripcion'];
    $precio_unidad = $_POST['precio_unidad'];
    $precio_mayor = $_POST['precio_mayor'];

    $stmt = $conexion->prepare("UPDATE productos SET estilo=?, descripcion=?, precio_unidad=?, precio_mayor=? WHERE id=?");
    $stmt->execute([$estilo, $descripcion, $precio_unidad, $precio_mayor, $id]);

    // Variantes con registro de movimientos de stock
    if(isset($_POST['id_variante'])){
        for($i=0; $i<count($_POST['id_variante']); $i++){
            $var_id = intval($_POST['id_variante'][$i]);
            $color = $_POST['color'][$i];
            $talla = $_POST['talla'][$i];
            $stock_nuevo = intval($_POST['stock'][$i]);

            if($var_id === 0){
                // Insertar variante nueva
                $stmt = $conexion->prepare("INSERT INTO variantes (id_producto,color,talla,stock) VALUES (?,?,?,?)");
                $stmt->execute([$id, $color, $talla, $stock_nuevo]);
                $id_var = $conexion->lastInsertId();

                // Registrar entrada
                $stmtMov = $conexion->prepare("INSERT INTO movimientos_stock (id_variante,tipo,cantidad,referencia) VALUES (?,?,?,?)");
                $stmtMov->execute([$id_var,'entrada',$stock_nuevo,'Ingreso inicial al crear variante']);
            } else {
                // Obtener stock actual
                $stmtStock = $conexion->prepare("SELECT stock FROM variantes WHERE id=?");
                $stmtStock->execute([$var_id]);
                $stock_actual = $stmtStock->fetchColumn();

                // Actualizar variante
                $stmt = $conexion->prepare("UPDATE variantes SET color=?, talla=?, stock=? WHERE id=?");
                $stmt->execute([$color, $talla, $stock_nuevo, $var_id]);

                // Registrar diferencia en movimientos
                $diferencia = $stock_nuevo - $stock_actual;
                if($diferencia != 0){
                    $tipo = $diferencia > 0 ? 'entrada' : 'salida';
                    $cantidad = abs($diferencia);
                    $stmtMov = $conexion->prepare("INSERT INTO movimientos_stock (id_variante,tipo,cantidad,referencia) VALUES (?,?,?,?)");
                    $stmtMov->execute([$var_id,$tipo,$cantidad,'Ajuste de stock en edición']);
                }
            }
        }
    }

    // Imágenes nuevas
    if(isset($_FILES['imagenes'])){
        $files = $_FILES['imagenes'];
        $colores_imagen = $_POST['color_imagen'] ?? [];

        for($i=0;$i<count($files['name']);$i++){
            if($files['name'][$i] != ''){
                $nombre = uniqid() . '_' . basename($files['name'][$i]);
                $destino = "../../uploads/" . $nombre; 
                $colorImagen = $colores_imagen[$i] ?? null;

                if(move_uploaded_file($files['tmp_name'][$i], $destino)){
                    $stmt = $conexion->prepare("INSERT INTO imagenes_producto (id_producto,url,color) VALUES (?,?,?)");
                    $stmt->execute([$id,$nombre,$colorImagen]);
                }
            }
        }
    }

    echo "<p class='text-success'>Producto actualizado!</p>";
}
?>

<div class="d-flex">

    <!-- Sidebar oscuro -->
    <nav id="sidebar" class="d-flex flex-column p-3 bg-dark text-white" style="min-width: 220px; min-height: 100vh;">
        <a href="../dashboard.php" class="d-flex align-items-center mb-3 mb-md-0 me-md-auto text-white text-decoration-none">
            <span class="fs-4">Branyey Admin</span>
        </a>
        <hr class="border-secondary">
        <ul class="nav nav-pills flex-column mb-auto">
            <li class="nav-item">
                <a href="../dashboard.php" class="nav-link text-white <?= $current_page=='dashboard.php' ? 'active' : '' ?>">
                    <i class="bi bi-speedometer2 me-2"></i> Dashboard
                </a>
            </li>
            <li>
                <a href="../productos.php" class="nav-link text-white <?= in_array($current_page, ['productos.php','editar_producto.php','ingresar_producto.php']) ? 'active' : '' ?>">
                    <i class="bi bi-box-seam me-2"></i> Gestion Productos
                </a>
            </li>
            <li>
                <a href="../ventas.php" class="nav-link text-white <?= $current_page=='ventas.php' ? 'active' : '' ?>">
                    <i class="bi bi-cart-check me-2"></i> Ventas
                </a>
            </li>
            <li>
                <a href="../gestion_usuarios.php" class="nav-link text-white <?= $current_page=='gestion_usuarios.php' ? 'active' : '' ?>">
                    <i class="bi bi-people me-2"></i> Usuarios
                </a>
            </li>
            <li>
                <a href="../reportes.php" class="nav-link text-white <?= $current_page=='reportes.php' ? 'active' : '' ?>">
                    <i class="bi bi-file-earmark-pdf me-2"></i> Reportes
                </a>
            </li>
        </ul>
        <hr class="border-secondary">
        <div class="text-center small text-white">© 2025 Branyey</div>
    </nav>

    <!-- Main content -->
    <div id="content" class="flex-fill p-4 bg-light">
        <h2 class="mb-4">Editar Producto</h2>

        <form method="post" enctype="multipart/form-data">
            <input type="text" name="estilo" value="<?= htmlspecialchars($producto['estilo']) ?>" class="form-control mb-2" required>
            <textarea name="descripcion" class="form-control mb-2"><?= htmlspecialchars($producto['descripcion']) ?></textarea>
            <input type="number" step="0.01" name="precio_unidad" value="<?= $producto['precio_unidad'] ?>" class="form-control mb-2" required>
            <input type="number" step="0.01" name="precio_mayor" value="<?= $producto['precio_mayor'] ?>" class="form-control mb-2" required>

            <h4>Variantes</h4>
            <div id="variantes">
                <?php foreach($variantes as $v): ?>
                <div class="mb-2">
                    <input type="hidden" name="id_variante[]" value="<?= $v['id'] ?>">
                    <input type="text" name="color[]" value="<?= htmlspecialchars($v['color']) ?>" class="form-control mb-1" required>
                    <input type="text" name="talla[]" value="<?= htmlspecialchars($v['talla']) ?>" class="form-control mb-1" required>
                    <input type="number" name="stock[]" value="<?= $v['stock'] ?>" class="form-control mb-1" required>
                </div>
                <?php endforeach; ?>
            </div>
            <button type="button" class="btn btn-secondary mb-2" onclick="agregarVariante()">Agregar Variante</button>

            <h4>Imágenes actuales</h4>
            <div class="row mb-2">
                <?php foreach($imagenes as $img): ?>
                    <div class="col-md-2 mb-2 text-center">
                        <img src="../../uploads/<?= htmlspecialchars($img['url']) ?>" class="img-fluid mb-1" alt="">
                        <p>Color: <?= htmlspecialchars($img['color'] ?? 'N/A') ?></p>
                        <a href="?id=<?= $id ?>&eliminar_img=<?= $img['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('¿Eliminar esta imagen?')">Eliminar</a>
                    </div>
                <?php endforeach; ?>
            </div>

            <h4>Agregar nuevas imágenes</h4>
            <div id="imagenes-nuevas">
                <div class="mb-2">
                    <input type="file" name="imagenes[]" class="form-control mb-1">
                    <input type="text" name="color_imagen[]" placeholder="Color de la imagen (opcional)" class="form-control mb-1">
                </div>
            </div>
            <button type="button" class="btn btn-secondary mb-2" onclick="agregarImagen()">Agregar Imagen</button>

            <button type="submit" class="btn btn-success">Actualizar Producto</button>
            <a href="../productos.php" class="btn btn-danger">Volver</a>
       </form>
    </div>
</div>

<script>
function agregarVariante(){
    let div = document.createElement('div');
    div.classList.add('mb-2');
    div.innerHTML = `
        <input type="hidden" name="id_variante[]" value="0">
        <input type="text" name="color[]" placeholder="Color" class="form-control mb-1" required>
        <input type="text" name="talla[]" placeholder="Talla" class="form-control mb-1" required>
        <input type="number" name="stock[]" placeholder="Stock" class="form-control mb-1" required>
    `;
    document.getElementById('variantes').appendChild(div);
}

function agregarImagen(){
    let div = document.createElement('div');
    div.classList.add('mb-2');
    div.innerHTML = `
        <input type="file" name="imagenes[]" class="form-control mb-1">
        <input type="text" name="color_imagen[]" placeholder="Color de la imagen (opcional)" class="form-control mb-1">
    `;
    document.getElementById('imagenes-nuevas').appendChild(div);
}
</script>

<?php include '../../includes/footer.php'; ?>

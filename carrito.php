    <?php
    include 'includes/header.php';
    $id_usuario = $_SESSION['usuario_id'] ?? null;
    $rol_usuario = $_SESSION['rol'] ?? null; // 'mayorista' o 'minorista'
    ?>
    <div class="container mt-4">
        <h2>Carrito de Compras</h2>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Producto</th>
                    <th>Color</th>
                    <th>Talla</th>
                    <th>Cantidad</th>
                    <th>Precio Unitario</th>
                    <th>Subtotal</th>
                    <th>Acción</th>
                </tr>
            </thead>
            <tbody id="tabla-carrito"></tbody>
        </table>

        <h4>Total: <span id="total-carrito">$0.00</span></h4>

        <!-- Formulario de datos -->
        <?php if (!$id_usuario || $rol_usuario == 'comprador'): ?>
        <div id="form-envio" class="mb-3">
            <h5>Datos de envío</h5>
            <?php if(!$id_usuario): ?>
                <div class="mb-2"><input type="text" id="nombre" placeholder="Nombre completo" class="form-control" required></div>
                <div class="mb-2"><input type="text" id="telefono" placeholder="Teléfono" class="form-control" required></div>
                <div class="mb-2"><input type="text" id="cedula" placeholder="Número de cédula" class="form-control" required></div>
            <?php endif; ?>
            <div class="mb-2"><input type="text" id="direccion" placeholder="Dirección de envío" class="form-control" required></div>
        </div>
        <?php endif; ?>

        <!-- Botón Finalizar Compra -->
        <button type="button" id="btn-checkout" class="btn btn-success">Finalizar Compra</button>

        <!-- Contenedor de resumen y factura -->
        <div id="resumen-compra" class="mt-4"></div>
    </div>

    <script src="assets/js/carrito.js"></script>
    <script src="assets/js/checkout.js"></script>
    <?php include 'includes/footer.php'; ?>

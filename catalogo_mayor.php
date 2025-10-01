    <?php
    include 'includes/conexion.php';
    include 'includes/header.php';

    // Traer productos
    $stmt = $conexion->prepare("SELECT * FROM productos ORDER BY created_at DESC");
    $stmt->execute();
    $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    ?>

    <h2>Catálogo de Productos</h2>
    <div class="catalogo-grid">
    <?php foreach($productos as $p):
        $stmt_var = $conexion->prepare("SELECT * FROM variantes WHERE id_producto=? AND stock>0");
        $stmt_var->execute([$p['id']]);
        $variantes = $stmt_var->fetchAll(PDO::FETCH_ASSOC);
        if(count($variantes)==0) continue;

        $stmt_img = $conexion->prepare("SELECT * FROM imagenes_producto WHERE id_producto=?");
        $stmt_img->execute([$p['id']]);
        $imagenes = $stmt_img->fetchAll(PDO::FETCH_ASSOC);
    ?>
    <div class="producto-card" 
        data-id="<?= $p['id'] ?>" 
        data-estilo="<?= htmlspecialchars($p['estilo'], ENT_QUOTES) ?>" 
        data-precio="<?= $p['precio_mayor'] ?>" 
        data-variantes='<?= htmlspecialchars(json_encode($variantes), ENT_QUOTES) ?>'>
        <div class="card">
            <div id="carousel<?= $p['id'] ?>" class="carousel slide" data-bs-ride="carousel">
                <div class="carousel-inner">
                    <?php foreach($imagenes as $i => $img): ?>
                    <div class="carousel-item <?= $i==0?'active':'' ?>" data-color="<?= htmlspecialchars($img['color']) ?>">
                        <img src="uploads/<?= htmlspecialchars($img['url']) ?>" class="d-block w-100" style="height:400px; object-fit:cover;">
                        <?php if(!empty($img['color'])): ?>
                        <span style="position:absolute; top:10px; left:10px; background:rgba(0,0,0,0.6); color:#fff; padding:5px 10px; border-radius:5px; font-weight:bold;">
                            <?= htmlspecialchars($img['color']) ?>
                        </span>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php if(count($imagenes)>1): ?>
                <button class="carousel-control-prev" type="button" data-bs-target="#carousel<?= $p['id'] ?>" data-bs-slide="prev">
                    <span class="carousel-control-prev-icon bg-dark rounded-circle p-2"></span>
                </button>
                <button class="carousel-control-next" type="button" data-bs-target="#carousel<?= $p['id'] ?>" data-bs-slide="next">
                    <span class="carousel-control-next-icon bg-dark rounded-circle p-2"></span>
                </button>
                <?php endif; ?>
            </div>

            <div class="card-body">
                <h5 class="card-title"><?= htmlspecialchars($p['estilo']) ?></h5>
                <p class="card-text"><?= htmlspecialchars($p['descripcion']) ?></p>
                <p>Precio unidad: $<span class="precio"><?= $p['precio_mayor'] ?></span></p>

                <form class="add-to-cart-form" onsubmit="return false;">
                    <input type="number" name="cantidad" value="1" min="1" class="form-control mb-1" required>

                    <select name="color" class="form-select mb-1 color-select" required>
                        <option value="">Seleccione Color</option>
                        <?php 
                        $colores_unicos = array_unique(array_column($variantes,'color'));
                        foreach($colores_unicos as $c) echo "<option value='$c'>$c</option>";
                        ?>
                    </select>

                    <select name="talla" class="form-select mb-1 talla-select" required>
                        <option value="">Seleccione Talla</option>
                    </select>

                    <button type="button" class="btn btn-primary w-100 add-to-cart">Agregar al carrito</button>
                </form>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
    </div>

    <script src="assets/js/carrito.js"></script>
    <script>
    // Manejo de variantes y carrusel
    document.querySelectorAll('.producto-card').forEach(card=>{
        const variantes = JSON.parse(card.dataset.variantes);
        const estilo = card.dataset.estilo;
        const precio = parseFloat(card.dataset.precio);
        const colorSelect = card.querySelector('.color-select');
        const tallaSelect = card.querySelector('.talla-select');
        const cantidadInput = card.querySelector('input[name="cantidad"]');
        const carouselInner = card.querySelector('.carousel-inner');

        colorSelect.addEventListener('change', function(){
            const color = this.value;
            tallaSelect.innerHTML = '<option value="">Seleccione Talla</option>';
            variantes.filter(v=>v.color===color).forEach(v=>{
                const opt = document.createElement('option');
                opt.value = v.talla;
                opt.textContent = v.talla + ' (Stock: '+v.stock+')';
                tallaSelect.appendChild(opt);
            });
            carouselInner.querySelectorAll('.carousel-item').forEach(item=>{
                item.classList.toggle('active', item.dataset.color === color);
            });
            cantidadInput.value = 1;
        });

        tallaSelect.addEventListener('change', function(){
            const color = colorSelect.value;
            const talla = this.value;
            const variante = variantes.find(v=>v.color===color && v.talla===talla);
            cantidadInput.max = variante ? variante.stock : 1;
            cantidadInput.value = 1;
        });

        card.querySelector('.add-to-cart').addEventListener('click', function(){
            const color = colorSelect.value;
            const talla = tallaSelect.value;
            const cantidad = parseInt(cantidadInput.value);
            if(!color || !talla || cantidad<1){ alert('Seleccione color, talla y cantidad'); return; }

            const variante = variantes.find(v=>v.color===color && v.talla===talla);
            if(!variante){ alert('Variante no disponible'); return; }

            const producto = {
                id_variante: variante.id,
                id_producto: card.dataset.id,
                estilo: estilo,
                color: color,
                talla: talla,
                cantidad: cantidad,
                precio: precio
            };
            agregarAlCarrito(producto);
        });
    });
    </script>

    <?php include 'includes/footer.php'; ?>

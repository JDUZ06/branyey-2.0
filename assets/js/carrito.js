    // ---------------------------
    // Funciones del carrito
    // ---------------------------

    // Obtener el carrito desde sesión
    async function obtenerCarrito() {
        let res = await fetch("carrito_acciones.php", {
            method: "POST",
            body: new URLSearchParams({ accion: "obtener" })
        });
        let data = await res.json();
        return data.carrito || [];
    }

    // Agregar producto al carrito
    async function agregarAlCarrito(producto) {
        let res = await fetch("carrito_acciones.php", {
            method: "POST",
            body: new URLSearchParams({ ...producto, accion: "agregar" })
        });
        let data = await res.json();
        if (data.success) {
            alert("Producto agregado al carrito!");
            mostrarCarrito();
            actualizarContador();
        }
    }

    // Mostrar carrito en tabla
    async function mostrarCarrito() {
        let carrito = await obtenerCarrito();
        let tabla = document.getElementById("tabla-carrito");
        if (!tabla) return;

        tabla.innerHTML = "";
        let total = 0;

        carrito.forEach((item) => {
            let subtotal = parseFloat(item.precio) * parseInt(item.cantidad);
            total += subtotal;

            let row = document.createElement("tr");
            row.innerHTML = `
                <td>${item.estilo}</td>
                <td>${item.color}</td>
                <td>${item.talla}</td>
                <td>${item.cantidad}</td>
                <td>$${parseFloat(item.precio).toFixed(2)}</td>
                <td>$${subtotal.toFixed(2)}</td>
                <td><button class="btn btn-danger btn-sm" onclick="eliminarDelCarrito(${item.id_variante})">Eliminar</button></td>
            `;
            tabla.appendChild(row);
        });

        let totalSpan = document.getElementById("total-carrito");
        if (totalSpan) totalSpan.textContent = "$" + total.toFixed(2);
    }

    // Eliminar producto del carrito
    async function eliminarDelCarrito(id_variante) {
        let res = await fetch("carrito_acciones.php", {
            method: "POST",
            body: new URLSearchParams({ accion: "eliminar", id_variante })
        });
        let data = await res.json();
        if (data.success) {
            mostrarCarrito();
            actualizarContador();
        }
    }

    // Actualizar contador de carrito en header
    async function actualizarContador() {
        let carrito = await obtenerCarrito();
        const contador = document.getElementById("contador-carrito");
        if (contador) contador.textContent = carrito.length;
    }

    // ---------------------------
    // Lógica del catálogo
    // ---------------------------
    document.addEventListener("DOMContentLoaded", function () {
        mostrarCarrito();
        actualizarContador();

        document.querySelectorAll('.producto-card').forEach(card => {
            const variantes = JSON.parse(card.dataset.variantes);
            const estilo = card.dataset.estilo;
            const precio = parseFloat(card.dataset.precio);
            const colorSelect = card.querySelector('.color-select');
            const tallaSelect = card.querySelector('.talla-select');
            const cantidadInput = card.querySelector('input[name="cantidad"]');
            const carouselInner = card.querySelector('.carousel-inner');
            const addBtn = card.querySelector('.add-to-cart');

            // Limpiar listeners previos
            const newBtn = addBtn.cloneNode(true);
            addBtn.parentNode.replaceChild(newBtn, addBtn);

            // Al cambiar color
            colorSelect.addEventListener('change', function() {
                const color = this.value;
                tallaSelect.innerHTML = '<option value="">Seleccione Talla</option>';
                variantes.filter(v => v.color === color).forEach(v => {
                    const opt = document.createElement('option');
                    opt.value = v.talla;
                    opt.textContent = v.talla + ' (Stock: ' + v.stock + ')';
                    tallaSelect.appendChild(opt);
                });
                carouselInner.querySelectorAll('.carousel-item').forEach(item => {
                    item.classList.toggle('active', item.dataset.color === color);
                });
                cantidadInput.value = 1;
                cantidadInput.max = variantes.filter(v => v.color === color)
                                            .reduce((max, v) => parseInt(v.stock,10) > max ? parseInt(v.stock,10) : max, 1);
            });

            // Al cambiar talla
            tallaSelect.addEventListener('change', function() {
                const color = colorSelect.value;
                const talla = this.value;
                const variante = variantes.find(v => v.color === color && v.talla === talla);
                cantidadInput.max = variante ? parseInt(variante.stock,10) : 1;
                cantidadInput.value = 1;
            });

            // Agregar al carrito
            newBtn.addEventListener('click', function() {
                const color = colorSelect.value;
                const talla = tallaSelect.value;
                const cantidad = parseInt(cantidadInput.value, 10);

                if (!color || !talla || cantidad < 1) {
                    alert('Seleccione color, talla y cantidad');
                    return;
                }

                const variante = variantes.find(v => v.color === color && v.talla === talla);
                if (!variante) {
                    alert('Variante no disponible');
                    return;
                }

                if (cantidad > parseInt(variante.stock, 10)) {
                    alert(`La cantidad supera el stock disponible (${variante.stock})`);
                    return;
                }

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
    });

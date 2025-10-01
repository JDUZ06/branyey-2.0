    document.addEventListener("DOMContentLoaded", () => {
        const btn = document.getElementById("btn-checkout");
        if(!btn) return;

        btn.addEventListener("click", async (e) => {
            e.preventDefault();

            let formData = new FormData();

            const formEnvio = document.getElementById("form-envio");
            if(formEnvio){
                const nombre = document.getElementById("nombre")?.value.trim();
                const telefono = document.getElementById("telefono")?.value.trim();
                const direccion = document.getElementById("direccion")?.value.trim();
                const cedula = document.getElementById("cedula")?.value.trim();

                // Validar campos obligatorios
                if(!direccion || (!nombre && document.getElementById("nombre")) || (!telefono && document.getElementById("telefono")) || (!cedula && document.getElementById("cedula"))){
                    alert("Por favor complete todos los campos obligatorios del formulario.");
                    return;
                }

                if(nombre) formData.append("nombre", nombre);
                if(telefono) formData.append("telefono", telefono);
                if(direccion) formData.append("direccion", direccion);
                if(cedula) formData.append("cedula", cedula);
            }

            try {
                const res = await fetch("checkout.php", { method: "POST", body: formData });
                const data = await res.json();

                alert(data.message);

                if(data.success){
                    let contenedor = document.getElementById("resumen-compra");
                    if(!contenedor){
                        contenedor = document.createElement("div");
                        contenedor.id = "resumen-compra";
                        contenedor.className = "mt-3";
                        document.querySelector(".container").appendChild(contenedor);
                    }

                    let html = `<h4>Resumen de la compra</h4>
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Producto</th>
                                    <th>Color</th>
                                    <th>Talla</th>
                                    <th>Cantidad</th>
                                    <th>Precio</th>
                                </tr>
                            </thead>
                            <tbody>`;

                    data.productos.forEach(p=>{
                        html += `<tr>
                            <td>${p.estilo}</td>
                            <td>${p.color}</td>
                            <td>${p.talla}</td>
                            <td>${p.cantidad}</td>
                            <td>$${p.precio.toFixed(2)}</td>
                        </tr>`;
                    });

                    html += `</tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="4" style="text-align:right;"><strong>Total</strong></td>
                                    <td><strong>$${data.total.toFixed(2)}</strong></td>
                                </tr>
                            </tfoot>
                        </table>`;

                    html += `<a href="factura_cliente.php?id=${data.id_venta}" target="_blank" class="btn btn-success">
                                Descargar factura
                            </a>`;

                    contenedor.innerHTML = html;

                    if(typeof mostrarCarrito === 'function') mostrarCarrito();
                    if(typeof actualizarContador === 'function') actualizarContador();
                }

            } catch(error){
                console.error("Error en checkout:", error);
                alert("Ocurrió un error al procesar la compra.");
            }
        });
    });

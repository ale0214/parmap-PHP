<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: index.php");
    exit();
}
if ($_SESSION['rol'] != 'administrador') {
    header("Location: tienda.php");
    exit();
}
?>
include 'conexion.php';

// Obtener todas las ventas con su total
$ventas = mysqli_query($conexion, "SELECT v.*, COALESCE(u.nombre, 'Cliente invitado') as cliente 
    FROM ventas v 
    LEFT JOIN usuarios u ON v.id_usuario = u.id 
    ORDER BY v.fecha DESC");

// Obtener productos para el formulario
$productos = mysqli_query($conexion, "SELECT * FROM productos WHERE stock > 0");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>PARMAP - Ventas</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background-color: #f5f5f5; display: flex; }
        .sidebar { width: 200px; min-width: 200px; background-color: #E87722; min-height: 100vh; padding: 20px 0; }
        .sidebar .logo { text-align: center; margin-bottom: 30px; }
        .sidebar .logo img { width: 80px; }
        .sidebar ul { list-style: none; }
        .sidebar ul li a { display: block; padding: 12px 20px; color: white; text-decoration: none; font-size: 14px; }
        .sidebar ul li a:hover { background-color: #1a1a1a; }
        .contenido { flex: 1; padding: 30px; }
        .encabezado { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .encabezado h1 { color: #333; }
        .btn-nueva { background-color: #E87722; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; font-size: 14px; text-decoration: none; }
        .btn-nueva:hover { background-color: #1a1a1a; }
        .tabla-section { background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        table { width: 100%; border-collapse: collapse; }
        th { background-color: #E87722; color: white; padding: 10px; text-align: left; }
        td { padding: 10px; border-bottom: 1px solid #eee; }
        tr:hover { background-color: #fff8f3; }
        .modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); justify-content: center; align-items: center; z-index: 999; }
        .modal.activo { display: flex; }
        .modal-contenido { background: white; padding: 30px; border-radius: 10px; width: 500px; max-height: 90vh; overflow-y: auto; }
        .modal-contenido h2 { margin-bottom: 20px; color: #333; }
        select, input { width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 5px; font-size: 14px; }
        .fila-producto { display: flex; gap: 10px; align-items: center; margin-bottom: 10px; }
        .fila-producto select { flex: 2; margin: 0; }
        .fila-producto input { flex: 1; margin: 0; }
        .fila-producto button { background: #e74c3c; color: white; border: none; border-radius: 5px; padding: 10px; cursor: pointer; }
        .btn-add-producto { background: #27ae60; color: white; border: none; border-radius: 5px; padding: 10px 15px; cursor: pointer; margin-bottom: 15px; width: 100%; }
        .total-box { background: #fff8f3; border: 2px solid #E87722; border-radius: 5px; padding: 15px; margin-bottom: 15px; text-align: right; font-size: 18px; font-weight: bold; color: #E87722; }
        .modal-botones { display: flex; gap: 10px; }
        .btn-guardar { background-color: #E87722; color: white; padding: 12px; border: none; border-radius: 5px; cursor: pointer; flex: 1; font-size: 15px; }
        .btn-cancelar { background-color: #999; color: white; padding: 12px; border: none; border-radius: 5px; cursor: pointer; flex: 1; font-size: 15px; }
        .btn-ver { background-color: #3498db; color: white; padding: 5px 10px; border: none; border-radius: 3px; cursor: pointer; font-size: 12px; text-decoration: none; }
    </style>
</head>
<body>

    <div class="sidebar">
        <div class="logo"><img src="images/logo.png" alt="PARMAP"></div>
        <ul>
            <li><a href="dashboard.php">🏠 Home</a></li>
            <li><a href="productos.php">📦 Producto</a></li>
            <li><a href="inventario.php">📊 Inventario</a></li>
            <li><a href="ventas.php">💰 Ventas</a></li>
            <li><a href="pedidos.php">🛒 Pedidos</a></li>
            <li><a href="envios.php">🚚 Envíos</a></li>
            <li><a href="usuarios.php">👥 Manage User</a></li>
            <li><a href="configuracion.php">⚙️ Configuración</a></li>
            <li><a href="logout.php">🚪 Log Out</a></li>
        </ul>
    </div>

    <div class="contenido">
        <div class="encabezado">
            <h1>💰 Ventas</h1>
            <a href="#" class="btn-nueva" onclick="abrirModal()">+ Nueva Venta</a>
        </div>

        <div class="tabla-section">
            <table>
                <tr>
                    <th>ID Venta</th>
                    <th>Fecha</th>
                    <th>Cliente</th>
                    <th>Total</th>
                    <th>Acción</th>
                </tr>
                <?php if (mysqli_num_rows($ventas) > 0): ?>
                    <?php while ($venta = mysqli_fetch_assoc($ventas)): ?>
                    <tr>
                        <td>#<?php echo $venta['id']; ?></td>
                        <td><?php echo date('d/m/Y H:i', strtotime($venta['fecha'])); ?></td>
                        <td><?php echo htmlspecialchars($venta['cliente']); ?></td>
                        <td>$<?php echo number_format($venta['total'], 0, ',', '.'); ?></td>
                        <td><a href="detalle_venta.php?id=<?php echo $venta['id']; ?>" class="btn-ver">👁️ Ver detalle</a></td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="5" style="text-align:center; color:#999; padding:20px;">No hay ventas registradas aún</td></tr>
                <?php endif; ?>
            </table>
        </div>
    </div>

    <!-- MODAL NUEVA VENTA -->
    <div class="modal" id="modal">
        <div class="modal-contenido">
            <h2>💰 Nueva Venta</h2>
            <form method="POST" action="guardar_venta.php" id="formVenta">
                
                <div id="productos-container">
                    <div class="fila-producto">
                        <select name="productos[]" onchange="calcularTotal()">
                            <option value="">Seleccionar producto</option>
                            <?php 
                            mysqli_data_seek($productos, 0);
                            while ($p = mysqli_fetch_assoc($productos)): ?>
                                <option value="<?php echo $p['id']; ?>" 
                                    data-precio="<?php echo $p['precio']; ?>"
                                    data-stock="<?php echo $p['stock']; ?>">
                                    <?php echo $p['nombre']; ?> - Stock: <?php echo $p['stock']; ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                        <input type="number" name="cantidades[]" placeholder="Cant." min="1" value="1" onchange="calcularTotal()">
                    </div>
                </div>

                <button type="button" class="btn-add-producto" onclick="agregarProducto()">+ Agregar otro producto</button>

                <div class="total-box">
                    Total: $<span id="total">0</span>
                </div>

                <input type="hidden" name="total" id="totalHidden">

                <div class="modal-botones">
                    <button type="submit" class="btn-guardar">Registrar Venta</button>
                    <button type="button" class="btn-cancelar" onclick="cerrarModal()">Cancelar</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Productos disponibles con sus precios
        const productosData = {
            <?php 
            mysqli_data_seek($productos, 0);
            while ($p = mysqli_fetch_assoc($productos)) {
                echo $p['id'] . ": { precio: " . $p['precio'] . ", stock: " . $p['stock'] . " },";
            }
            ?>
        };

        function abrirModal() {
            document.getElementById('modal').classList.add('activo');
        }

        function cerrarModal() {
            document.getElementById('modal').classList.remove('activo');
        }

        function calcularTotal() {
            let total = 0;
            const filas = document.querySelectorAll('.fila-producto');
            filas.forEach(fila => {
                const select = fila.querySelector('select');
                const cantidad = fila.querySelector('input');
                const id = select.value;
                if (id && productosData[id]) {
                    total += productosData[id].precio * parseInt(cantidad.value || 0);
                }
            });
            document.getElementById('total').textContent = total.toLocaleString('es-CO');
            document.getElementById('totalHidden').value = total;
        }

        function agregarProducto() {
            const container = document.getElementById('productos-container');
            const primeraFila = container.querySelector('.fila-producto');
            const nuevaFila = primeraFila.cloneNode(true);
            nuevaFila.querySelector('input').value = 1;
            nuevaFila.querySelector('select').value = '';
            
            // Agregar boton eliminar fila
            const btnEliminar = document.createElement('button');
            btnEliminar.type = 'button';
            btnEliminar.textContent = '✕';
            btnEliminar.onclick = function() { 
                this.parentElement.remove(); 
                calcularTotal(); 
            };
            nuevaFila.appendChild(btnEliminar);
            container.appendChild(nuevaFila);
        }
    </script>

</body>
</html>
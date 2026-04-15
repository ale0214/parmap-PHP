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

include 'conexion.php';



// Contar total de productos
$total_productos = mysqli_fetch_assoc(mysqli_query($conexion, "SELECT COUNT(*) as total FROM productos"))['total'];

// Sumar el stock total
$stock_total = mysqli_fetch_assoc(mysqli_query($conexion, "SELECT SUM(stock) as total FROM productos"))['total'];

// Contar productos con stock bajo (menos de 5)
$stock_bajo = mysqli_fetch_assoc(mysqli_query($conexion, "SELECT COUNT(*) as total FROM productos WHERE stock <= 5"))['total'];

// Obtener los ultimos 5 productos agregados
$ultimos = mysqli_query($conexion, "SELECT * FROM productos ORDER BY id DESC LIMIT 5");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>PARMAP - Dashboard</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background-color: #f5f5f5; display: flex; }
        
        .sidebar {
            width: 200px;
            min-width: 200px;
            background-color: #E87722;
            min-height: 100vh;
            padding: 20px 0;
        }
        .sidebar .logo { text-align: center; margin-bottom: 30px; }
        .sidebar .logo img { width: 80px; }
        .sidebar ul { list-style: none; }
        .sidebar ul li a {
            display: block;
            padding: 12px 20px;
            color: white;
            text-decoration: none;
            font-size: 14px;
        }
        .sidebar ul li a:hover { background-color: #1a1a1a; }

        .contenido { flex: 1; padding: 30px; }

        .bienvenida { margin-bottom: 30px; }
        .bienvenida h1 { color: #333; font-size: 24px; }
        .bienvenida p { color: #999; font-size: 14px; }

        /* TARJETAS */
        .tarjetas {
            display: flex;
            gap: 20px;
            margin-bottom: 30px;
        }

        .tarjeta {
            background: white;
            padding: 25px;
            border-radius: 10px;
            flex: 1;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            border-left: 4px solid #E87722;
        }

        .tarjeta h3 { font-size: 13px; color: #999; margin-bottom: 10px; text-transform: uppercase; }
        .tarjeta .numero { font-size: 32px; font-weight: bold; color: #333; }
        .tarjeta .icono { font-size: 30px; float: right; margin-top: -35px; }
        .tarjeta.alerta { border-left-color: #e74c3c; }
        .tarjeta.alerta .numero { color: #e74c3c; }

        /* TABLA */
        .tabla-section {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        .tabla-section h2 { 
            color: #333; 
            margin-bottom: 20px;
            font-size: 16px;
            border-bottom: 2px solid #E87722;
            padding-bottom: 10px;
        }

        table { width: 100%; border-collapse: collapse; }
        th { background-color: #E87722; color: white; padding: 10px; text-align: left; font-size: 13px; }
        td { padding: 10px; border-bottom: 1px solid #eee; font-size: 13px; }
        tr:hover { background-color: #fff8f3; }

        .badge-bajo { background-color: #e74c3c; color: white; padding: 3px 8px; border-radius: 10px; font-size: 11px; }
        .badge-ok { background-color: #27ae60; color: white; padding: 3px 8px; border-radius: 10px; font-size: 11px; }

        .ver-todos {
            display: block;
            text-align: right;
            margin-top: 15px;
            color: #E87722;
            text-decoration: none;
            font-size: 13px;
        }
    </style>
</head>
<body>

    <div class="sidebar">
        <div class="logo"><img src="images/logo.png" alt="PARMAP"></div>
        <ul>
            <li><a href="dashboard.php">🏠 Home</a></li>
            <li><a href="productos.php">📦 Producto</a></li>
            <li><a href="inventario.php">📊 Inventario</a></li>
            <li><a href="pedidos.php">🛒 Pedidos</a></li>
            <li><a href="ventas.php">💰 Pagos</a></li>
            <li><a href="envios.php">🚚 Envíos</a></li>
            <li><a href="ventas.php">💰 Ventas</a></li>
            <li><a href="usuarios.php">👥 Manage User</a></li>
            <li><a href="configuracion.php">⚙️ Configuración</a></li>
            <li><a href="logout.php">🚪 Log Out</a></li>
        </ul>
    </div>

    <div class="contenido">
        <div class="bienvenida">
            <h1>Bienvenida, <?php echo $_SESSION['nombre']; ?> 👋</h1>
            <p><?php echo date('d \d\e F \d\e Y'); ?></p>
        </div>

        <!-- TARJETAS CON DATOS REALES -->
        <div class="tarjetas">
            <div class="tarjeta">
                <h3>Total Productos</h3>
                <div class="numero"><?php echo $total_productos; ?></div>
                <div class="icono">📦</div>
            </div>
            <div class="tarjeta">
                <h3>Unidades en Stock</h3>
                <div class="numero"><?php echo $stock_total ?? 0; ?></div>
                <div class="icono">🏪</div>
            </div>
            <div class="tarjeta alerta">
                <h3>Stock Bajo</h3>
                <div class="numero"><?php echo $stock_bajo; ?></div>
                <div class="icono">⚠️</div>
            </div>
            <div class="tarjeta">
                <h3>Categorías</h3>
                <div class="numero">
                    <?php 
                    $cats = mysqli_fetch_assoc(mysqli_query($conexion, "SELECT COUNT(DISTINCT categoria) as total FROM productos"))['total'];
                    echo $cats;
                    ?>
                </div>
                <div class="icono">🏷️</div>
            </div>
        </div>

        <!-- TABLA ULTIMOS PRODUCTOS -->
        <div class="tabla-section">
            <h2>📦 Últimos productos agregados</h2>
            <table>
                <tr>
                    <th>ID</th>
                    <th>Producto</th>
                    <th>Categoría</th>
                    <th>Precio</th>
                    <th>Stock</th>
                    <th>Estado</th>
                </tr>
                <?php while ($fila = mysqli_fetch_assoc($ultimos)): ?>
                <tr>
                    <td><?php echo $fila['id']; ?></td>
                    <td><?php echo $fila['nombre']; ?></td>
                    <td><?php echo $fila['categoria']; ?></td>
                    <td>$<?php echo number_format($fila['precio'], 0, ',', '.'); ?></td>
                    <td><?php echo $fila['stock']; ?></td>
                    <td>
                        <?php if ($fila['stock'] <= 5): ?>
                            <span class="badge-bajo">Stock Bajo</span>
                        <?php else: ?>
                            <span class="badge-ok">Disponible</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endwhile; ?>
            </table>
            <a href="productos.php" class="ver-todos">Ver todos los productos →</a>
        </div>
    </div>

</body>
</html>
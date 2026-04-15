<?php
session_start();
if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'administrador') {
    header("Location: index.php");
    exit();
}
include 'conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_pedido = isset($_POST['id_pedido']) ? (int) $_POST['id_pedido'] : 0;
    $estado = trim($_POST['estado'] ?? '');
    $estados_validos = ['Pendiente', 'Pagado', 'Preparando', 'Enviado', 'Entregado', 'Cancelado'];

    if ($id_pedido > 0 && in_array($estado, $estados_validos, true)) {
        $stmt = mysqli_prepare($conexion, "UPDATE ventas SET estado = ? WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "si", $estado, $id_pedido);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }

    header("Location: pedidos.php");
    exit();
}

$pedidos = mysqli_query($conexion, "SELECT v.*, COALESCE(u.nombre, 'Cliente invitado') as cliente
    FROM ventas v
    LEFT JOIN usuarios u ON v.id_usuario = u.id
    ORDER BY v.fecha DESC");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>PARMAP - Pedidos</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background-color: #f5f5f5; display: flex; }
        .sidebar { width: 200px; min-width: 200px; background-color: #E87722; min-height: 100vh; padding: 20px 0; }
        .sidebar .logo { text-align: center; margin-bottom: 30px; }
        .sidebar .logo img { width: 80px; }
        .sidebar ul { list-style: none; }
        .sidebar ul li a { display: block; padding: 12px 20px; color: white; text-decoration: none; font-size: 14px; }
        .sidebar ul li a:hover, .sidebar ul li a.activo { background-color: #1a1a1a; }
        .contenido { flex: 1; padding: 30px; }
        .encabezado { margin-bottom: 20px; }
        .encabezado h1 { color: #333; margin-bottom: 6px; }
        .encabezado p { color: #777; font-size: 14px; }
        .tabla-section { background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        table { width: 100%; border-collapse: collapse; }
        th { background-color: #E87722; color: white; padding: 10px; text-align: left; font-size: 13px; }
        td { padding: 10px; border-bottom: 1px solid #eee; font-size: 13px; vertical-align: middle; }
        tr:hover { background-color: #fff8f3; }
        .estado { padding: 4px 8px; border-radius: 999px; font-size: 12px; font-weight: bold; display: inline-block; }
        .estado.Pendiente { background: #fff3cd; color: #856404; }
        .estado.Pagado { background: #d1ecf1; color: #0c5460; }
        .estado.Preparando { background: #d4edda; color: #155724; }
        .estado.Enviado { background: #cce5ff; color: #004085; }
        .estado.Entregado { background: #d4edda; color: #155724; }
        .estado.Cancelado { background: #f8d7da; color: #721c24; }
        .acciones { display: flex; gap: 8px; align-items: center; flex-wrap: wrap; }
        select { padding: 7px 9px; border-radius: 5px; border: 1px solid #ccc; font-size: 12px; }
        button { background-color: #E87722; color: white; border: none; padding: 7px 10px; border-radius: 5px; cursor: pointer; font-size: 12px; }
        button:hover { background-color: #1a1a1a; }
        .btn-ver { background: #3498db; color: white; padding: 7px 10px; border-radius: 5px; text-decoration: none; font-size: 12px; }
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
            <li><a href="pedidos.php" class="activo">🛒 Pedidos</a></li>
            <li><a href="envios.php">🚚 Envíos</a></li>
            <li><a href="usuarios.php">👥 Manage User</a></li>
            <li><a href="configuracion.php">⚙️ Configuración</a></li>
            <li><a href="logout.php">🚪 Log Out</a></li>
        </ul>
    </div>

    <div class="contenido">
        <div class="encabezado">
            <h1>🛒 Gestión de Pedidos</h1>
            <p>Aquí puedes revisar pedidos y actualizar su estado general.</p>
        </div>

        <div class="tabla-section">
            <table>
                <tr>
                    <th>Pedido</th>
                    <th>Fecha</th>
                    <th>Cliente</th>
                    <th>Total</th>
                    <th>Estado</th>
                    <th>Acción</th>
                </tr>
                <?php if (mysqli_num_rows($pedidos) > 0): ?>
                    <?php while ($pedido = mysqli_fetch_assoc($pedidos)): ?>
                    <?php $estado = $pedido['estado'] ?: 'Pendiente'; ?>
                    <tr>
                        <td>#<?php echo $pedido['id']; ?></td>
                        <td><?php echo date('d/m/Y H:i', strtotime($pedido['fecha'])); ?></td>
                        <td><?php echo htmlspecialchars($pedido['cliente']); ?></td>
                        <td>$<?php echo number_format($pedido['total'], 0, ',', '.'); ?></td>
                        <td><span class="estado <?php echo str_replace(' ', '', $estado); ?>"><?php echo htmlspecialchars($estado); ?></span></td>
                        <td>
                            <div class="acciones">
                                <a href="detalle_venta.php?id=<?php echo $pedido['id']; ?>&origen=pedidos" class="btn-ver">Ver detalle</a>
                                <form method="POST" style="display:flex; gap:8px; align-items:center;">
                                    <input type="hidden" name="id_pedido" value="<?php echo $pedido['id']; ?>">
                                    <select name="estado">
                                        <?php foreach (['Pendiente', 'Pagado', 'Preparando', 'Enviado', 'Entregado', 'Cancelado'] as $opcion): ?>
                                            <option value="<?php echo $opcion; ?>" <?php echo $estado === $opcion ? 'selected' : ''; ?>>
                                                <?php echo $opcion; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <button type="submit">Guardar</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="6" style="text-align:center; color:#999; padding:20px;">No hay pedidos registrados.</td></tr>
                <?php endif; ?>
            </table>
        </div>
    </div>
</body>
</html>
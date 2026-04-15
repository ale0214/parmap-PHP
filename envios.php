<?php
session_start();
if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'administrador') {
    header("Location: index.php");
    exit();
}
include 'conexion.php';

$envios = mysqli_query($conexion, "SELECT v.*, COALESCE(u.nombre, 'Cliente invitado') as cliente
    FROM ventas v
    LEFT JOIN usuarios u ON v.id_usuario = u.id
    ORDER BY v.fecha DESC");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>PARMAP - Envíos</title>
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
        .panel { display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; margin-bottom: 24px; }
        .card { background: white; border-radius: 10px; padding: 20px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .card h3 { color: #777; font-size: 13px; margin-bottom: 10px; text-transform: uppercase; }
        .card strong { color: #333; font-size: 28px; }
        .tabla-section { background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        table { width: 100%; border-collapse: collapse; }
        th { background-color: #E87722; color: white; padding: 10px; text-align: left; font-size: 13px; }
        td { padding: 10px; border-bottom: 1px solid #eee; font-size: 13px; }
        .etiqueta { padding: 4px 8px; border-radius: 999px; font-size: 12px; font-weight: bold; }
        .Pendiente, .Pagado { background: #fff3cd; color: #856404; }
        .Preparando { background: #d1ecf1; color: #0c5460; }
        .Enviado { background: #cce5ff; color: #004085; }
        .Entregado { background: #d4edda; color: #155724; }
        .Cancelado { background: #f8d7da; color: #721c24; }
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
            <li><a href="envios.php" class="activo">🚚 Envíos</a></li>
            <li><a href="usuarios.php">👥 Manage User</a></li>
            <li><a href="configuracion.php">⚙️ Configuración</a></li>
            <li><a href="logout.php">🚪 Log Out</a></li>
        </ul>
    </div>

    <div class="contenido">
        <div class="panel">
            <div class="card">
                <h3>Por Preparar</h3>
                <strong><?php echo mysqli_num_rows(mysqli_query($conexion, "SELECT id FROM ventas WHERE estado IN ('Pendiente', 'Pagado', 'Preparando')")); ?></strong>
            </div>
            <div class="card">
                <h3>En Camino</h3>
                <strong><?php echo mysqli_num_rows(mysqli_query($conexion, "SELECT id FROM ventas WHERE estado = 'Enviado'")); ?></strong>
            </div>
            <div class="card">
                <h3>Entregados</h3>
                <strong><?php echo mysqli_num_rows(mysqli_query($conexion, "SELECT id FROM ventas WHERE estado = 'Entregado'")); ?></strong>
            </div>
        </div>

        <div class="tabla-section">
            <table>
                <tr>
                    <th>Pedido</th>
                    <th>Cliente</th>
                    <th>Fecha</th>
                    <th>Total</th>
                    <th>Estado de envío</th>
                </tr>
                <?php if (mysqli_num_rows($envios) > 0): ?>
                    <?php while ($envio = mysqli_fetch_assoc($envios)): ?>
                    <?php $estado = $envio['estado'] ?: 'Pendiente'; ?>
                    <tr>
                        <td>#<?php echo $envio['id']; ?></td>
                        <td><?php echo htmlspecialchars($envio['cliente']); ?></td>
                        <td><?php echo date('d/m/Y H:i', strtotime($envio['fecha'])); ?></td>
                        <td>$<?php echo number_format($envio['total'], 0, ',', '.'); ?></td>
                        <td><span class="etiqueta <?php echo str_replace(' ', '', $estado); ?>"><?php echo htmlspecialchars($estado); ?></span></td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="5" style="text-align:center; color:#999; padding:20px;">No hay envíos registrados.</td></tr>
                <?php endif; ?>
            </table>
        </div>
    </div>
</body>
</html>
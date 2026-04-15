<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: index.php");
    exit();
}
include 'conexion.php';

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$es_admin = ($_SESSION['rol'] ?? '') === 'administrador';
$id_usuario_sesion = isset($_SESSION['id_usuario']) ? (int) $_SESSION['id_usuario'] : 0;
$origen = $_GET['origen'] ?? '';

if ($id <= 0) {
    header("Location: " . ($es_admin ? "ventas.php" : "mis_pedidos.php"));
    exit();
}

$sql_venta = "SELECT v.*, COALESCE(u.nombre, 'Cliente invitado') as cliente
    FROM ventas v
    LEFT JOIN usuarios u ON v.id_usuario = u.id
    WHERE v.id = ?";

if (!$es_admin) {
    $sql_venta .= " AND v.id_usuario = ?";
}

$stmt_venta = mysqli_prepare($conexion, $sql_venta);

if ($es_admin) {
    mysqli_stmt_bind_param($stmt_venta, "i", $id);
} else {
    mysqli_stmt_bind_param($stmt_venta, "ii", $id, $id_usuario_sesion);
}

mysqli_stmt_execute($stmt_venta);
$resultado_venta = mysqli_stmt_get_result($stmt_venta);
$venta = mysqli_fetch_assoc($resultado_venta);
mysqli_stmt_close($stmt_venta);

if (!$venta) {
    header("Location: " . ($es_admin ? "ventas.php" : "mis_pedidos.php"));
    exit();
}

$url_volver = $es_admin ? "ventas.php" : "mis_pedidos.php";
$texto_volver = $es_admin ? "← Volver a ventas" : "← Volver a mis pedidos";

if ($origen === 'pedidos' && $es_admin) {
    $url_volver = "pedidos.php";
    $texto_volver = "← Volver a pedidos";
}

$stmt_detalles = mysqli_prepare($conexion, "SELECT dv.*, p.nombre FROM detalle_ventas dv JOIN productos p ON dv.id_producto = p.id WHERE dv.id_venta = ?");
mysqli_stmt_bind_param($stmt_detalles, "i", $id);
mysqli_stmt_execute($stmt_detalles);
$detalles = mysqli_stmt_get_result($stmt_detalles);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Detalle Pedido #<?php echo $id; ?></title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background: #f5f5f5; display: flex; justify-content: center; align-items: center; min-height: 100vh; }
        .contenedor { background: white; padding: 40px; border-radius: 10px; width: 600px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .encabezado { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; border-bottom: 2px solid #E87722; padding-bottom: 15px; }
        .encabezado h2 { color: #333; }
        .encabezado span { color: #999; font-size: 14px; }
        .info { margin-bottom: 20px; color: #666; font-size: 14px; display: grid; gap: 8px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th { background-color: #E87722; color: white; padding: 10px; text-align: left; }
        td { padding: 10px; border-bottom: 1px solid #eee; }
        .total-final { text-align: right; font-size: 20px; font-weight: bold; color: #E87722; padding: 15px 0; border-top: 2px solid #E87722; }
        .btn-volver { display: inline-block; background: #1a1a1a; color: white; padding: 10px 20px; border-radius: 5px; text-decoration: none; margin-top: 10px; }
        .btn-volver:hover { background: #E87722; }
        .estado { display: inline-block; padding: 4px 10px; border-radius: 999px; font-size: 12px; font-weight: bold; }
        .estado.Pendiente { background: #fff3cd; color: #856404; }
        .estado.Pagado { background: #d1ecf1; color: #0c5460; }
        .estado.Preparando { background: #d4edda; color: #155724; }
        .estado.Enviado { background: #cce5ff; color: #004085; }
        .estado.Entregado { background: #d4edda; color: #155724; }
        .estado.Cancelado { background: #f8d7da; color: #721c24; }
    </style>
</head>
<body>
    <div class="contenedor">
        <div class="encabezado">
            <h2>🧾 Pedido #<?php echo $id; ?></h2>
            <span><?php echo htmlspecialchars(date('d/m/Y H:i', strtotime($venta['fecha']))); ?></span>
        </div>
        <?php $estado = $venta['estado'] ?: 'Pendiente'; ?>
        <div class="info">
            <p><strong>Cliente:</strong> <?php echo htmlspecialchars($venta['cliente']); ?></p>
            <p><strong>Estado:</strong> <span class="estado <?php echo str_replace(' ', '', $estado); ?>"><?php echo htmlspecialchars($estado); ?></span></p>
        </div>
        <table>
            <tr>
                <th>Producto</th>
                <th>Cantidad</th>
                <th>Precio Unit.</th>
                <th>Subtotal</th>
            </tr>
            <?php while ($d = mysqli_fetch_assoc($detalles)): ?>
            <tr>
                <td><?php echo htmlspecialchars($d['nombre']); ?></td>
                <td><?php echo $d['cantidad']; ?></td>
                <td>$<?php echo number_format($d['precio_unitario'], 0, ',', '.'); ?></td>
                <td>$<?php echo number_format($d['subtotal'], 0, ',', '.'); ?></td>
            </tr>
            <?php endwhile; ?>
        </table>
        <div class="total-final">
            Total: $<?php echo number_format($venta['total'], 0, ',', '.'); ?>
        </div>
        <a href="<?php echo $url_volver; ?>" class="btn-volver"><?php echo $texto_volver; ?></a>
    </div>
</body>
</html>
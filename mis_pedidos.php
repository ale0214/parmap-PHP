<?php
session_start();
include 'conexion.php';

if (!isset($_SESSION['usuario']) || ($_SESSION['rol'] ?? '') !== 'cliente' || !isset($_SESSION['id_usuario'])) {
    header("Location: index.php");
    exit();
}

$id_usuario = (int) $_SESSION['id_usuario'];

$stmt = mysqli_prepare(
    $conexion,
    "SELECT v.id, v.fecha, v.total, v.estado,
            COALESCE(SUM(dv.cantidad), 0) AS total_items
     FROM ventas v
     LEFT JOIN detalle_ventas dv ON dv.id_venta = v.id
     WHERE v.id_usuario = ?
     GROUP BY v.id, v.fecha, v.total, v.estado
     ORDER BY v.fecha DESC"
);
mysqli_stmt_bind_param($stmt, "i", $id_usuario);
mysqli_stmt_execute($stmt);
$pedidos = mysqli_stmt_get_result($stmt);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis pedidos - PARMAP</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Arial, sans-serif; background: #FFF8F0; color: #333; }
        header {
            background: white;
            padding: 14px 28px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            gap: 16px;
            flex-wrap: wrap;
        }
        .marca { display: flex; align-items: center; gap: 10px; text-decoration: none; color: inherit; }
        .marca img { width: 48px; }
        .marca strong { color: #E87722; font-size: 22px; }
        .acciones-header { display: flex; gap: 10px; flex-wrap: wrap; }
        .acciones-header a {
            text-decoration: none;
            padding: 10px 16px;
            border-radius: 999px;
            font-size: 13px;
            font-weight: bold;
        }
        .btn-claro { background: #fff2e8; color: #E87722; }
        .btn-oscuro { background: #1a1a1a; color: white; }
        .contenedor { max-width: 1100px; margin: 36px auto; padding: 0 20px; }
        .hero {
            background: linear-gradient(135deg, #1f1f1f 0%, #3a2a1d 100%);
            color: white;
            border-radius: 22px;
            padding: 30px;
            margin-bottom: 24px;
            box-shadow: 0 14px 30px rgba(26,26,26,0.12);
        }
        .hero h1 { font-size: 30px; margin-bottom: 8px; }
        .hero p { color: #f3d6c2; max-width: 680px; line-height: 1.5; }
        .resumen {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 16px;
            margin-bottom: 24px;
        }
        .tarjeta {
            background: white;
            border-radius: 18px;
            padding: 20px;
            box-shadow: 0 8px 18px rgba(0,0,0,0.06);
            border: 1px solid #f3e3d5;
        }
        .tarjeta span { display: block; font-size: 12px; text-transform: uppercase; color: #999; margin-bottom: 8px; }
        .tarjeta strong { font-size: 28px; color: #E87722; }
        .lista {
            background: white;
            border-radius: 22px;
            padding: 10px;
            box-shadow: 0 10px 24px rgba(0,0,0,0.06);
        }
        .pedido {
            display: grid;
            grid-template-columns: 1.2fr 1fr 1fr auto;
            gap: 18px;
            align-items: center;
            padding: 18px;
            border-radius: 16px;
        }
        .pedido + .pedido { border-top: 1px solid #f4ede7; }
        .pedido:hover { background: #fff8f3; }
        .pedido h2 { font-size: 18px; margin-bottom: 6px; }
        .pedido small, .pedido p { color: #777; font-size: 13px; }
        .valor { font-size: 20px; color: #1a1a1a; font-weight: bold; }
        .estado {
            display: inline-block;
            padding: 7px 12px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: bold;
        }
        .estado.Pendiente { background: #fff3cd; color: #856404; }
        .estado.Pagado { background: #d1ecf1; color: #0c5460; }
        .estado.Preparando { background: #d4edda; color: #155724; }
        .estado.Enviado { background: #cce5ff; color: #004085; }
        .estado.Entregado { background: #d4edda; color: #155724; }
        .estado.Cancelado { background: #f8d7da; color: #721c24; }
        .btn-detalle {
            display: inline-block;
            text-decoration: none;
            background: #E87722;
            color: white;
            padding: 11px 16px;
            border-radius: 12px;
            font-size: 13px;
            font-weight: bold;
            text-align: center;
        }
        .btn-detalle:hover { background: #1a1a1a; }
        .vacio {
            text-align: center;
            padding: 50px 20px;
            color: #777;
        }
        .vacio a {
            display: inline-block;
            margin-top: 14px;
            text-decoration: none;
            color: white;
            background: #E87722;
            padding: 12px 18px;
            border-radius: 999px;
            font-weight: bold;
        }
        @media (max-width: 860px) {
            .pedido { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
<header>
    <a href="tienda.php" class="marca">
        <img src="images/logo.png" alt="PARMAP">
        <div>
            <strong>PARMAP</strong>
            <div style="font-size:12px; color:#888;">Historial de compras</div>
        </div>
    </a>
    <div class="acciones-header">
        <a href="perfil.php" class="btn-claro">Mi perfil</a>
        <a href="tienda.php" class="btn-oscuro">Seguir comprando</a>
    </div>
</header>

<main class="contenedor">
    <section class="hero">
        <h1>Mis pedidos</h1>
        <p>Consulta el estado de cada compra, revisa cuántos productos incluye y entra al detalle cuando necesites confirmar cantidades o valores.</p>
    </section>

    <?php
    $resumen_pedidos = [];
    mysqli_data_seek($pedidos, 0);
    while ($fila = mysqli_fetch_assoc($pedidos)) {
        $resumen_pedidos[] = $fila;
    }
    $cantidad_pedidos = count($resumen_pedidos);
    $total_gastado = 0;
    foreach ($resumen_pedidos as $pedido) {
        $total_gastado += (float) $pedido['total'];
    }
    ?>

    <section class="resumen">
        <article class="tarjeta">
            <span>Pedidos realizados</span>
            <strong><?php echo $cantidad_pedidos; ?></strong>
        </article>
        <article class="tarjeta">
            <span>Total comprado</span>
            <strong>$<?php echo number_format($total_gastado, 0, ',', '.'); ?></strong>
        </article>
    </section>

    <section class="lista">
        <?php if ($cantidad_pedidos > 0): ?>
            <?php foreach ($resumen_pedidos as $pedido): ?>
                <?php $estado = $pedido['estado'] ?: 'Pendiente'; ?>
                <article class="pedido">
                    <div>
                        <h2>Pedido #<?php echo (int) $pedido['id']; ?></h2>
                        <small>Realizado el <?php echo htmlspecialchars(date('d/m/Y H:i', strtotime($pedido['fecha']))); ?></small>
                    </div>
                    <div>
                        <p><?php echo (int) $pedido['total_items']; ?> producto(s)</p>
                        <div class="valor">$<?php echo number_format($pedido['total'], 0, ',', '.'); ?></div>
                    </div>
                    <div>
                        <p>Estado actual</p>
                        <span class="estado <?php echo str_replace(' ', '', $estado); ?>"><?php echo htmlspecialchars($estado); ?></span>
                    </div>
                    <div>
                        <a href="detalle_venta.php?id=<?php echo (int) $pedido['id']; ?>&origen=mis_pedidos" class="btn-detalle">Ver detalle</a>
                    </div>
                </article>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="vacio">
                <h2>Aún no tienes pedidos registrados</h2>
                <p>Cuando completes tu primera compra, aquí verás el historial y el detalle de cada pedido.</p>
                <a href="catalogo.php">Explorar catálogo</a>
            </div>
        <?php endif; ?>
    </section>
</main>
</body>
</html>
<?php
mysqli_stmt_close($stmt);
?>
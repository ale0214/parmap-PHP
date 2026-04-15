<?php
session_start();

if (!isset($_SESSION['pedido_exitoso']) || !isset($_GET['id_venta']) || (int) $_GET['id_venta'] !== (int) $_SESSION['pedido_exitoso']) {
    header("Location: tienda.php");
    exit();
}

$id_venta = (int) $_GET['id_venta'];
unset($_SESSION['pedido_exitoso']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pedido confirmado - PARMAP</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            min-height: 100vh;
            background: linear-gradient(180deg, #fff7ef 0%, #fff 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
            color: #333;
        }
        .card {
            width: 100%;
            max-width: 720px;
            background: white;
            border-radius: 24px;
            padding: 36px;
            box-shadow: 0 18px 45px rgba(0,0,0,0.08);
            text-align: center;
        }
        .badge {
            width: 74px;
            height: 74px;
            border-radius: 50%;
            background: #E87722;
            color: white;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 34px;
            margin-bottom: 18px;
        }
        h1 { font-size: 32px; margin-bottom: 12px; }
        p { color: #666; line-height: 1.6; margin-bottom: 10px; }
        .pedido {
            margin: 22px auto;
            background: #fff6ee;
            border: 1px solid #f7dcc5;
            border-radius: 18px;
            padding: 18px;
            max-width: 320px;
        }
        .pedido strong { color: #E87722; font-size: 28px; display: block; margin-top: 6px; }
        .acciones {
            display: flex;
            justify-content: center;
            gap: 12px;
            flex-wrap: wrap;
            margin-top: 24px;
        }
        .acciones a {
            text-decoration: none;
            padding: 12px 18px;
            border-radius: 999px;
            font-weight: bold;
            font-size: 14px;
        }
        .btn-principal { background: #E87722; color: white; }
        .btn-secundario { background: #1a1a1a; color: white; }
    </style>
</head>
<body>
    <main class="card">
        <div class="badge">✓</div>
        <h1>Pedido realizado con éxito</h1>
        <p>Gracias por confiar en <strong>PARMAP</strong> para tus repuestos de maquinaria pesada.</p>
        <p>Ya registramos tu compra y podrás consultar su avance desde tu historial.</p>

        <section class="pedido">
            <span>Número de pedido</span>
            <strong>#<?php echo $id_venta; ?></strong>
        </section>

        <div class="acciones">
            <a href="detalle_venta.php?id=<?php echo $id_venta; ?>&origen=mis_pedidos" class="btn-principal">Ver detalle</a>
            <a href="mis_pedidos.php" class="btn-secundario">Ir a mis pedidos</a>
            <a href="tienda.php" class="btn-principal">Volver a la tienda</a>
        </div>
    </main>
</body>
</html>
<?php
session_start();
include 'conexion.php';

$error = "";

// Si no hay carrito redirigir
if (!isset($_SESSION['carrito']) || count($_SESSION['carrito']) == 0) {
    header("Location: carrito.php");
    exit();
}

// Calcular total
$total = 0;
foreach ($_SESSION['carrito'] as $item) {
    $total += $item['precio'] * $item['cantidad'];
}

// Procesar el pedido cuando envian el formulario
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = trim($_POST['nombre'] ?? '');
    $telefono = trim($_POST['telefono'] ?? '');
    $direccion = trim($_POST['direccion'] ?? '');
    $ciudad = trim($_POST['ciudad'] ?? '');
    $notas = trim($_POST['notas'] ?? '');

    if ($nombre === '' || $telefono === '' || $direccion === '' || $ciudad === '') {
        $error = "Completa todos los datos obligatorios del envío.";
    } else {
        $id_usuario = isset($_SESSION['id_usuario']) ? (int) $_SESSION['id_usuario'] : null;

        mysqli_begin_transaction($conexion);

        try {
            if ($id_usuario === null) {
                $stmt_venta = mysqli_prepare($conexion, "INSERT INTO ventas (id_usuario, total) VALUES (NULL, ?)");
                mysqli_stmt_bind_param($stmt_venta, "d", $total);
            } else {
                $stmt_venta = mysqli_prepare($conexion, "INSERT INTO ventas (id_usuario, total) VALUES (?, ?)");
                mysqli_stmt_bind_param($stmt_venta, "id", $id_usuario, $total);
            }

            mysqli_stmt_execute($stmt_venta);
            $id_venta = mysqli_insert_id($conexion);
            mysqli_stmt_close($stmt_venta);

            $stmt_producto = mysqli_prepare($conexion, "SELECT nombre, precio, stock FROM productos WHERE id = ?");
            $stmt_detalle = mysqli_prepare($conexion, "INSERT INTO detalle_ventas (id_venta, id_producto, cantidad, precio_unitario, subtotal) VALUES (?, ?, ?, ?, ?)");
            $stmt_stock = mysqli_prepare($conexion, "UPDATE productos SET stock = stock - ? WHERE id = ? AND stock >= ?");

            foreach ($_SESSION['carrito'] as $id_producto => $item) {
                $id_producto = (int) $id_producto;
                $cantidad = (int) ($item['cantidad'] ?? 0);

                if ($id_producto <= 0 || $cantidad <= 0) {
                    throw new Exception("Hay productos inválidos en el carrito.");
                }

                mysqli_stmt_bind_param($stmt_producto, "i", $id_producto);
                mysqli_stmt_execute($stmt_producto);
                $producto = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_producto));

                if (!$producto) {
                    throw new Exception("Uno de los productos ya no existe.");
                }

                if ((int) $producto['stock'] < $cantidad) {
                    throw new Exception("No hay stock suficiente para " . $producto['nombre'] . ".");
                }

                $precio_unitario = (float) $producto['precio'];
                $subtotal = $precio_unitario * $cantidad;

                mysqli_stmt_bind_param($stmt_detalle, "iiidd", $id_venta, $id_producto, $cantidad, $precio_unitario, $subtotal);
                mysqli_stmt_execute($stmt_detalle);

                mysqli_stmt_bind_param($stmt_stock, "iii", $cantidad, $id_producto, $cantidad);
                mysqli_stmt_execute($stmt_stock);

                if (mysqli_stmt_affected_rows($stmt_stock) !== 1) {
                    throw new Exception("No fue posible actualizar el stock de " . $producto['nombre'] . ".");
                }
            }

            mysqli_stmt_close($stmt_producto);
            mysqli_stmt_close($stmt_detalle);
            mysqli_stmt_close($stmt_stock);
            mysqli_commit($conexion);

            $_SESSION['datos_envio'] = [
                'nombre' => $nombre,
                'telefono' => $telefono,
                'direccion' => $direccion,
                'ciudad' => $ciudad,
                'notas' => $notas,
            ];

            unset($_SESSION['carrito']);
            $_SESSION['pedido_exitoso'] = $id_venta;
            header("Location: confirmacion.php?id_venta=" . $id_venta);
            exit();
        } catch (Exception $e) {
            mysqli_rollback($conexion);
            $error = $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Finalizar Pedido - PARMAP</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Arial, sans-serif; background-color: #FFF8F0; color: #333; }

        header {
            background: white;
            padding: 12px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .header-logo { display: flex; align-items: center; gap: 10px; text-decoration: none; }
        .header-logo img { width: 50px; }
        .header-logo strong { font-size: 22px; color: #E87722; }

        .banner-envios { background-color: #E87722; color: white; text-align: center; padding: 10px; font-size: 13px; font-weight: bold; }

        .contenedor {
            max-width: 1000px;
            margin: 40px auto;
            padding: 0 20px;
            display: grid;
            grid-template-columns: 1fr 380px;
            gap: 30px;
        }

        h1 { font-size: 22px; color: #333; margin-bottom: 25px; grid-column: 1/-1; }

        /* FORMULARIO */
        .formulario {
            background: white;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.07);
        }

        .formulario h2 {
            font-size: 16px;
            color: #333;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #E87722;
        }

        .grupo {
            margin-bottom: 18px;
        }

        .grupo label {
            display: block;
            font-size: 13px;
            font-weight: bold;
            color: #555;
            margin-bottom: 6px;
        }

        .grupo input,
        .grupo textarea,
        .grupo select {
            width: 100%;
            padding: 11px 14px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 14px;
            font-family: inherit;
            transition: 0.2s;
        }

        .grupo input:focus,
        .grupo textarea:focus {
            outline: none;
            border-color: #E87722;
        }

        .grupo textarea { resize: vertical; min-height: 80px; }

        .dos-columnas {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }

        /* RESUMEN DEL PEDIDO */
        .resumen {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.07);
            height: fit-content;
            position: sticky;
            top: 20px;
        }

        .resumen h2 {
            font-size: 16px;
            color: #333;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #E87722;
        }

        .resumen-item {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #f5f5f5;
            font-size: 13px;
        }

        .resumen-item span:first-child { color: #555; }
        .resumen-item span:last-child { font-weight: bold; color: #E87722; }

        .resumen-total {
            display: flex;
            justify-content: space-between;
            padding: 15px 0 0;
            font-size: 18px;
            font-weight: bold;
            color: #E87722;
        }

        .btn-confirmar {
            display: block;
            width: 100%;
            background-color: #E87722;
            color: white;
            padding: 14px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            margin-top: 20px;
            transition: 0.3s;
        }

        .btn-confirmar:hover { background-color: #1a1a1a; }

        .btn-volver {
            display: block;
            text-align: center;
            margin-top: 12px;
            color: #999;
            text-decoration: none;
            font-size: 13px;
        }

        .btn-volver:hover { color: #E87722; }

        footer { background-color: #111; color: white; padding: 30px 40px; text-align: center; margin-top: 50px; }
        footer p { color: #aaa; font-size: 13px; }
    </style>
</head>
<body>

<header>
    <a href="tienda.php" class="header-logo">
        <img src="images/logo.png" alt="PARMAP">
        <strong>PARMAP</strong>
    </a>
    <a href="carrito.php" style="text-decoration:none; color:#E87722; font-weight:bold; font-size:14px;">← Volver al carrito</a>
</header>

<div class="banner-envios">🚚 ENVÍOS EN MENOS DE 48 HORAS A TODO EL PAÍS</div>

<form method="POST">
<div class="contenedor">
    <h1>📦 Finalizar Pedido</h1>

    <?php if ($error !== ""): ?>
    <div style="grid-column:1/-1; background:#fdecea; color:#b42318; border:1px solid #f5c2c7; padding:14px 16px; border-radius:10px;">
        <?php echo htmlspecialchars($error); ?>
    </div>
    <?php endif; ?>

    <!-- FORMULARIO DE ENVIO -->
    <div class="formulario">
        <h2>📍 Datos de envío</h2>

        <div class="grupo">
            <label>Nombre completo *</label>
            <input type="text" name="nombre" 
                value="<?php echo htmlspecialchars($_POST['nombre'] ?? ($_SESSION['nombre'] ?? '')); ?>" 
                placeholder="Tu nombre completo" required>
        </div>

        <div class="dos-columnas">
            <div class="grupo">
                <label>Teléfono *</label>
                <input type="text" name="telefono" value="<?php echo htmlspecialchars($_POST['telefono'] ?? ''); ?>" placeholder="+57 300 000 0000" required>
            </div>
            <div class="grupo">
                <label>Ciudad *</label>
                <input type="text" name="ciudad" value="<?php echo htmlspecialchars($_POST['ciudad'] ?? ''); ?>" placeholder="Ibagué" required>
            </div>
        </div>

        <div class="grupo">
            <label>Dirección de entrega *</label>
            <input type="text" name="direccion" value="<?php echo htmlspecialchars($_POST['direccion'] ?? ''); ?>" placeholder="Calle 00 # 00-00, Barrio" required>
        </div>

        <div class="grupo">
            <label>Notas adicionales</label>
            <textarea name="notas" placeholder="Instrucciones especiales para el envío, referencias de la dirección..."><?php echo htmlspecialchars($_POST['notas'] ?? ''); ?></textarea>
        </div>

        <h2 style="margin-top:20px;">💳 Método de pago</h2>
        <div class="grupo">
            <label>
                <input type="radio" name="pago" value="contraentrega" checked>
                💵 Pago contraentrega
            </label>
        </div>
        <div class="grupo">
            <label>
                <input type="radio" name="pago" value="transferencia">
                🏦 Transferencia bancaria
            </label>
        </div>
    </div>

    <!-- RESUMEN -->
    <div class="resumen">
        <h2>🛒 Resumen del pedido</h2>
        <?php foreach ($_SESSION['carrito'] as $item): ?>
        <div class="resumen-item">
            <span><?php echo htmlspecialchars($item['nombre']); ?> x<?php echo $item['cantidad']; ?></span>
            <span>$<?php echo number_format($item['precio'] * $item['cantidad'], 0, ',', '.'); ?></span>
        </div>
        <?php endforeach; ?>

        <div class="resumen-item">
            <span>Envío</span>
            <span style="color:#27ae60;">✅ Gratis</span>
        </div>

        <div class="resumen-total">
            <span>Total</span>
            <span>$<?php echo number_format($total, 0, ',', '.'); ?></span>
        </div>

        <button type="submit" class="btn-confirmar">✅ Confirmar Pedido</button>
        <a href="carrito.php" class="btn-volver">← Volver al carrito</a>
    </div>
</div>
</form>

<footer>
    <p>© 2026 Almacén PARMAP - Todos los derechos reservados</p>
</footer>

</body>
</html>
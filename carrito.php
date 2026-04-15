<?php
session_start();
include 'conexion.php';

// Si llega un producto por la URL (?id=X) lo agregamos al carrito
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $producto = mysqli_fetch_assoc(mysqli_query($conexion, "SELECT * FROM productos WHERE id=$id AND stock > 0"));
    
    if ($producto) {
        // Si el carrito no existe lo creamos
        if (!isset($_SESSION['carrito'])) {
            $_SESSION['carrito'] = [];
        }
        
        // Si el producto ya está en el carrito sumamos 1
        if (isset($_SESSION['carrito'][$id])) {
            $_SESSION['carrito'][$id]['cantidad']++;
        } else {
            // Si no está lo agregamos
            $_SESSION['carrito'][$id] = [
                'nombre' => $producto['nombre'],
                'precio' => floatval($producto['precio']),
                'cantidad' => 1
            ];
        }
    }
    // Redirigir de vuelta al catalogo
    header("Location: catalogo.php");
    exit();
}

// Eliminar un producto del carrito
if (isset($_GET['eliminar'])) {
    $id = $_GET['eliminar'];
    unset($_SESSION['carrito'][$id]);
    header("Location: carrito.php");
    exit();
}

// Vaciar todo el carrito
if (isset($_GET['vaciar'])) {
    unset($_SESSION['carrito']);
    header("Location: carrito.php");
    exit();
}

// Calcular el total
$total = 0;
if (isset($_SESSION['carrito'])) {
    foreach ($_SESSION['carrito'] as $item) {
        $total += $item['precio'] * $item['cantidad'];
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Carrito - Almacén PARMAP</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Arial, sans-serif; background-color: #FFF8F0; color: #333; }

        header {
            background-color: white;
            padding: 12px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .header-logo { display: flex; align-items: center; gap: 10px; text-decoration: none; }
        .header-logo img { width: 50px; }
        .header-logo-texto strong { display: block; font-size: 24px; color: #E87722; }
        .header-logo-texto span { display: block; font-size: 11px; color: #999; }

        .banner-envios { background-color: #E87722; color: white; text-align: center; padding: 10px; font-size: 13px; font-weight: bold; }

        .contenedor {
            max-width: 900px;
            margin: 40px auto;
            padding: 0 20px;
        }

        h1 { font-size: 24px; color: #333; margin-bottom: 25px; }

        /* TABLA DEL CARRITO */
        .tabla-carrito {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            margin-bottom: 25px;
        }

        table { width: 100%; border-collapse: collapse; }

        th {
            background-color: #E87722;
            color: white;
            padding: 14px 20px;
            text-align: left;
            font-size: 14px;
        }

        td {
            padding: 14px 20px;
            border-bottom: 1px solid #f0f0f0;
            font-size: 14px;
        }

        tr:last-child td { border-bottom: none; }
        tr:hover td { background-color: #FFF8F0; }

        .precio-td { color: #E87722; font-weight: bold; font-size: 16px; }

        .btn-eliminar {
            background-color: #e74c3c;
            color: white;
            border: none;
            padding: 6px 12px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 12px;
            text-decoration: none;
        }

        .btn-eliminar:hover { background-color: #c0392b; }

        /* RESUMEN TOTAL */
        .resumen {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }

        .resumen-fila {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #f0f0f0;
            font-size: 14px;
        }

        .resumen-total {
            display: flex;
            justify-content: space-between;
            padding: 15px 0 0;
            font-size: 20px;
            font-weight: bold;
            color: #E87722;
        }

        .botones-carrito {
            display: flex;
            gap: 15px;
            margin-top: 20px;
        }

        .btn-seguir {
            flex: 1;
            background-color: #f0f0f0;
            color: #333;
            padding: 14px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 15px;
            text-decoration: none;
            text-align: center;
            font-weight: bold;
        }

        .btn-seguir:hover { background-color: #ddd; }

        .btn-pedir {
            flex: 2;
            background-color: #E87722;
            color: white;
            padding: 14px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 15px;
            font-weight: bold;
            text-align: center;
            text-decoration: none;
        }

        .btn-pedir:hover { background-color: #1a1a1a; }

        .btn-vaciar {
            background: none;
            border: none;
            color: #e74c3c;
            font-size: 13px;
            cursor: pointer;
            margin-top: 10px;
            display: block;
            text-decoration: none;
        }

        /* Carrito vacío */
        .carrito-vacio {
            text-align: center;
            padding: 60px 20px;
            background: white;
            border-radius: 12px;
        }

        .carrito-vacio .icono { font-size: 70px; margin-bottom: 20px; }
        .carrito-vacio h2 { color: #333; margin-bottom: 10px; }
        .carrito-vacio p { color: #999; margin-bottom: 25px; }

        .btn-ir-catalogo {
            background-color: #E87722;
            color: white;
            padding: 12px 30px;
            border-radius: 25px;
            text-decoration: none;
            font-weight: bold;
        }

        footer { background-color: #111; color: white; padding: 30px 40px; text-align: center; margin-top: 50px; }
        footer p { color: #aaa; font-size: 13px; }
    </style>
</head>
<body>

<header>
    <a href="tienda.php" class="header-logo">
        <img src="images/logo.png" alt="PARMAP">
        <div class="header-logo-texto">
            <strong>PARMAP</strong>
            <span>Partes para maquinaria pesada</span>
        </div>
    </a>
   <nav style="display:flex; gap:20px; align-items:center;">
    <a href="tienda.php" style="text-decoration:none; color:#333; font-weight:bold; font-size:13px;">← Seguir comprando</a>
    <a href="catalogo.php" style="text-decoration:none; color:#333; font-weight:bold; font-size:13px;">Catálogo</a>

    <?php if (isset($_SESSION['usuario'])): ?>
        <a href="perfil.php" style="font-weight: bold; color:#333; text-decoration:none;">
            👋 <?php echo $_SESSION['nombre']; ?>
        </a>
        <a href="logout.php" style="background:#E87722; color:white; padding:8px 20px; border-radius:5px; text-decoration:none; font-size:13px;">
            Salir
        </a>
    <?php else: ?>
        <a href="index.php" style="background:#E87722; color:white; padding:8px 20px; border-radius:5px; text-decoration:none; font-size:13px;">
            Iniciar Sesión
        </a>
    <?php endif; ?>
</nav>
</header>

<div class="banner-envios">🚚 ENVÍOS EN MENOS DE 48 HORAS A TODO EL PAÍS</div>

<div class="contenedor">
    <h1>🛒 Mi Carrito</h1>

    <?php if (!isset($_SESSION['carrito']) || count($_SESSION['carrito']) == 0): ?>
        <!-- CARRITO VACIO -->
        <div class="carrito-vacio">
            <div class="icono">🛒</div>
            <h2>Tu carrito está vacío</h2>
            <p>Agrega productos desde el catálogo para verlos aquí</p>
            <a href="catalogo.php" class="btn-ir-catalogo">Ver Catálogo</a>
        </div>

    <?php else: ?>
        <!-- PRODUCTOS EN EL CARRITO -->
        <div class="tabla-carrito">
            <table>
                <tr>
                    <th>Producto</th>
                    <th>Precio Unit.</th>
                    <th>Cantidad</th>
                    <th>Subtotal</th>
                    <th>Acción</th>
                </tr>
                <?php foreach ($_SESSION['carrito'] as $id => $item): ?>
                <tr>
                    <td><strong><?php echo htmlspecialchars($item['nombre']); ?></strong></td>
                    <td class="precio-td">$<?php echo number_format($item['precio'], 0, ',', '.'); ?></td>
                    <td>
                        <!-- Botones para cambiar cantidad -->
                        <a href="actualizar_carrito.php?id=<?php echo $id; ?>&accion=restar" style="text-decoration:none; background:#eee; padding:3px 8px; border-radius:3px; color:#333;">−</a>
                        <strong style="margin:0 8px;"><?php echo $item['cantidad']; ?></strong>
                        <a href="actualizar_carrito.php?id=<?php echo $id; ?>&accion=sumar" style="text-decoration:none; background:#E87722; padding:3px 8px; border-radius:3px; color:white;">+</a>
                    </td>
                    <td class="precio-td">$<?php echo number_format($item['precio'] * $item['cantidad'], 0, ',', '.'); ?></td>
                    <td><a href="carrito.php?eliminar=<?php echo $id; ?>" class="btn-eliminar" onclick="return confirm('¿Quitar este producto?')">🗑️ Quitar</a></td>
                </tr>
                <?php endforeach; ?>
            </table>
        </div>

        <!-- RESUMEN -->
        <div class="resumen">
            <div class="resumen-fila">
                <span>Subtotal</span>
                <span>$<?php echo number_format($total, 0, ',', '.'); ?></span>
            </div>
            <div class="resumen-fila">
                <span>Envío</span>
                <span style="color:#27ae60;">✅ Gratis</span>
            </div>
            <div class="resumen-total">
                <span>Total</span>
                <span>$<?php echo number_format($total, 0, ',', '.'); ?></span>
            </div>

            <div class="botones-carrito">
                <a href="catalogo.php" class="btn-seguir">← Seguir comprando</a>
                <a href="checkout.php" class="btn-pedir">Realizar Pedido →</a>
            </div>
            <a href="carrito.php?vaciar=1" class="btn-vaciar" onclick="return confirm('¿Vaciar todo el carrito?')">🗑️ Vaciar carrito</a>
        </div>
    <?php endif; ?>
</div>

<footer>
    <p>© 2026 Almacén PARMAP - Todos los derechos reservados</p>
</footer>

</body>
</html>
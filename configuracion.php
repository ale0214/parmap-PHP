<?php
session_start();
if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'administrador') {
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>PARMAP - Configuración</title>
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
        .card { background: white; padding: 24px; border-radius: 10px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); margin-bottom: 20px; }
        .card h2 { color: #333; margin-bottom: 10px; }
        .card p { color: #666; line-height: 1.6; margin-bottom: 8px; }
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
            <li><a href="configuracion.php" class="activo">⚙️ Configuración</a></li>
            <li><a href="logout.php">🚪 Log Out</a></li>
        </ul>
    </div>

    <div class="contenido">
        <div class="card">
            <h2>⚙️ Configuración General</h2>
            <p>Esta sección quedó lista como base para futuras opciones del sistema.</p>
            <p>Aquí podremos conectar correo, métodos de pago, datos de contacto y ajustes visuales de la tienda.</p>
        </div>

        <div class="card">
            <h2>👤 Sesión actual</h2>
            <p><strong>Usuario:</strong> <?php echo htmlspecialchars($_SESSION['usuario']); ?></p>
            <p><strong>Rol:</strong> <?php echo htmlspecialchars($_SESSION['rol']); ?></p>
        </div>
    </div>
</body>
</html>
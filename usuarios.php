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

// ELIMINAR USUARIO
if (isset($_GET['eliminar'])) {
    $id = (int) $_GET['eliminar'];

    if ($id > 0 && $id !== (int) $_SESSION['id_usuario']) {
        $stmt_delete = mysqli_prepare($conexion, "DELETE FROM usuarios WHERE id = ?");
        mysqli_stmt_bind_param($stmt_delete, "i", $id);
        mysqli_stmt_execute($stmt_delete);
        mysqli_stmt_close($stmt_delete);
    }

    header("Location: usuarios.php");
    exit();
}

$usuarios = mysqli_query($conexion, "SELECT * FROM usuarios ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>PARMAP - Usuarios</title>
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
        .btn-agregar { background-color: #E87722; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; font-size: 14px; text-decoration: none; }
        .btn-agregar:hover { background-color: #1a1a1a; }
        .tabla-section { background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        table { width: 100%; border-collapse: collapse; }
        th { background-color: #E87722; color: white; padding: 10px; text-align: left; }
        td { padding: 10px; border-bottom: 1px solid #eee; }
        tr:hover { background-color: #fff8f3; }
        .badge-admin { background-color: #1a1a1a; color: white; padding: 3px 10px; border-radius: 10px; font-size: 12px; }
        .badge-empleado { background-color: #3498db; color: white; padding: 3px 10px; border-radius: 10px; font-size: 12px; }
        .badge-cliente { background-color: #27ae60; color: white; padding: 3px 10px; border-radius: 10px; font-size: 12px; }
        .btn-editar { background-color: #3498db; color: white; padding: 5px 10px; border: none; border-radius: 3px; cursor: pointer; font-size: 12px; text-decoration: none; margin-right: 5px; }
        .btn-eliminar { background-color: #e74c3c; color: white; padding: 5px 10px; border: none; border-radius: 3px; cursor: pointer; font-size: 12px; text-decoration: none; }
        .modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); justify-content: center; align-items: center; z-index: 999; }
        .modal.activo { display: flex; }
        .modal-contenido { background: white; padding: 30px; border-radius: 10px; width: 400px; }
        .modal-contenido h2 { margin-bottom: 20px; color: #333; }
        .modal-contenido input, .modal-contenido select { width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 5px; font-size: 14px; }
        .modal-botones { display: flex; gap: 10px; }
        .btn-guardar { background-color: #E87722; color: white; padding: 10px; border: none; border-radius: 5px; cursor: pointer; flex: 1; }
        .btn-cancelar { background-color: #999; color: white; padding: 10px; border: none; border-radius: 5px; cursor: pointer; flex: 1; }
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
            <h1>👥 Gestión de Usuarios</h1>
            <a href="#" class="btn-agregar" onclick="abrirModal()">+ Nuevo Usuario</a>
        </div>

        <div class="tabla-section">
            <table>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Usuario</th>
                    <th>Rol</th>
                    <th>Acción</th>
                </tr>
                <?php if (mysqli_num_rows($usuarios) > 0): ?>
                    <?php while ($u = mysqli_fetch_assoc($usuarios)): ?>
                    <tr>
                        <td><?php echo $u['id']; ?></td>
                        <td><?php echo $u['nombre']; ?></td>
                        <td><?php echo $u['usuario']; ?></td>
                        <td>
                            <?php if ($u['rol'] == 'administrador'): ?>
                                <span class="badge-admin">👑 Administrador</span>
                            <?php else: ?>
                                <span class="badge-cliente">🛒 Cliente</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="editar_usuario.php?id=<?php echo $u['id']; ?>" class="btn-editar">✏️ Editar</a>
                            <?php if ($u['usuario'] != $_SESSION['usuario']): ?>
                                <a href="usuarios.php?eliminar=<?php echo $u['id']; ?>" class="btn-eliminar" onclick="return confirm('¿Seguro que quieres eliminar este usuario?')">🗑️ Eliminar</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="5" style="text-align:center; color:#999; padding:20px;">No hay usuarios registrados</td></tr>
                <?php endif; ?>
            </table>
        </div>
    </div>

    <!-- MODAL NUEVO USUARIO -->
    <div class="modal" id="modal">
        <div class="modal-contenido">
            <h2>➕ Nuevo Usuario</h2>
            <form method="POST" action="guardar_usuario.php">
                <input type="text" name="nombre" placeholder="Nombre completo" required>
                <input type="text" name="usuario" placeholder="Nombre de usuario" required>
                <input type="password" name="contrasena" placeholder="Contraseña" required>
                <select name="rol">
                    <option value="cliente">🛒 Cliente</option>
                    <option value="administrador">👑 Administrador</option>
                </select>
                <div class="modal-botones">
                    <button type="submit" class="btn-guardar">Guardar</button>
                    <button type="button" class="btn-cancelar" onclick="cerrarModal()">Cancelar</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function abrirModal() { document.getElementById('modal').classList.add('activo'); }
        function cerrarModal() { document.getElementById('modal').classList.remove('activo'); }
    </script>

</body>
</html>
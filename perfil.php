<?php
session_start();
include 'conexion.php';

// Si no ha iniciado sesión, lo sacamos
if (!isset($_SESSION['usuario'])) {
    header("Location: index.php");
    exit();
}

// Obtener datos del usuario desde la BD
$usuario = $_SESSION['usuario'];
$stmt = mysqli_prepare($conexion, "SELECT id, nombre, usuario, rol FROM usuarios WHERE usuario = ?");
mysqli_stmt_bind_param($stmt, "s", $usuario);
mysqli_stmt_execute($stmt);
$resultado = mysqli_stmt_get_result($stmt);
$datos = mysqli_fetch_assoc($resultado);
mysqli_stmt_close($stmt);

if (!$datos) {
    session_destroy();
    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mi Perfil - PARMAP</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f5f5f5;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .perfil {
            background: white;
            padding: 40px;
            border-radius: 10px;
            width: 400px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            text-align: center;
        }

        h2 {
            margin-bottom: 20px;
            color: #333;
        }

        .dato {
            margin-bottom: 15px;
            text-align: left;
        }

        .dato strong {
            color: #E87722;
        }

        .btn {
            display: block;
            margin-top: 15px;
            padding: 12px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: bold;
        }

        .btn-editar {
            background-color: #E87722;
            color: white;
        }

        .btn-salir {
            background-color: #1a1a1a;
            color: white;
        }

        .btn:hover {
            opacity: 0.9;
        }
    </style>
</head>
<body>

<div class="perfil">
    <h2>👤 Mi Perfil</h2>

    <div class="dato">
        <strong>Nombre:</strong><br>
        <?php echo htmlspecialchars($datos['nombre']); ?>
    </div>

    <div class="dato">
        <strong>Usuario:</strong><br>
        <?php echo htmlspecialchars($datos['usuario']); ?>
    </div>

    <div class="dato">
        <strong>Rol:</strong><br>
        <?php echo htmlspecialchars($datos['rol']); ?>
    </div>

    <a href="editar_usuario.php?id=<?php echo $datos['id']; ?>" class="btn btn-editar">
        ✏️ Editar datos
    </a>

    <?php if ($datos['rol'] === 'cliente'): ?>
    <a href="mis_pedidos.php" class="btn btn-editar" style="background-color:#1a1a1a;">
        🧾 Mis pedidos
    </a>
    <?php endif; ?>

    <a href="tienda.php" class="btn">
        🏠 Volver a la tienda
    </a>

    <a href="logout.php" class="btn btn-salir">
        🚪 Cerrar sesión
    </a>
</div>

</body>

</body>

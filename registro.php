<?php
include 'conexion.php';

$error = "";
$exito = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = trim($_POST['nombre']);
    $usuario = trim($_POST['usuario']);
    $contrasena_plana = trim($_POST['contrasena']);
    $contrasena = password_hash($contrasena_plana, PASSWORD_DEFAULT);
    $confirmar = $_POST['confirmar'];

    // Verificar que las contraseñas coincidan
    if ($contrasena_plana != $confirmar) {
    $error = "Las contraseñas no coinciden";
    } else {
        // Verificar que el usuario no exista ya
        $check = mysqli_prepare($conexion, "SELECT id FROM usuarios WHERE usuario = ?");
        if (!$check) {
            $error = "La base de datos no está disponible en este momento. Revisa MariaDB/XAMPP.";
        } else {
        mysqli_stmt_bind_param($check, "s", $usuario);
        mysqli_stmt_execute($check);
        $resultado = mysqli_stmt_get_result($check);

        if (mysqli_num_rows($resultado) > 0) {
            $error = "Ese nombre de usuario ya está en uso";
        } else {
            // Todo bien, crear el usuario como cliente
            $stmt = mysqli_prepare($conexion, "INSERT INTO usuarios (nombre, usuario, contrasena, rol) VALUES (?, ?, ?, 'cliente')");
            if (!$stmt) {
                $error = "No se pudo registrar el usuario porque la base de datos está fallando.";
            } else {
                mysqli_stmt_bind_param($stmt, "sss", $nombre, $usuario, $contrasena);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
                $exito = "Cuenta creada exitosamente! Ya puedes iniciar sesión.";
            }
        }

        mysqli_stmt_close($check);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>PARMAP - Registro</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            background-color: #1a1a1a;
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .contenedor {
            background-color: white;
            padding: 40px;
            border-radius: 10px;
            width: 380px;
            text-align: center;
        }
        .logo { width: 100px; margin-bottom: 15px; }
        h2 { color: #333; font-size: 20px; margin-bottom: 25px; }
        input {
            width: 100%;
            padding: 12px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 15px;
        }
        button {
            width: 100%;
            padding: 12px;
            background-color: #E87722;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
        }
        button:hover { background-color: #1a1a1a; }
        .error { color: red; font-size: 14px; margin-bottom: 15px; }
        .exito { color: green; font-size: 14px; margin-bottom: 15px; }
        .links { margin-top: 15px; font-size: 14px; color: #999; }
        .links a { color: #E87722; text-decoration: none; }
        .links a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="contenedor">
        <img src="images/logo.png" alt="PARMAP" class="logo">
        <h2>Crear Cuenta</h2>

        <?php if ($error != ""): ?>
            <p class="error">❌ <?php echo $error; ?></p>
        <?php endif; ?>

        <?php if ($exito != ""): ?>
            <p class="exito">✅ <?php echo $exito; ?></p>
        <?php endif; ?>

        <?php if ($exito == ""): ?>
        <form method="POST">
            <input type="text" name="nombre" placeholder="Nombre completo" required>
            <input type="text" name="usuario" placeholder="Nombre de usuario" required>
            <input type="password" name="contrasena" placeholder="Contraseña" required>
            <input type="password" name="confirmar" placeholder="Confirmar contraseña" required>
            <button type="submit">Registrarse</button>
        </form>
        <?php endif; ?>

        <div class="links">
            ¿Ya tienes cuenta? <a href="index.php">Inicia sesión aquí</a>
        </div>
    </div>
</body>
</html>
<?php
include 'conexion.php';
session_start();

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $usuario = trim($_POST["usuario"]);
    $contrasena = trim($_POST["contrasena"]);

    $stmt = mysqli_prepare($conexion, "SELECT id, nombre, usuario, contrasena, rol FROM usuarios WHERE usuario = ?");
    if (!$stmt) {
        $error = "La base de datos no está disponible en este momento. Revisa MariaDB/XAMPP.";
    } else {
        mysqli_stmt_bind_param($stmt, "s", $usuario);
        mysqli_stmt_execute($stmt);
        $resultado = mysqli_stmt_get_result($stmt);

        if (mysqli_num_rows($resultado) == 1) {

            $datos = mysqli_fetch_assoc($resultado);

            if (password_verify($contrasena, $datos['contrasena'])) {

                $_SESSION['id_usuario'] = $datos['id'];
                $_SESSION['usuario'] = $usuario;
                $_SESSION['nombre'] = $datos['nombre'];
                $_SESSION['rol'] = $datos['rol'];

                // Redirigir según el rol
                if ($datos['rol'] == 'cliente') {
                    header("Location: tienda.php");
                } else {
                    header("Location: dashboard.php");
                }
                exit();
            } else {
                $error = "Contraseña incorrecta";
            }

        } else {
            $error = "Usuario no existe";
        }

        mysqli_stmt_close($stmt);
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>PARMAP - Login</title>
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
            width: 350px;
            text-align: center;
        }
        .logo { width: 120px; margin-bottom: 15px; }
        .contenedor h2 { color: #333; font-size: 18px; margin-bottom: 25px; }
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
            background-color: #1a1a1a;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
        }
        button:hover { background-color: #E87722; }
        .error {
            color: red;
            font-size: 14px;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="contenedor">
        <img src="images/logo.png" alt="Logo PARMAP" class="logo">
        <h2>Inicio de Sesión</h2>

        <?php if ($error != ""): ?>
            <p class="error"><?php echo $error; ?></p>
        <?php endif; ?>

        <form method="POST" action="">
            <input type="text" name="usuario" placeholder="Usuario" required>
            <input type="password" name="contrasena" placeholder="Contraseña" required>
            <button type="submit">Ingresar</button>
        </form>

    <div style="margin-top: 15px; font-size: 14px; color: #999;">
    ¿No tienes cuenta? <a href="registro.php" style="color: #E87722;">Regístrate aquí</a>
    </div>
    </div>
</body>
</html>
<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: index.php");
    exit();
}
include 'conexion.php';

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($id <= 0) {
    header("Location: usuarios.php");
    exit();
}

$es_admin = isset($_SESSION['rol']) && $_SESSION['rol'] === 'administrador';
$es_dueno = isset($_SESSION['id_usuario']) && (int) $_SESSION['id_usuario'] === $id;

if (!$es_admin && !$es_dueno) {
    header("Location: tienda.php");
    exit();
}

$stmt_usuario = mysqli_prepare($conexion, "SELECT * FROM usuarios WHERE id = ?");
mysqli_stmt_bind_param($stmt_usuario, "i", $id);
mysqli_stmt_execute($stmt_usuario);
$resultado_usuario = mysqli_stmt_get_result($stmt_usuario);
$u = mysqli_fetch_assoc($resultado_usuario);
mysqli_stmt_close($stmt_usuario);

if (!$u) {
    header("Location: usuarios.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = trim($_POST['nombre'] ?? '');
    $usuario = trim($_POST['usuario'] ?? '');
    $rol = $es_admin ? ($_POST['rol'] ?? $u['rol']) : $u['rol'];

    if ($nombre === '' || $usuario === '') {
        die("Faltan datos obligatorios.");
    }

    if (!in_array($rol, ['cliente', 'administrador'], true)) {
        $rol = $u['rol'];
    }

    $stmt_check = mysqli_prepare($conexion, "SELECT id FROM usuarios WHERE usuario = ? AND id != ?");
    mysqli_stmt_bind_param($stmt_check, "si", $usuario, $id);
    mysqli_stmt_execute($stmt_check);
    $resultado_check = mysqli_stmt_get_result($stmt_check);

    if (mysqli_num_rows($resultado_check) > 0) {
        mysqli_stmt_close($stmt_check);
        die("Ese nombre de usuario ya está en uso.");
    }

    mysqli_stmt_close($stmt_check);
    
    // Solo actualizar contrasena si escribio una nueva
  if (!empty($_POST['contrasena'])) {

    if ($_POST['contrasena'] !== $_POST['confirmar']) {
        die("❌ Las contraseñas no coinciden");
    }

    $contrasena = password_hash($_POST['contrasena'], PASSWORD_DEFAULT);
    $stmt_update = mysqli_prepare($conexion, "UPDATE usuarios SET nombre = ?, usuario = ?, contrasena = ?, rol = ? WHERE id = ?");
    mysqli_stmt_bind_param($stmt_update, "ssssi", $nombre, $usuario, $contrasena, $rol, $id);
    mysqli_stmt_execute($stmt_update);
    mysqli_stmt_close($stmt_update);
} else {
    $stmt_update = mysqli_prepare($conexion, "UPDATE usuarios SET nombre = ?, usuario = ?, rol = ? WHERE id = ?");
    mysqli_stmt_bind_param($stmt_update, "sssi", $nombre, $usuario, $rol, $id);
    mysqli_stmt_execute($stmt_update);
    mysqli_stmt_close($stmt_update);
}
    header("Location: " . ($es_admin ? "usuarios.php" : "perfil.php"));
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Usuario</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background: #f5f5f5; display: flex; justify-content: center; align-items: center; height: 100vh; }
        .formulario { background: white; padding: 40px; border-radius: 10px; width: 400px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h2 { margin-bottom: 20px; color: #333; }
        input, select { width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 5px; font-size: 14px; }
        .btn-guardar { width: 100%; background-color: #E87722; color: white; padding: 12px; border: none; border-radius: 5px; cursor: pointer; font-size: 15px; }
        .btn-guardar:hover { background-color: #1a1a1a; }
        .btn-volver { display: block; text-align: center; margin-top: 10px; color: #999; text-decoration: none; }
        small { color: #999; font-size: 12px; display: block; margin-top: -10px; margin-bottom: 15px; }
    </style>
</head>
<body>
    <div class="formulario">
        <h2>✏️ Editar Usuario</h2>
        <form method="POST">
    <input type="text" name="nombre" value="<?php echo $u['nombre']; ?>" placeholder="Nombre completo" required>

    <input type="text" name="usuario" value="<?php echo $u['usuario']; ?>" placeholder="Usuario" required>

    <!-- CONTRASEÑA -->
    <input type="password" name="contrasena" id="pass" placeholder="Nueva contraseña">
<small>🔒 Mínimo 6 caracteres</small>
    
    <!-- CONFIRMAR -->
    <input type="password" name="confirmar" id="confirmar" placeholder="Confirmar contraseña">
<small id="mensaje" style="color:red;"></small>

    <!-- SOLO ADMIN VE EL ROL -->
    <?php if ($es_admin): ?>
        <select name="rol">
            <option value="cliente" <?php echo $u['rol']=='cliente'?'selected':''; ?>>🛒 Cliente</option>
            <option value="administrador" <?php echo $u['rol']=='administrador'?'selected':''; ?>>👑 Administrador</option>
        </select>
    <?php endif; ?>

    <button type="submit" class="btn-guardar">Guardar Cambios</button>
</form>
    </div>
<script>
document.querySelector("form").addEventListener("submit", function(e) {
    const pass = document.getElementById("pass").value;
    const confirmar = document.getElementById("confirmar").value;
    const mensaje = document.getElementById("mensaje");

    if (pass.length < 6 && pass !== "") {
        e.preventDefault();
        mensaje.textContent = "❌ Mínimo 6 caracteres";
        return;
    }

    if (pass !== confirmar) {
        e.preventDefault();
        mensaje.textContent = "❌ Las contraseñas no coinciden";
        return;
    }
});
</script>

</body>
</html>
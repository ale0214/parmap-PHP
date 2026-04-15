<?php
session_start();

if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'administrador') {
    header("Location: index.php");
    exit();
}

include 'conexion.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: usuarios.php");
    exit();
}

$nombre = trim($_POST['nombre'] ?? '');
$usuario = trim($_POST['usuario'] ?? '');
$contrasena_plana = $_POST['contrasena'] ?? '';
$rol = $_POST['rol'] ?? 'cliente';

if ($nombre === '' || $usuario === '' || $contrasena_plana === '') {
    header("Location: usuarios.php");
    exit();
}

if (!in_array($rol, ['cliente', 'administrador'], true)) {
    $rol = 'cliente';
}

$check = mysqli_prepare($conexion, "SELECT id FROM usuarios WHERE usuario = ?");
mysqli_stmt_bind_param($check, "s", $usuario);
mysqli_stmt_execute($check);
$resultado = mysqli_stmt_get_result($check);

if (mysqli_num_rows($resultado) > 0) {
    mysqli_stmt_close($check);
    header("Location: usuarios.php");
    exit();
}

mysqli_stmt_close($check);

$contrasena = password_hash($contrasena_plana, PASSWORD_DEFAULT);
$stmt = mysqli_prepare($conexion, "INSERT INTO usuarios (nombre, usuario, contrasena, rol) VALUES (?, ?, ?, ?)");
mysqli_stmt_bind_param($stmt, "ssss", $nombre, $usuario, $contrasena, $rol);
mysqli_stmt_execute($stmt);
mysqli_stmt_close($stmt);

header("Location: usuarios.php");
exit();
?>
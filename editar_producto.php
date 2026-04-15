<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: index.php");
    exit();
}
include 'conexion.php';

$id = $_GET['id'];
$producto = mysqli_fetch_assoc(mysqli_query($conexion, "SELECT * FROM productos WHERE id=$id"));

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = $_POST['nombre'];
    $categoria = $_POST['categoria'];
    $precio = $_POST['precio'];
    $stock = $_POST['stock'];
    
    mysqli_query($conexion, "UPDATE productos SET nombre='$nombre', categoria='$categoria', precio='$precio', stock='$stock' WHERE id=$id");
    header("Location: productos.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>PARMAP - Editar Producto</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background-color: #f5f5f5; display: flex; justify-content: center; align-items: center; height: 100vh; }
        .formulario { background: white; padding: 40px; border-radius: 10px; width: 400px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .formulario h2 { margin-bottom: 20px; color: #333; }
        input { width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 5px; font-size: 14px; }
        .btn-guardar { width: 100%; background-color: #E87722; color: white; padding: 12px; border: none; border-radius: 5px; cursor: pointer; font-size: 15px; }
        .btn-guardar:hover { background-color: #1a1a1a; }
        .btn-volver { display: block; text-align: center; margin-top: 10px; color: #999; text-decoration: none; }
    </style>
</head>
<body>
    <div class="formulario">
        <h2>✏️ Editar Producto</h2>
        <form method="POST">
            <input type="text" name="nombre" value="<?php echo $producto['nombre']; ?>" required>
            <input type="text" name="categoria" value="<?php echo $producto['categoria']; ?>" required>
            <input type="number" name="precio" value="<?php echo $producto['precio']; ?>" required>
            <input type="number" name="stock" value="<?php echo $producto['stock']; ?>" required>
            <button type="submit" class="btn-guardar">Guardar Cambios</button>
        </form>
        <a href="productos.php" class="btn-volver">← Volver a productos</a>
    </div>
</body>
</html>
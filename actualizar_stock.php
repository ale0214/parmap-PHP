<?php
include 'conexion.php';

$id_producto = $_POST['id_producto'];
$nuevo_stock = $_POST['nuevo_stock'];
$stock_minimo = $_POST['stock_minimo'];

// Primero actualizamos el stock en productos
$sql1 = "UPDATE productos SET stock = '$nuevo_stock' WHERE id = '$id_producto'";
mysqli_query($conexion, $sql1);

// Luego verificamos si ya existe en inventario
$check = "SELECT * FROM inventario WHERE id_producto = '$id_producto'";
$resultado = mysqli_query($conexion, $check);

if (mysqli_num_rows($resultado) > 0) {
    // Si existe lo actualizamos
    $sql2 = "UPDATE inventario SET stock_minimo = '$stock_minimo', ultima_actualizacion = NOW() WHERE id_producto = '$id_producto'";
} else {
    // Si no existe lo creamos
    $sql2 = "INSERT INTO inventario (id_producto, cantidad, stock_minimo) VALUES ('$id_producto', '$nuevo_stock', '$stock_minimo')";
}

mysqli_query($conexion, $sql2);
header("Location: inventario.php");
exit();
?>
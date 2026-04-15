<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: index.php");
    exit();
}
include 'conexion.php';

$total = $_POST['total'];
$productos = $_POST['productos'];
$cantidades = $_POST['cantidades'];

// Obtener id del usuario de la sesion
$usuario = mysqli_fetch_assoc(mysqli_query($conexion, "SELECT id FROM usuarios WHERE usuario='" . $_SESSION['usuario'] . "'"));
$id_usuario = $usuario['id'];

// Insertar la venta
mysqli_query($conexion, "INSERT INTO ventas (id_usuario, total) VALUES ('$id_usuario', '$total')");
$id_venta = mysqli_insert_id($conexion);

// Insertar cada producto del detalle
for ($i = 0; $i < count($productos); $i++) {
    $id_producto = $productos[$i];
    $cantidad = $cantidades[$i];
    
    if ($id_producto == '') continue;
    
    // Obtener precio del producto
    $prod = mysqli_fetch_assoc(mysqli_query($conexion, "SELECT precio FROM productos WHERE id=$id_producto"));
    $precio_unitario = $prod['precio'];
    $subtotal = $precio_unitario * $cantidad;
    
    // Insertar detalle
    mysqli_query($conexion, "INSERT INTO detalle_ventas (id_venta, id_producto, cantidad, precio_unitario, subtotal) 
        VALUES ('$id_venta', '$id_producto', '$cantidad', '$precio_unitario', '$subtotal')");
    
    // Descontar stock automaticamente
    mysqli_query($conexion, "UPDATE productos SET stock = stock - $cantidad WHERE id = $id_producto");
}

header("Location: ventas.php");
exit();
?>
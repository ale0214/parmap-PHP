<?php
include 'conexion.php';

$nombre = $_POST['nombre'];
$categoria = $_POST['categoria'];
$precio = $_POST['precio'];
$stock = $_POST['stock'];

$sql = "INSERT INTO productos (nombre, categoria, precio, stock) 
        VALUES ('$nombre', '$categoria', '$precio', '$stock')";

if (mysqli_query($conexion, $sql)) {
    header("Location: productos.php");
} else {
    echo "Error: " . mysqli_error($conexion);
}
?>
<?php
session_start();

if (!isset($_SESSION['carrito']) || count($_SESSION['carrito']) === 0) {
    header("Location: carrito.php");
    exit();
}

// Mantenemos una sola lógica de checkout para evitar errores entre flujos viejos y nuevos.
header("Location: checkout.php");
exit();
?>
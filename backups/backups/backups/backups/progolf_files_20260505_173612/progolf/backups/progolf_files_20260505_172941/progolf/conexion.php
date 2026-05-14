<?php
// Datos de conexión
$host = "localhost";
$user = "root";       // Usuario estándar de XAMPP
$pass = "";           // Contraseña vacía por defecto
$db   = "progolf";    // Tu base de datos

$conexion = mysqli_connect($host, $user, $pass, $db);

// Verificar si funciona
if (!$conexion) {
    die("Error al conectar con progolf: " . mysqli_connect_error());
}
?>
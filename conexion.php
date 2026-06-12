<?php
// Datos de conexión
$host = "127.0.0.1";
$port = 3306;
$user = "root";
$pass = "";
$db   = "progolf";

$conexion = mysqli_connect($host, $user, $pass, $db, $port);

// Verificar si funciona
if (!$conexion) {
    die("Error al conectar con progolf: " . mysqli_connect_error());
}
?>
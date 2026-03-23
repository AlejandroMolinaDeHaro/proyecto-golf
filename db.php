<?php
$host = "localhost";
$user = "root";
$password = "";
$database = "golf_web";

$conn = mysqli_connect($host, $user, $password);

if (!$conn) {
    die("Error de conexión: " . mysqli_connect_error());
}

// Crear base de datos si no existe
mysqli_query($conn, "CREATE DATABASE IF NOT EXISTS $database");
mysqli_select_db($conn, $database);

// Crear tabla usuarios
$sql = "CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
mysqli_query($conn, $sql);
?>

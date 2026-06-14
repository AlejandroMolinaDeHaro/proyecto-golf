<?php
$host = "127.0.0.1";
$port = 3306;
$user = "root";
$password = "";
$database = "progolf";

$conn = mysqli_connect($host, $user, $password, "", $port);

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
    rol VARCHAR(20) DEFAULT 'user',
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
mysqli_query($conn, $sql);
// Migración: agregar columna rol si no existe (para tablas ya creadas)
$check_rol = mysqli_query($conn, "SHOW COLUMNS FROM usuarios LIKE 'rol'");
if (mysqli_num_rows($check_rol) == 0) {
    mysqli_query($conn, "ALTER TABLE usuarios ADD COLUMN rol VARCHAR(20) DEFAULT 'user'");
}

// Crear tabla carrito
mysqli_query($conn, "CREATE TABLE IF NOT EXISTS carrito (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    producto_nombre VARCHAR(255) NOT NULL,
    producto_precio DECIMAL(10,2) NOT NULL,
    cantidad INT DEFAULT 1,
    fecha_agregado TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
)");

// Crear tabla encuestas
mysqli_query($conn, "CREATE TABLE IF NOT EXISTS encuestas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT DEFAULT NULL,
    mejor_jugador VARCHAR(255) NOT NULL,
    campo_torneo VARCHAR(255) NOT NULL,
    fecha_respuesta TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");
?>

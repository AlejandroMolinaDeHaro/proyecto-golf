<?php
// =========================================================
// DB_CONNECT.PHP - conexion a la base de datos MySQL
// =========================================================
// CONFIGURACION - cambiar si tu XAMPP es diferente
$host     = 'localhost';
$dbname   = 'golfpro';
$user     = 'root';
$password = ''; // en XAMPP por defecto no hay contraseña
 
try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8",
        $user,
        $password
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE,            PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
 
} catch (PDOException $e) {
    // mostrar pagina de error amigable en vez de pantalla blanca
    $msg = $e->getMessage();
    $hint = '';
 
    if (strpos($msg, 'Unknown database') !== false) {
        $hint = '
            <p>La base de datos <strong>golf_pro_apate</strong> no existe todavia.</p>
            <p><strong>Solucion:</strong></p>
            <ol>
                <li>Abre <a href="http://localhost/phpmyadmin" target="_blank">phpMyAdmin</a></li>
                <li>Haz click en <strong>Nueva</strong> (en el panel izquierdo)</li>
                <li>Escribe <strong>golf_pro_apate</strong> como nombre</li>
                <li>Haz click en <strong>Crear</strong></li>
                <li>Ve a la pestaña <strong>SQL</strong> y pega el contenido de <strong>database.sql</strong></li>
                <li>Haz click en <strong>Continuar</strong></li>
            </ol>';
    } elseif (strpos($msg, 'Access denied') !== false) {
        $hint = '
            <p>Usuario o contraseña incorrectos.</p>
            <p>Abre <strong>db_connect.php</strong> y comprueba que <code>$user</code> y <code>$password</code> son correctos.</p>
            <p>En XAMPP por defecto: usuario = <strong>root</strong>, contraseña = <strong>vacia</strong></p>';
    } elseif (strpos($msg, 'Connection refused') !== false || strpos($msg, "Can't connect") !== false) {
        $hint = '
            <p>MySQL no esta arrancado.</p>
            <p><strong>Solucion:</strong> Abre el Panel de Control de XAMPP y pulsa <strong>START</strong> en MySQL.</p>';
    } else {
        $hint = '<p>Error: ' . htmlspecialchars($msg) . '</p>';
    }
 
    // mostrar pagina de error con instrucciones
    die('<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Error de base de datos - Golf Pro Apate</title>
    <style>
        body { font-family: Arial, sans-serif; background: #1b5e20; display:flex; align-items:center; justify-content:center; min-height:100vh; margin:0; }
        .box { background:white; border-radius:8px; padding:40px; max-width:600px; width:90%; border-top:5px solid #f44336; }
        h2 { color:#c62828; font-family:"Times New Roman",serif; margin-bottom:16px; }
        p  { color:#555; margin-bottom:10px; line-height:1.6; }
        ol { color:#555; padding-left:20px; line-height:2; }
        code { background:#f5f5f5; padding:2px 6px; border-radius:3px; font-size:14px; }
        a  { color:#2e7d32; }
        .back { display:inline-block; margin-top:20px; padding:10px 20px; background:#2e7d32; color:#ffd700; text-decoration:none; border-radius:4px; font-weight:bold; }
    </style>
</head>
<body>
    <div class="box">
        <h2>⚠️ Error de conexion a la base de datos</h2>
        ' . $hint . '
        <a href="index.php" class="back">← Volver al inicio</a>
    </div>
</body>
</html>');
}
?>

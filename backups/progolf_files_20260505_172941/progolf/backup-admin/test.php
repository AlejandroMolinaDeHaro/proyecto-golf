<?php
session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    die('No autorizado');
}

echo "<h2>Diagnóstico del Sistema</h2>";

// Verificar si exec está disponible
echo "<h3>Funciones PHP:</h3>";
echo "exec(): " . (function_exists('exec') ? '✓ Disponible' : '✗ Deshabilitado') . "<br>";
echo "shell_exec(): " . (function_exists('shell_exec') ? '✓ Disponible' : '✗ Deshabilitado') . "<br>";

// Verificar directorio de backups
echo "<h3>Directorio de backups:</h3>";
$backup_dir = '/opt/lampp/htdocs/progolf/backups';
if (!is_dir($backup_dir)) {
    mkdir($backup_dir, 0777, true);
    echo "Directorio creado<br>";
}
echo "Existe: " . (is_dir($backup_dir) ? '✓ Sí' : '✗ No') . "<br>";
echo "Escribible: " . (is_writable($backup_dir) ? '✓ Sí' : '✗ No') . "<br>";
chmod($backup_dir, 0777);
echo "Después de chmod 777: " . (is_writable($backup_dir) ? '✓ Sí' : '✗ No') . "<br>";

// Verificar comandos
echo "<h3>Comandos del sistema:</h3>";
$commands = ['tar', '/opt/lampp/bin/mysqldump', 'gzip', 'find'];
foreach ($commands as $cmd) {
    $path = $cmd[0] == '/' ? $cmd : trim(shell_exec('which ' . $cmd));
    echo "$cmd: " . (file_exists($path) ? '✓ Existe' : '✗ No encontrado') . "<br>";
}

// Test de escritura
echo "<h3>Test de escritura:</h3>";
$test_file = $backup_dir . '/test_' . time() . '.txt';
if (file_put_contents($test_file, 'test') !== false) {
    echo "✓ Escritura exitosa<br>";
    unlink($test_file);
} else {
    echo "✗ Error al escribir<br>";
}

// Probar mysqldump
echo "<h3>Test MySQL:</h3>";
$output = [];
$return_var = 0;
exec('/opt/lampp/bin/mysqldump --version 2>&1', $output, $return_var);
echo "mysqldump: " . ($return_var === 0 ? '✓ Funciona' : '✗ Error: ' . implode(', ', $output)) . "<br>";

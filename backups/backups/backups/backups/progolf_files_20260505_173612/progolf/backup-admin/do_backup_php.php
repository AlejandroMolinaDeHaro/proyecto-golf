<?php
session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    die(json_encode(['success' => false, 'message' => 'No autorizado']));
}

$backup_dir = '/opt/lampp/htdocs/progolf/backups';
$web_dir = '/opt/lampp/htdocs/progolf';
$db_name = 'progolf';
$db_user = 'root';
$db_pass = '';
$date = date('Ymd_His');

// Crear directorio si no existe
if (!is_dir($backup_dir)) {
    if (!mkdir($backup_dir, 0777, true)) {
        die(json_encode(['success' => false, 'message' => 'No se pudo crear directorio de backups']));
    }
}
@chmod($backup_dir, 0777);

$errors = [];
$success = [];

// Backup archivos web usando tar.gz
$tar_file = $backup_dir . '/progolf_files_' . $date . '.tar.gz';
exec('tar -czf ' . escapeshellarg($tar_file) . ' -C /opt/lampp/htdocs progolf 2>&1', $output, $return_var);
if ($return_var === 0 && file_exists($tar_file)) {
    $success[] = 'Archivos web respaldados (TAR.GZ)';
} else {
    // Método alternativo: crear directorio copiando archivos
    $backup_files_dir = $backup_dir . '/progolf_files_' . $date;
    mkdir($backup_files_dir, 0777, true);
    exec('cp -r ' . escapeshellarg($web_dir) . ' ' . escapeshellarg($backup_files_dir) . ' 2>&1', $output2, $return_var2);
    if ($return_var2 === 0) {
        $success[] = 'Archivos web respaldados (directorio)';
    } else {
        $errors[] = 'Error al respaldar archivos: ' . implode(', ', array_merge($output, $output2));
    }
}

// Backup base de datos usando PHP
$sql_file = $backup_dir . '/progolf_db_' . $date . '.sql';
$conn = new mysqli('localhost', $db_user, $db_pass, $db_name);
if ($conn->connect_error) {
    $errors[] = 'Error de conexión a BD: ' . $conn->connect_error;
} else {
    $sql_content = "-- Backup de $db_name generado el " . date('Y-m-d H:i:s') . "\n";
    $sql_content .= "-- Servidor: localhost\n\n";
    
    // Obtener tablas
    $tables = [];
    $result = $conn->query('SHOW TABLES');
    while ($row = $result->fetch_row()) {
        $tables[] = $row[0];
    }
    
    foreach ($tables as $table) {
        // Estructura de la tabla
        $sql_content .= "\n-- Estructura de tabla: $table\n";
        $sql_content .= "DROP TABLE IF EXISTS `$table`;\n";
        $res = $conn->query("SHOW CREATE TABLE `$table`");
        $row = $res->fetch_row();
        $sql_content .= $row[1] . ";\n\n";
        
        // Datos de la tabla
        $sql_content .= "-- Datos de tabla: $table\n";
        $res = $conn->query("SELECT * FROM `$table`");
        while ($row = $res->fetch_assoc()) {
            $keys = array_keys($row);
            $values = array_map(function($val) use ($conn) {
                return "'" . $conn->real_escape_string($val) . "'";
            }, array_values($row));
            $sql_content .= "INSERT INTO `$table` (`" . implode('`,`', $keys) . "`) VALUES (" . implode(',', $values) . ");\n";
        }
    }
    
    if (file_put_contents($sql_file, $sql_content) !== false) {
        exec('gzip ' . escapeshellarg($sql_file));
        $success[] = 'Base de datos respaldada';
    } else {
        $errors[] = 'Error al escribir archivo SQL';
    }
    $conn->close();
}

// Limpiar backups antiguos (7 días)
$files = glob($backup_dir . '/*');
foreach ($files as $file) {
    if (is_file($file) && time() - filemtime($file) > 7 * 24 * 60 * 60) {
        unlink($file);
    }
}

// Escribir log
$log = '[' . date('Y-m-d H:i:s') . '] Backup manual: ' . (empty($errors) ? 'ÉXITO - ' : 'ERROR - ') . implode(', ', array_merge($success, $errors)) . PHP_EOL;
file_put_contents($backup_dir . '/backup.log', $log, FILE_APPEND);

if (!empty($errors)) {
    echo json_encode(['success' => false, 'message' => implode('<br>', $errors)]);
} else {
    echo json_encode(['success' => true, 'message' => 'Backup completado: ' . implode(', ', $success)]);
}

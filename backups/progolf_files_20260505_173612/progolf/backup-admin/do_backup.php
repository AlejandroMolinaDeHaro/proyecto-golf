<?php
session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    die(json_encode(['success' => false, 'message' => 'No autorizado']));
}

$backup_dir = '/home/pol/backups/progolf';
$web_dir = '/opt/lampp/htdocs/progolf';
$db_name = 'progolf';
$db_user = 'root';
$db_pass = '';
$date = date('Ymd_His');

// Crear directorio si no existe
if (!is_dir($backup_dir)) {
    mkdir($backup_dir, 0777, true);
}
@chmod($backup_dir, 0777);

$errors = [];
$success = [];

// Backup archivos web - usando PHP para crear archivo tar
$zip_file = $backup_dir . '/progolf_files_' . $date . '.tar.gz';
// Intentar con exec
$output = [];
$return_var = 0;
exec('tar -czf ' . escapeshellarg($zip_file) . ' -C /opt/lampp/htdocs progolf 2>&1', $output, $return_var);
if ($return_var === 0 && file_exists($zip_file)) {
    $success[] = 'Archivos web respaldados';
} else {
    // Intentar método alternativo con PHP
    exec('cd /opt/lampp/htdocs && tar -czf ' . escapeshellarg($zip_file) . ' progolf 2>&1', $output2, $return_var2);
    if ($return_var2 === 0 && file_exists($zip_file)) {
        $success[] = 'Archivos web respaldados';
    } else {
        $errors[] = 'Error al respaldar archivos: ' . implode(', ', array_merge($output, $output2));
    }
}

// Backup base de datos
$sql_file = $backup_dir . '/progolf_db_' . $date . '.sql';
exec('/opt/lampp/bin/mysqldump -u ' . escapeshellarg($db_user) . ' --password=' . escapeshellarg($db_pass) . ' ' . escapeshellarg($db_name) . ' > ' . escapeshellarg($sql_file) . ' 2>&1', $output3, $return_var3);
if ($return_var3 === 0 && file_exists($sql_file)) {
    exec('gzip ' . escapeshellarg($sql_file));
    $success[] = 'Base de datos respaldada';
} else {
    $errors[] = 'Error al respaldar BD (código ' . $return_var3 . '): ' . implode(', ', $output3);
}

// Limpiar backups antiguos (7 días)
exec('find ' . escapeshellarg($backup_dir) . ' -type f -mtime +7 -delete 2>&1');

// Escribir log
$log = '[' . date('Y-m-d H:i:s') . '] Backup manual: ' . (empty($errors) ? 'ÉXITO - ' : 'ERROR - ') . implode(', ', array_merge($success, $errors)) . PHP_EOL;
file_put_contents($backup_dir . '/backup.log', $log, FILE_APPEND);

if (!empty($errors)) {
    echo json_encode(['success' => false, 'message' => implode('<br>', $errors)]);
} else {
    echo json_encode(['success' => true, 'message' => 'Backup completado: ' . implode(', ', $success)]);
}

<?php
set_time_limit(0);
$correct_token = 'admin123';
$token = $_GET['token'] ?? ($_POST['token'] ?? '');
if ($token !== $correct_token) {
    die('Acceso denegado');
}

$backup_dir = __DIR__ . '/../../backups';
@mkdir($backup_dir, 0777, true);
@chmod($backup_dir, 0777);

$date = date('Ymd_His');
$errors = [];
$success = [];

// Backup archivos
$zip_file = $backup_dir . '/progolf_files_' . $date . '.zip';
$zip = new ZipArchive();
if ($zip->open($zip_file, ZipArchive::CREATE) === TRUE) {
    $dir = __DIR__ . '/../..';
    $files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::LEAVES_ONLY
    );
    foreach ($files as $file) {
        if (!$file->isDir()) {
            $filePath = $file->getRealPath();
            $relativePath = substr($filePath, strlen(realpath($dir)) + 1);
            if (strpos($relativePath, 'backups/') !== 0 && strpos($relativePath, 'admin/api/') !== 0 && strpos($relativePath, '.git/') !== 0) {
                $zip->addFile($filePath, $relativePath);
            }
        }
    }
    $zip->close();
    $success[] = 'Archivos web respaldados (ZIP)';
} else {
    $errors[] = 'Error al crear ZIP';
}

// Backup BD (si MySQL está disponible)
$sql_file = $backup_dir . '/progolf_db_' . $date . '.sql';
$conn = @new mysqli('127.0.0.1', 'root', '', 'progolf', 3306);
if ($conn->connect_error) {
    $success[] = 'Base de datos no disponible, solo archivos';
} else {
    $sql_content = "-- Backup " . date('Y-m-d H:i:s') . "\nSET FOREIGN_KEY_CHECKS = 0;\n";
    $tables = [];
    $res = $conn->query('SHOW TABLES');
    while ($row = $res->fetch_row()) $tables[] = $row[0];
    foreach ($tables as $table) {
        $sql_content .= "\nDROP TABLE IF EXISTS `$table`;\n";
        $res2 = $conn->query("SHOW CREATE TABLE `$table`");
        $row2 = $res2->fetch_row();
        $sql_content .= $row2[1] . ";\n";
        $res3 = $conn->query("SELECT * FROM `$table`");
        while ($row3 = $res3->fetch_assoc()) {
            $keys = array_keys($row3);
            $vals = array_map(function($v) use ($conn) {
                return is_null($v) ? 'NULL' : "'" . $conn->real_escape_string($v) . "'";
            }, array_values($row3));
            $sql_content .= "INSERT INTO `$table` (`" . implode('`,`', $keys) . "`) VALUES (" . implode(',', $vals) . ");\n";
        }
    }
    $sql_content .= "\nSET FOREIGN_KEY_CHECKS = 1;\n";
    if (file_put_contents($sql_file, $sql_content) !== false) {
        exec('gzip ' . escapeshellarg($sql_file));
        $success[] = 'Base de datos respaldada';
    } else {
        $errors[] = 'Error al escribir SQL';
    }
    $conn->close();
}

// Limpiar backups > 7 días
foreach (glob($backup_dir . '/*') as $f) {
    if (is_file($f) && time() - filemtime($f) > 7*24*60*60) @unlink($f);
}

$log = '[' . date('Y-m-d H:i:s') . '] ' . (empty($errors) ? 'OK' : 'ERROR') . ': ' . implode(', ', array_merge($success, $errors)) . "\n";
file_put_contents($backup_dir . '/backup.log', $log, FILE_APPEND);

$result = [
    'success' => empty($errors),
    'message' => implode('<br>', array_merge($success, $errors))
];

if (!empty($_GET['redirect'])) {
    $status = $result['success'] ? 'success' : 'error';
    header('Location: ../index.php?token=' . urlencode($correct_token) . '&section=backups&msg=' . urlencode($result['message']) . '&status=' . $status);
    exit;
}

echo json_encode($result);

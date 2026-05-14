<?php
session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    die(json_encode(['success' => false, 'message' => 'No autorizado']));
}

if (!isset($_POST['file'])) {
    die(json_encode(['success' => false, 'message' => 'No se especificó archivo']));
}

$backup_dir = '/opt/lampp/htdocs/progolf/backups';
$web_dir = '/opt/lampp/htdocs/progolf';
$db_name = 'progolf';
$db_user = 'root';
$db_pass = '';

$file = basename($_POST['file']);
$file_path = $backup_dir . '/' . $file;

if (!file_exists($file_path)) {
    die(json_encode(['success' => false, 'message' => 'Archivo no encontrado']));
}

$errors = [];
$success = [];

// Restaurar archivos web
if (strpos($file, 'files_') !== false) {
    // Si es un directorio de backup
    if (is_dir($file_path)) {
        // Eliminar archivos actuales excepto backup-admin y backups
        $exclude = ['backup-admin', 'backups', 'backups'];
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($web_dir, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );
        foreach ($files as $fileinfo) {
            $rel_path = str_replace($web_dir . '/', '', $fileinfo->getPathname());
            if (!in_array(explode('/', $rel_path)[0], $exclude)) {
                if ($fileinfo->isDir()) {
                    @rmdir($fileinfo->getRealPath());
                } else {
                    @unlink($fileinfo->getRealPath());
                }
            }
        }
        
        // Copiar archivos del backup
        exec('cp -r ' . escapeshellarg($file_path . '/progolf') . ' ' . escapeshellarg($web_dir . '/') . ' 2>&1', $output, $return_var);
        if ($return_var === 0) {
            $success[] = 'Archivos restaurados desde directorio';
        } else {
            $errors[] = 'Error al restaurar desde directorio: ' . implode(', ', $output);
        }
    }
    // Si es un archivo tar.gz
    elseif (strpos($file, '.tar.gz') !== false) {
        // Eliminar archivos actuales excepto backup-admin y backups
        $exclude = ['backup-admin', 'backups', 'backups'];
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($web_dir, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );
        foreach ($files as $fileinfo) {
            $rel_path = str_replace($web_dir . '/', '', $fileinfo->getPathname());
            if (!in_array(explode('/', $rel_path)[0], $exclude)) {
                if ($fileinfo->isDir()) {
                    @rmdir($fileinfo->getRealPath());
                } else {
                    @unlink($fileinfo->getRealPath());
                }
            }
        }
        
        // Extraer tar.gz
        exec('tar -xzf ' . escapeshellarg($file_path) . ' -C /opt/lampp/htdocs 2>&1', $output, $return_var);
        if ($return_var === 0) {
            $success[] = 'Archivos restaurados desde TAR.GZ';
        } else {
            $errors[] = 'Error al extraer TAR.GZ: ' . implode(', ', $output);
        }
    }
}
// Restaurar base de datos
elseif (strpos($file, 'db_') !== false) {
    $sql_file = $file_path;
    
    // Si es .sql.gz, descomprimir primero
    if (strpos($file, '.sql.gz') !== false) {
        exec('gunzip -c ' . escapeshellarg($file_path) . ' > ' . escapeshellarg($file_path . '.tmp') . ' 2>&1', $output0, $return_var0);
        if ($return_var0 === 0) {
            $sql_file = $file_path . '.tmp';
        } else {
            $errors[] = 'Error al descomprimir .gz: ' . implode(', ', $output0);
        }
    }
    
    if (empty($errors)) {
        $conn = new mysqli('localhost', $db_user, $db_pass);
        if ($conn->connect_error) {
            $errors[] = 'Error de conexión a BD: ' . $conn->connect_error;
        } else {
            // Eliminar BD y recrear
            $conn->query("DROP DATABASE IF EXISTS `$db_name`");
            $conn->query("CREATE DATABASE `$db_name`");
            $conn->select_db($db_name);
            
            // Ejecutar SQL
            $sql_content = file_get_contents($sql_file);
            if ($sql_content) {
                // Dividir por ; para ejecutar múltiples consultas
                $queries = array_filter(array_map('trim', explode(';', $sql_content)));
                foreach ($queries as $query) {
                    if (!empty($query)) {
                        $conn->query($query);
                    }
                }
                $success[] = 'Base de datos restaurada';
            } else {
                $errors[] = 'Error al leer archivo SQL';
            }
            $conn->close();
            
            // Eliminar archivo temporal
            if (isset($sql_file) && $sql_file != $file_path) {
                unlink($sql_file);
            }
        }
    }
}

// Escribir log
$log = '[' . date('Y-m-d H:i:s') . '] Restauración de ' . $file . ': ' . (empty($errors) ? 'ÉXITO - ' : 'ERROR - ') . implode(', ', array_merge($success, $errors)) . PHP_EOL;
file_put_contents($backup_dir . '/backup.log', $log, FILE_APPEND);

if (!empty($errors)) {
    echo json_encode(['success' => false, 'message' => implode('<br>', $errors)]);
} else {
    echo json_encode(['success' => true, 'message' => 'Restauración completada: ' . implode(', ', $success)]);
}

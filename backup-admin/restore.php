<?php
// Configuración simple - token fijo
$correct_token = 'admin123';

// Verificar token
$token = $_GET['token'] ?? ($_POST['token'] ?? '');
if ($token !== $correct_token) {
    die(json_encode(['success' => false, 'message' => 'Acceso denegado']));
}

if (!isset($_POST['file'])) {
    die(json_encode(['success' => false, 'message' => 'No especificó archivo']));
}

$backup_dir = '/opt/lampp/htdocs/progolf/backups';
$web_dir = '/opt/lampp/htdocs/progolf';

$file = basename($_POST['file']);
$file_path = $backup_dir . '/' . $file;

if (!file_exists($file_path)) {
    die(json_encode(['success' => false, 'message' => 'Archivo no encontrado: ' . $file_path]));
}

$errors = [];
$success = [];

// Restaurar archivos web
if (strpos($file, 'files_') !== false) {
    $zip = new ZipArchive();
    if ($zip->open($file_path) === TRUE) {
        $count = 0;
        // Extraer archivo por archivo
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $filename = $zip->getNameIndex($i);
            
            // Saltar directorios y archivos especiales
            if (substr($filename, -1) == '/' || strpos($filename, '__MACOSX') !== false) {
                continue;
            }
            
            // Saltar backups y backup-admin del ZIP
            if (strpos($filename, 'backups/') === 0 || strpos($filename, 'backup-admin/') === 0) {
                continue;
            }
            
            // Leer contenido del archivo desde el ZIP
            $content = $zip->getFromIndex($i);
            if ($content !== false) {
                $dest_path = $web_dir . '/' . $filename;
                $dest_dir = dirname($dest_path);
                
                // Crear directorio si no existe
                if (!is_dir($dest_dir)) {
                    mkdir($dest_dir, 0777, true);
                }
                
                // Escribir archivo
                if (file_put_contents($dest_path, $content) !== false) {
                    chmod($dest_path, 0666);
                    $count++;
                }
            }
        }
        $zip->close();
        
        if ($count > 0) {
            $success[] = 'Archivos restaurados desde ZIP (' . $count . ' archivos)';
        } else {
            $errors[] = 'No se restauró ningún archivo (¿ZIP vacío o sin archivos válidos?)';
        }
    } else {
        $errors[] = 'Error al abrir ZIP';
    }
}

// Restaurar base de datos
elseif (strpos($file, 'db_') !== false) {
    $sql_file = $file_path;
    
    if (substr($file, -7) === '.sql.gz') {
        exec('gunzip -c ' . escapeshellarg($file_path) . ' > ' . escapeshellarg($file_path . '.tmp'));
        $sql_file = $file_path . '.tmp';
    }
    
    $sql = file_get_contents($sql_file);
    if ($sql) {
        $conn = new mysqli('localhost', 'root', '');
        if ($conn->connect_error) {
            $errors[] = 'Error BD: ' . $conn->connect_error;
        } else {
            $conn->query("DROP DATABASE IF EXISTS `progolf`");
            $conn->query("CREATE DATABASE `progolf`");
            $conn->select_db('progolf');
            
            if ($conn->multi_query($sql)) {
                do {
                    if ($result = $conn->store_result()) {
                        $result->free();
                    }
                } while ($conn->more_results() && $conn->next_result());
            }
            
            if ($conn->error) {
                $errors[] = 'Error al restaurar BD: ' . $conn->error;
            } else {
                $success[] = 'Base de datos restaurada';
            }
            $conn->close();
        }
        if ($sql_file != $file_path) @unlink($sql_file);
    } else {
        $errors[] = 'Error al leer SQL';
    }
}

// Log
$log_msg = '[' . date('Y-m-d H:i:s') . '] Restaurar ' . $file . ': ' . (empty($errors) ? 'OK' : 'ERROR') . ': ' . implode(', ', array_merge($success, $errors)) . "\n";
file_put_contents($backup_dir . '/backup.log', $log_msg, FILE_APPEND);

echo json_encode([
    'success' => empty($errors),
    'message' => implode('<br>', array_merge($success, $errors))
]);
?>

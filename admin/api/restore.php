<?php
set_time_limit(0);
$correct_token = 'admin123';
$token = $_GET['token'] ?? ($_POST['token'] ?? '');
if ($token !== $correct_token) {
    echo json_encode(['success' => false, 'message' => 'Acceso denegado']);
    exit;
}

$backup_dir = __DIR__ . '/../../backups';
$file = basename($_POST['file'] ?? '');
$path = $backup_dir . '/' . $file;

if (!file_exists($path)) {
    $msg = 'Archivo no encontrado: ' . $file;
    if (!empty($_POST['redirect'])) {
        header('Location: ../index.php?token=' . urlencode($correct_token) . '&section=backups&msg=' . urlencode($msg) . '&status=error');
        exit;
    }
    echo json_encode(['success' => false, 'message' => $msg]);
    exit;
}

$errors = [];
$success = [];
$ext = pathinfo($path, PATHINFO_EXTENSION);

// Restaurar ZIP (archivos del proyecto)
if ($ext === 'zip') {
    $zip = new ZipArchive();
    if ($zip->open($path) === TRUE) {
        $project_dir = realpath(__DIR__ . '/../..');
        $extract_to = $project_dir . '/restaurado_temp';
        @mkdir($extract_to, 0777, true);
        $zip->extractTo($extract_to);
        $zip->close();

        $restored = 0;
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($extract_to, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::LEAVES_ONLY
        );
        foreach ($iterator as $item) {
            if (!$item->isDir()) {
                $rel = substr($item->getRealPath(), strlen(realpath($extract_to)) + 1);
                $dest = $project_dir . '/' . $rel;
                $dest_dir = dirname($dest);
                if (!is_dir($dest_dir)) mkdir($dest_dir, 0777, true);
                copy($item->getRealPath(), $dest);
                $restored++;
            }
        }

        // Limpiar temp
        $del = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($extract_to, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );
        foreach ($del as $d) $d->isDir() ? rmdir($d->getRealPath()) : unlink($d->getRealPath());
        rmdir($extract_to);

        $success[] = 'Archivos restaurados desde ZIP (' . $restored . ' archivos)';
    } else {
        $errors[] = 'Error al abrir ZIP';
    }
}
// Restaurar BD (.sql o .sql.gz)
elseif ($ext === 'sql' || $ext === 'gz') {
    if ($ext === 'sql') {
        $sql = file_get_contents($path);
    } else {
        $sql = gzfile($path);
        $sql = implode('', $sql);
    }

    $conn = new mysqli('127.0.0.1', 'root', '', 'progolf', 3306);
    if ($conn->connect_error) {
        $msg = 'Error de conexión: ' . $conn->connect_error;
        if (!empty($_POST['redirect'])) {
            header('Location: ../index.php?token=' . urlencode($correct_token) . '&section=backups&msg=' . urlencode($msg) . '&status=error');
            exit;
        }
        echo json_encode(['success' => false, 'message' => $msg]);
        exit;
    }

    if ($conn->multi_query($sql)) {
        do { } while ($conn->more_results() && $conn->next_result());
        $success[] = 'Base de datos restaurada desde: ' . $file;
    } else {
        $errors[] = 'Error al restaurar BD: ' . $conn->error;
    }
    $conn->close();
} else {
    $msg = 'Formato no soportado. Usa: .sql, .sql.gz (BD) o .zip (archivos)';
    if (!empty($_POST['redirect'])) {
        header('Location: ../index.php?token=' . urlencode($correct_token) . '&section=backups&msg=' . urlencode($msg) . '&status=error');
        exit;
    }
    echo json_encode(['success' => false, 'message' => $msg]);
    exit;
}

$result = [
    'success' => empty($errors),
    'message' => implode('<br>', array_merge($success, $errors))
];

if (!empty($_POST['redirect'])) {
    $status = $result['success'] ? 'success' : 'error';
    header('Location: ../index.php?token=' . urlencode($correct_token) . '&section=backups&msg=' . urlencode($result['message']) . '&status=' . $status);
    exit;
}

echo json_encode($result);

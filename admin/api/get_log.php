<?php
$correct_token = 'admin123';
$token = $_GET['token'] ?? ($_POST['token'] ?? '');
if ($token !== $correct_token) {
    die('Acceso denegado');
}

$log_file = __DIR__ . '/../../backups/backup.log';
if (file_exists($log_file)) {
    echo file_get_contents($log_file);
} else {
    echo 'No hay registros de backup.';
}

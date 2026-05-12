<?php
// Configuración simple - token fijo
$correct_token = 'admin123';

// Verificar token
$token = $_GET['token'] ?? ($_POST['token'] ?? '');
if ($token !== $correct_token) {
    die('Acceso denegado. Usa: ?token=admin123');
}

$backup_dir = '/opt/lampp/htdocs/progolf/backups';
$log_file = $backup_dir . '/backup.log';

if (file_exists($log_file)) {
    $content = file_get_contents($log_file);
    echo htmlspecialchars($content);
} else {
    echo 'No hay registros de backup aún.';
}
?>

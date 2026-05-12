<?php
$backup_dir = '/opt/lampp/htdocs/progolf/backups';
$file = isset($_GET['file']) ? basename($_GET['file']) : '';
$path = $backup_dir . '/' . $file;

if (empty($file) || !file_exists($path)) {
    http_response_code(404);
    die('Archivo no encontrado');
}

header('Content-Description: File Transfer');
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . $file . '"');
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Pragma: public');
header('Content-Length: ' . filesize($path));
readfile($path);
exit;

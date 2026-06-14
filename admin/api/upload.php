<?php
$correct_token = 'admin123';
$token = $_POST['token'] ?? ($_GET['token'] ?? '');
if ($token !== $correct_token) {
    die('Acceso denegado');
}

$backup_dir = __DIR__ . '/../../backups';

if (!isset($_FILES['backup_file']) || $_FILES['backup_file']['error'] !== UPLOAD_ERR_OK) {
    $error_code = $_FILES['backup_file']['error'] ?? -1;
    $msg = 'Error al subir archivo (código: ' . $error_code . ')';
    if (!empty($_POST['redirect'])) {
        header('Location: ../index.php?token=' . urlencode($correct_token) . '&section=backups&msg=' . urlencode($msg) . '&status=error');
        exit;
    }
    echo json_encode(['success' => false, 'message' => $msg]);
    exit;
}

$ext = strtolower(pathinfo($_FILES['backup_file']['name'], PATHINFO_EXTENSION));
if (!in_array($ext, ['sql', 'gz', 'zip'])) {
    $msg = 'Formato no soportado. Solo .sql, .sql.gz o .zip';
    if (!empty($_POST['redirect'])) {
        header('Location: ../index.php?token=' . urlencode($correct_token) . '&section=backups&msg=' . urlencode($msg) . '&status=error');
        exit;
    }
    echo json_encode(['success' => false, 'message' => $msg]);
    exit;
}

$dest = $backup_dir . '/' . basename($_FILES['backup_file']['name']);

// Si ya existe, agregar sufijo numérico
if (file_exists($dest)) {
    $info = pathinfo($_FILES['backup_file']['name']);
    $i = 1;
    while (file_exists($backup_dir . '/' . $info['filename'] . '_' . $i . '.' . $info['extension'])) $i++;
    $dest = $backup_dir . '/' . $info['filename'] . '_' . $i . '.' . $info['extension'];
}

if (move_uploaded_file($_FILES['backup_file']['tmp_name'], $dest)) {
    $msg = 'Backup subido: ' . basename($dest);
    if (!empty($_POST['redirect'])) {
        header('Location: ../index.php?token=' . urlencode($correct_token) . '&section=backups&msg=' . urlencode($msg) . '&status=success');
        exit;
    }
    echo json_encode(['success' => true, 'message' => $msg]);
} else {
    $msg = 'Error al guardar el archivo';
    if (!empty($_POST['redirect'])) {
        header('Location: ../index.php?token=' . urlencode($correct_token) . '&section=backups&msg=' . urlencode($msg) . '&status=error');
        exit;
    }
    echo json_encode(['success' => false, 'message' => $msg]);
}

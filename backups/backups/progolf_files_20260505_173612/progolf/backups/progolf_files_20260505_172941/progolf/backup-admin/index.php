<?php
session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: login.php');
    exit;
}
$backup_dir = '/opt/lampp/htdocs/progolf/backups';
$script_path = '/home/pol/backup_progolf.sh';
$log_file = $backup_dir . '/backup.log';

// Ejecutar backup manual - ahora se hace vía AJAX

// Eliminar backup
if (isset($_GET['delete'])) {
    $file = basename($_GET['delete']);
    $path = $backup_dir . '/' . $file;
    if (file_exists($path) && unlink($path)) {
        $message = 'Backup eliminado: ' . $file;
    }
}

// Listar backups
$backups = [];
if (is_dir($backup_dir)) {
    $files = scandir($backup_dir, SCANDIR_SORT_DESCENDING);
    foreach ($files as $file) {
        if ($file != '.' && $file != '..' && $file != 'backup.log') {
            $path = $backup_dir . '/' . $file;
            $backups[] = [
                'name' => $file,
                'size' => filesize($path),
                'date' => date('Y-m-d H:i:s', filemtime($path)),
                'type' => strpos($file, 'db_') !== false ? 'BD' : 'Archivos'
            ];
        }
    }
}

// Obtener último backup
$last_backup = 'Nunca';
if (!empty($backups)) {
    $last_backup = $backups[0]['date'];
}

// Espacio en disco
$disk_free = disk_free_space($backup_dir);
$disk_total = disk_total_space($backup_dir);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Backup - Progolf</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background: #f4f4f4; }
        .header { background: #2c3e50; color: white; padding: 20px; text-align: center; }
        .container { max-width: 1200px; margin: 20px auto; padding: 0 20px; }
        .stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .stat-card { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .stat-card h3 { color: #7f8c8d; font-size: 14px; margin-bottom: 10px; }
        .stat-card .value { font-size: 24px; font-weight: bold; color: #2c3e50; }
        .btn { padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; font-size: 14px; text-decoration: none; display: inline-block; }
        .btn-primary { background: #3498db; color: white; }
        .btn-primary:hover { background: #2980b9; }
        .btn-danger { background: #e74c3c; color: white; }
        .btn-success { background: #27ae60; color: white; }
        .btn-small { padding: 5px 10px; font-size: 12px; }
        .card { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #f8f9fa; font-weight: bold; }
        .message { padding: 15px; margin-bottom: 20px; border-radius: 4px; }
        .message.success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .message.error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .empty { text-align: center; padding: 40px; color: #7f8c8d; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Panel de Administración de Backups</h1>
        <p>Progolf - Gestión de Copias de Seguridad</p>
    </div>

    <div class="container">
        <?php if (isset($message)): ?>
            <div class="message <?php echo strpos($message, 'Error') !== false ? 'error' : 'success'; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <div class="stats">
            <div class="stat-card">
                <h3>Último Backup</h3>
                <div class="value"><?php echo $last_backup; ?></div>
            </div>
            <div class="stat-card">
                <h3>Total Backups</h3>
                <div class="value"><?php echo count($backups); ?></div>
            </div>
            <div class="stat-card">
                <h3>Espacio Libre</h3>
                <div class="value"><?php echo round($disk_free / 1024 / 1024 / 1024, 2); ?> GB</div>
            </div>
            <div class="stat-card">
                <h3>Espacio Total</h3>
                <div class="value"><?php echo round($disk_total / 1024 / 1024 / 1024, 2); ?> GB</div>
            </div>
        </div>

        <div class="card">
            <h2 style="margin-bottom: 20px;">Acciones</h2>
            <button onclick="ejecutarBackup()" class="btn btn-primary" id="btnBackup">
                Ejecutar Backup Manual
            </button>
            <a href="?view=log" class="btn btn-success" style="margin-left: 10px;">Ver Log</a>
            <div id="backupResult" style="margin-top: 15px;"></div>
        </div>

        <script>
        function ejecutarBackup() {
            const btn = document.getElementById('btnBackup');
            const result = document.getElementById('backupResult');
            btn.disabled = true;
            btn.textContent = 'Ejecutando backup...';
            result.innerHTML = '<div style="color: #3498db;">⏳ Ejecutando backup, por favor espera...</div>';

            fetch('do_backup_php.php')
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        result.innerHTML = '<div class="message success">' + data.message + '</div>';
                        setTimeout(() => location.reload(), 2000);
                    } else {
                        result.innerHTML = '<div class="message error">' + data.message + '</div>';
                    }
                })
                .catch(err => {
                    result.innerHTML = '<div class="message error">Error: ' + err + '</div>';
                })
                .finally(() => {
                    btn.disabled = false;
                    btn.textContent = 'Ejecutar Backup Manual';
                });
        }
        </script>

        <?php if (isset($_GET['view']) && $_GET['view'] == 'log' && file_exists($log_file)): ?>
        <div class="card">
            <h2 style="margin-bottom: 20px;">Log de Backups</h2>
            <pre style="background: #f8f9fa; padding: 15px; border-radius: 4px; overflow-x: auto; max-height: 400px; overflow-y: auto;">
                <?php echo htmlspecialchars(file_get_contents($log_file)); ?>
            </pre>
        </div>
        <?php endif; ?>

        <div class="card">
            <h2 style="margin-bottom: 20px;">Backups Disponibles</h2>
            <?php if (empty($backups)): ?>
                <div class="empty">No hay backups disponibles</div>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Archivo</th>
                            <th>Tipo</th>
                            <th>Fecha</th>
                            <th>Tamaño</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($backups as $backup): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($backup['name']); ?></td>
                            <td><span style="padding: 3px 8px; background: <?php echo $backup['type'] == 'BD' ? '#3498db' : '#27ae60'; ?>; color: white; border-radius: 3px; font-size: 12px;"><?php echo $backup['type']; ?></span></td>
                            <td><?php echo $backup['date']; ?></td>
                            <td><?php echo round($backup['size'] / 1024 / 1024, 2); ?> MB</td>
                            <td>
                                <a href="download.php?file=<?php echo urlencode($backup['name']); ?>" class="btn btn-primary btn-small">Descargar</a>
                                <a href="?delete=<?php echo urlencode($backup['name']); ?>" class="btn btn-danger btn-small" onclick="return confirm('¿Eliminar este backup?')">Eliminar</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
        <div class="card" style="text-align: right;">
            <a href="logout.php" class="btn btn-danger">Cerrar Sesión</a>
        </div>
    </div>
</body>
</html>

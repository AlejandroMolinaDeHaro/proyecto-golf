<?php
$backup_dir = __DIR__ . '/../backups';
$log_file = $backup_dir . '/backup.log';

// Eliminar backup
if (isset($_GET['delete'])) {
    $file = basename($_GET['delete']);
    $path = $backup_dir . '/' . $file;
    if (file_exists($path)) {
        if (is_dir($path)) {
            foreach (glob($path . '/*') as $f) unlink($f);
            $deleted = rmdir($path);
        } else {
            $deleted = unlink($path);
        }
        if ($deleted) {
            $message = 'Backup eliminado: ' . $file;
        }
    }
}

// Listar backups
$backups = [];
if (is_dir($backup_dir)) {
    $files = scandir($backup_dir, SCANDIR_SORT_DESCENDING);
    foreach ($files as $file) {
        if ($file != '.' && $file != '..' && $file != 'backup.log' && !is_dir($backup_dir . '/' . $file)) {
            $path = $backup_dir . '/' . $file;
            $backups[] = [
                'name' => $file,
                'size' => filesize($path),
                'size_mb' => round(filesize($path) / 1024 / 1024, 2),
                'date' => date('Y-m-d H:i:s', filemtime($path)),
                'type' => strpos($file, 'db_') !== false ? 'BD' : 'Archivos'
            ];
        }
    }
}

$last_backup = 'Nunca';
if (!empty($backups)) {
    $last_backup = $backups[0]['date'];
}
$disk_free = disk_free_space($backup_dir);
$disk_total = disk_total_space($backup_dir);
?>
<div class="page-header">
    <h1>Backups</h1>
    <p>Gestión de copias de seguridad</p>
</div>

<?php if (isset($message) && $message !== ''): ?>
    <div class="message <?php echo $msg_status === 'error' ? 'error' : 'success'; ?>">
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
    <h2>Acciones</h2>
    <a href="api/backup.php?token=<?php echo htmlspecialchars($token); ?>&redirect=1" class="btn btn-primary" onclick="return confirm('¿Ejecutar backup ahora?')">
        Ejecutar Backup Manual
    </a>
    <button onclick="verLog()" class="btn btn-success" style="margin-left: 10px;">Ver Log</button>
    <div id="backupResult" class="mt-20"></div>
</div>

<div class="card">
    <h2>Subir Backup</h2>
    <form method="POST" enctype="multipart/form-data" action="api/upload.php" style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;">
        <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
        <input type="hidden" name="redirect" value="1">
        <input type="file" name="backup_file" required style="flex:1;min-width:200px;padding:10px;border:2px solid #ddd;border-radius:10px;background:var(--cream);font-family:'Lato',sans-serif;">
        <button type="submit" class="btn btn-primary">Subir Backup</button>
    </form>
</div>

<div class="card" id="logCard" style="display: none;">
    <h2>Log de Backups</h2>
    <pre style="background: #f8f9fa; padding: 15px; border-radius: 4px; overflow-x: auto; max-height: 400px; overflow-y: auto; font-size: 13px;" id="logContent"></pre>
</div>

<div class="card">
    <h2>Backups Disponibles</h2>
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
                    <td><span class="badge <?php echo $backup['type'] == 'BD' ? 'badge-blue' : 'badge-green'; ?>"><?php echo $backup['type']; ?></span></td>
                    <td><?php echo $backup['date']; ?></td>
                    <td><?php echo $backup['size_mb']; ?> MB</td>
                    <td>
                        <form method="POST" action="api/restore.php" style="display:inline" onsubmit="return confirm('¿Restaurar desde <?php echo addslashes($backup['name']); ?>?\n\n¡ESTO SOBRESCRIBIRÁ LOS DATOS ACTUALES!')">
                            <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
                            <input type="hidden" name="file" value="<?php echo htmlspecialchars($backup['name']); ?>">
                            <input type="hidden" name="redirect" value="1">
                            <button type="submit" class="btn btn-success btn-small">Restaurar</button>
                        </form>
                        <a href="?token=<?php echo htmlspecialchars($token); ?>&section=backups&delete=<?php echo urlencode($backup['name']); ?>" class="btn btn-danger btn-small" onclick="return confirm('¿Eliminar este backup?')">Eliminar</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<script>
const TOKEN = <?php echo json_encode($token); ?>;

function verLog() {
    const card = document.getElementById('logCard');
    const content = document.getElementById('logContent');

    if (card.style.display === 'none') {
        fetch('api/get_log.php?token=' + TOKEN)
            .then(r => r.text())
            .then(data => {
                content.textContent = data;
                card.style.display = 'block';
            });
    } else {
        card.style.display = 'none';
    }
}
</script>

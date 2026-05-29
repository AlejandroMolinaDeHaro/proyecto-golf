<?php
$backup_dir = '/opt/lampp/htdocs/progolf/backups';
$log_file = $backup_dir . '/backup.log';

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
    <h2>Acciones</h2>
    <button onclick="ejecutarBackup()" class="btn btn-primary" id="btnBackup">
        Ejecutar Backup Manual
    </button>
    <button onclick="verLog()" class="btn btn-success" style="margin-left: 10px;">Ver Log</button>
    <div id="backupResult" class="mt-20"></div>
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
                        <button onclick="restaurar('<?php echo addslashes($backup['name']); ?>')" class="btn btn-success btn-small">Restaurar</button>
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

function ejecutarBackup() {
    const btn = document.getElementById('btnBackup');
    const result = document.getElementById('backupResult');
    btn.disabled = true;
    btn.textContent = 'Ejecutando backup...';
    result.innerHTML = '<div class="message info">⏳ Ejecutando backup, por favor espera...</div>';

    fetch('../backup-admin/backup.php?token=' + TOKEN)
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

function restaurar(filename) {
    if (!confirm('¿Restaurar desde ' + filename + '?\n\n¡ESTO SOBRESCRIBIRÁ LOS DATOS ACTUALES!')) {
        return;
    }

    const result = document.getElementById('backupResult');
    result.innerHTML = '<div class="message info">⏳ Restaurando, por favor espera...</div>';

    fetch('../backup-admin/restore.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'token=' + TOKEN + '&file=' + encodeURIComponent(filename)
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            result.innerHTML = '<div class="message success">' + data.message + '</div>';
            setTimeout(() => location.reload(), 3000);
        } else {
            result.innerHTML = '<div class="message error">' + data.message + '</div>';
        }
    })
    .catch(err => {
        result.innerHTML = '<div class="message error">Error: ' + err + '</div>';
    });
}

function verLog() {
    const card = document.getElementById('logCard');
    const content = document.getElementById('logContent');

    if (card.style.display === 'none') {
        fetch('../backup-admin/get_log.php?token=' + TOKEN)
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

<?php
require __DIR__ . '/../db.php';

if (isset($_GET['eliminar'])) {
    $id = (int)$_GET['eliminar'];
    if ($id > 0) {
        mysqli_query($conn, "DELETE FROM encuestas WHERE id=$id");
        $success = "Encuesta eliminada correctamente.";
    }
}

$encuestas = mysqli_query($conn, "SELECT e.*, COALESCE(u.nombre, 'Usuario eliminado') as nombre_usuario FROM encuestas e LEFT JOIN usuarios u ON e.usuario_id = u.id ORDER BY e.fecha_respuesta DESC");
?>
<div class="page-header">
    <h1>Encuestas</h1>
    <p>Respuestas de los usuarios</p>
</div>

<?php if (isset($success)): ?>
    <div class="message success"><?php echo htmlspecialchars($success); ?></div>
<?php endif; ?>

<div class="card">
    <?php if (mysqli_num_rows($encuestas) === 0): ?>
        <div class="empty">No hay encuestas registradas</div>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Usuario</th>
                    <th>Mejor Jugador</th>
                    <th>Torneo Favorito</th>
                    <th>Fecha</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($e = mysqli_fetch_assoc($encuestas)): ?>
                <tr>
                    <td><?php echo $e['id']; ?></td>
                    <td><strong><?php echo htmlspecialchars($e['nombre_usuario']); ?></strong></td>
                    <td><?php echo htmlspecialchars($e['mejor_jugador']); ?></td>
                    <td><?php echo htmlspecialchars($e['campo_torneo']); ?></td>
                    <td><?php echo $e['fecha_respuesta']; ?></td>
                    <td>
                        <a href="?token=<?php echo htmlspecialchars($token); ?>&section=encuestas&eliminar=<?php echo $e['id']; ?>"
                           class="btn btn-danger btn-small"
                           onclick="return confirm('¿Eliminar esta encuesta?')">
                            Eliminar
                        </a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

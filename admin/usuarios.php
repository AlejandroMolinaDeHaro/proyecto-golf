<?php
require __DIR__ . '/../db.php';

// Crear usuario
if (isset($_POST['crear_usuario'])) {
    $nombre = trim($_POST['nombre']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $rol = $_POST['rol'] ?? 'user';

    if (empty($nombre) || empty($email) || empty($password)) {
        $error = "Todos los campos son obligatorios.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "El email no tiene un formato válido.";
    } elseif (strlen($password) < 6) {
        $error = "La contraseña debe tener al menos 6 caracteres.";
    } else {
        $check = mysqli_query($conn, "SELECT id FROM usuarios WHERE email='" . mysqli_real_escape_string($conn, $email) . "'");
        if (mysqli_num_rows($check) > 0) {
            $error = "Este email ya está registrado.";
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $rol_esc = mysqli_real_escape_string($conn, $rol);
            $sql = "INSERT INTO usuarios (nombre, email, password, rol) VALUES ('" . mysqli_real_escape_string($conn, $nombre) . "','" . mysqli_real_escape_string($conn, $email) . "','$hash','$rol_esc')";
            if (mysqli_query($conn, $sql)) {
                $success = "Usuario creado exitosamente.";
            } else {
                $error = "Error al crear el usuario.";
            }
        }
    }
}

// Cambiar rol de usuario
if (isset($_GET['cambiar_rol'])) {
    $id = (int)$_GET['cambiar_rol'];
    $nuevo_rol = $_GET['nuevo_rol'] === 'admin' ? 'admin' : 'user';
    if ($id > 0) {
        mysqli_query($conn, "UPDATE usuarios SET rol='$nuevo_rol' WHERE id=$id");
        $success = "Rol de usuario actualizado a: $nuevo_rol";
    }
}

// Eliminar usuario
if (isset($_GET['eliminar'])) {
    $id = (int)$_GET['eliminar'];
    if ($id > 0) {
        mysqli_query($conn, "DELETE FROM carrito WHERE usuario_id = $id");
        mysqli_query($conn, "DELETE FROM encuestas WHERE usuario_id = $id");
        $sql = "DELETE FROM usuarios WHERE id = $id";
        if (mysqli_query($conn, $sql)) {
            $success = "Usuario eliminado correctamente.";
        } else {
            $error = "Error al eliminar el usuario.";
        }
    }
}

// Listar usuarios
$usuarios = mysqli_query($conn, "SELECT * FROM usuarios ORDER BY fecha_registro DESC");
?>
<div class="page-header flex-between">
    <div>
        <h1>Usuarios</h1>
        <p>Gestión de usuarios registrados</p>
    </div>
    <a href="#crear" class="btn btn-success">+ Nuevo Usuario</a>
</div>

<?php if (isset($error)): ?>
    <div class="message error"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>
<?php if (isset($success)): ?>
    <div class="message success"><?php echo htmlspecialchars($success); ?></div>
<?php endif; ?>

<div class="card">
    <h2>Usuarios Registrados</h2>
    <?php if (mysqli_num_rows($usuarios) === 0): ?>
        <div class="empty">No hay usuarios registrados</div>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Email</th>
                    <th>Rol</th>
                    <th>Fecha Registro</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($u = mysqli_fetch_assoc($usuarios)): 
                    $rol_usuario = $u['rol'] ?? 'user';
                ?>
                <tr>
                    <td><?php echo $u['id']; ?></td>
                    <td><strong><?php echo htmlspecialchars($u['nombre']); ?></strong></td>
                    <td><?php echo htmlspecialchars($u['email']); ?></td>
                    <td>
                        <span class="badge <?php echo $rol_usuario === 'admin' ? 'badge-blue' : 'badge-green'; ?>">
                            <?php echo $rol_usuario === 'admin' ? 'Admin' : 'Usuario'; ?>
                        </span>
                    </td>
                    <td><?php echo $u['fecha_registro']; ?></td>
                    <td>
                        <?php if ($rol_usuario !== 'admin'): ?>
                            <a href="?token=<?php echo htmlspecialchars($token); ?>&section=usuarios&cambiar_rol=<?php echo $u['id']; ?>&nuevo_rol=admin"
                               class="btn btn-warning btn-small">
                                Hacer Admin
                            </a>
                        <?php else: ?>
                            <a href="?token=<?php echo htmlspecialchars($token); ?>&section=usuarios&cambiar_rol=<?php echo $u['id']; ?>&nuevo_rol=user"
                               class="btn btn-small" style="background:#6c757d;color:white;">
                                Quitar Admin
                            </a>
                        <?php endif; ?>
                        <a href="?token=<?php echo htmlspecialchars($token); ?>&section=usuarios&eliminar=<?php echo $u['id']; ?>"
                           class="btn btn-danger btn-small"
                           onclick="return confirm('¿Eliminar a <?php echo addslashes($u['nombre']); ?>?\n\nSe eliminarán también sus registros de carrito y encuestas.')">
                            Eliminar
                        </a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<div class="card" id="crear">
    <h2>Crear Nuevo Usuario</h2>
    <form method="POST" style="max-width: 500px;">
        <div class="form-group">
            <label>Nombre completo</label>
            <input type="text" name="nombre" placeholder="Nombre del usuario" required>
        </div>
        <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" placeholder="correo@ejemplo.com" required>
        </div>
        <div class="form-group">
            <label>Contraseña</label>
            <input type="password" name="password" placeholder="Mínimo 6 caracteres" required>
        </div>
        <div class="form-group">
            <label>Rol</label>
            <select name="rol">
                <option value="user">Usuario</option>
                <option value="admin">Administrador</option>
            </select>
        </div>
        <button type="submit" name="crear_usuario" class="btn btn-primary">Crear Usuario</button>
    </form>
</div>

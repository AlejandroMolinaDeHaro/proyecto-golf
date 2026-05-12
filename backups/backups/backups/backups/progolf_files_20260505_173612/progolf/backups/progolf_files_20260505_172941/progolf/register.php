<?php
session_start();
require 'db.php';

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = trim($_POST['nombre']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm = $_POST['confirm'];

    if (empty($nombre) || empty($email) || empty($password)) {
        $error = "Por favor, rellena todos los campos.";
    } elseif ($password !== $confirm) {
        $error = "Las contraseñas no coinciden.";
    } elseif (strlen($password) < 6) {
        $error = "La contraseña debe tener al menos 6 caracteres.";
    } else {
        $check = mysqli_query($conn, "SELECT id FROM usuarios WHERE email='".mysqli_real_escape_string($conn,$email)."'");
        if (mysqli_num_rows($check) > 0) {
            $error = "Este email ya está registrado.";
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $sql = "INSERT INTO usuarios (nombre, email, password) VALUES ('".mysqli_real_escape_string($conn,$nombre)."','".mysqli_real_escape_string($conn,$email)."','$hash')";
            if (mysqli_query($conn, $sql)) {
                $success = "¡Cuenta creada con éxito! Ya puedes iniciar sesión.";
            } else {
                $error = "Error al registrar. Inténtalo de nuevo.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registro – Golf Club</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="auth-page">
    <div class="auth-container">
        <div class="auth-logo">⛳</div>
        <h1 class="auth-title">Crear Cuenta</h1>
        <p class="auth-sub">Únete al club</p>

        <?php if ($error): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label>Nombre completo</label>
                <input type="text" name="nombre" placeholder="Tu nombre" required>
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
                <label>Confirmar contraseña</label>
                <input type="password" name="confirm" placeholder="Repite la contraseña" required>
            </div>
            <button type="submit" class="btn-auth">Registrarse</button>
        </form>

        <p class="auth-link">¿Ya tienes cuenta? <a href="login.php">Inicia sesión</a></p>
        <p class="auth-link"><a href="index.php">← Volver al inicio</a></p>
    </div>
</body>
</html>

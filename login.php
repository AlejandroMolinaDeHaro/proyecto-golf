<?php
session_start();
require 'db.php';

$error = "";

if (isset($_SESSION['usuario_id'])) {
    header("Location: index.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $error = "Por favor, rellena todos los campos.";
    } else {
        $sql = "SELECT * FROM usuarios WHERE email='".mysqli_real_escape_string($conn,$email)."'";
        $result = mysqli_query($conn, $sql);
        if ($row = mysqli_fetch_assoc($result)) {
            if (password_verify($password, $row['password'])) {
                $_SESSION['usuario_id'] = $row['id'];
                $_SESSION['usuario_nombre'] = $row['nombre'];
                header("Location: index.php");
                exit;
            } else {
                $error = "Contraseña incorrecta.";
            }
        } else {
            $error = "No existe ninguna cuenta con ese email.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Iniciar Sesión – Golf Club</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="auth-page">
    <div class="auth-container">
        <div class="auth-logo">⛳</div>
        <h1 class="auth-title">Bienvenido</h1>
        <p class="auth-sub">Inicia sesión en tu cuenta</p>

        <?php if ($error): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" placeholder="correo@ejemplo.com" required>
            </div>
            <div class="form-group">
                <label>Contraseña</label>
                <input type="password" name="password" placeholder="Tu contraseña" required>
            </div>
            <button type="submit" class="btn-auth">Iniciar Sesión</button>
        </form>

        <p class="auth-link">¿No tienes cuenta? <a href="register.php">Regístrate</a></p>
        <p class="auth-link"><a href="index.php">← Volver al inicio</a></p>
    </div>
</body>
</html>

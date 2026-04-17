<?php
// =========================================================
// LOGIN.PHP - pagina de inicio de sesion
// =========================================================
require_once 'session.php';


// si ya estas logueado mostramos aviso pero NO redirigimos
// antes redirigía y eso confundia al usuario

$errors = [];
if (!empty($_GET['errors'])) {
    $errors = explode('|', urldecode($_GET['errors']));
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión — Golf Pro Apate</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="auth.css">
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>⛳</text></svg>">
</head>
<body class="auth-page">

<div class="auth-card">
    <!-- Logo -->
    <div class="auth-logo">
        <div class="auth-logo-icon">⛳</div>
        <div class="auth-logo-text">Golf Pro <span>Apate</span></div>
    </div>

    <?php if ($loggedIn): ?>
        <!-- ya esta logueado -->
        <div class="alert alert-success">
            ✅ Ya tienes sesión iniciada como <strong><?= $username ?></strong>
        </div>
        <div style="text-align:center; margin-top: 16px; display:flex; flex-direction:column; gap:10px;">
            <a href="index.html" class="btn btn-primary form-btn">Ir al inicio →</a>
            <a href="logout.php" class="btn btn-outline form-btn">Cerrar sesión actual</a>
        </div>

    <?php else: ?>
        <h2 class="auth-title">Bienvenido de nuevo</h2>
        <p class="auth-sub">Inicia sesión para acceder a tu cuenta</p>

        <!-- Errores del login -->
        <?php if (!empty($errors)): ?>
        <div class="alert alert-error">
            <?php foreach ($errors as $e): ?>
                <div>⚠️ <?= htmlspecialchars($e) ?></div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <!-- Formulario -->
        <form method="POST" action="process_login.php">
            <div class="form-group">
                <label class="form-label" for="email">Correo electrónico</label>
                <input class="form-input" type="email" id="email" name="email" 
                       placeholder="tu@email.com" 
                       value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                       required autofocus>
            </div>
            <div class="form-group">
                <label class="form-label" for="password">Contraseña</label>
                <input class="form-input" type="password" id="password" name="password"
                       placeholder="••••••••" required>
            </div>
            <button type="submit" class="btn btn-primary form-btn">Iniciar Sesión →</button>
        </form>

        <div class="auth-divider">o</div>
        <div class="auth-link-wrap">
            ¿No tienes cuenta? <a href="register.php" class="auth-link">Regístrate gratis</a>
        </div>
        <a href="index.php" class="back-link">← Volver al inicio</a>
    <?php endif; ?>
</div>

</body>
</html>

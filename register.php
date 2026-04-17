<?php
// =========================================================
// REGISTER.PHP - pagina de registro
// =========================================================
require_once 'register.php'; // siempre primero

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
    <title>Registrarse — Golf Pro Apate</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="auth.css">
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>⛳</text></svg>">
</head>
<body class="auth-page">

<div class="auth-card register">
    <div class="auth-logo">
        <div class="auth-logo-icon">⛳</div>
        <div class="auth-logo-text">Golf Pro <span>Apate</span></div>
    </div>

    <?php if ($loggedIn): ?>
        <!-- ya esta logueado -->
        <div class="alert alert-success">
            ✅ Ya tienes una cuenta activa como <strong><?= $username ?></strong>
        </div>
        <div style="text-align:center; margin-top:16px; display:flex; flex-direction:column; gap:10px;">
            <a href="index.php" class="btn btn-primary form-btn">Ir al inicio →</a>
            <a href="logout.php" class="btn btn-outline form-btn">Cerrar sesión y crear otra cuenta</a>
        </div>

    <?php else: ?>
        <h2 class="auth-title">Crear cuenta</h2>
        <p class="auth-sub">Únete a la comunidad de golf de élite</p>

        <?php if (!empty($errors)): ?>
        <div class="alert alert-error">
            <?php foreach ($errors as $e): ?>
                <div>⚠️ <?= htmlspecialchars($e) ?></div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <form method="POST" action="process_register.php">
            <div class="form-group">
                <label class="form-label" for="full_name">Nombre completo</label>
                <input class="form-input" type="text" id="full_name" name="full_name"
                       placeholder="John Doe"
                       value="<?= htmlspecialchars($_POST['full_name'] ?? '') ?>"
                       required autofocus>
            </div>
            <div class="form-group">
                <label class="form-label" for="username">Nombre de usuario</label>
                <input class="form-input" type="text" id="username" name="username"
                       placeholder="johndoe_golf"
                       value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" required>
            </div>
            <div class="form-group">
                <label class="form-label" for="email">Correo electrónico</label>
                <input class="form-input" type="email" id="email" name="email"
                       placeholder="tu@email.com"
                       value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
            </div>
            <div class="form-group">
                <label class="form-label" for="password">Contraseña</label>
                <input class="form-input" type="password" id="password" name="password"
                       placeholder="Mínimo 6 caracteres" required>
            </div>
            <div class="form-group">
                <label class="form-label" for="confirm_password">Confirmar contraseña</label>
                <input class="form-input" type="password" id="confirm_password" name="confirm_password"
                       placeholder="Repite tu contraseña" required>
            </div>
            <button type="submit" class="btn btn-primary form-btn">Crear Cuenta →</button>
        </form>

        <div class="auth-divider">o</div>
        <div class="auth-link-wrap">
            ¿Ya tienes cuenta? <a href="login.php" class="auth-link">Iniciar sesión</a>
        </div>
        <a href="index.php" class="back-link">← Volver al inicio</a>
    <?php endif; ?>
</div>

</body>
</html>

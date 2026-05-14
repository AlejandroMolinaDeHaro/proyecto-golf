<?php
session_start();

$usuarios = [
    'admin' => password_hash('admin123', PASSWORD_DEFAULT)
];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user = $_POST['username'] ?? '';
    $pass = $_POST['password'] ?? '';
    
    if (isset($usuarios[$user]) && password_verify($pass, $usuarios[$user])) {
        $_SESSION['logged_in'] = true;
        $_SESSION['user'] = $user;
        header('Location: index.php');
        exit;
    } else {
        $error = 'Usuario o contraseña incorrectos';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Login - Backup Admin</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial; background: #2c3e50; display: flex; justify-content: center; align-items: center; height: 100vh; }
        .login-box { background: white; padding: 40px; border-radius: 8px; width: 350px; box-shadow: 0 4px 6px rgba(0,0,0,0.3); }
        h2 { text-align: center; margin-bottom: 30px; color: #2c3e50; }
        input { width: 100%; padding: 12px; margin-bottom: 15px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px; }
        button { width: 100%; padding: 12px; background: #3498db; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 16px; }
        button:hover { background: #2980b9; }
        .error { color: #e74c3c; text-align: center; margin-bottom: 15px; }
        .info { text-align: center; margin-top: 20px; color: #7f8c8d; font-size: 12px; }
    </style>
</head>
<body>
    <div class="login-box">
        <h2>Backup Admin Panel</h2>
        <?php if (isset($error)): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        <form method="POST">
            <input type="text" name="username" placeholder="Usuario" required>
            <input type="password" name="password" placeholder="Contraseña" required>
            <button type="submit">Iniciar Sesión</button>
        </form>
        <div class="info">Usuario: admin / Contraseña: admin123</div>
    </div>
</body>
</html>

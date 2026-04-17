<?php
// =========================================================
// PROCESS_LOGIN.PHP - procesa el formulario de login
// =========================================================
require_once 'session.php';
require_once 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email']    ?? '');
    $password =      $_POST['password'] ?? '';
    $errors   = [];

    if (empty($email))    $errors[] = 'El email es obligatorio.';
    if (empty($password)) $errors[] = 'La contraseña es obligatoria.';

    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            // login correcto - guardar en sesion
            $_SESSION['user_id']   = $user['id'];
            $_SESSION['username']  = $user['username'];
            $_SESSION['full_name'] = $user['full_name'];

            // actualizar ultimo login
            $pdo->prepare("UPDATE users SET last_login = NOW() WHERE id = ?")
                ->execute([$user['id']]);

            header('Location: index.php');
            exit;
        } else {
            $errors[] = 'Email o contraseña incorrectos.';
        }
    }

    $errorStr = urlencode(implode('|', $errors));
    header("Location: login.php?errors=$errorStr");
    exit;
}

// si alguien entra directamente a este archivo sin enviar el form
header('Location: login.php');
exit;
?>

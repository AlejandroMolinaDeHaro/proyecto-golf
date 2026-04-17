<?php
// =========================================================
// PROCESS_REGISTER.PHP - procesa el formulario de registro
// =========================================================
require_once 'session.php';
require_once 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name']        ?? '');
    $username  = trim($_POST['username']         ?? '');
    $email     = trim($_POST['email']            ?? '');
    $password  =      $_POST['password']         ?? '';
    $confirm   =      $_POST['confirm_password'] ?? '';
    $errors    = [];

    if (empty($full_name)) $errors[] = 'El nombre completo es obligatorio.';
    if (empty($username))  $errors[] = 'El nombre de usuario es obligatorio.';
    if (empty($email))     $errors[] = 'El email es obligatorio.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'El email no tiene un formato válido.';
    if (strlen($password) < 6) $errors[] = 'La contraseña debe tener al menos 6 caracteres.';
    if ($password !== $confirm)  $errors[] = 'Las contraseñas no coinciden.';

    if (empty($errors)) {
        // comprobar si ya existe ese email o usuario
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? OR username = ?");
        $stmt->execute([$email, $username]);
        if ($stmt->fetch()) {
            $errors[] = 'Ese email o nombre de usuario ya están en uso.';
        }
    }

    if (empty($errors)) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (full_name, username, email, password) VALUES (?, ?, ?, ?)");
        $stmt->execute([$full_name, $username, $email, $hash]);

        $userId = $pdo->lastInsertId();
        $_SESSION['user_id']   = $userId;
        $_SESSION['username']  = $username;
        $_SESSION['full_name'] = $full_name;

        header('Location: index.php?registered=1');
        exit;
    }

    $errorStr = urlencode(implode('|', $errors));
    header("Location: register.php?errors=$errorStr");
    exit;
}

header('Location: register.php');
exit;
?>

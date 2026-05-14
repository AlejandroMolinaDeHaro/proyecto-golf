<?php
session_start();
require 'db.php';

if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mejor_jugador = trim($_POST['mejor_jugador'] ?? '');
    $campo_torneo = trim($_POST['campo_torneo'] ?? '');
    $usuario_id = $_SESSION['usuario_id'];
    
    if (empty($mejor_jugador) || empty($campo_torneo)) {
        $error = 'Por favor, responde ambas preguntas.';
    } else {
        $stmt = mysqli_prepare($conn, "INSERT INTO encuestas (usuario_id, mejor_jugador, campo_torneo) VALUES (?, ?, ?)");
        mysqli_stmt_bind_param($stmt, 'iss', $usuario_id, $mejor_jugador, $campo_torneo);
        
        if (mysqli_stmt_execute($stmt)) {
            $message = '¡Respuestas guardadas correctamente!';
        } else {
            $error = 'Error al guardar: ' . mysqli_error($conn);
        }
    }
}

// Obtener respuestas anteriores del usuario
$respuestas = [];
if (isset($_SESSION['usuario_id'])) {
    $result = mysqli_query($conn, "SELECT * FROM encuestas WHERE usuario_id = " . $_SESSION['usuario_id'] . " ORDER BY fecha_respuesta DESC");
    while ($row = mysqli_fetch_assoc($result)) {
        $respuestas[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Encuesta - Golf Club</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="auth-page">
    <div class="auth-container" style="max-width: 600px;">
        <div class="auth-logo">⛳</div>
        <h1 class="auth-title">Encuesta Golfística</h1>
        <p class="auth-sub">Danos tu opinión sobre el mundo del golf</p>

        <?php if ($message): ?>
            <div class="alert" style="background: #d4edda; color: #155724; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" style="text-align: left;">
            <div class="form-group">
                <label style="display: block; margin-bottom: 10px; font-weight: bold;">¿Quién crees que es el mejor del mundo actualmente según tu criterio?</label>
                <textarea name="mejor_jugador" rows="3" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-family: inherit;" placeholder="Escribe tu opinión..."><?= htmlspecialchars($_POST['mejor_jugador'] ?? '') ?></textarea>
            </div>

            <div class="form-group" style="margin-top: 20px;">
                <label style="display: block; margin-bottom: 10px; font-weight: bold;">¿Qué te parece el campo para el próximo torneo?</label>
                <textarea name="campo_torneo" rows="3" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-family: inherit;" placeholder="Describe tu opinión sobre el campo..."><?= htmlspecialchars($_POST['campo_torneo'] ?? '') ?></textarea>
            </div>

            <button type="submit" class="btn-auth" style="margin-top: 20px;">Enviar Respuestas</button>
        </form>

        <?php if (!empty($respuestas)): ?>
        <div style="margin-top: 40px;">
            <h3 style="margin-bottom: 20px;">Tus respuestas anteriores:</h3>
            <?php foreach ($respuestas as $resp): ?>
            <div style="background: #f8f9fa; padding: 15px; border-radius: 8px; margin-bottom: 15px;">
                <p><strong>Mejor jugador:</strong> <?= htmlspecialchars($resp['mejor_jugador']) ?></p>
                <p style="margin-top: 10px;"><strong>Campo torneo:</strong> <?= htmlspecialchars($resp['campo_torneo']) ?></p>
                <small style="color: #666; margin-top: 10px; display: block;">Respondido el: <?= $resp['fecha_respuesta'] ?></small>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <p class="auth-link" style="margin-top: 30px;">
            <a href="index.php">← Volver al inicio</a>
        </p>
    </div>
</body>
</html>

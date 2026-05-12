<?php
session_start();
$loggedIn = isset($_SESSION['usuario_id']);
$userName = $loggedIn ? $_SESSION['usuario_nombre'] : '';

$fav_message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_favorito'])) {
    require 'conexion.php';
    $jugador = trim($_POST['mejor_jugador'] ?? '');
    $torneo = trim($_POST['campo_torneo'] ?? '');
    if (!empty($jugador) && !empty($torneo)) {
        $jugador_esc = mysqli_real_escape_string($conexion, $jugador);
        $torneo_esc = mysqli_real_escape_string($conexion, $torneo);
        if ($loggedIn) {
            $uid = (int)$_SESSION['usuario_id'];
            $sql = "INSERT INTO encuestas (usuario_id, mejor_jugador, campo_torneo) VALUES ($uid, '$jugador_esc', '$torneo_esc')";
        } else {
            $sql = "INSERT INTO encuestas (mejor_jugador, campo_torneo) VALUES ('$jugador_esc', '$torneo_esc')";
        }
        if (mysqli_query($conexion, $sql)) {
            $fav_message = '¡Gracias por compartir tus favoritos!';
        }
    } else {
        $fav_message = 'Completa ambos campos';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tus Favoritos - Golf Club</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,700;1,400&family=Lato:wght@300;400;700&display=swap" rel="stylesheet">
    <style>
        .fav-page { min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 40px 20px; background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); }
        .fav-card { background: white; padding: 50px; border-radius: 16px; box-shadow: 0 10px 40px rgba(0,0,0,0.1); max-width: 550px; width: 100%; text-align: center; }
        .fav-card h1 { font-family: 'Playfair Display', serif; font-size: 2.2rem; color: #2c3e50; margin-bottom: 8px; }
        .fav-card p { color: #666; margin-bottom: 30px; }
        .fav-card .field { margin-bottom: 24px; text-align: left; }
        .fav-card .field label { display: block; font-weight: bold; margin-bottom: 8px; color: #2c3e50; font-size: 15px; }
        .fav-card .field input { width: 100%; padding: 14px; border: 2px solid #e0e0e0; border-radius: 8px; font-size: 16px; transition: border-color 0.3s; }
        .fav-card .field input:focus { border-color: #2c3e50; outline: none; }
        .fav-card .btn-submit { width: 100%; padding: 16px; background: #2c3e50; color: white; border: none; border-radius: 8px; font-size: 18px; cursor: pointer; transition: background 0.3s; font-weight: bold; }
        .fav-card .btn-submit:hover { background: #1a252f; }
        .fav-card .msg { padding: 15px; border-radius: 8px; margin-bottom: 20px; }
        .fav-card .msg.ok { background: #d4edda; color: #155724; }
        .fav-card .msg.err { background: #f8d7da; color: #721c24; }
        .fav-card .back-link { display: inline-block; margin-top: 20px; color: #3498db; text-decoration: none; }
        .fav-card .back-link:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="fav-page">
        <div class="fav-card">
            <h1>🏆 Tus Favoritos</h1>
            <p>Cuéntanos cuál es tu jugador y torneo preferido</p>

            <?php if ($fav_message): ?>
                <div class="msg ok"><?= htmlspecialchars($fav_message) ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="field">
                    <label>🏌️ ¿Cuál es tu jugador favorito?</label>
                    <input type="text" name="mejor_jugador" placeholder="Ej: Jon Rahm, Scottie Scheffler..." required>
                </div>
                <div class="field">
                    <label>⛳ ¿Cuál es tu torneo favorito?</label>
                    <input type="text" name="campo_torneo" placeholder="Ej: The Masters, US Open..." required>
                </div>
                <button type="submit" name="submit_favorito" class="btn-submit">Enviar</button>
            </form>

            <a href="index.php" class="back-link">← Volver al inicio</a>
        </div>
    </div>
</body>
</html>

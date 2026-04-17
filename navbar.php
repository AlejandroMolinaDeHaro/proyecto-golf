<?php
// =========================================================
// NAVBAR.PHP - barra de navegacion comun
// incluir en todas las paginas despues de session.php
// Hecho por: alumno SMIX 2oB
// =========================================================
// USO: primero include session.php, luego include navbar.php
//      y pasar la variable $activePage con el nombre de la pagina activa
//      Ej: $activePage = 'players';
// =========================================================
?>
<nav class="navbar" id="navbar">
    <div class="navbar-inner">
        <a href="index.html" class="nav-logo">
            <div class="nav-logo-icon">⛳</div>
            <span class="nav-logo-text">Golf Pro <span>Apate</span></span>
        </a>

        <ul class="nav-links" id="navLinks">
            <li><a href="index.php"       <?= ($activePage ?? '') === 'index'       ? 'class="active"' : '' ?>>Inicio</a></li>
            <li><a href="players.php"     <?= ($activePage ?? '') === 'players'     ? 'class="active"' : '' ?>>Jugadores</a></li>
            <li><a href="tournaments.php" <?= ($activePage ?? '') === 'tournaments' ? 'class="active"' : '' ?>>Torneos</a></li>
            <li><a href="shop.php"        <?= ($activePage ?? '') === 'shop'        ? 'class="active"' : '' ?>>Tienda</a></li>
        </ul>

        <div class="nav-actions">
            <?php if ($loggedIn): ?>
                <!-- usuario logueado: mostrar nombre y boton de cerrar sesion -->
                <div class="nav-user">
                    <div class="nav-avatar"><?= strtoupper(substr($username, 0, 2)) ?></div>
                    <span style="color:white;font-size:14px;">Hola, <strong style="color:#ffd700;"><?= $username ?></strong></span>
                    <a href="logout.php" class="btn btn-outline btn-sm">Cerrar sesión</a>
                </div>
            <?php else: ?>
                <!-- usuario no logueado: mostrar botones de login y registro -->
                <div class="nav-user">
                    <a href="login.php"    class="btn btn-outline btn-sm">Iniciar sesión</a>
                    <a href="register.php" class="btn btn-primary btn-sm">Registrarse</a>
                </div>
            <?php endif; ?>

            <div class="hamburger" id="hamburger">
                <span></span><span></span><span></span>
            </div>
        </div>
    </div>
</nav>

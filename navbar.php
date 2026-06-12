<header class="navbar">
    <div class="nav-brand">⛳ Golf Club</div>
    <nav class="nav-links">
        <a href="index.php#jugadores">Jugadores</a>
        <a href="index.php#torneos">Torneos</a>
        <a href="index.php#tienda">Tienda</a>
        <a href="index.php#favoritos">Favoritos</a>
        <a href="carrito.php">Carrito 🛒</a>
        <?php if ($loggedIn): ?>
            <span class="nav-user">👤 <?= htmlspecialchars($userName) ?></span>
            <?php if (isset($userRole) && $userRole === 'admin'): ?>
                <a href="admin/index.php" class="btn-nav-admin" style="color:#c9a84c;font-weight:700;">⚙ Admin</a>
            <?php endif; ?>
            <a href="logout.php" class="btn-nav-logout">Cerrar sesión</a>
        <?php else: ?>
            <a href="login.php" class="btn-nav-login">Iniciar sesión</a>
            <a href="register.php" class="btn-nav-register">Registrarse</a>
        <?php endif; ?>
    </nav>
</header>

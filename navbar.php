<nav class="navbar">
    <a href="index.php" class="logo">Golf Club</a>
    <div class="nav-links">
        <a href="index.php#jugadores">Jugadores</a>
        <a href="index.php#tienda">Tienda</a>
        <a href="index.php#torneos">Torneos</a>
        <a href="encuesta.php">Encuesta</a>
        <a href="carrito.php">Carrito 🛒</a>
        <?php if ($loggedIn): ?>
            <a href="logout.php" class="nav-user">Salir (<?php echo htmlspecialchars($userName); ?>)</a>
        <?php else: ?>
            <a href="login.php">Login</a>
            <a href="register.php" class="nav-register">Registro</a>
        <?php endif; ?>
    </div>
</nav>

<footer class="footer">
    <div class="footer-inner">
        <p class="footer-logo">⛳ Golf Club</p>
        <p class="footer-copy">© 2025 Golf Club. Todos los derechos reservados.</p>
        <?php if ($loggedIn): ?>
            <p class="footer-user">Sesión iniciada como <strong><?= htmlspecialchars($userName) ?></strong> · <a href="logout.php">Cerrar sesión</a></p>
        <?php endif; ?>
    </div>
</footer>

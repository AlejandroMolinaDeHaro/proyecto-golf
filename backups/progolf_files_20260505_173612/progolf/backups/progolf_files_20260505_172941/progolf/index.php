<?php
session_start();
$loggedIn = isset($_SESSION['usuario_id']);
$userName = $loggedIn ? $_SESSION['usuario_nombre'] : '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Golf Club – El mundo del golf</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,700;1,400&family=Lato:wght@300;400;700&display=swap" rel="stylesheet">
</head>
<body>

<!-- ===== NAVBAR ===== -->
<header class="navbar">
    <div class="nav-brand">⛳ Golf Club</div>
    <nav class="nav-links">
        <a href="#jugadores">Jugadores</a>
        <a href="#torneos">Torneos</a>
        <a href="#tienda">Tienda</a>
        <?php if ($loggedIn): ?>
            <span class="nav-user">👤 <?= htmlspecialchars($userName) ?></span>
            <a href="logout.php" class="btn-nav-logout">Cerrar sesión</a>
        <?php else: ?>
            <a href="login.php" class="btn-nav-login">Iniciar sesión</a>
            <a href="register.php" class="btn-nav-register">Registrarse</a>
        <?php endif; ?>
    </nav>
</header>

<!-- ===== HERO ===== -->
<section class="hero">
    <div class="hero-overlay"></div>
    <div class="hero-content">
        <p class="hero-tag">Temporada 2025</p>
        <h1 class="hero-title">El Mundo<br><em>del Golf</em></h1>
        <p class="hero-desc">Jugadores, torneos y todo lo que necesitas para jugar al mejor nivel.</p>
        <a href="#jugadores" class="hero-btn">Explorar</a>
    </div>
    <div class="hero-balls">
        <div class="ball b1"></div>
        <div class="ball b2"></div>
        <div class="ball b3"></div>
    </div>
</section>

<!-- ===== JUGADORES ===== -->
<section id="jugadores" class="section">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">Jugadores</h2>
            <p class="section-sub">Los mejores del ranking mundial</p>
        </div>

        <!-- Tabs -->
        <div class="tabs">
            <button class="tab active" onclick="showTab('hombres', this)">🏌️ Hombres</button>
            <button class="tab" onclick="showTab('mujeres', this)">🏌️‍♀️ Mujeres</button>
        </div>

        <!-- Hombres -->
        <div id="hombres" class="tab-content active">
            <div class="players-grid">
                <?php
                $hombres = [
                    ["Scottie Scheffler","🇺🇸 EE.UU.", "#1", "68.4", "72.1%"],
                    ["Rory McIlroy","🇬🇧 Irlanda del Norte", "#2", "69.1", "67.3%"],
                    ["Jon Rahm","🇪🇸 España", "#3", "69.5", "65.8%"],
                    ["Viktor Hovland","🇳🇴 Noruega", "#4", "69.7", "64.2%"],
                    ["Xander Schauffele","🇺🇸 EE.UU.", "#5", "69.9", "63.5%"],
                    ["Patrick Cantlay","🇺🇸 EE.UU.", "#6", "70.1", "62.9%"],
                ];
                foreach ($hombres as $j): ?>
                <div class="player-card">
                    <div class="player-rank"><?= $j[2] ?></div>
                    <div class="player-avatar"><?= strtoupper(substr($j[0],0,1)) ?></div>
                    <h3 class="player-name"><?= $j[0] ?></h3>
                    <p class="player-country"><?= $j[1] ?></p>
                    <div class="player-stats">
                        <div class="stat"><span class="stat-val"><?= $j[3] ?></span><span class="stat-lbl">Avg. golpes</span></div>
                        <div class="stat"><span class="stat-val"><?= $j[4] ?></span><span class="stat-lbl">Fairways</span></div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Mujeres -->
        <div id="mujeres" class="tab-content">
            <div class="players-grid">
                <?php
                $mujeres = [
                    ["Nelly Korda","🇺🇸 EE.UU.", "#1", "69.2", "73.5%"],
                    ["Celine Boutier","🇫🇷 Francia", "#2", "69.8", "70.1%"],
                    ["Lydia Ko","🇳🇿 Nueva Zelanda", "#3", "70.0", "68.7%"],
                    ["Lilia Vu","🇺🇸 EE.UU.", "#4", "70.3", "67.2%"],
                    ["Brooke Henderson","🇨🇦 Canadá", "#5", "70.5", "65.9%"],
                    ["Atthaya Thitikul","🇹🇭 Tailandia", "#6", "70.7", "64.4%"],
                ];
                foreach ($mujeres as $j): ?>
                <div class="player-card female">
                    <div class="player-rank"><?= $j[2] ?></div>
                    <div class="player-avatar"><?= strtoupper(substr($j[0],0,1)) ?></div>
                    <h3 class="player-name"><?= $j[0] ?></h3>
                    <p class="player-country"><?= $j[1] ?></p>
                    <div class="player-stats">
                        <div class="stat"><span class="stat-val"><?= $j[3] ?></span><span class="stat-lbl">Avg. golpes</span></div>
                        <div class="stat"><span class="stat-val"><?= $j[4] ?></span><span class="stat-lbl">Fairways</span></div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</section>

<!-- ===== TORNEOS ===== -->
<section id="torneos" class="section section-dark">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title light">Torneos 2025</h2>
            <p class="section-sub light">Calendario de la temporada</p>
        </div>
        <div class="torneos-list">
            <?php
            $torneos = [
                ["The Masters","Augusta, Georgia – EE.UU.","10–13 Abr 2025","Major","✅ Finalizado","$18.000.000"],
                ["US Open","Oakmont CC – Pennsylvania","12–15 Jun 2025","Major","🔜 Próximo","$21.500.000"],
                ["The Open Championship","Royal Portrush – Irlanda","17–20 Jul 2025","Major","🔜 Próximo","$17.000.000"],
                ["PGA Championship","Quail Hollow – Carolina del N.","15–18 May 2025","Major","🔜 Próximo","$19.000.000"],
                ["Ryder Cup","Adare Manor – Irlanda","26–28 Sep 2025","Equipo","🔜 Próximo","—"],
                ["DP World Tour","Dubai – EAU","18–21 Dic 2025","Tour","🔜 Próximo","$10.000.000"],
                ["Augusta National Women's Amateur","Augusta – EE.UU.","2–5 Abr 2025","Femenino","✅ Finalizado","—"],
                ["AIG Women's Open","Carnoustie – Escocia","7–10 Ago 2025","Major Fem.","🔜 Próximo","$9.000.000"],
            ];
            foreach ($torneos as $t): ?>
            <div class="torneo-card">
                <div class="torneo-left">
                    <span class="torneo-badge <?= $t[4]==='✅ Finalizado' ? 'badge-done' : 'badge-next' ?>"><?= $t[4] ?></span>
                    <h3 class="torneo-name"><?= $t[0] ?></h3>
                    <p class="torneo-lugar">📍 <?= $t[1] ?></p>
                </div>
                <div class="torneo-mid">
                    <span class="torneo-fecha">📅 <?= $t[2] ?></span>
                    <span class="torneo-tipo"><?= $t[3] ?></span>
                </div>
                <div class="torneo-right">
                    <span class="torneo-prize">💰 <?= $t[5] ?></span>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ===== TIENDA ===== -->
<section id="tienda" class="section">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">Tienda</h2>
            <p class="section-sub">Equípate con lo mejor</p>
        </div>
        <div class="shop-grid">
            <?php
            $productos = [
                ["🏌️","Driver TaylorMade Qi10","El driver más vendido de la temporada.","€549","Palos"],
                ["🥊","Set de Hierros Callaway","Precisión y control en cada golpe.","€899","Palos"],
                ["⛳","Pelotas Pro V1x (docena)","Las favoritas de los pros en el tour.","€59","Pelotas"],
                ["🎽","Polo Nike Dri-FIT","Comodidad y estilo en el campo.","€79","Ropa"],
                ["👟","Zapatos FootJoy Pro SL","Agarre y comodidad durante 18 hoyos.","€189","Calzado"],
                ["🎒","Bolsa de Golf Titleist","Ligera y con múltiples compartimentos.","€329","Accesorios"],
                ["🧢","Gorra Callaway Tour","Protección solar con estilo.","€35","Ropa"],
                ["🔭","Rangefinder Bushnell","Mide distancias con precisión láser.","€249","Accesorios"],
            ];
            foreach ($productos as $p): ?>
            <div class="product-card">
                <div class="product-icon"><?= $p[0] ?></div>
                <span class="product-cat"><?= $p[4] ?></span>
                <h3 class="product-name"><?= $p[1] ?></h3>
                <p class="product-desc"><?= $p[2] ?></p>
                <div class="product-footer">
                    <span class="product-price"><?= $p[3] ?></span>
                    <?php if ($loggedIn): ?>
                        <button class="btn-buy" onclick="addToCart(this)">Añadir</button>
                    <?php else: ?>
                        <a href="login.php" class="btn-buy-login">Inicia sesión</a>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <?php if (!$loggedIn): ?>
        <div class="shop-notice">
            <p>🔒 <a href="login.php">Inicia sesión</a> o <a href="register.php">regístrate</a> para poder comprar productos.</p>
        </div>
        <?php endif; ?>

        <?php if ($loggedIn): ?>
        <div class="cart-bar" id="cartBar" style="display:none">
            <span id="cartMsg">🛒 Artículo añadido al carrito</span>
            <button onclick="document.getElementById('cartBar').style.display='none'">✕</button>
        </div>
        <?php endif; ?>
    </div>
</section>

<!-- ===== FOOTER ===== -->
<footer class="footer">
    <div class="footer-inner">
        <p class="footer-logo">⛳ Golf Club</p>
        <p class="footer-copy">© 2025 Golf Club. Todos los derechos reservados.</p>
        <?php if ($loggedIn): ?>
            <p class="footer-user">Sesión iniciada como <strong><?= htmlspecialchars($userName) ?></strong> · <a href="logout.php">Cerrar sesión</a></p>
        <?php endif; ?>
    </div>
</footer>

<script>
function showTab(tab, btn) {
    document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
    document.querySelectorAll('.tab').forEach(b => b.classList.remove('active'));
    document.getElementById(tab).classList.add('active');
    btn.classList.add('active');
}

function addToCart(btn) {
    const bar = document.getElementById('cartBar');
    bar.style.display = 'flex';
    btn.textContent = '✓ Añadido';
    btn.style.background = '#2e7d32';
    setTimeout(() => {
        btn.textContent = 'Añadir';
        btn.style.background = '';
    }, 2000);
}

// Smooth scroll
document.querySelectorAll('a[href^="#"]').forEach(a => {
    a.addEventListener('click', e => {
        e.preventDefault();
        document.querySelector(a.getAttribute('href')).scrollIntoView({behavior:'smooth'});
    });
});
</script>
</body>
</html>

<?php
session_start();
$loggedIn = isset($_SESSION['usuario_id']);
$userName = $loggedIn ? $_SESSION['usuario_nombre'] : '';
$userRole = $loggedIn ? ($_SESSION['usuario_rol'] ?? 'user') : '';

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
        <a href="#favoritos">Favoritos</a>
        <a href="carrito.php">Carrito 🛒</a>
        <?php if ($loggedIn): ?>
            <span class="nav-user">👤 <?= htmlspecialchars($userName) ?></span>
            <?php if ($userRole === 'admin'): ?>
                <a href="admin/index.php" style="color:#c9a84c;font-weight:700;">⚙ Admin</a>
            <?php endif; ?>
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

        <!-- Filtro jugadores -->
        <div style="margin:20px 0;text-align:center;">
            <input type="text" id="filtroJugador" placeholder="Buscar jugador..." style="padding:10px;width:250px;border:1px solid #ddd;border-radius:4px;" onkeyup="filtrarJugadores()">
            <select id="filtroPais" style="padding:10px;margin-left:10px;border:1px solid #ddd;border-radius:4px;" onchange="filtrarJugadores()">
                <option value="">Todos los países</option>
                <option value="EE.UU.">EE.UU.</option>
                <option value="España">España</option>
                <option value="Noruega">Noruega</option>
                <option value="Reino Unido">Reino Unido</option>
                <option value="Francia">Francia</option>
                <option value="Nueva Zelanda">Nueva Zelanda</option>
                <option value="Canadá">Canadá</option>
                <option value="Tailandia">Tailandia</option>
                <option value="Suecia">Suecia</option>
                <option value="Inglaterra">Inglaterra</option>
                <option value="Japón">Japón</option>
                <option value="Corea del Sur">Corea del Sur</option>
                <option value="Australia">Australia</option>
                <option value="China">China</option>
            </select>
        </div>

        <!-- Hombres -->
        <div id="hombres" class="tab-content active">
            <div class="players-grid">
                <?php
                $hombres = [
                    ["Scottie Scheffler","🇺🇸 EE.UU.", "#1", "68.4", "72.1%"],
                    ["Rory McIlroy","🇬🇧 Reino Unido", "#2", "69.1", "67.3%"],
                    ["Jon Rahm","🇪🇸 España", "#3", "69.5", "65.8%"],
                    ["Viktor Hovland","🇳🇴 Noruega", "#4", "69.7", "64.2%"],
                    ["Xander Schauffele","🇺🇸 EE.UU.", "#5", "69.9", "63.5%"],
                    ["Patrick Cantlay","🇺🇸 EE.UU.", "#6", "70.1", "62.9%"],
                    ["Collin Morikawa","🇺🇸 EE.UU.", "#7", "70.3", "66.1%"],
                    ["Ludvig Åberg","🇸🇪 Suecia", "#8", "70.4", "64.8%"],
                    ["Wyndham Clark","🇺🇸 EE.UU.", "#9", "70.6", "63.2%"],
                    ["Tommy Fleetwood","🏴󠁧󠁢󠁥󠁮󠁧󠁿 Inglaterra", "#10", "70.8", "62.7%"],
                    ["Hideki Matsuyama","🇯🇵 Japón", "#11", "70.9", "61.5%"],
                    ["Max Homa","🇺🇸 EE.UU.", "#12", "71.1", "65.3%"],
                    ["Justin Thomas","🇺🇸 EE.UU.", "#13", "71.2", "62.8%"],
                    ["Jordan Spieth","🇺🇸 EE.UU.", "#14", "71.4", "61.9%"],
                    ["Tony Finau","🇺🇸 EE.UU.", "#15", "71.5", "64.2%"],
                    ["Sam Burns","🇺🇸 EE.UU.", "#16", "71.6", "63.5%"],
                    ["Cameron Young","🇺🇸 EE.UU.", "#17", "71.7", "62.1%"],
                    ["Sungjae Im","🇰🇷 Corea del Sur", "#18", "71.8", "64.7%"],
                    ["Matt Fitzpatrick","🏴󠁧󠁢󠁥󠁮󠁧󠁿 Inglaterra", "#19", "71.9", "61.4%"],
                    ["Shane Lowry","🇮🇪 Irlanda", "#20", "72.0", "63.8%"],
                    ["Corey Conners","🇨🇦 Canadá", "#21", "72.1", "65.1%"],
                    ["Tom Kim","🇰🇷 Corea del Sur", "#22", "72.2", "62.5%"],
                    ["Russell Henley","🇺🇸 EE.UU.", "#23", "72.3", "64.3%"],
                    ["Sepp Straka","🇦🇹 Austria", "#24", "72.4", "61.8%"],
                    ["Chris Kirk","🇺🇸 EE.UU.", "#25", "72.5", "63.1%"],
                    ["Min Woo Lee","🇦🇺 Australia", "#26", "72.6", "62.4%"],
                    ["Nicolai Højgaard","🇩🇰 Dinamarca", "#27", "72.7", "61.7%"],
                    ["Adam Scott","🇦🇺 Australia", "#28", "72.8", "60.9%"],
                    ["Rickie Fowler","🇺🇸 EE.UU.", "#29", "72.9", "62.2%"],
                    ["Keegan Bradley","🇺🇸 EE.UU.", "#30", "73.0", "61.5%"],
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
                    ["Jin Young Ko","🇰🇷 Corea del Sur", "#7", "70.8", "66.2%"],
                    ["Minjee Lee","🇦🇺 Australia", "#8", "71.0", "63.8%"],
                    ["Ruoning Yin","🇨🇳 China", "#9", "71.2", "62.5%"],
                    ["Charley Hull","🏴󠁧󠁢󠁥󠁮󠁧󠁿 Inglaterra", "#10", "71.3", "64.9%"],
                    ["Lexi Thompson","🇺🇸 EE.UU.", "#11", "71.5", "61.7%"],
                    ["Rose Zhang","🇺🇸 EE.UU.", "#12", "71.6", "65.1%"],
                    ["Megan Khang","🇺🇸 EE.UU.", "#13", "71.7", "64.3%"],
                    ["Ally Ewing","🇺🇸 EE.UU.", "#14", "71.8", "63.0%"],
                    ["Hyo Joo Kim","🇰🇷 Corea del Sur", "#15", "71.9", "65.2%"],
                    ["Carlota Ciganda","🇪🇸 España", "#16", "72.0", "62.8%"],
                    ["Hannah Green","🇦🇺 Australia", "#17", "72.1", "64.1%"],
                    ["Leona Maguire","🇮🇪 Irlanda", "#18", "72.2", "63.5%"],
                    ["Gemma Dryburgh","🏴󠁧󠁢󠁳󠁣󠁴󠁿 Escocia", "#19", "72.3", "62.4%"],
                    ["Yuka Saso","🇯🇵 Japón", "#20", "72.4", "61.8%"],
                    ["Madelene Sagström","🇸🇪 Suecia", "#21", "72.5", "63.2%"],
                    ["Stephanie Kyriacou","🇦🇺 Australia", "#22", "72.6", "62.1%"],
                    ["Nanna Koerstz Madsen","🇩🇰 Dinamarca", "#23", "72.7", "61.5%"],
                    ["Linn Grant","🇸🇪 Suecia", "#24", "72.8", "63.9%"],
                    ["Georgia Hall","🏴󠁧󠁢󠁥󠁮󠁧󠁿 Inglaterra", "#25", "72.9", "62.6%"],
                    ["Ayaka Furue","🇯🇵 Japón", "#26", "73.0", "61.3%"],
                    ["Maja Stark","🇸🇪 Suecia", "#27", "73.1", "62.9%"],
                    ["Anna Nordqvist","🇸🇪 Suecia", "#28", "73.2", "61.1%"],
                    ["Gaby López","🇲🇽 México", "#29", "73.3", "62.3%"],
                    ["Patty Tavatanakit","🇹🇭 Tailandia", "#30", "73.4", "61.7%"],
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
        <!-- Filtro torneos -->
        <div style="margin:20px 0;text-align:center;">
            <input type="text" id="filtroTorneo" placeholder="Buscar torneo..." style="padding:10px;width:250px;border:1px solid #555;border-radius:4px;background:#333;color:white;" onkeyup="filtrarTorneos()">
            <select id="filtroTipo" style="padding:10px;margin-left:10px;border:1px solid #555;border-radius:4px;background:#333;color:white;" onchange="filtrarTorneos()">
                <option value="">Todos los tipos</option>
                <option value="Major">Major</option>
                <option value="Major Fem.">Major Femenino</option>
                <option value="Equipo">Equipo</option>
                <option value="Tour">Tour</option>
                <option value="Femenino">Femenino</option>
            </select>
            <select id="filtroEstado" style="padding:10px;margin-left:10px;border:1px solid #555;border-radius:4px;background:#333;color:white;" onchange="filtrarTorneos()">
                <option value="">Todos</option>
                <option value="✅ Finalizado">Finalizados</option>
                <option value="✅ Finalizado">Próximos</option>
            </select>
        </div>
        <div class="torneos-list">
            <?php
            $torneos = [
                ["The Masters","Augusta, Georgia – EE.UU.","10–13 Abr 2026","Major","✅ Finalizado","$18.000.000"],
                ["US Open","Oakmont CC – Pennsylvania","12–15 Jun 2026","Major","🔜 Próximo","$21.500.000"],
                ["The Open Championship","Royal Portrush – Irlanda","17–20 Jul 2026","Major","🔜 Próximo","$17.000.000"],
                ["PGA Championship","Quail Hollow – Carolina del N.","15–18 May 2026","Major","✅ Finalizado","$19.000.000"],
                ["Ryder Cup","Adare Manor – Irlanda","26–28 Sep 2026","Equipo","🔜 Próximo","—"],
                ["DP World Tour Finals","Dubai – EAU","18–21 Dic 2026","Tour","🔜 Próximo","$10.000.000"],
                ["Augusta National Women's Amateur","Augusta – EE.UU.","2–5 Abr 2026","Femenino","✅ Finalizado","—"],
                ["AIG Women's Open","Carnoustie – Escocia","7–10 Ago 2026","Major Fem.","🔜 Próximo","$9.000.000"],
                ["The Players Championship","Ponte Vedra – EE.UU.","13–16 Mar 2026","Tour","✅ Finalizado","$25.000.000"],
                ["FedEx Cup Playoffs","Atlanta – EE.UU.","28–31 Ago 2026","Tour","🔜 Próximo","$18.000.000"],
                ["Solheim Cup","Gainesville – EE.UU.","12–14 Sep 2026","Equipo","🔜 Próximo","—"],
                ["Memorial Tournament","Dublin – EE.UU.","5–8 Jun 2026","Tour","🔜 Próximo","$12.000.000"],
                ["Genesis Scottish Open","North Berwick – Escocia","10–13 Jul 2026","Tour","🔜 Próximo","$8.000.000"],
                ["Chevron Championship","Houston – EE.UU.","24–27 Abr 2026","Major Fem.","✅ Finalizado","$7.900.000"],
                ["Women's PGA Championship","Seattle – EE.UU.","19–22 Jun 2026","Major Fem.","🔜 Próximo","$8.500.000"],
                ["Evian Championship","Évian-les-Bains – Francia","14–17 Ago 2026","Major Fem.","🔜 Próximo","$8.000.000"],
            ];
            foreach ($torneos as $t): ?>
            <div class="torneo-card">
                <div class="torneo-left">
                    <span class="torneo-badge <?= $t[4]==='✅ Finalizado' ? 'badge-done' : 'badge-next' ?>"><?= $t[4] ?></span>
                    <h3 class="torneo-name"><?= $t[0] ?></h3>
                    <p class="torneo-lugar">📍 <?= $t[1] ?></p>
                </div>
                <div class="torneo-mid">
                    <span class="torneo-fecha"><?= $t[2] ?></span>
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
        <!-- Filtro tienda -->
        <div style="margin:20px 0;text-align:center;">
            <input type="text" id="filtroProducto" placeholder="Buscar producto..." style="padding:10px;width:250px;border:1px solid #ddd;border-radius:4px;" onkeyup="filtrarProductos()">
            <select id="filtroCategoria" style="padding:10px;margin-left:10px;border:1px solid #ddd;border-radius:4px;" onchange="filtrarProductos()">
                <option value="">Todas las categorías</option>
                <option value="Palos">Palos</option>
                <option value="Pelotas">Pelotas</option>
                <option value="Ropa">Ropa</option>
                <option value="Calzado">Calzado</option>
                <option value="Accesorios">Accesorios</option>
            </select>
            <select id="filtroPrecio" style="padding:10px;margin-left:10px;border:1px solid #ddd;border-radius:4px;" onchange="filtrarProductos()">
                <option value="">Todos los precios</option>
                <option value="0-100">€0 - €100</option>
                <option value="100-300">€100 - €300</option>
                <option value="300+">Más de €300</option>
            </select>
        </div>
        <div class="shop-grid">
            <?php
            $productos = [
                ["🏌️","Driver TaylorMade Qi10","El driver más vendido de la temporada.","€549","Palos",""],
                ["🏌️‍♂️","Set de Hierros Callaway","Precisión y control en cada golpe.","€899","Palos",""],
                ["⛳","Pelotas Pro V1x (docena)","Las favoritas de los pros en el tour.","€59","Pelotas",""],
                ["👚","Polo Nike Dri-FIT","Comodidad y estilo en el campo.","€79","Ropa",""],
                ["👟","Zapatos FootJoy Pro SL","Agarre y comodidad durante 18 hoyos.","€189","Calzado",""],
                ["🎒","Bolsa de Golf Titleist","Ligera y con múltiples compartimentos.","€329","Accesorios",""],
                ["🧢","Gorra Callaway Tour","Protección solar con estilo.","€35","Ropa",""],
                ["🔭","Rangefinder Bushnell","Mide distancias con precisión láser.","€249","Accesorios",""],
            ];
            foreach ($productos as $p): ?>
            <div class="product-card">
                <div class="product-icon"><?php if ($p[5]): ?><img src="<?= htmlspecialchars($p[5]) ?>" alt="<?= htmlspecialchars($p[1]) ?>" style="width:100%;height:100%;object-fit:contain;"><?php else: ?><?= $p[0] ?><?php endif; ?></div>
                <span class="product-cat"><?= $p[4] ?></span>
                <h3 class="product-name"><?= $p[1] ?></h3>
                <p class="product-desc"><?= $p[2] ?></p>
                <div class="product-footer">
                    <span class="product-price"><?= $p[3] ?></span>
                    <?php if ($loggedIn): ?>
                        <form method="POST" action="carrito.php" style="display:inline;">
                            <input type="hidden" name="add_to_cart" value="1">
                            <input type="hidden" name="producto_nombre" value="<?= htmlspecialchars($p[1]) ?>">
                            <input type="hidden" name="producto_precio" value="<?= str_replace('€', '', $p[3]) ?>">
                            <button type="submit" class="btn-buy" onclick="this.textContent='✓ Añadido'">Añadir</button>
                        </form>
                    <?php else: ?>
                        <a href="login.php" class="btn-buy-login">Inicia sesión</a>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <?php if (!$loggedIn): ?>
        <div class="shop-notice">
            <p><a href="login.php">Inicia sesión</a> o <a href="register.php">regístrate</a> para poder comprar productos.</p>
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

<!-- ===== FAVORITOS ===== -->
<section id="favoritos" class="section" style="background:#f8f9fa;">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">Tus Favoritos</h2>
            <p class="section-sub">Cuéntanos cuál es tu jugador y torneo preferido</p>
        </div>

        <?php if ($fav_message): ?>
            <div style="background:#d4edda;color:#155724;padding:15px;border-radius:8px;margin-bottom:20px;text-align:center;"><?= htmlspecialchars($fav_message) ?></div>
        <?php endif; ?>

        <form method="POST" style="max-width:550px;margin:0 auto;background:white;padding:40px;border-radius:12px;box-shadow:0 4px 6px rgba(0,0,0,0.1);">
            <div style="margin-bottom:24px;">
                <label style="display:block;font-weight:bold;margin-bottom:8px;color:#2c3e50;">¿Cuál es tu jugador favorito?</label>
                <input type="text" name="mejor_jugador" placeholder="Ej: Jon Rahm, Scottie Scheffler..." required style="width:100%;padding:14px;border:2px solid #e0e0e0;border-radius:8px;font-size:16px;">
            </div>
            <div style="margin-bottom:24px;">
                <label style="display:block;font-weight:bold;margin-bottom:8px;color:#2c3e50;">¿Cuál es tu torneo favorito?</label>
                <input type="text" name="campo_torneo" placeholder="Ej: The Masters, US Open..." required style="width:100%;padding:14px;border:2px solid #e0e0e0;border-radius:8px;font-size:16px;">
            </div>
            <button type="submit" name="submit_favorito" style="width:100%;padding:16px;background:#2c3e50;color:white;border:none;border-radius:8px;font-size:18px;cursor:pointer;font-weight:bold;">Enviar Favoritos</button>
        </form>
    </div>
</section>

<!-- ===== FOOTER ===== -->
<footer class="footer">
    <div class="footer-inner">
        <p class="footer-logo">⛳ Golf Club</p>
        <p class="footer-copy">© 2025 Golf Club. Todos los derechos reservados.</p>
        <?php if ($loggedIn): ?>
            <p class="footer-user">Sesión iniciada como <strong><?= htmlspecialchars($userName) ?></strong> · <a href="logout.php">Cerrar sesión</a></p>
        <?php else: ?>
            <p class="footer-user"><a href="login.php">Inicia sesión</a> o <a href="register.php">regístrate</a> para poder comprar.</p>
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

// Filtro jugadores
function filtrarJugadores() {
    const busqueda = document.getElementById('filtroJugador').value.toLowerCase();
    const pais = document.getElementById('filtroPais').value.toLowerCase();
    ['hombres', 'mujeres'].forEach(tab => {
        document.querySelectorAll('#' + tab + ' .player-card').forEach(card => {
            const nombre = card.querySelector('.player-name').textContent.toLowerCase();
            const paisCard = card.querySelector('.player-country').textContent.toLowerCase();
            card.style.display = (nombre.includes(busqueda) && (!pais || paisCard.includes(pais))) ? 'block' : 'none';
        });
    });
}

// Filtro torneos
function filtrarTorneos() {
    const busqueda = document.getElementById('filtroTorneo').value.toLowerCase();
    const tipo = document.getElementById('filtroTipo').value;
    const estado = document.getElementById('filtroEstado').value;
    document.querySelectorAll('.torneo-card').forEach(card => {
        const nombre = card.querySelector('.torneo-name').textContent.toLowerCase();
        const tipoCard = card.querySelector('.torneo-tipo').textContent;
        const estadoCard = card.querySelector('.torneo-badge').textContent;
        const coincideNombre = nombre.includes(busqueda);
        const coincideTipo = !tipo || tipoCard === tipo;
        const coincideEstado = !estado || estadoCard.includes(estado);
        card.style.display = coincideNombre && coincideTipo && coincideEstado ? 'flex' : 'none';
    });
}

// Filtro productos
function filtrarProductos() {
    const busqueda = document.getElementById('filtroProducto').value.toLowerCase();
    const categoria = document.getElementById('filtroCategoria').value;
    const precio = document.getElementById('filtroPrecio').value;
    document.querySelectorAll('.product-card').forEach(card => {
        const nombre = card.querySelector('.product-name').textContent.toLowerCase();
        const catCard = card.querySelector('.product-cat').textContent;
        const precioNum = parseFloat(card.querySelector('.product-price').textContent.replace('€', ''));
        let coincidePrecio = true;
        if (precio === '0-100') coincidePrecio = precioNum <= 100;
        else if (precio === '100-300') coincidePrecio = precioNum > 100 && precioNum <= 300;
        else if (precio === '300+') coincidePrecio = precioNum > 300;
        card.style.display = (nombre.includes(busqueda) && (!categoria || catCard === categoria) && coincidePrecio) ? 'block' : 'none';
    });
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

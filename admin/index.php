<?php
session_start();

// Conexión a BD solo cuando se necesita (no para backups)
$conn = null;
function conectar_bd() {
    global $conn;
    if ($conn !== null) return $conn;
    $host = "127.0.0.1"; $port = 3306; $user = "root"; $password = ""; $database = "progolf";
    $conn = @mysqli_connect($host, $user, $password, "", $port);
    if ($conn) {
        @mysqli_query($conn, "CREATE DATABASE IF NOT EXISTS $database");
        @mysqli_select_db($conn, $database);
        @mysqli_query($conn, "CREATE TABLE IF NOT EXISTS usuarios (id INT AUTO_INCREMENT PRIMARY KEY, nombre VARCHAR(100) NOT NULL, email VARCHAR(150) NOT NULL UNIQUE, password VARCHAR(255) NOT NULL, rol VARCHAR(20) DEFAULT 'user', fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP)");
        $check = @mysqli_query($conn, "SHOW COLUMNS FROM usuarios LIKE 'rol'");
        if ($check && mysqli_num_rows($check) == 0) {
            @mysqli_query($conn, "ALTER TABLE usuarios ADD COLUMN rol VARCHAR(20) DEFAULT 'user'");
        }
        @mysqli_query($conn, "CREATE TABLE IF NOT EXISTS carrito (id INT AUTO_INCREMENT PRIMARY KEY, usuario_id INT NOT NULL, producto_nombre VARCHAR(255) NOT NULL, producto_precio DECIMAL(10,2) NOT NULL, cantidad INT DEFAULT 1, fecha_agregado TIMESTAMP DEFAULT CURRENT_TIMESTAMP, FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE)");
        @mysqli_query($conn, "CREATE TABLE IF NOT EXISTS encuestas (id INT AUTO_INCREMENT PRIMARY KEY, usuario_id INT DEFAULT NULL, mejor_jugador VARCHAR(255) NOT NULL, campo_torneo VARCHAR(255) NOT NULL, fecha_respuesta TIMESTAMP DEFAULT CURRENT_TIMESTAMP)");
    }
    return $conn;
}

$correct_token = 'admin123';
$error = '';

// Logout
if (isset($_GET['logout'])) {
    unset($_SESSION['admin_auth']);
    unset($_SESSION['usuario_id']);
    unset($_SESSION['usuario_nombre']);
    unset($_SESSION['usuario_rol']);
    header('Location: index.php');
    exit;
}

// Procesar login del admin
if (isset($_POST['admin_login'])) {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (!empty($email) && !empty($password)) {
        $db = conectar_bd();
        $sql = "SELECT * FROM usuarios WHERE email='" . mysqli_real_escape_string($db, $email) . "'";
        $result = mysqli_query($db, $sql);
        if ($row = mysqli_fetch_assoc($result)) {
            if (password_verify($password, $row['password']) && ($row['rol'] ?? 'user') === 'admin') {
                $_SESSION['admin_auth'] = true;
                $_SESSION['usuario_id'] = $row['id'];
                $_SESSION['usuario_nombre'] = $row['nombre'];
                $_SESSION['usuario_rol'] = 'admin';
            } else {
                $error = 'Credenciales inválidas o no tienes permisos de administrador.';
            }
        } else {
            $error = 'Credenciales inválidas o no tienes permisos de administrador.';
        }
    } else {
        $error = 'Por favor, rellena todos los campos.';
    }
}

// Verificar autenticación: token O sesión admin O admin_auth
$token = $_GET['token'] ?? ($_POST['token'] ?? '');
$is_admin_session = (isset($_SESSION['usuario_rol']) && $_SESSION['usuario_rol'] === 'admin')
                 || !empty($_SESSION['admin_auth']);

if (!$is_admin_session && $token !== $correct_token) {
    // Mostrar formulario de login
    ?>
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Admin Login - Progolf</title>
        <link rel="stylesheet" href="../style.css">
        <style>
            .auth-container { min-height: 100vh; display: flex; align-items: center; justify-content: center; background: transparent; box-shadow: none; }
            .auth-container .auth-box { background: white; padding: 40px; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.1); width: 100%; max-width: 420px; text-align: center; }
            .auth-container h1 { font-family: 'Playfair Display', serif; color: #1a3c2b; margin-bottom: 8px; }
            .auth-container p { color: #666; margin-bottom: 24px; font-size: 14px; }
            .auth-container input { width: 100%; padding: 12px 16px; border: 2px solid #e0e0e0; border-radius: 8px; font-size: 15px; margin-bottom: 16px; font-family: 'Lato', sans-serif; box-sizing: border-box; }
            .auth-container input:focus { outline: none; border-color: #2e6b49; }
            .auth-container button { width: 100%; padding: 14px; background: #1a3c2b; color: white; border: none; border-radius: 8px; font-size: 16px; font-weight: 700; cursor: pointer; font-family: 'Lato', sans-serif; transition: background 0.2s; }
            .auth-container button:hover { background: #2e6b49; }
            .auth-container .error { background: #fdecea; color: #c0392b; padding: 12px; border-radius: 8px; margin-bottom: 16px; font-size: 14px; }
            .auth-container .admin-icon { font-size: 48px; margin-bottom: 16px; }
            .auth-container .back-link { display: block; margin-top: 16px; color: #2e6b49; text-decoration: none; font-size: 14px; }
            .auth-container .back-link:hover { text-decoration: underline; }
        </style>
    </head>
    <body class="auth-page">
        <div class="auth-container">
            <div class="auth-box">
                <h1>Admin Progolf</h1>
                <p>Ingresa con tu cuenta de administrador</p>
                <?php if ($error): ?>
                    <div class="error"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>
                <form method="POST">
                    <input type="email" name="email" placeholder="Email" required autofocus>
                    <input type="password" name="password" placeholder="Contraseña" required>
                    <button type="submit" name="admin_login">Acceder al Panel</button>
                </form>
                <a href="../index.php" class="back-link">← Volver al inicio</a>
            </div>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// Si entró por sesión, usar el token para las llamadas JS internas
if ($is_admin_session) {
    $token = $correct_token;
}

$section = $_GET['section'] ?? 'dashboard';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administración - Progolf</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,700;1,400&family=Lato:wght@300;400;700&display=swap');
        :root {
            --green-dark: #1a3c2b;
            --green-mid: #2e6b49;
            --green-light: #4a9b6f;
            --gold: #c9a84c;
            --gold-light: #e8c97a;
            --cream: #f9f5ee;
            --white: #ffffff;
            --gray-light: #f0ece4;
            --gray-mid: #9e9e9e;
            --text-dark: #1c1c1c;
            --text-mid: #444;
            --shadow: 0 4px 20px rgba(0,0,0,0.10);
            --radius: 12px;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Lato', sans-serif; background: var(--cream); display: flex; min-height: 100vh; color: var(--text-dark); }
        .sidebar {
            width: 240px;
            background: var(--green-dark);
            color: white;
            display: flex;
            flex-direction: column;
            position: fixed;
            top: 0;
            left: 0;
            bottom: 0;
            z-index: 100;
        }
        .sidebar-header {
            padding: 24px 20px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            text-align: center;
        }
        .sidebar-header h2 { font-family: 'Playfair Display', serif; font-size: 20px; margin-bottom: 4px; color: var(--gold); }
        .sidebar-header p { font-size: 12px; color: #7eb898; }
        .sidebar-nav { padding: 10px 0; flex: 1; }
        .sidebar-nav a {
            display: block;
            padding: 12px 20px;
            color: #a8c8b8;
            text-decoration: none;
            font-size: 14px;
            transition: all 0.2s;
            border-left: 3px solid transparent;
            font-weight: 400;
        }
        .sidebar-nav a:hover { background: rgba(255,255,255,0.05); color: var(--gold-light); }
        .sidebar-nav a.active {
            background: rgba(201,168,76,0.1);
            color: var(--gold-light);
            border-left-color: var(--gold);
        }
        .sidebar-footer {
            padding: 15px 20px;
            border-top: 1px solid rgba(255,255,255,0.1);
            font-size: 12px;
            color: #7eb898;
        }
        .sidebar-footer a { color: var(--gold); text-decoration: none; font-weight: 700; }
        .sidebar-footer a:hover { color: var(--gold-light); }
        .main-content {
            margin-left: 240px;
            flex: 1;
            padding: 30px 35px;
        }
        .page-header {
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid #e0dcd4;
        }
        .page-header h1 { font-family: 'Playfair Display', serif; font-size: 28px; color: var(--green-dark); }
        .page-header p { color: var(--gray-mid); font-size: 14px; margin-top: 4px; }
        .stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .stat-card { background: var(--white); padding: 22px; border-radius: var(--radius); box-shadow: var(--shadow); border-top: 3px solid var(--green-light); }
        .stat-card h3 { color: var(--gray-mid); font-size: 12px; margin-bottom: 8px; text-transform: uppercase; letter-spacing: 1px; font-weight: 700; }
        .stat-card .value { font-size: 28px; font-weight: 700; color: var(--green-dark); }
        .btn {
            padding: 10px 22px;
            border: none;
            border-radius: 20px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 700;
            text-decoration: none;
            display: inline-block;
            font-family: 'Lato', sans-serif;
            transition: all 0.2s;
        }
        .btn-primary { background: var(--green-mid); color: white; }
        .btn-primary:hover { background: var(--green-dark); transform: translateY(-1px); }
        .btn-danger { background: #c0392b; color: white; }
        .btn-danger:hover { background: #a93226; transform: translateY(-1px); }
        .btn-success { background: var(--green-light); color: white; }
        .btn-success:hover { background: var(--green-mid); transform: translateY(-1px); }
        .btn-warning { background: var(--gold); color: var(--green-dark); }
        .btn-warning:hover { background: var(--gold-light); transform: translateY(-1px); }
        .btn-small { padding: 6px 14px; font-size: 12px; border-radius: 16px; }
        .card { background: var(--white); padding: 24px; border-radius: var(--radius); box-shadow: var(--shadow); margin-bottom: 22px; }
        .card h2 { font-family: 'Playfair Display', serif; font-size: 20px; margin-bottom: 18px; color: var(--green-dark); }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 14px 12px; text-align: left; border-bottom: 1px solid #e8e4dc; }
        th { background: var(--gray-light); font-weight: 700; color: var(--green-dark); font-size: 12px; text-transform: uppercase; letter-spacing: 1px; }
        td { font-size: 14px; }
        tr:hover { background: #f5f1ea; }
        .message {
            padding: 14px 18px;
            margin-bottom: 20px;
            border-radius: var(--radius);
            font-size: 14px;
            font-weight: 700;
        }
        .message.success { background: #e8f5e9; color: #2e7d32; border: 1px solid #c8e6c9; }
        .message.error { background: #fdecea; color: #c0392b; border: 1px solid #f5c6c2; }
        .message.info { background: #d1ecf1; color: #0c5460; border: 1px solid #bee5eb; }
        .empty { text-align: center; padding: 50px; color: var(--gray-mid); font-size: 15px; }
        .form-group { margin-bottom: 18px; }
        .form-group label { display: block; font-size: 12px; font-weight: 700; color: var(--text-mid); margin-bottom: 6px; text-transform: uppercase; letter-spacing: 0.5px; }
        .form-group input, .form-group select {
            width: 100%;
            padding: 11px 14px;
            border: 2px solid #ddd;
            border-radius: 10px;
            font-size: 14px;
            font-family: 'Lato', sans-serif;
            transition: border-color 0.2s;
            background: var(--cream);
        }
        .form-group input:focus, .form-group select:focus {
            outline: none;
            border-color: var(--green-mid);
            background: white;
        }
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 18px; }
        .badge {
            padding: 4px 10px;
            border-radius: 10px;
            font-size: 11px;
            font-weight: 700;
            color: white;
        }
        .badge-blue { background: var(--green-mid); }
        .badge-green { background: var(--green-light); }
        .text-muted { color: var(--gray-mid); font-size: 13px; }
        .mt-20 { margin-top: 20px; }
        .mb-20 { margin-bottom: 20px; }
        .flex { display: flex; }
        .flex-between { display: flex; justify-content: space-between; align-items: center; }
        .gap-10 { gap: 10px; }
        .w-half { width: 50%; }
        a { color: var(--green-mid); font-weight: 700; text-decoration: none; }
        a:hover { color: var(--green-dark); }
        @media (max-width: 768px) {
            .sidebar { width: 60px; }
            .sidebar-header h2, .sidebar-header p, .sidebar-nav a span, .sidebar-footer { display: none; }
            .sidebar-nav a { text-align: center; padding: 15px 5px; }
            .main-content { margin-left: 60px; padding: 15px; }
            .form-row { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

<aside class="sidebar">
    <div class="sidebar-header">
        <h2>Admin Progolf</h2>
        <p>Panel de control</p>
    </div>
    <nav class="sidebar-nav">
        <a href="?token=<?= htmlspecialchars($token) ?>&section=dashboard" class="<?= $section === 'dashboard' ? 'active' : '' ?>">
            <span>Dashboard</span>
        </a>
        <a href="?token=<?= htmlspecialchars($token) ?>&section=backups" class="<?= $section === 'backups' ? 'active' : '' ?>">
            <span>Backups</span>
        </a>
        <a href="?token=<?= htmlspecialchars($token) ?>&section=usuarios" class="<?= $section === 'usuarios' ? 'active' : '' ?>">
            <span>Usuarios</span>
        </a>
        <a href="?token=<?= htmlspecialchars($token) ?>&section=encuestas" class="<?= $section === 'encuestas' ? 'active' : '' ?>">
            <span>Encuestas</span>
        </a>
    </nav>
    <div class="sidebar-footer">
        <a href="../index.php">Volver al sitio</a>
        <span style="display:block;margin-top:8px;"><a href="?logout=1" style="color:#e74c3c;">Cerrar sesión</a></span>
    </div>
</aside>

<main class="main-content">
    <?php
    $message = $_GET['msg'] ?? '';
    $msg_status = $_GET['status'] ?? '';

    switch ($section) {
        case 'backups':
            require 'backups.php';
            break;
        case 'usuarios':
            require 'usuarios.php';
            break;
        case 'encuestas':
            require 'encuestas.php';
            break;
        default:
            // Dashboard
            $db = conectar_bd();
            $total_usuarios = 0;
            $total_encuestas = 0;
            if ($db) {
                $r = @mysqli_query($db, "SELECT COUNT(*) as count FROM usuarios");
                $total_usuarios = $r ? (int)mysqli_fetch_assoc($r)['count'] : 0;
                $r2 = @mysqli_query($db, "SELECT COUNT(*) as count FROM encuestas");
                $total_encuestas = $r2 ? (int)mysqli_fetch_assoc($r2)['count'] : 0;
            }
            $total_encuestas = $sql_check ? mysqli_fetch_assoc($sql_check)['count'] : 0;
            ?>
            <div class="page-header">
                <h1>Dashboard</h1>
                <p>Resumen del sistema Progolf</p>
            </div>
            <div class="stats">
                <div class="stat-card">
                    <h3>Usuarios Registrados</h3>
                    <div class="value"><?= $total_usuarios ?></div>
                </div>
                <div class="stat-card">
                    <h3>Encuestas Recibidas</h3>
                    <div class="value"><?= $total_encuestas ?></div>
                </div>
                <div class="stat-card">
                    <h3>Backups</h3>
                    <div class="value"><?php
                        $backup_dir = __DIR__ . '/../backups';
                        $count = 0;
                        if (is_dir($backup_dir)) {
                            foreach (scandir($backup_dir) as $f) {
                                if ($f != '.' && $f != '..' && $f != 'backup.log') $count++;
                            }
                        }
                        echo $count;
                    ?></div>
                </div>
                <div class="stat-card">
                    <h3>Espacio Libre</h3>
                    <div class="value"><?= round(disk_free_space(__DIR__ . '/../backups') / 1024 / 1024 / 1024, 2) ?> GB</div>
                </div>
            </div>
            <div class="card">
                <h2>Acceso Rápido</h2>
                <div class="flex gap-10">
                    <a href="?token=<?= htmlspecialchars($token) ?>&section=backups" class="btn btn-primary">Gestionar Backups</a>
                    <a href="?token=<?= htmlspecialchars($token) ?>&section=usuarios" class="btn btn-success">Gestionar Usuarios</a>
                </div>
            </div>
            <?php
            break;
    }
    ?>
</main>

</body>
</html>

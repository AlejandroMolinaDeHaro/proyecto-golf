<?php
session_start();
require 'db.php';

if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

$usuario_id = $_SESSION['usuario_id'];
$loggedIn = true;
$userName = $_SESSION['usuario_nombre'];
$message = '';

// Agregar producto al carrito
if (isset($_POST['add_to_cart'])) {
    $producto_nombre = $_POST['producto_nombre'];
    $producto_precio = floatval($_POST['producto_precio']);
    
    // Verificar si ya está en el carrito
    $check = mysqli_query($conn, "SELECT * FROM carrito WHERE usuario_id = $usuario_id AND producto_nombre = '" . mysqli_real_escape_string($conn, $producto_nombre) . "'");
    
    if (mysqli_num_rows($check) > 0) {
        mysqli_query($conn, "UPDATE carrito SET cantidad = cantidad + 1 WHERE usuario_id = $usuario_id AND producto_nombre = '" . mysqli_real_escape_string($conn, $producto_nombre) . "'");
    } else {
        mysqli_query($conn, "INSERT INTO carrito (usuario_id, producto_nombre, producto_precio) VALUES ($usuario_id, '" . mysqli_real_escape_string($conn, $producto_nombre) . "', $producto_precio)");
    }
    $message = 'Producto añadido al carrito';
}

// Eliminar del carrito
if (isset($_GET['remove'])) {
    $item_id = intval($_GET['remove']);
    mysqli_query($conn, "DELETE FROM carrito WHERE id = $item_id AND usuario_id = $usuario_id");
    $message = 'Producto eliminado del carrito';
}

// Actualizar cantidad
if (isset($_POST['update_qty'])) {
    $item_id = intval($_POST['item_id']);
    $cantidad = intval($_POST['cantidad']);
    if ($cantidad > 0) {
        mysqli_query($conn, "UPDATE carrito SET cantidad = $cantidad WHERE id = $item_id AND usuario_id = $usuario_id");
    }
}

// Obtener items del carrito
$carrito_items = [];
$total = 0;
$result = mysqli_query($conn, "SELECT * FROM carrito WHERE usuario_id = $usuario_id ORDER BY fecha_agregado DESC");
while ($row = mysqli_fetch_assoc($result)) {
    $row['subtotal'] = $row['producto_precio'] * $row['cantidad'];
    $carrito_items[] = $row;
    $total += $row['subtotal'];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Carrito - Golf Club</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .cart-container { max-width: 900px; margin: 40px auto; padding: 0 20px; }
        .cart-item { display: flex; align-items: center; background: var(--white); padding: 22px; border-radius: var(--radius); margin-bottom: 15px; box-shadow: var(--shadow); border-left: 4px solid var(--green-light); }
        .cart-item-info { flex: 1; }
        .cart-item-name { font-family: 'Playfair Display', serif; font-weight: 700; font-size: 18px; color: var(--text-dark); }
        .cart-item-price { color: var(--green-mid); font-size: 16px; margin-top: 5px; font-weight: 700; }
        .cart-item-qty { display: flex; align-items: center; gap: 10px; margin: 0 20px; }
        .cart-item-qty input { width: 60px; padding: 7px; text-align: center; border: 2px solid #ddd; border-radius: 8px; font-size: 14px; font-family: 'Lato', sans-serif; background: var(--cream); }
        .cart-item-qty input:focus { outline: none; border-color: var(--green-mid); background: white; }
        .cart-item-subtotal { font-weight: 700; min-width: 100px; text-align: right; font-size: 18px; color: var(--green-dark); }
        .cart-total { background: var(--white); padding: 28px; border-radius: var(--radius); margin-top: 20px; text-align: right; box-shadow: var(--shadow); border-top: 3px solid var(--gold); }
        .cart-total h3 { font-size: 26px; color: var(--green-dark); font-family: 'Playfair Display', serif; }
        .btn-remove { background: #c0392b; color: white; border: none; padding: 8px 18px; border-radius: 20px; cursor: pointer; font-weight: 700; font-size: 13px; font-family: 'Lato', sans-serif; transition: all 0.2s; }
        .btn-remove:hover { background: #a93226; transform: translateY(-1px); }
        .btn-update { background: var(--green-mid); color: white; border: none; padding: 8px 18px; border-radius: 20px; cursor: pointer; font-weight: 700; font-size: 13px; font-family: 'Lato', sans-serif; transition: all 0.2s; }
        .btn-update:hover { background: var(--green-dark); transform: translateY(-1px); }
        .empty-cart { text-align: center; padding: 60px; background: var(--white); border-radius: var(--radius); box-shadow: var(--shadow); }
        .empty-cart h2 { font-family: 'Playfair Display', serif; color: var(--text-dark); }
        .btn-continue { display: inline-block; margin-top: 15px; padding: 12px 30px; background: var(--gold); color: var(--green-dark); font-weight: 700; border-radius: 25px; text-decoration: none; font-family: 'Lato', sans-serif; transition: all 0.2s; }
        .btn-continue:hover { background: var(--gold-light); transform: translateY(-2px); }
        .cart-container h1 { font-family: 'Playfair Display', serif; margin-bottom: 30px; color: var(--green-dark); }
        .cart-container a { color: var(--green-mid); font-weight: 700; text-decoration: none; }
        .cart-container a:hover { color: var(--green-dark); }
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>

    <div class="cart-container">
        <h1>🛒 Tu Carrito</h1>

        <?php if ($message): ?>
            <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>

        <?php if (empty($carrito_items)): ?>
            <div class="empty-cart">
                <h2>Tu carrito está vacío</h2>
                <p style="margin-top: 15px; color: var(--gray-mid);">Añade productos desde nuestra <a href="index.php#tienda">tienda</a></p>
            </div>
        <?php else: ?>
            <?php foreach ($carrito_items as $item): ?>
            <div class="cart-item">
                <div class="cart-item-info">
                    <div class="cart-item-name"><?= htmlspecialchars($item['producto_nombre']) ?></div>
                    <div class="cart-item-price">€<?= number_format($item['producto_precio'], 2) ?></div>
                </div>
                <form method="POST" class="cart-item-qty">
                    <input type="hidden" name="item_id" value="<?= $item['id'] ?>">
                    <label>Cantidad:</label>
                    <input type="number" name="cantidad" value="<?= $item['cantidad'] ?>" min="1">
                    <button type="submit" name="update_qty" class="btn-update">Actualizar</button>
                </form>
                <div class="cart-item-subtotal">€<?= number_format($item['subtotal'], 2) ?></div>
                <a href="?remove=<?= $item['id'] ?>" class="btn-remove" onclick="return confirm('¿Eliminar este producto?')">Eliminar</a>
            </div>
            <?php endforeach; ?>

            <div class="cart-total">
                <h3>Total: €<?= number_format($total, 2) ?></h3>
                <a href="index.php#tienda" class="btn-continue">Seguir Comprando</a>
            </div>
        <?php endif; ?>
    </div>

    <?php include 'footer.php'; ?>
</body>
</html>

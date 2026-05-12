<?php
session_start();
require 'db.php';

if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

$usuario_id = $_SESSION['usuario_id'];
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
        .cart-item { display: flex; align-items: center; background: white; padding: 20px; border-radius: 8px; margin-bottom: 15px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .cart-item-info { flex: 1; }
        .cart-item-name { font-weight: bold; font-size: 18px; }
        .cart-item-price { color: #27ae60; font-size: 16px; margin-top: 5px; }
        .cart-item-qty { display: flex; align-items: center; gap: 10px; margin: 0 20px; }
        .cart-item-qty input { width: 60px; padding: 5px; text-align: center; border: 1px solid #ddd; border-radius: 4px; }
        .cart-item-subtotal { font-weight: bold; min-width: 100px; text-align: right; }
        .cart-total { background: white; padding: 25px; border-radius: 8px; margin-top: 20px; text-align: right; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .cart-total h3 { font-size: 24px; color: #2c3e50; }
        .btn-remove { background: #e74c3c; color: white; border: none; padding: 8px 15px; border-radius: 4px; cursor: pointer; }
        .btn-remove:hover { background: #c0392b; }
        .btn-update { background: #3498db; color: white; border: none; padding: 8px 15px; border-radius: 4px; cursor: pointer; margin-left: 10px; }
        .empty-cart { text-align: center; padding: 60px; background: white; border-radius: 8px; }
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>

    <div class="cart-container">
        <h1 style="margin-bottom: 30px;">🛒 Tu Carrito</h1>

        <?php if ($message): ?>
            <div style="background: #d4edda; color: #155724; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>

        <?php if (empty($carrito_items)): ?>
            <div class="empty-cart">
                <h2>Tu carrito está vacío</h2>
                <p style="margin-top: 15px; color: #666;">Añade productos desde nuestra <a href="index.php#tienda">tienda</a></p>
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
                <a href="index.php#tienda" style="display: inline-block; margin-top: 15px; padding: 12px 30px; background: #27ae60; color: white; text-decoration: none; border-radius: 4px;">Seguir Comprando</a>
            </div>
        <?php endif; ?>
    </div>

    <?php include 'footer.php'; ?>
</body>
</html>

<?php
require 'includes/header.php';
require 'includes/db_mysql.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
$userId = $_SESSION['user_id'];

// Handle Add to Cart
if (isset($_POST['add_to_cart'])) {
    $productId = $_POST['product_id'];
    $quantity = (int)$_POST['quantity'];

    $stmt = $pdo->prepare("SELECT id, quantity FROM cart WHERE user_id = ? AND product_id = ?");
    $stmt->execute([$userId, $productId]);
    $existing = $stmt->fetch();

    if ($existing) {
        $newQty = $existing['quantity'] + $quantity;
        $update = $pdo->prepare("UPDATE cart SET quantity = ? WHERE id = ?");
        $update->execute([$newQty, $existing['id']]);
    } else {
        $insert = $pdo->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)");
        $insert->execute([$userId, $productId, $quantity]);
    }
    header('Location: cart.php');
    exit;
}

// Handle Remove
if (isset($_GET['remove'])) {
    $cartId = (int)$_GET['remove'];
    $delete = $pdo->prepare("DELETE FROM cart WHERE id = ? AND user_id = ?");
    $delete->execute([$cartId, $userId]);
    header('Location: cart.php');
    exit;
}

// Fetch cart items
$items = [];
$stmt = $pdo->prepare("SELECT id, product_id, quantity FROM cart WHERE user_id = ?");
$stmt->execute([$userId]);
$cartRows = $stmt->fetchAll();

if ($cartRows) {
    require 'includes/db_mongo.php';
    $ids = [];
    foreach ($cartRows as $row) {
        $ids[] = new MongoDB\BSON\ObjectId($row['product_id']);
    }
    $productCursor = $productsCollection->find(['_id' => ['$in' => $ids]]);
    $products = [];
    foreach ($productCursor as $p) {
        $products[(string)$p->_id] = $p;
    }
    foreach ($cartRows as $row) {
        if (isset($products[$row['product_id']])) {
            $items[] = [
                'cart_id'  => $row['id'],
                'product'  => $products[$row['product_id']],
                'quantity' => $row['quantity'],
                'subtotal' => $products[$row['product_id']]->price * $row['quantity']
            ];
        }
    }
}
?>

<h2>Your Cart</h2>
<?php if (empty($items)): ?>
    <p>Your cart is empty.</p>
<?php else: ?>
    <table border="1" cellpadding="5">
        <tr><th>Product</th><th>Price</th><th>Qty</th><th>Subtotal</th><th></th></tr>
        <?php foreach ($items as $item): ?>
        <tr>
            <td><?= htmlspecialchars($item['product']->name) ?></td>
            <td>$<?= number_format($item['product']->price, 2) ?></td>
            <td><?= $item['quantity'] ?></td>
            <td>$<?= number_format($item['subtotal'], 2) ?></td>
            <td><a href="cart.php?remove=<?= $item['cart_id'] ?>" onclick="return confirm('Remove item?')">Remove</a></td>
        </tr>
        <?php endforeach; ?>
    </table>
    <p><a href="checkout.php"><button>Proceed to Checkout</button></a></p>
<?php endif; ?>
<?php require 'includes/footer.php'; ?>
<?php
require 'includes/header.php';
require 'includes/db_mysql.php';

if (!isset($_SESSION['user_id'])) { header('Location: login.php'); exit; }
$userId = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT id, order_date, total FROM orders WHERE user_id = ? ORDER BY order_date DESC");
$stmt->execute([$userId]);
$orders = $stmt->fetchAll();

if (empty($orders)) {
    echo "<p>No orders yet. <a href='index.php'>Start shopping</a></p>";
    require 'includes/footer.php';
    exit;
}

require 'includes/db_mongo.php';
foreach ($orders as &$order) {
    $itemsStmt = $pdo->prepare("SELECT product_id, quantity, price FROM order_items WHERE order_id = ?");
    $itemsStmt->execute([$order['id']]);
    $items = $itemsStmt->fetchAll();
    $productIds = [];
    foreach ($items as $it) $productIds[] = new MongoDB\BSON\ObjectId($it['product_id']);
    $cursor = $productsCollection->find(['_id' => ['$in' => $productIds]]);
    $products = [];
    foreach ($cursor as $p) $products[(string)$p->_id] = $p;
    $order['items'] = [];
    foreach ($items as $it) {
        $name = $products[$it['product_id']]->name ?? 'Unknown';
        $order['items'][] = [
            'name'      => $name,
            'quantity'  => $it['quantity'],
            'price'     => $it['price'],
            'subtotal'  => $it['quantity'] * $it['price']
        ];
    }
}
unset($order);
?>

<h2>Your Orders</h2>
<?php if (isset($_GET['success'])): ?><p style="color:green">Order placed successfully!</p><?php endif; ?>
<?php foreach ($orders as $order): ?>
    <div style="border:1px solid #ccc; margin:10px; padding:10px;">
        <h3>Order #<?= $order['id'] ?> – <?= $order['order_date'] ?> | Total $<?= number_format($order['total'], 2) ?></h3>
        <table border="1" cellpadding="3">
            <tr><th>Product</th><th>Price</th><th>Qty</th><th>Subtotal</th></tr>
            <?php foreach ($order['items'] as $it): ?>
            <tr>
                <td><?= htmlspecialchars($it['name']) ?></td>
                <td>$<?= number_format($it['price'], 2) ?></td>
                <td><?= $it['quantity'] ?></td>
                <td>$<?= number_format($it['subtotal'], 2) ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>
<?php endforeach; ?>
<?php require 'includes/footer.php'; ?>
<?php
require 'includes/header.php';
require 'includes/db_mysql.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
$userId = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT product_id, quantity FROM cart WHERE user_id = ?");
$stmt->execute([$userId]);
$cartItems = $stmt->fetchAll();

if (empty($cartItems)) {
    echo "<p>Your cart is empty. <a href='index.php'>Go shopping</a></p>";
    require 'includes/footer.php';
    exit;
}

require 'includes/db_mongo.php';
$ids = [];
foreach ($cartItems as $item) {
    $ids[] = new MongoDB\BSON\ObjectId($item['product_id']);
}
$productCursor = $productsCollection->find(['_id' => ['$in' => $ids]]);
$products = [];
foreach ($productCursor as $p) {
    $products[(string)$p->_id] = $p;
}

$orderTotal = 0;
$orderLines = [];
$stockErrors = [];

foreach ($cartItems as $item) {
    $pid = $item['product_id'];
    $qty = (int)$item['quantity'];
    if (!isset($products[$pid])) {
        $stockErrors[] = "Product with ID $pid no longer exists.";
        continue;
    }
    $price = $products[$pid]->price;

    $invStmt = $pdo->prepare("SELECT stock FROM inventory WHERE product_id = ?");
    $invStmt->execute([$pid]);
    $inv = $invStmt->fetch();
    if (!$inv || $inv['stock'] < $qty) {
        $stockErrors[] = "Not enough stock for " . $products[$pid]->name;
        continue;
    }

    $orderLines[] = ['product_id' => $pid, 'quantity' => $qty, 'price' => $price];
    $orderTotal += $price * $qty;
}

if (!empty($stockErrors)) {
    echo "<p style='color:red'>Some items could not be ordered:</p><ul>";
    foreach ($stockErrors as $e) echo "<li>" . htmlspecialchars($e) . "</li>";
    echo "</ul><a href='cart.php'>Back to cart</a>";
    require 'includes/footer.php';
    exit;
}

try {
    $pdo->beginTransaction();

    $orderStmt = $pdo->prepare("INSERT INTO orders (user_id, total) VALUES (?, ?)");
    $orderStmt->execute([$userId, $orderTotal]);
    $orderId = $pdo->lastInsertId();

    $itemStmt = $pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
    $stockUpdate = $pdo->prepare("UPDATE inventory SET stock = stock - ? WHERE product_id = ? AND stock >= ?");

    foreach ($orderLines as $line) {
        $itemStmt->execute([$orderId, $line['product_id'], $line['quantity'], $line['price']]);
        $stockUpdate->execute([$line['quantity'], $line['product_id'], $line['quantity']]);
        if ($stockUpdate->rowCount() == 0) {
            throw new Exception("Stock deduction failed for product_id: " . $line['product_id']);
        }
    }

    $pdo->prepare("DELETE FROM cart WHERE user_id = ?")->execute([$userId]);
    $pdo->commit();

    header("Location: order_history.php?order_success=1");
    exit;
} catch (Exception $e) {
    $pdo->rollBack();
    echo "<p style='color:red'>Order failed: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<a href='cart.php'>Back to cart</a>";
}

require 'includes/footer.php';
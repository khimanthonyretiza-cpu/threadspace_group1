<?php
require 'includes/header.php';
require 'includes/db_mysql.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

require 'includes/db_mongo.php';

$message = '';

if (isset($_POST['add_product'])) {
    $name        = $_POST['name'];
    $price       = (float)$_POST['price'];
    $category    = $_POST['category'];
    $description = $_POST['description'];
    $image_url   = $_POST['image_url'];
    $stock       = (int)$_POST['stock'];

    $attributes = json_decode($_POST['attributes_json'], true);
    if ($attributes === null) {
        $message = "Invalid JSON in attributes.";
    } else {
        $productDoc = [
            'name'        => $name,
            'price'       => $price,
            'category'    => $category,
            'description' => $description,
            'image_url'   => $image_url,
            'attributes'  => $attributes
        ];
        $insertResult = $productsCollection->insertOne($productDoc);
        $productId = (string)$insertResult->getInsertedId();

        $stmt = $pdo->prepare("INSERT INTO inventory (product_id, category, stock) VALUES (?, ?, ?)");
        $stmt->execute([$productId, $category, $stock]);

        $message = "Product added successfully (ID: $productId).";
    }
}
?>

<h2>Admin Panel</h2>
<?php if ($message): ?><p style="color:green"><?= htmlspecialchars($message) ?></p><?php endif; ?>

<h3>Add New Product</h3>
<form method="POST">
    <label>Name: <input type="text" name="name" required></label><br>
    <label>Price: <input type="number" step="0.01" name="price" required></label><br>
    <label>Category:
        <select name="category" required>
            <option value="shirts">Shirts</option>
            <option value="footwear">Footwear</option>
            <option value="accessories">Accessories</option>
            <option value="bags">Bags</option>
        </select>
    </label><br>
    <label>Description: <textarea name="description" rows="3" cols="40"></textarea></label><br>
    <label>Image URL: <input type="text" name="image_url" value="images/placeholder.jpg"></label><br>
    <label>Initial Stock: <input type="number" name="stock" min="0" required></label><br>
    <label>Attributes (JSON):<br>
        <textarea name="attributes_json" rows="5" cols="50" required>
{
  "sizes": ["S", "M", "L"],
  "colors": ["red", "blue"]
}
        </textarea>
    </label><br>
    <button type="submit" name="add_product">Add Product</button>
</form>

<h3>All Orders</h3>
<?php
$orderQuery = $pdo->query("SELECT o.id, o.user_id, o.order_date, o.total, u.username FROM orders o JOIN users u ON o.user_id = u.id ORDER BY o.order_date DESC");
$allOrders = $orderQuery->fetchAll();
if (empty($allOrders)) {
    echo "<p>No orders yet.</p>";
} else {
    echo "<table border='1' cellpadding='5'><tr><th>Order ID</th><th>User</th><th>Date</th><th>Total</th></tr>";
    foreach ($allOrders as $ord) {
        echo "<tr>
                <td>{$ord['id']}</td>
                <td>" . htmlspecialchars($ord['username']) . "</td>
                <td>{$ord['order_date']}</td>
                <td>\$" . number_format($ord['total'], 2) . "</td>
              </tr>";
    }
    echo "</table>";
}
?>

<?php require 'includes/footer.php'; ?>
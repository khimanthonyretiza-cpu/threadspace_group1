<?php
require 'includes/db_mongo.php';
$products = $productsCollection->find([]);
require 'includes/header.php';
?>
<h1>Our Products</h1>
<div class="product-grid">
    <?php foreach ($products as $product): ?>
        <div class="product-card">
            <img src="<?= htmlspecialchars($product->image_url) ?>" alt="<?= htmlspecialchars($product->name) ?>" width="150">
            <h3><?= htmlspecialchars($product->name) ?></h3>
            <p class="category"><?= htmlspecialchars($product->category) ?></p>
            <p class="price">$<?= number_format($product->price, 2) ?></p>
            <p><?= htmlspecialchars($product->description) ?></p>
            <?php if (isset($_SESSION['user_id'])): ?>
                <form method="POST" action="cart.php">
                    <input type="hidden" name="product_id" value="<?= $product->_id ?>">
                    <input type="number" name="quantity" value="1" min="1" style="width:50px">
                    <button type="submit" name="add_to_cart">Add to Cart</button>
                </form>
            <?php else: ?>
                <p><a href="login.php">Log in to buy</a></p>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
</div>
<?php require 'includes/footer.php'; ?>
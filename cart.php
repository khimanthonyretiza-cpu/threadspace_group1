<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require 'includes/header.php';
require 'includes/db_mongo.php';

if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
  $_SESSION['cart'] = [];
}

// Add to cart (Option A: only allow real Mongo ObjectIds)
if (isset($_POST['add_to_cart'])) {
  $productId = (string)($_POST['product_id'] ?? '');
  $quantity = max(1, (int)($_POST['quantity'] ?? 1));

  // Block placeholders outright
  if ($productId !== '' && !str_starts_with($productId, 'ph-')) {
    // Validate it looks like a MongoDB ObjectId
    try {
      new MongoDB\BSON\ObjectId($productId);
      $_SESSION['cart'][$productId] = (int)($_SESSION['cart'][$productId] ?? 0) + $quantity;
    } catch (Exception $e) {
      // ignore invalid ids
    }
  }

  header('Location: cart.php'); exit;
}

// Remove item
if (isset($_GET['remove'])) {
  $pid = (string)$_GET['remove'];
  unset($_SESSION['cart'][$pid]);
  header('Location: cart.php'); exit;
}

$cart = $_SESSION['cart'];

// Clean out placeholder/invalid ids if any were saved previously
foreach (array_keys($cart) as $pid) {
  if (str_starts_with((string)$pid, 'ph-')) {
    unset($_SESSION['cart'][(string)$pid]);
    continue;
  }
  try {
    new MongoDB\BSON\ObjectId((string)$pid);
  } catch (Exception $e) {
    unset($_SESSION['cart'][(string)$pid]);
  }
}
$cart = $_SESSION['cart'];

$items = [];
$grandTotal = 0;

if (!empty($cart)) {
  $ids = [];
  foreach (array_keys($cart) as $pid) {
    try { $ids[] = new MongoDB\BSON\ObjectId($pid); } catch (Exception $e) {}
  }

  if (!empty($ids)) {
    $cursor = $productsCollection->find(['_id' => ['$in' => $ids]]);
    $products = [];
    foreach ($cursor as $p) $products[(string)$p->_id] = $p;

    foreach ($cart as $pid => $qty) {
      if (!isset($products[$pid])) continue;

      $p = $products[$pid];
      $price = (float)($p->price ?? 0);
      $subtotal = $price * (int)$qty;
      $grandTotal += $subtotal;

      $items[] = [
        'product' => $p,
        'pid' => $pid,
        'quantity' => (int)$qty,
        'subtotal' => $subtotal,
      ];
    }
  }
}
?>

<section class="ts-page">
  <div class="container">
    <div class="d-flex align-items-end justify-content-between flex-wrap gap-2 mb-3">
      <div>
        <h2 class="ts-section-title mb-1">Your Cart</h2>
        <div class="ts-muted">Cart only accepts real products (placeholders are view-only).</div>
      </div>
      <a href="shop.php" class="btn ts-btn-light ts-btn-pill">
        <i class="bi bi-arrow-left me-1"></i> Continue Shopping
      </a>
    </div>

    <?php if (empty($items)): ?>
      <div class="ts-surface p-4">
        <p class="mb-1"><strong>Your cart is empty.</strong></p>
        <p class="ts-muted mb-3">Go to the shop page and add real products from the database.</p>
        <a href="shop.php" class="btn ts-btn-primary ts-btn-pill">Go to Shop</a>
      </div>
    <?php else: ?>
      <div class="row g-4">
        <div class="col-lg-8">
          <div class="ts-surface">
            <div class="ts-surface-body p-0">
              <div class="table-responsive">
                <table class="ts-table">
                  <thead>
                    <tr>
                      <th style="min-width: 260px;">Product</th>
                      <th>Price</th>
                      <th>Qty</th>
                      <th>Subtotal</th>
                      <th></th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($items as $item): ?>
                      <?php
                        $p = $item['product'];
                        $img = $p->images[0] ?? 'https://placehold.co/120x120/E49BFF/850F8D?text=Item';
                        $name = (string)($p->name ?? 'Item');
                        $price = (float)($p->price ?? 0);
                      ?>
                      <tr>
                        <td>
                          <div class="d-flex align-items-center gap-3">
                            <img class="ts-mini-thumb" src="<?= htmlspecialchars($img) ?>" alt="<?= htmlspecialchars($name) ?>">
                            <div>
                              <a href="product.php?id=<?= urlencode($item['pid']) ?>" style="text-decoration:none; color: var(--ts-dark); font-weight:800;">
                                <?= htmlspecialchars($name) ?>
                              </a>
                              <?php if (!empty($p->category ?? '')): ?>
                                <div class="ts-muted" style="font-size:0.82rem;"><?= htmlspecialchars((string)$p->category) ?></div>
                              <?php endif; ?>
                            </div>
                          </div>
                        </td>
                        <td class="ts-price">₱<?= number_format($price, 2) ?></td>
                        <td style="font-weight:900;"><?= (int)$item['quantity'] ?></td>
                        <td class="ts-price">₱<?= number_format((float)$item['subtotal'], 2) ?></td>
                        <td class="text-end">
                          <a class="btn ts-btn-light ts-btn-pill btn-sm"
                             href="cart.php?remove=<?= urlencode($item['pid']) ?>"
                             onclick="return confirm('Remove this item from cart?')">
                            <i class="bi bi-x-lg"></i>
                          </a>
                        </td>
                      </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>

        <div class="col-lg-4">
          <div class="ts-surface">
            <div class="ts-surface-body">
              <h3 class="ts-section-title" style="font-size:1.3rem;">Summary</h3>

              <div class="ts-kpi mt-3">
                <span class="ts-muted">Items</span>
                <strong><?= count($items) ?></strong>
              </div>
              <div class="ts-kpi">
                <span class="ts-muted">Total</span>
                <strong class="ts-price">₱<?= number_format($grandTotal, 2) ?></strong>
              </div>

              <a href="checkout.php" class="btn ts-btn-primary ts-btn-pill w-100 mt-3">
                Proceed to Checkout <i class="bi bi-arrow-right ms-1"></i>
              </a>

              <div class="ts-muted mt-3" style="font-size:0.85rem; line-height:1.6;">
                Tip: session cart resets if the browser session is cleared.
              </div>
            </div>
          </div>
        </div>

      </div>
    <?php endif; ?>

  </div>
</section>

<?php require 'includes/footer.php'; ?>
<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require 'includes/header.php';
require 'includes/db_mysql.php';
require 'includes/db_mongo.php';

$cart = $_SESSION['cart'] ?? [];
if (empty($cart)) {
  ?>
  <section class="ts-page">
    <div class="container">
      <div class="ts-surface p-4">
        <h2 class="ts-section-title mb-2">Cart is empty</h2>
        <p class="ts-muted mb-3">Add items to your cart before checking out.</p>
        <a href="index.php#collection" class="btn ts-btn-primary ts-btn-pill">Shop Now</a>
      </div>
    </div>
  </section>
  <?php
  require 'includes/footer.php';
  exit;
}

// Load products from MongoDB
$ids = [];
foreach (array_keys($cart) as $pid) {
  try { $ids[] = new MongoDB\BSON\ObjectId($pid); } catch (Exception $e) {}
}

$cursor = $productsCollection->find(['_id' => ['$in' => $ids]]);
$products = [];
foreach ($cursor as $p) $products[(string)$p->_id] = $p;

// Build order lines
$orderTotal = 0;
$orderLines = [];
$previewLines = [];

foreach ($cart as $pid => $qty) {
  if (!isset($products[$pid])) continue;
  $qty = max(1, (int)$qty);

  $price = (float)($products[$pid]->price ?? 0);
  $lineTotal = $price * $qty;
  $orderTotal += $lineTotal;

  $orderLines[] = ['product_id' => $pid, 'quantity' => $qty, 'price' => $price];
  $previewLines[] = [
    'pid' => $pid,
    'name' => (string)($products[$pid]->name ?? 'Item'),
    'img' => $products[$pid]->images[0] ?? 'https://placehold.co/120x120/E49BFF/850F8D?text=Item',
    'qty' => $qty,
    'price' => $price,
    'total' => $lineTotal,
  ];
}

if (empty($orderLines)) {
  ?>
  <section class="ts-page">
    <div class="container">
      <div class="ts-surface p-4">
        <h2 class="ts-section-title mb-2">No valid items</h2>
        <p class="ts-muted mb-3">Some products may no longer exist.</p>
        <a href="cart.php" class="btn ts-btn-primary ts-btn-pill">Back to Cart</a>
      </div>
    </div>
  </section>
  <?php
  require 'includes/footer.php';
  exit;
}

$checkoutError = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['place_order'])) {
  $guestName = trim($_POST['guest_name'] ?? '');
  $guestPhone = trim($_POST['guest_phone'] ?? '');
  $guestAddress = trim($_POST['guest_address'] ?? '');

  if ($guestName === '' || $guestPhone === '' || $guestAddress === '') {
    $checkoutError = 'Please fill in name, phone, and address.';
  } else {
    try {
      $pdo->beginTransaction();

      // NOTE: requires orders table columns: guest_name, guest_phone, guest_address, total
      $orderStmt = $pdo->prepare("INSERT INTO orders (guest_name, guest_phone, guest_address, total) VALUES (?, ?, ?, ?)");
      $orderStmt->execute([$guestName, $guestPhone, $guestAddress, $orderTotal]);
      $orderId = $pdo->lastInsertId();

      $itemStmt = $pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
      foreach ($orderLines as $line) {
        $itemStmt->execute([$orderId, $line['product_id'], $line['quantity'], $line['price']]);
      }

      // Clear cart
      $_SESSION['cart'] = [];

      $pdo->commit();

      // simple success redirect
      header("Location: order_history.php?order_id=" . urlencode($orderId));
      exit;
    } catch (Exception $e) {
      $pdo->rollBack();
      $checkoutError = "Checkout failed: " . $e->getMessage();
    }
  }
}
?>

<section class="ts-page">
  <div class="container">
    <div class="d-flex align-items-end justify-content-between flex-wrap gap-2 mb-3">
      <div>
        <h2 class="ts-section-title mb-1">Checkout</h2>
        <div class="ts-muted">Guest checkout — confirm details.</div>
      </div>
      <a href="cart.php" class="btn ts-btn-light ts-btn-pill">
        <i class="bi bi-arrow-left me-1"></i> Back to Cart
      </a>
    </div>

    <?php if ($checkoutError): ?>
      <div class="ts-alert mb-3" style="border-color: rgba(199,56,189,0.35);">
        <strong><?= htmlspecialchars($checkoutError) ?></strong>
      </div>
    <?php endif; ?>

    <div class="row g-4">
      <div class="col-lg-7">
        <div class="ts-surface">
          <div class="ts-surface-body">
            <h3 class="ts-section-title" style="font-size:1.3rem;">Delivery Details</h3>

            <form method="POST" class="mt-3">
              <div class="mb-3">
                <label class="form-label" style="font-weight:800;">Full Name</label>
                <input class="form-control ts-field" type="text" name="guest_name" required>
              </div>
              <div class="mb-3">
                <label class="form-label" style="font-weight:800;">Phone Number</label>
                <input class="form-control ts-field" type="text" name="guest_phone" required>
              </div>
              <div class="mb-3">
                <label class="form-label" style="font-weight:800;">Address</label>
                <textarea class="form-control ts-field" name="guest_address" rows="4" required></textarea>
              </div>

              <button class="btn ts-btn-primary ts-btn-pill w-100" type="submit" name="place_order">
                Place Order <i class="bi bi-check2-circle ms-1"></i>
              </button>
            </form>

            <div class="ts-muted mt-3" style="font-size:0.85rem; line-height:1.6;">
              This is a simplified checkout (no online payment).
            </div>
          </div>
        </div>
      </div>

      <div class="col-lg-5">
        <div class="ts-surface">
          <div class="ts-surface-body">
            <h3 class="ts-section-title" style="font-size:1.3rem;">Order Summary</h3>

            <div class="mt-3">
              <?php foreach ($previewLines as $line): ?>
                <div class="d-flex align-items-center justify-content-between gap-3 py-2" style="border-bottom:1px solid rgba(133,15,141,0.10);">
                  <div class="d-flex align-items-center gap-2">
                    <img class="ts-mini-thumb" src="<?= htmlspecialchars($line['img']) ?>" alt="<?= htmlspecialchars($line['name']) ?>">
                    <div>
                      <div style="font-weight:900;"><?= htmlspecialchars($line['name']) ?></div>
                      <div class="ts-muted" style="font-size:0.82rem;">Qty: <?= (int)$line['qty'] ?></div>
                    </div>
                  </div>
                  <div class="ts-price">₱<?= number_format($line['total'], 2) ?></div>
                </div>
              <?php endforeach; ?>
            </div>

            <div class="ts-kpi mt-3">
              <span class="ts-muted">Total</span>
              <strong class="ts-price">₱<?= number_format($orderTotal, 2) ?></strong>
            </div>

            <div class="ts-muted mt-3" style="font-size:0.85rem; line-height:1.6;">
              Polyglot: Products (MongoDB) + Orders (MySQL).
            </div>
          </div>
        </div>
      </div>

    </div>
  </div>
</section>

<?php require 'includes/footer.php'; ?>
<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require 'includes/db_mongo.php';

function build_placeholders_for_category(string $cat, int $count = 20): array {
  $out = [];
  for ($i=1; $i<=$count; $i++) {
    $out[] = (object)[
      '_id' => "ph-$i",
      'name' => ucfirst($cat) . " Placeholder $i",
      'category' => $cat,
      'description' => "Category placeholder for $cat.",
      'price' => 399 + ($i * 29),
      'images' => ["https://placehold.co/600x760/850F8D/F8F9D7?text=" . urlencode(ucfirst($cat)."+$i")]
    ];
  }
  return $out;
}

$allowed = ['clothing','footwear','bags','accessories'];
$c = $_GET['c'] ?? '';
if (!in_array($c, $allowed, true)) {
  header('Location: shop.php');
  exit;
}

$products = iterator_to_array($productsCollection->find(['category' => $c]));
if (empty($products)) $products = build_placeholders_for_category($c, 20);

include 'includes/header.php';
?>

<section class="ts-page">
  <div class="container">
    <div class="ts-page-header">
      <div class="ts-page-kicker">Category</div>
      <h1 class="ts-section-title"><?= htmlspecialchars(ucfirst($c)) ?></h1>
      <p class="ts-page-lead">Explore <?= htmlspecialchars($c) ?> picks curated for Threadspace.</p>
    </div>

    <div class="ts-pills mb-4">
      <a class="ts-pill <?= $c==='clothing' ? 'active' : '' ?>" href="category.php?c=clothing">Clothing</a>
      <a class="ts-pill <?= $c==='footwear' ? 'active' : '' ?>" href="category.php?c=footwear">Footwear</a>
      <a class="ts-pill <?= $c==='bags' ? 'active' : '' ?>" href="category.php?c=bags">Bags</a>
      <a class="ts-pill <?= $c==='accessories' ? 'active' : '' ?>" href="category.php?c=accessories">Accessories</a>
      <a class="ts-pill" href="sale.php">Sale</a>
      <a class="ts-pill" href="shop.php">All Products</a>
    </div>

    <div class="row g-4">
      <?php foreach ($products as $product): ?>
        <?php
          $idRaw = (string)($product->_id ?? '');
          $isPlaceholder = str_starts_with($idRaw, 'ph-');
          $productUrl = $isPlaceholder ? ("product.php?ph=" . urlencode(substr($idRaw, 3))) : ("product.php?id=" . urlencode($idRaw));

          $name = (string)($product->name ?? 'Item');
          $desc = (string)($product->description ?? '');
          $price = (float)($product->price ?? 0);
          $img = ($product->images[0] ?? 'https://placehold.co/300x380/E49BFF/850F8D?text=Product');
        ?>
        <div class="col-6 col-md-4 col-lg-3">
          <div class="ts-product-card">
            <div class="ts-product-img-wrap">
              <a href="<?= htmlspecialchars($productUrl) ?>">
                <img src="<?= htmlspecialchars($img) ?>" alt="<?= htmlspecialchars($name) ?>" class="ts-product-img"/>
              </a>

              <div class="ts-product-actions">
                <?php if (!$isPlaceholder): ?>
                  <form method="POST" action="cart.php" class="d-inline">
                    <input type="hidden" name="product_id" value="<?= htmlspecialchars($idRaw) ?>">
                    <input type="hidden" name="quantity" value="1">
                    <button class="ts-action-btn" type="submit" name="add_to_cart" title="Add to Cart" aria-label="Add to Cart">
                      <i class="bi bi-bag-plus"></i>
                    </button>
                  </form>
                <?php endif; ?>

                <a class="ts-action-btn" href="<?= htmlspecialchars($productUrl) ?>" title="View Product" aria-label="View Product">
                  <i class="bi bi-eye"></i>
                </a>
              </div>
            </div>

            <div class="ts-product-info">
              <p class="ts-product-name"><?= htmlspecialchars($name) ?></p>
              <p class="ts-product-category"><?= htmlspecialchars($c) ?></p>
              <p class="ts-product-price">₱<?= number_format($price, 2) ?></p>
              <?php if ($desc): ?><p class="ts-product-desc"><?= htmlspecialchars(mb_strimwidth($desc, 0, 80, '...')) ?></p><?php endif; ?>
              <?php if ($isPlaceholder): ?>
                <div class="ts-muted" style="font-size:0.8rem; margin-top:8px;">Placeholder (view-only)</div>
              <?php endif; ?>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>

  </div>
</section>

<?php require 'includes/footer.php'; ?>
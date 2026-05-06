<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require 'includes/db_mongo.php';

function build_placeholders(int $count = 20): array {
  $out = [];
  $cats = ['clothing','footwear','bags','accessories'];
  for ($i=1; $i<=$count; $i++) {
    $cat = $cats[($i-1) % count($cats)];
    $out[] = (object)[
      '_id' => "ph-$i",
      'name' => "Placeholder Item $i",
      'category' => $cat,
      'description' => "Limited-time deal placeholder for sale page.",
      'price' => 499 + ($i * 37),
      'images' => ["https://placehold.co/600x760/C738BD/F8F9D7?text=Sale+$i"]
    ];
  }
  return $out;
}

$products = iterator_to_array($productsCollection->find([]));
if (empty($products)) $products = build_placeholders(20);

// choose 12 items as sale picks
$saleItems = array_slice($products, 0, 12);

include 'includes/header.php';
?>

<section class="ts-page">
  <div class="container">
    <div class="ts-page-header">
      <div class="ts-page-kicker">Limited Time</div>
      <h1 class="ts-section-title">Threadspace Sale</h1>
      <p class="ts-page-lead">
        Trending picks and discounted styles — rotating deals for a limited time.
      </p>
    </div>

    <div class="ts-surface mb-4">
      <div class="ts-surface-body d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div class="ts-chip"><i class="bi bi-lightning-charge-fill"></i> Deals update regularly</div>
        <a class="btn ts-btn-light ts-btn-pill" href="shop.php">
          Browse all products <i class="bi bi-arrow-right ms-1"></i>
        </a>
      </div>
    </div>

    <div class="row g-4">
      <?php foreach ($saleItems as $idx => $product): ?>
        <?php
          $idRaw = (string)($product->_id ?? '');
          $isPlaceholder = str_starts_with($idRaw, 'ph-');
          $productUrl = $isPlaceholder ? ("product.php?ph=" . urlencode(substr($idRaw, 3))) : ("product.php?id=" . urlencode($idRaw));

          $name = (string)($product->name ?? 'Deal Item');
          $category = (string)($product->category ?? '');
          $price = (float)($product->price ?? 0);
          $img = ($product->images[0] ?? 'https://placehold.co/300x380/C738BD/F8F9D7?text=Sale');

          // simulate discount
          $discountPct = 15 + (($idx % 4) * 10); // 15,25,35,45
          $oldPrice = $price > 0 ? ($price / (1 - ($discountPct/100))) : 0;
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
              <?php if ($category): ?><p class="ts-product-category"><?= htmlspecialchars($category) ?></p><?php endif; ?>
              <p class="ts-product-price">
                ₱<?= number_format($price, 2) ?>
                <?php if ($oldPrice > 0): ?>
                  <s class="ts-product-old-price">₱<?= number_format($oldPrice, 2) ?></s>
                <?php endif; ?>
              </p>
              <div class="ts-muted" style="font-size:0.82rem; margin-top:6px; font-weight:800; color: var(--ts-secondary);">
                <?= (int)$discountPct ?>% OFF · Limited Time
              </div>
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
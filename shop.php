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
      'description' => "A stylish Threadspace placeholder item for UI testing and layout consistency.",
      'price' => 499 + ($i * 37),
      'images' => ["https://placehold.co/600x760/E49BFF/850F8D?text=Item+$i"]
    ];
  }
  return $out;
}

$q = $_GET['q'] ?? '';
$c = $_GET['c'] ?? ''; // optional category filter

$filter = [];
$options = [];

if ($q !== '') {
  $filter['$text'] = ['$search' => $q];
  $options['projection'] = ['score' => ['$meta' => 'textScore']];
  $options['sort'] = ['score' => ['$meta' => 'textScore']];
}
if ($c !== '') {
  $filter['category'] = $c;
}

$cursor = $productsCollection->find($filter, $options);
$products = iterator_to_array($cursor);
if (empty($products)) $products = build_placeholders(20);

include 'includes/header.php';
?>

<section class="ts-page">
  <div class="container">
    <div class="ts-page-header">
      <div class="ts-page-kicker">Threadspace Shop</div>
      <h1 class="ts-section-title">All Products</h1>
      <p class="ts-page-lead">
        Browse everything in one place. Use search or categories to find what fits your vibe.
      </p>
    </div>

    <div class="ts-surface mb-4">
      <div class="ts-surface-body">
        <form class="row g-2 align-items-center" method="GET" action="shop.php">
          <div class="col-12 col-lg-6">
            <input class="form-control ts-field" type="text" name="q" value="<?= htmlspecialchars($q) ?>" placeholder="Search products (name, description, etc.)">
          </div>
          <div class="col-12 col-lg-4">
            <select class="form-select ts-field" name="c">
              <option value="">All categories</option>
              <?php foreach (['clothing','footwear','bags','accessories'] as $cat): ?>
                <option value="<?= $cat ?>" <?= $c===$cat ? 'selected' : '' ?>><?= ucfirst($cat) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-12 col-lg-2">
            <button class="btn ts-btn-primary ts-btn-pill w-100" type="submit">
              Search <i class="bi bi-search ms-1"></i>
            </button>
          </div>
        </form>

        <div class="ts-pills mt-3">
          <a class="ts-pill <?= $c==='' ? 'active' : '' ?>" href="shop.php<?= $q!=='' ? ('?q='.urlencode($q)) : '' ?>">All</a>
          <a class="ts-pill <?= $c==='clothing' ? 'active' : '' ?>" href="shop.php?c=clothing<?= $q!=='' ? ('&q='.urlencode($q)) : '' ?>">Clothing</a>
          <a class="ts-pill <?= $c==='footwear' ? 'active' : '' ?>" href="shop.php?c=footwear<?= $q!=='' ? ('&q='.urlencode($q)) : '' ?>">Footwear</a>
          <a class="ts-pill <?= $c==='bags' ? 'active' : '' ?>" href="shop.php?c=bags<?= $q!=='' ? ('&q='.urlencode($q)) : '' ?>">Bags</a>
          <a class="ts-pill <?= $c==='accessories' ? 'active' : '' ?>" href="shop.php?c=accessories<?= $q!=='' ? ('&q='.urlencode($q)) : '' ?>">Accessories</a>
          <a class="ts-pill" href="sale.php">Sale</a>
        </div>

        <?php if ($q !== ''): ?>
          <div class="ts-muted mt-3">Results for <strong><?= htmlspecialchars($q) ?></strong> — <?= count($products) ?> item(s)</div>
        <?php endif; ?>
      </div>
    </div>

    <div class="row g-4">
      <?php foreach ($products as $product): ?>
        <?php
          $idRaw = (string)($product->_id ?? '');
          $isPlaceholder = str_starts_with($idRaw, 'ph-');
          $productUrl = $isPlaceholder ? ("product.php?ph=" . urlencode(substr($idRaw, 3))) : ("product.php?id=" . urlencode($idRaw));

          $name = (string)($product->name ?? 'Unnamed Product');
          $category = (string)($product->category ?? '');
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
              <?php if ($category): ?><p class="ts-product-category"><?= htmlspecialchars($category) ?></p><?php endif; ?>
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
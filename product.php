<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require 'includes/db_mongo.php';

$id = $_GET['id'] ?? '';
$ph = $_GET['ph'] ?? '';

$product = null;

if ($id) {
  try {
    $product = $productsCollection->findOne(['_id' => new MongoDB\BSON\ObjectId($id)]);
  } catch (Exception $e) {
    $product = null;
  }
} elseif ($ph !== '') {
  // Placeholder product support
  $n = (int)$ph;
  if ($n < 1) $n = 1;
  $cats = ['clothing','footwear','bags','accessories'];
  $cat = $cats[($n-1) % count($cats)];
  $product = (object)[
    '_id' => "ph-$n",
    'name' => "Placeholder Item $n",
    'category' => $cat,
    'description' => "This is a placeholder product page used while product images and seed data are still being prepared.",
    'price' => 499 + ($n * 37),
    'images' => [
      "https://placehold.co/900x900/E49BFF/850F8D?text=Item+$n",
      "https://placehold.co/600x600/C738BD/F8F9D7?text=Alt+View",
      "https://placehold.co/600x600/850F8D/F8F9D7?text=Details",
      "https://placehold.co/600x600/F8F9D7/850F8D?text=Texture"
    ],
    'attributes' => (object)[
      'material' => 'Cotton blend (demo)',
      'sizes' => ['S','M','L','XL'],
      'color' => 'Threadspace Purple (demo)'
    ]
  ];
}

if (!$product) {
  http_response_code(404);
  require 'includes/header.php';
  ?>
  <section class="ts-page">
    <div class="container">
      <div class="ts-surface p-4">
        <h2 class="ts-section-title mb-2">Product not found</h2>
        <p class="ts-muted mb-3">The link may be invalid.</p>
        <a href="shop.php" class="btn ts-btn-primary ts-btn-pill">Back to Shop</a>
      </div>
    </div>
  </section>
  <?php
  require 'includes/footer.php';
  exit;
}

require 'includes/header.php';

$name = (string)($product->name ?? 'Unnamed Product');
$category = (string)($product->category ?? '');
$desc = (string)($product->description ?? '');
$price = (float)($product->price ?? 0);
$images = $product->images ?? [];
$mainImg = $images[0] ?? 'https://placehold.co/900x900/E49BFF/850F8D?text=Product';
$attrs = $product->attributes ?? new stdClass();
?>

<section class="ts-page">
  <div class="container">
    <div class="mb-3">
      <a class="ts-breadcrumb-link" href="shop.php"><i class="bi bi-arrow-left"></i> Back to shop</a>
    </div>

    <div class="row g-4">
      <div class="col-lg-6">
        <div class="ts-gallery-grid">
          <div class="ts-gallery-main">
            <img src="<?= htmlspecialchars($mainImg) ?>" alt="<?= htmlspecialchars($name) ?>">
          </div>

          <div class="ts-gallery-thumbs">
            <?php
              $thumbs = array_slice($images ?: [$mainImg], 0, 4);
              foreach ($thumbs as $img):
            ?>
              <div class="ts-gallery-thumb">
                <img src="<?= htmlspecialchars($img) ?>" alt="<?= htmlspecialchars($name) ?>">
              </div>
            <?php endforeach; ?>
          </div>
        </div>
      </div>

      <div class="col-lg-6">
        <div class="ts-surface">
          <div class="ts-surface-body">
            <?php if ($category): ?>
              <div class="mb-2"><span class="ts-chip"><i class="bi bi-tag"></i> <?= htmlspecialchars($category) ?></span></div>
            <?php endif; ?>

            <h1 class="ts-section-title mb-2"><?= htmlspecialchars($name) ?></h1>

            <div class="d-flex align-items-center gap-3 flex-wrap mb-3">
              <div class="ts-price" style="font-size:1.35rem;">₱<?= number_format($price, 2) ?></div>
              <div class="ts-muted" style="font-size:0.9rem;">Guest checkout enabled</div>
            </div>

            <?php if ($desc): ?>
              <p class="ts-muted mb-4" style="line-height:1.8;"><?= nl2br(htmlspecialchars($desc)) ?></p>
            <?php endif; ?>

            <form method="POST" action="cart.php" class="row g-2 align-items-center">
              <input type="hidden" name="product_id" value="<?= htmlspecialchars((string)$product->_id) ?>">
              <div class="col-5 col-sm-4 col-md-3">
                <input class="form-control ts-field" type="number" name="quantity" value="1" min="1">
              </div>
              <div class="col-7 col-sm-8 col-md-9">
                <button class="btn ts-btn-primary ts-btn-pill w-100" type="submit" name="add_to_cart">
                  <i class="bi bi-bag-plus me-1"></i> Add to Cart
                </button>
              </div>
            </form>

          </div>
        </div>

        <div class="ts-surface mt-4">
          <div class="ts-surface-body">
            <h3 class="ts-section-title" style="font-size:1.3rem;">Specifications</h3>
            <div class="mt-3">
              <?php
                $attrArray = is_object($attrs) ? get_object_vars($attrs) : (is_array($attrs) ? $attrs : []);
              ?>
              <?php if (empty($attrArray)): ?>
                <p class="ts-muted mb-0">No specifications provided.</p>
              <?php else: ?>
                <div class="row g-3">
                  <?php foreach ($attrArray as $key => $value): ?>
                    <div class="col-12 col-md-6">
                      <div class="ts-alert" style="background: rgba(255,255,255,0.65);">
                        <div style="font-size:0.78rem; letter-spacing:1px; text-transform:uppercase; color: var(--ts-gray); font-weight:800;">
                          <?= htmlspecialchars(ucwords(str_replace('_', ' ', (string)$key))) ?>
                        </div>
                        <div style="margin-top:6px; font-weight:700;">
                          <?php
                            if (is_bool($value)) echo $value ? 'Yes' : 'No';
                            elseif (is_array($value)) echo htmlspecialchars(implode(', ', array_map('strval', $value)));
                            else echo htmlspecialchars((string)$value);
                          ?>
                        </div>
                      </div>
                    </div>
                  <?php endforeach; ?>
                </div>
              <?php endif; ?>
            </div>
          </div>
        </div>

      </div>
    </div>
  </div>
</section>

<?php require 'includes/footer.php'; ?>
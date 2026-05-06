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

if (!empty($q)) {
  $filter = ['$text' => ['$search' => $q]];
  $options = [
    'projection' => ['score' => ['$meta' => 'textScore']],
    'sort' => ['score' => ['$meta' => 'textScore']]
  ];
  $cursor = $productsCollection->find($filter, $options);
} else {
  $cursor = $productsCollection->find([]);
}
$products = iterator_to_array($cursor);

if (empty($products)) $products = build_placeholders(20);

$collectionPreview = array_slice($products, 0, 8);
$trends = array_slice($products, 0, 5);

include 'includes/header.php';
?>

<section class="ts-hero">
  <div class="ts-hero-bg-blob"></div>
  <div class="container h-100">
    <div class="row h-100 align-items-center">
      <div class="col-lg-6 ts-hero-text">
        <p class="ts-hero-eyebrow">New Collection · 2025</p>
        <h1 class="ts-hero-title">Wear Your<br/><em>Thread</em>,<br/>Own Your Space.</h1>
        <p class="ts-hero-sub">
          Curated fashion for the bold, the soft, and the beautifully in-between.
          Clothing, footwear, bags & accessories — all in one place.
        </p>
        <div class="d-flex gap-3 flex-wrap mt-4">
          <a href="shop.php" class="btn ts-btn-primary">Shop Now</a>
          <a href="#collection" class="btn ts-btn-ghost">Explore Collection</a>
        </div>

        <?php if (!empty($q)): ?>
          <div class="ts-search-result-meta mt-4">
            Showing results for <strong><?= htmlspecialchars($q) ?></strong>
            <span class="ts-muted">(<?= count($products) ?> found)</span>
          </div>
        <?php endif; ?>
      </div>

      <div class="col-lg-6 d-flex justify-content-center ts-hero-img-col">
        <div class="ts-hero-img-wrap">
          <div class="ts-hero-img-card ts-hero-img-card--main">
            <img src="https://placehold.co/420x540/E49BFF/850F8D?text=Featured+Look" alt="Featured Look" class="ts-hero-img"/>
            <div class="ts-hero-tag">Trending Now</div>
          </div>
          <div class="ts-hero-img-card ts-hero-img-card--side">
            <img src="https://placehold.co/200x260/C738BD/F8F9D7?text=New+In" alt="New In" class="ts-hero-img"/>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="ts-marquee-wrap">
    <div class="ts-marquee-track">
      <span>Clothing</span><span class="ts-marquee-dot">✦</span>
      <span>Footwear</span><span class="ts-marquee-dot">✦</span>
      <span>Bags</span><span class="ts-marquee-dot">✦</span>
      <span>Accessories</span><span class="ts-marquee-dot">✦</span>
      <span>New Arrivals</span><span class="ts-marquee-dot">✦</span>
      <span>Sale</span><span class="ts-marquee-dot">✦</span>
      <span>Clothing</span><span class="ts-marquee-dot">✦</span>
      <span>Footwear</span><span class="ts-marquee-dot">✦</span>
      <span>Bags</span><span class="ts-marquee-dot">✦</span>
      <span>Accessories</span><span class="ts-marquee-dot">✦</span>
      <span>New Arrivals</span><span class="ts-marquee-dot">✦</span>
      <span>Sale</span><span class="ts-marquee-dot">✦</span>
    </div>
  </div>
</section>

<section class="ts-trends" id="trends">
  <div class="container">
    <div class="ts-section-header mb-3">
      <h2 class="ts-section-title">Trends & Limited-Time Deals</h2>
      <a href="sale.php" class="ts-see-all">Go to Sale <i class="bi bi-arrow-right"></i></a>
    </div>

    <div id="tsTrendsCarousel" class="carousel slide" data-bs-ride="carousel" data-bs-interval="3200">
      <div class="carousel-inner">
        <?php foreach ($trends as $i => $p): ?>
          <?php
            $pidRaw = (string)($p->_id ?? '');
            $isPlaceholder = str_starts_with($pidRaw, 'ph-');
            $img = $p->images[0] ?? 'https://placehold.co/1200x675/E49BFF/850F8D?text=Trend';
            $name = (string)($p->name ?? 'Trending Item');
          ?>
          <div class="carousel-item <?= $i === 0 ? 'active' : '' ?>">
            <a href="sale.php" style="text-decoration:none;">
              <div class="ts-trends-card">
                <img src="<?= htmlspecialchars($img) ?>" class="ts-trends-img" alt="<?= htmlspecialchars($name) ?>">
                <div class="ts-trends-meta">
                  <div class="d-flex align-items-center justify-content-between gap-3 flex-wrap">
                    <div>
                      <div class="ts-trends-title"><?= htmlspecialchars($name) ?></div>
                      <div class="ts-trends-sub">Tap to view discounted products on the Sale page.</div>
                    </div>
                    <span class="ts-trends-badge"><i class="bi bi-lightning-charge-fill"></i> Limited Time</span>
                  </div>
                  <?php if ($isPlaceholder): ?>
                    <div class="ts-muted mt-2" style="font-size:0.85rem;">(Demo carousel using placeholders)</div>
                  <?php endif; ?>
                </div>
              </div>
            </a>
          </div>
        <?php endforeach; ?>
      </div>

      <button class="carousel-control-prev" type="button" data-bs-target="#tsTrendsCarousel" data-bs-slide="prev">
        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
        <span class="visually-hidden">Previous</span>
      </button>
      <button class="carousel-control-next" type="button" data-bs-target="#tsTrendsCarousel" data-bs-slide="next">
        <span class="carousel-control-next-icon" aria-hidden="true"></span>
        <span class="visually-hidden">Next</span>
      </button>
    </div>
  </div>
</section>

<section class="ts-categories py-5">
  <div class="container">
    <div class="ts-section-header mb-4">
      <h2 class="ts-section-title">Browse by Category</h2>
      <a href="shop.php" class="ts-see-all">See all <i class="bi bi-arrow-right"></i></a>
    </div>

    <div class="row g-3">
      <div class="col-6 col-md-3">
        <a href="category.php?c=clothing" class="ts-cat-card">
          <img src="https://placehold.co/300x380/E49BFF/850F8D?text=Clothing" class="ts-cat-img" alt="Clothing"/>
          <div class="ts-cat-overlay"><span>Clothing</span></div>
        </a>
      </div>
      <div class="col-6 col-md-3">
        <a href="category.php?c=footwear" class="ts-cat-card">
          <img src="https://placehold.co/300x380/C738BD/F8F9D7?text=Footwear" class="ts-cat-img" alt="Footwear"/>
          <div class="ts-cat-overlay"><span>Footwear</span></div>
        </a>
      </div>
      <div class="col-6 col-md-3">
        <a href="category.php?c=bags" class="ts-cat-card">
          <img src="https://placehold.co/300x380/850F8D/F8F9D7?text=Bags" class="ts-cat-img" alt="Bags"/>
          <div class="ts-cat-overlay"><span>Bags</span></div>
        </a>
      </div>
      <div class="col-6 col-md-3">
        <a href="category.php?c=accessories" class="ts-cat-card">
          <img src="https://placehold.co/300x380/F8F9D7/850F8D?text=Accessories" class="ts-cat-img" alt="Accessories"/>
          <div class="ts-cat-overlay"><span>Accessories</span></div>
        </a>
      </div>
    </div>
  </div>
</section>

<section class="ts-collection py-5" id="collection">
  <div class="container">
    <div class="ts-section-header mb-3">
      <h2 class="ts-section-title">Our Collection</h2>
      <a href="shop.php" class="ts-see-all">See all <i class="bi bi-arrow-right"></i></a>
    </div>

    <div class="row g-4">
      <?php foreach ($collectionPreview as $product): ?>
        <?php
          $idRaw = (string)($product->_id ?? '');
          $isPlaceholder = str_starts_with($idRaw, 'ph-');

          $productUrl = $isPlaceholder
            ? ("product.php?ph=" . urlencode(substr($idRaw, 3)))
            : ("product.php?id=" . urlencode($idRaw));

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

<section class="ts-promo-banner my-5">
  <div class="container">
    <div class="ts-promo-inner">
      <div class="ts-promo-blob"></div>
      <div class="row align-items-center">
        <div class="col-md-7">
          <p class="ts-promo-eyebrow">Limited Time Offer</p>
          <h2 class="ts-promo-title">Up to 40% Off<br/>Selected Styles</h2>
          <p class="ts-promo-sub">Don’t miss out on our hottest deals. Explore what’s trending now.</p>
          <a href="sale.php" class="btn ts-btn-primary mt-3">Shop the Sale</a>
        </div>
        <div class="col-md-5 text-center mt-4 mt-md-0">
          <img src="https://placehold.co/320x320/F8F9D7/850F8D?text=Sale+Looks" alt="Sale" class="ts-promo-img"/>
        </div>
      </div>
    </div>
  </div>
</section>

<section class="ts-about py-5" id="about">
  <div class="container">
    <div class="row align-items-center g-5">
      <div class="col-md-5">
        <img src="https://placehold.co/460x400/E49BFF/850F8D?text=About+Threadspace" alt="About Threadspace" class="ts-about-img"/>
      </div>
      <div class="col-md-7">
        <p class="ts-hero-eyebrow">Who We Are</p>
        <h2 class="ts-section-title">Fashion That Fits <em>You</em></h2>
        <p class="ts-about-text">
          Threadspace is more than a store — it's a space where personal style meets curated fashion.
          We bring together clothing, footwear, bags, and accessories that help you express who you are, every single day.
        </p>
        <div class="ts-about-contacts mt-4">
          <div class="ts-contact-item"><i class="bi bi-telephone-fill"></i><span>[Phone Number]</span></div>
          <div class="ts-contact-item"><i class="bi bi-envelope-fill"></i><span>[Email Address]</span></div>
        </div>
      </div>
    </div>
  </div>
</section>

<?php require 'includes/footer.php'; ?>
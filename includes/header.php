<?php
if (session_status() === PHP_SESSION_NONE) session_start();

$q = $_GET['q'] ?? '';

// guest cart count
$cartCount = 0;
if (!empty($_SESSION['cart']) && is_array($_SESSION['cart'])) {
  foreach ($_SESSION['cart'] as $qty) $cartCount += (int)$qty;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Threadspace</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"/>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet"/>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,700;1,400&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet"/>
  <link href="css/styles.css" rel="stylesheet"/>
</head>
<body>

<nav class="navbar navbar-expand-lg ts-navbar sticky-top">
  <div class="container">

    <div class="collapse navbar-collapse" id="navLeft">
      <ul class="navbar-nav me-auto gap-2 align-items-lg-center">
        <li class="nav-item"><a class="nav-link ts-nav-link" href="index.php">Home</a></li>
        <li class="nav-item"><a class="nav-link ts-nav-link" href="shop.php">Shop</a></li>
        <li class="nav-item"><a class="nav-link ts-nav-link" href="sale.php">Sale</a></li>

        <li class="nav-item dropdown">
          <a class="nav-link ts-nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            Categories
          </a>
          <ul class="dropdown-menu">
            <li><a class="dropdown-item" href="category.php?c=clothing">Clothing</a></li>
            <li><a class="dropdown-item" href="category.php?c=footwear">Footwear</a></li>
            <li><a class="dropdown-item" href="category.php?c=bags">Bags</a></li>
            <li><a class="dropdown-item" href="category.php?c=accessories">Accessories</a></li>
          </ul>
        </li>

        <li class="nav-item"><a class="nav-link ts-nav-link" href="index.php#about">About</a></li>
      </ul>
    </div>

    <a class="navbar-brand ts-logo mx-auto" href="index.php">
      <span class="ts-logo-text">Thread<em>space</em></span>
    </a>

    <div class="d-flex align-items-center gap-3 ts-nav-right">
      <form class="ts-search-wrap d-none d-lg-flex" action="shop.php" method="GET" role="search">
        <input type="text" class="ts-search-input" name="q" placeholder="Search products..." value="<?= htmlspecialchars($q) ?>"/>
        <button class="ts-search-btn" type="submit" aria-label="Search"><i class="bi bi-search"></i></button>
      </form>

      <a href="cart.php" class="ts-icon-btn ts-cart-btn" title="Cart">
        <i class="bi bi-bag"></i>
        <span class="ts-cart-badge"><?= (int)$cartCount ?></span>
      </a>

      <button class="navbar-toggler border-0 ts-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navLeft" aria-label="Toggle navigation">
        <i class="bi bi-list"></i>
      </button>
    </div>

  </div>

  <div class="container d-lg-none pb-2">
    <form class="ts-search-wrap" action="shop.php" method="GET" role="search">
      <input type="text" class="ts-search-input" name="q" placeholder="Search products..." value="<?= htmlspecialchars($q) ?>"/>
      <button class="ts-search-btn" type="submit" aria-label="Search"><i class="bi bi-search"></i></button>
    </form>
  </div>
</nav>
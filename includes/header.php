<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Shop</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<nav>
    <a href="index.php">Home</a> |
    <a href="cart.php">Cart</a> |
    <?php if (isset($_SESSION['user_id'])): ?>
        <a href="order_history.php">My Orders</a> |
        <span>Welcome, <?= htmlspecialchars($_SESSION['username']) ?></span> |
        <a href="logout.php">Logout</a>
        <?php if ($_SESSION['role'] === 'admin'): ?>
            | <a href="admin.php">Admin Panel</a>
        <?php endif; ?>
    <?php else: ?>
        <a href="login.php">Login</a> |
        <a href="register.php">Register</a>
    <?php endif; ?>
</nav>
<hr>
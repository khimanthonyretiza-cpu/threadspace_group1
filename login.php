<?php
require 'includes/header.php';
require 'includes/db_mysql.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare("SELECT id, username, password_hash FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password_hash'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        header('Location: index.php');
        exit;
    } else {
        $error = 'Invalid username or password.';
    }
}
?>

<section class="ts-page">
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-md-10 col-lg-6">

        <div class="text-center mb-3">
          <div class="ts-logo-text" style="display:inline-block;">Thread<em>space</em></div>
          <div class="ts-muted mt-1">Welcome back — sign in to continue.</div>
        </div>

        <?php if ($error): ?>
          <div class="ts-alert mb-3">
            <strong>Login failed:</strong> <?= htmlspecialchars($error) ?>
          </div>
        <?php endif; ?>

        <div class="ts-surface">
          <div class="ts-surface-body">
            <h2 class="ts-section-title" style="font-size:1.6rem;">Login</h2>

            <form method="POST" class="mt-3">
              <div class="mb-3">
                <label class="form-label" style="font-weight:800; color:var(--ts-dark);">Username</label>
                <input type="text" name="username" class="form-control ts-field" required autocomplete="username">
              </div>

              <div class="mb-3">
                <label class="form-label" style="font-weight:800; color:var(--ts-dark);">Password</label>
                <input type="password" name="password" class="form-control ts-field" required autocomplete="current-password">
              </div>

              <button type="submit" class="btn ts-btn-primary ts-btn-pill w-100">
                Sign In <i class="bi bi-arrow-right ms-1"></i>
              </button>
            </form>

            <div class="text-center mt-3 ts-muted">
              Don’t have an account? <a href="register.php" style="color:var(--ts-primary); font-weight:800; text-decoration:none;">Register</a>
            </div>
          </div>
        </div>

      </div>
    </div>
  </div>
</section>

<?php require 'includes/footer.php'; ?>
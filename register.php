<?php
require 'includes/header.php';
require 'includes/db_mysql.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (strlen($username) < 3 || strlen($password) < 4) {
        $error = 'Username must be 3+ characters and password 4+ characters.';
    } else {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        try {
            $stmt = $pdo->prepare("INSERT INTO users (username, password_hash) VALUES (?, ?)");
            $stmt->execute([$username, $hash]);
            header('Location: login.php');
            exit;
        } catch (PDOException $e) {
            $error = 'Username already taken.';
        }
    }
}
?>

<section class="ts-page">
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-md-10 col-lg-6">

        <div class="text-center mb-3">
          <div class="ts-logo-text" style="display:inline-block;">Thread<em>space</em></div>
          <div class="ts-muted mt-1">Create an account to start shopping.</div>
        </div>

        <?php if ($error): ?>
          <div class="ts-alert mb-3">
            <strong>Registration failed:</strong> <?= htmlspecialchars($error) ?>
          </div>
        <?php endif; ?>

        <div class="ts-surface">
          <div class="ts-surface-body">
            <h2 class="ts-section-title" style="font-size:1.6rem;">Register</h2>

            <form method="POST" class="mt-3">
              <div class="mb-3">
                <label class="form-label" style="font-weight:800; color:var(--ts-dark);">Username</label>
                <input type="text" name="username" class="form-control ts-field" required autocomplete="username">
              </div>

              <div class="mb-3">
                <label class="form-label" style="font-weight:800; color:var(--ts-dark);">Password</label>
                <input type="password" name="password" class="form-control ts-field" required autocomplete="new-password">
                <div class="ts-muted mt-2" style="font-size:0.85rem;">Tip: Use at least 4 characters (project requirement).</div>
              </div>

              <button type="submit" class="btn ts-btn-primary ts-btn-pill w-100">
                Create Account <i class="bi bi-check2-circle ms-1"></i>
              </button>
            </form>

            <div class="text-center mt-3 ts-muted">
              Already have an account? <a href="login.php" style="color:var(--ts-primary); font-weight:800; text-decoration:none;">Login</a>
            </div>
          </div>
        </div>

      </div>
    </div>
  </div>
</section>

<?php require 'includes/footer.php'; ?>
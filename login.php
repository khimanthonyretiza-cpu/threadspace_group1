<?php
require 'includes/header.php';
require 'includes/db_mysql.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT id, username, password_hash, role FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password_hash'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        header('Location: index.php');
        exit;
    } else {
        $error = 'Invalid credentials.';
    }
}
?>

<h2>Login</h2>
<?php if ($error): ?><p style="color:red"><?= htmlspecialchars($error) ?></p><?php endif; ?>
<form method="POST">
    <label>Username: <input type="text" name="username" required></label><br>
    <label>Password: <input type="password" name="password" required></label><br>
    <button type="submit">Login</button>
</form>
<p>Don't have an account? <a href="register.php">Register</a></p>

<?php require 'includes/footer.php'; ?>
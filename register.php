<?php
require 'includes/header.php';
require 'includes/db_mysql.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

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

<h2>Register</h2>
<?php if ($error): ?><p style="color:red"><?= htmlspecialchars($error) ?></p><?php endif; ?>
<form method="POST">
    <label>Username: <input type="text" name="username" required></label><br>
    <label>Password: <input type="password" name="password" required></label><br>
    <button type="submit">Register</button>
</form>
<p>Already have an account? <a href="login.php">Login</a></p>

<?php require 'includes/footer.php'; ?>
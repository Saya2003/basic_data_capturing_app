<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/app/models/Database.php';
require_once __DIR__ . '/app/models/User.php';

session_start();
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    if (!$username || !$password) {
        $error = 'All fields are required.';
    } else {
        $db = new Database();
        $userModel = new User($db);
        $user = $userModel->login($username, $password);
        if ($user) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            header('Location: /basic_data_capturing_app/index.php');
            exit;
        } else {
            $error = 'Invalid username or password.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>
    <h1>Login</h1>
    <?php if (!empty($error)): ?><div class="error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
    <?php if (isset($_GET['registered'])): ?><div class="success">Registration successful. Please log in.</div><?php endif; ?>
    <form method="post" action="">
        <label>Username: <input type="text" name="username" required value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"></label><br>
        <label>Password: <input type="password" name="password" required></label><br>
        <button type="submit">Login</button>
    </form>
    <p>Don't have an account? <a href="/basic_data_capturing_app/register.php">Register here</a>.</p>
</body>
</html>

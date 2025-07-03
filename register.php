<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/app/models/Database.php';
require_once __DIR__ . '/app/models/User.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm'] ?? '';
    $passwordPattern = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^a-zA-Z\d]).{8,}$/';
    if (!$username || !$password || !$confirm) {
        $error = 'All fields are required.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } elseif (!preg_match($passwordPattern, $password)) {
    } else {
        $db = new Database();
        $userModel = new User($db);
        if ($userModel->userExists($username)) {
            $error = 'Username already exists.';
        } elseif ($userModel->register($username, $password)) {
            echo '<script>alert("Registration successful! You will be redirected to the dashboard."); window.location.href = "/basic_data_capturing_app/index.php";</script>';
            exit;
        } else {
            $error = 'Registration failed.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register</title>
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>
    <h1>Register</h1>
    <?php if (!empty($error)): ?><div class="error"><span style="color:red;">&#9888;</span> <?= htmlspecialchars($error) ?></div><?php endif; ?>
    <form method="post" action="" id="registerForm">
        <label>Username: <input type="text" name="username" required value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"></label><br>
        <label>Password: <input type="password" name="password" id="password" required value="<?= htmlspecialchars($_POST['password'] ?? '') ?>" oninput="validatePassword()"></label><br>
        <label>Confirm Password: <input type="password" name="confirm" required value="<?= htmlspecialchars($_POST['confirm'] ?? '') ?>"></label><br>
        <button type="submit">Register</button>
    </form>
    <ul id="password-requirements" style="margin-bottom: 1em; list-style: none; padding-left: 0;">
        <strong>Your password must meet the following criteria:</strong>
        <li id="length"><span class="cross">&#10060;</span> Contain <b>at least 8 characters</b> in total</li>
        <li id="uppercase"><span class="cross">&#10060;</span> Include <b>at least one uppercase letter</b> (e.g., <code>A</code>, <code>B</code>, <code>C</code>, ...)</li>
        <li id="lowercase"><span class="cross">&#10060;</span> Include <b>at least one lowercase letter</b> (e.g., <code>a</code>, <code>b</code>, <code>c</code>, ...)</li>
        <li id="number"><span class="cross">&#10060;</span> Include <b>at least one number</b> (e.g., <code>0–9</code>)</li>
        <li id="special"><span class="cross">&#10060;</span> Include <b>at least one special character</b> (e.g., <code>@</code>, <code>#</code>, <code>$</code>, <code>%</code>, <code>!</code>, <code>&amp;</code>, <code>*</code>, etc.)</li>
    </ul>
    <style>
        .tick { color: green; font-weight: bold; }
        .cross { color: red; font-weight: bold; }
    </style>
    <script>
    function validatePassword() {
        const password = document.getElementById('password').value;
        document.getElementById('length').children[0].innerHTML = password.length >= 8 ? '✔️' : '❌';
        document.getElementById('length').children[0].className = password.length >= 8 ? 'tick' : 'cross';
        document.getElementById('uppercase').children[0].innerHTML = /[A-Z]/.test(password) ? '✔️' : '❌';
        document.getElementById('uppercase').children[0].className = /[A-Z]/.test(password) ? 'tick' : 'cross';
        document.getElementById('lowercase').children[0].innerHTML = /[a-z]/.test(password) ? '✔️' : '❌';
        document.getElementById('lowercase').children[0].className = /[a-z]/.test(password) ? 'tick' : 'cross';
        document.getElementById('number').children[0].innerHTML = /[0-9]/.test(password) ? '✔️' : '❌';
        document.getElementById('number').children[0].className = /[0-9]/.test(password) ? 'tick' : 'cross';
        document.getElementById('special').children[0].innerHTML = /[^a-zA-Z0-9]/.test(password) ? '✔️' : '❌';
        document.getElementById('special').children[0].className = /[^a-zA-Z0-9]/.test(password) ? 'tick' : 'cross';
    }
    </script>
    <p>Already have an account? <a href="/basic_data_capturing_app/login.php">Login here</a>.</p>
</body>
</html>

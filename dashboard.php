<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: /basic_data_capturing_app/login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>
    <link rel="stylesheet" href="/basic_data_capturing_app/public/css/style.css">
    <style>
.button, .dashboard-nav-btn {
    display: inline-block;
    padding: 10px 20px;
    margin: 0 10px 20px 0;
    background: #1a59fb;
    color: #fff;
    border-radius: 4px;
    text-decoration: none;
    font-weight: bold;
    transition: background 0.2s;
    border: none;
    cursor: pointer;
}
.button:hover, .dashboard-nav-btn:hover {
    background: #000caf;
}
</style>
</head>
<body>
    <?php include __DIR__ . '/app/views/navbar.php'; ?>
    <a class="button" href="/basic_data_capturing_app/landing.php">Home</a>
    <h1>Welcome, <?= htmlspecialchars($_SESSION['username']) ?>!</h1>
    <p>This is your dashboard. You are logged in.</p>
</body>
</html>

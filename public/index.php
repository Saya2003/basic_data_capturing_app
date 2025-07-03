<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: /basic_data_capturing_app/landing.php');
    exit;
}

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Basic router for the app
require_once __DIR__ . '/app/models/Database.php';
require_once __DIR__ . '/app/controllers/ClientController.php';
require_once __DIR__ . '/app/controllers/ContactController.php';

$db = new Database();
$controller = $_GET['controller'] ?? 'client';
$action = $_GET['action'] ?? 'index';

switch ($controller) {
    case 'client':
        $ctrl = new ClientController($db);
        break;
    case 'contact':
        $ctrl = new ContactController($db);
        break;
    default:
        $ctrl = new ClientController($db);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Basic Data Capturing App</title>
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>
    <?php
    if (method_exists($ctrl, $action)) {
        $ctrl->$action();
    } else {
        $ctrl->index();
    }
    ?>
</body>
</html>

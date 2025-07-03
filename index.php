<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (!isset($_SESSION['user_id'])) {
    header('Location: /basic_data_capturing_app/landing.php');
    exit;
}

// If no controller/action, show dashboard
if (!isset($_GET['controller']) && !isset($_GET['action'])) {
    header('Location: /basic_data_capturing_app/dashboard.php');
    exit;
}

require_once __DIR__ . '/app/models/Database.php';
require_once __DIR__ . '/app/controllers/ClientController.php';
require_once __DIR__ . '/app/controllers/ContactController.php';
require_once __DIR__ . '/app/controllers/SearchController.php';

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
    case 'search':
        $ctrl = new SearchController($db);
        break;
    default:
        $ctrl = new ClientController($db);
}

if (method_exists($ctrl, $action)) {
    $ctrl->$action();
} else {
    $ctrl->index();
}
?>

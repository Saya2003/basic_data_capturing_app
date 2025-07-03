<?php
// AJAX handler for instant search
require_once __DIR__ . '/../app/models/Database.php';
require_once __DIR__ . '/../app/models/Client.php';
require_once __DIR__ . '/../app/models/Contact.php';

header('Content-Type: application/json');

$query = trim($_GET['q'] ?? '');
$type = $_GET['type'] ?? 'all';
$clients = [];
$contacts = [];
if ($query) {
    $db = new Database();
    if ($type === 'all' || $type === 'clients') {
        $clientModel = new Client($db);
        if ($type === 'client_code') {
            $clients = $clientModel->searchByClientCode($query);
        } else {
            $clients = $clientModel->search($query);
        }
    }
    if ($type === 'all' || $type === 'contacts') {
        $contactModel = new Contact($db);
        if ($type === 'email') {
            $contacts = $contactModel->searchByEmail($query);
        } else {
            $contacts = $contactModel->search($query);
        }
    }
}
echo json_encode([
    'clients' => $clients,
    'contacts' => $contacts
]);

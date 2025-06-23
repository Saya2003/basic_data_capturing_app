<?php
// AJAX handler for linking/unlinking clients and contacts
require_once __DIR__ . '/../models/Database.php';
require_once __DIR__ . '/../models/ClientContact.php';

header('Content-Type: application/json');

$db = new Database();
$clientContact = new ClientContact($db);

$action = $_POST['action'] ?? '';
$response = ['success' => false];

if ($action === 'link') {
    $client_id = (int)($_POST['client_id'] ?? 0);
    $contact_id = (int)($_POST['contact_id'] ?? 0);
    if ($client_id && $contact_id) {
        try {
            $success = $clientContact->link($client_id, $contact_id);
            $response['success'] = $success;
        } catch (Exception $e) {
            $response['error'] = $e->getMessage();
        }
    }
} elseif ($action === 'unlink') {
    $client_id = (int)($_POST['client_id'] ?? 0);
    $contact_id = (int)($_POST['contact_id'] ?? 0);
    if ($client_id && $contact_id) {
        $success = $clientContact->unlink($client_id, $contact_id);
        $response['success'] = $success;
    }
} else {
    $response['error'] = 'Invalid action';
}
echo json_encode($response);

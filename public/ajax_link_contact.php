<?php
// AJAX handler for linking/unlinking clients and contacts from the contact side
require_once __DIR__ . '/../app/models/Database.php';
require_once __DIR__ . '/../app/models/ClientContact.php';

header('Content-Type: application/json');

$db = new Database();
$clientContact = new ClientContact($db);

$action = $_POST['action'] ?? '';
$response = ['success' => false];

error_log('ajax_link_contact.php called. POST: ' . json_encode($_POST));

if ($action === 'link') {
    $client_id = (int)($_POST['client_id'] ?? 0);
    $contact_id = (int)($_POST['contact_id'] ?? 0);
    error_log("Link request: client_id=$client_id, contact_id=$contact_id");
    if ($client_id && $contact_id) {
        try {
            $success = $clientContact->link($client_id, $contact_id);
            $response['success'] = $success;
        } catch (Exception $e) {
            error_log('Link error: ' . $e->getMessage());
            $response['error'] = $e->getMessage();
        }
    } else {
        $response['error'] = 'Missing client_id or contact_id';
        error_log('Link error: Missing client_id or contact_id');
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
    error_log('Invalid action: ' . $action);
}
echo json_encode($response);

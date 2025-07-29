<?php
// session_start(); // Already started in index.php, so do not start again here
if (!isset($_SESSION['user_id'])) {
    header('Location: /basic_data_capturing_app/landing.php');
    exit;
}

require_once __DIR__ . '/../models/Database.php';
require_once __DIR__ . '/../models/Client.php';
require_once __DIR__ . '/../models/ClientContact.php';

class ClientController {
    private $client;
    private $clientContact;
    private $errors = [];
    private $db;

    public function __construct($db) {
        try {
            $this->db = $db;
            $this->client = new Client($db);
            $this->clientContact = new ClientContact($db);
        } catch (Exception $e) {
            $this->handleError($e);
        }
    }

    public function index() {
        try {
            $clients = $this->client->getAll();
            include __DIR__ . '/../views/clients_list.php';
        } catch (ClientException $e) {
            $this->errors[] = "Error loading clients: " . $e->getMessage();
            $clients = [];
            include __DIR__ . '/../views/clients_list.php';
        } catch (Exception $e) {
            $this->errors[] = "An unexpected error occurred: " . $e->getMessage();
            $clients = [];
            include __DIR__ . '/../views/clients_list.php';
        }
    }

    public function delete() {
        header('Content-Type: application/json'); // Set header early

        if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest') {
            echo json_encode(['success' => false, 'error' => 'Invalid request']);
            return;
        }

        try {
            $json = file_get_contents('php://input');
            $data = json_decode($json, true);

            if (!isset($data['clientIds']) || !is_array($data['clientIds']) || empty($data['clientIds'])) { // Added empty check
                echo json_encode(['success' => false, 'error' => 'No clients selected or invalid data format']);
                return;
            }

            // Ensure all client IDs are integers for security
            $clientIds = array_map('intval', $data['clientIds']);

            $success = $this->client->deleteClients($clientIds); // Pass the sanitized array
            echo json_encode(['success' => $success]); // Respond with the actual success status from the model
        } catch (Exception $e) {
            // Log the error for debugging
            error_log("Client delete error: " . $e->getMessage());
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    public function create() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $name = trim(strip_tags($_POST['name'] ?? ''));
                if (!$name) {
                    throw new ClientException('Name is required.');
                }
                
                // Check if it's an AJAX request
                if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
                    strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                    
                    $id = $this->client->create($name);
                    header('Content-Type: application/json');
                    echo json_encode(['success' => true, 'redirect' => '/basic_data_capturing_app/index.php?controller=client&action=index']);
                    exit;
                } else {
                    $id = $this->client->create($name);
                    header('Location: /basic_data_capturing_app/index.php?controller=client&action=index');
                    exit;
                }
            } catch (ClientException $e) {
                if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
                    strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
                    exit;
                } else {
                    $error = $e->getMessage();
                    include __DIR__ . '/../views/client_form.php';
                }
            } catch (Exception $e) {
                if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
                    strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => false, 'error' => 'An unexpected error occurred. Please try again.']);
                    exit;
                } else {
                    $error = 'An unexpected error occurred. Please try again.';
                    include __DIR__ . '/../views/client_form.php';
                }
            }
        } else {
            include __DIR__ . '/../views/client_form.php';
        }
    }

    public function edit() {
        try {
            $id = $_GET['id'] ?? null;
            if (!$id) {
                throw new ClientException("Client ID is required");
            }

            // Handle linking a contact
            if (isset($_POST['link_contact_id'])) {
                $contactId = (int)$_POST['link_contact_id'];
                $this->clientContact->link($id, $contactId);
            }
            // Handle unlinking a contact
            if (isset($_GET['unlink_contact_id'])) {
                $contactId = (int)$_GET['unlink_contact_id'];
                $this->clientContact->unlink($id, $contactId);
                // Redirect to avoid resubmission
                header('Location: ?controller=client&action=edit&id=' . $id);
                exit;
            }

            $client = $this->client->getById($id);
            $contacts = $this->clientContact->getContactsByClient($id);
            // Fetch all contacts for linking
            require_once __DIR__ . '/../models/Contact.php';
            $contactModel = new Contact($this->db);
            $allContacts = $contactModel->getAll();
            include __DIR__ . '/../views/client_form.php';
        } catch (ClientException $e) {
            if ($e->getCode() === 404) {
                header('Location: ?controller=client');
                exit;
            }
            $this->errors[] = $e->getMessage();
            include __DIR__ . '/../views/client_form.php';
        } catch (Exception $e) {
            $this->errors[] = "An unexpected error occurred: " . $e->getMessage();
            include __DIR__ . '/../views/client_form.php';
        }
    }

    private function handleError($e) {
        if ($e instanceof DatabaseException) {
            error_log("Database Error: " . $e->getMessage());
            $this->errors[] = "A database error occurred. Please try again later.";
        } else {
            error_log("Application Error: " . $e->getMessage());
            $this->errors[] = "An unexpected error occurred. Please try again later.";
        }
    }
}
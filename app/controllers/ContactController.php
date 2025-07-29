<?php
require_once __DIR__ . '/../models/Database.php';
require_once __DIR__ . '/../models/Contact.php';
require_once __DIR__ . '/../models/ClientContact.php';

class ContactController {
    private $contact;
    private $clientContact;
    private $db;
    public function __construct($db) {
        $this->db = $db;
        $this->contact = new Contact($db);
        $this->clientContact = new ClientContact($db);
    }
    public function index() {
        $contacts = $this->contact->getAll();
        include __DIR__ . '/../views/contacts_list.php';
    }
    public function create() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = trim(strip_tags($_POST['name'] ?? ''));
            $surname = trim(strip_tags($_POST['surname'] ?? ''));
            $email = trim(strip_tags($_POST['email'] ?? ''));
            
            try {
                if (!$name || !$surname || !$email) {
                    throw new Exception('All fields are required.');
                }
                if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    throw new Exception('Invalid email address.');
                }

                $id = $this->contact->create($name, $surname, $email);
                
                // Check if it's an AJAX request
                if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
                    strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                    header('Content-Type: application/json');
                    echo json_encode([
                        'success' => true,
                        'redirect' => "/basic_data_capturing_app/index.php?controller=contact&action=edit&id=" . $id
                    ]);
                    exit;
                } else {
                    header('Location: ?controller=contact&action=edit&id=' . $id);
                    exit;
                }
            } catch (PDOException $e) {
                $error = 'Email must be unique.';
                if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
                    strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => false, 'error' => $error]);
                    exit;
                }
            } catch (Exception $e) {
                $error = $e->getMessage();
                if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
                    strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => false, 'error' => $error]);
                    exit;
                }
            }
        }
        include __DIR__ . '/../views/contact_form.php';
    }
    public function edit() {
        $id = $_GET['id'] ?? null;
        if (!$id) {
            header('Location: ?controller=contact');
            exit;
        }
        $error = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = trim(strip_tags($_POST['name'] ?? ''));
            $surname = trim(strip_tags($_POST['surname'] ?? ''));
            $email = trim(strip_tags($_POST['email'] ?? ''));
            if (!$name || !$surname || !$email) {
                $error = 'All fields are required.';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error = 'Invalid email address.';
            } else {
                try {
                    $this->contact->update($id, $name, $surname, $email);
                    // Refresh contact after update
                    $contact = $this->contact->getById($id);
                } catch (PDOException $e) {
                    $error = 'Email must be unique.';
                }
            }
        }
        $contact = $this->contact->getById($id);
        // Linked clients
        $clients = $this->clientContact->getClientsByContact($id);
        // All clients for linking
        require_once __DIR__ . '/../models/Client.php';
        $clientModel = new Client($this->db);
        $allClients = $clientModel->getAll();
        include __DIR__ . '/../views/contact_form.php';
    }
}

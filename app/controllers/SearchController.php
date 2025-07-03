<?php
// SearchController.php
require_once __DIR__ . '/../models/Database.php';
require_once __DIR__ . '/../models/Client.php';
require_once __DIR__ . '/../models/Contact.php';

class SearchController {
    private $db;
    public function __construct($db) {
        $this->db = $db;
    }
    public function results() {
        $query = trim($_GET['query'] ?? '');
        $type = $_GET['type'] ?? 'all';
        $clients = [];
        $contacts = [];
        if ($query) {
            if ($type === 'all' || $type === 'clients') {
                $clientModel = new Client($this->db);
                if ($type === 'client_code') {
                    $clients = $clientModel->searchByClientCode($query);
                } else {
                    $clients = $clientModel->search($query);
                }
            }
            if ($type === 'all' || $type === 'contacts') {
                $contactModel = new Contact($this->db);
                if ($type === 'email') {
                    $contacts = $contactModel->searchByEmail($query);
                } else {
                    $contacts = $contactModel->search($query);
                }
            }
        }
        include __DIR__ . '/../views/search_results.php';
    }
}

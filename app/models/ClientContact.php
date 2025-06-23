<?php
require_once 'Database.php';
class ClientContact {
    private $db;
    public function __construct($db) {
        $this->db = $db;
    }
    public function link($client_id, $contact_id) {
        if (empty($client_id) || empty($contact_id)) {
            error_log("Invalid link attempt: client_id=$client_id, contact_id=$contact_id");
            throw new Exception("Invalid client or contact selection.");
        }
        error_log("Attempting to link client_id=$client_id with contact_id=$contact_id");
        // Check if already linked
        $stmt = $this->db->pdo->prepare("SELECT COUNT(*) FROM client_contacts WHERE client_id = ? AND contact_id = ?");
        $stmt->execute([$client_id, $contact_id]);
        if ($stmt->fetchColumn() > 0) {
            error_log("Already linked: client_id=$client_id, contact_id=$contact_id");
            throw new Exception("You're already linked to this client/contact.");
        }
        $stmt = $this->db->pdo->prepare("INSERT INTO client_contacts (client_id, contact_id) VALUES (?, ?)");
        $result = $stmt->execute([$client_id, $contact_id]);
        error_log("Link result for client_id=$client_id, contact_id=$contact_id: " . ($result ? 'success' : 'fail'));
        return $result;
    }
    public function unlink($client_id, $contact_id) {
        $stmt = $this->db->pdo->prepare("DELETE FROM client_contacts WHERE client_id = ? AND contact_id = ?");
        return $stmt->execute([$client_id, $contact_id]);
    }
    public function getContactsByClient($client_id) {
        $stmt = $this->db->pdo->prepare("SELECT ct.* FROM contacts ct INNER JOIN client_contacts cc ON ct.id = cc.contact_id WHERE cc.client_id = ? ORDER BY ct.surname, ct.name ASC");
        $stmt->execute([$client_id]);
        return $stmt->fetchAll();
    }
    public function getClientsByContact($contact_id) {
        $stmt = $this->db->pdo->prepare("SELECT cl.* FROM clients cl INNER JOIN client_contacts cc ON cl.id = cc.client_id WHERE cc.contact_id = ? ORDER BY cl.name ASC");
        $stmt->execute([$contact_id]);
        return $stmt->fetchAll();
    }
}

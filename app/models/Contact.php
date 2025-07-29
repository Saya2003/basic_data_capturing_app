<?php
require_once 'Database.php';
class ContactException extends Exception {
    public function __construct($message = "", $code = 0, Throwable $previous = null) {
        parent::__construct($message, $code, $previous);
    }
}

class Contact {
    private $db;
    public function __construct($db) {
        $this->db = $db;
    }
    public function getAll() {
        $stmt = $this->db->pdo->query("SELECT c.*, COUNT(cc.client_id) as client_count FROM contacts c LEFT JOIN client_contacts cc ON c.id = cc.contact_id GROUP BY c.id ORDER BY c.surname, c.name ASC");
        return $stmt->fetchAll();
    }
    private function generateContactCode($surname, $name) {
        try {
            // Use first letter of surname and first letter of name
            $alpha = strtoupper(substr($surname, 0, 1) . substr($name, 0, 1));
            
            // Find the next available number for this prefix
            $query = "SELECT contact_code FROM contacts WHERE contact_code LIKE ? ORDER BY contact_code DESC LIMIT 1";
            $stmt = $this->db->pdo->prepare($query);
            $stmt->execute([$alpha . '%']);
            $last = $stmt->fetchColumn();
            
            $num = 1;
            if ($last) {
                $num = intval(substr($last, 2)) + 1;
            }
            return $alpha . str_pad($num, 4, '0', STR_PAD_LEFT);
        } catch (Exception $e) {
            throw new ContactException("Failed to generate contact code: " . $e->getMessage(), $e->getCode(), $e);
        }
    }

    private function validateName($name, $fieldName = 'Name') {
        if (empty(trim($name))) {
            throw new ContactException("$fieldName cannot be empty");
        }
        
        if (preg_match('/[^a-zA-Z0-9\s]/', $name)) {
            throw new ContactException("$fieldName cannot contain special characters. Only letters, numbers, and spaces are allowed.");
        }
    }

    public function create($name, $surname, $email) {
        // Validate both name and surname
        $this->validateName($name, 'First name');
        $this->validateName($surname, 'Surname');
        
        // Basic email validation
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new ContactException("Invalid email address format");
        }
        
        $contact_code = $this->generateContactCode($surname, $name);
        $stmt = $this->db->pdo->prepare("INSERT INTO contacts (name, surname, email, contact_code) VALUES (?, ?, ?, ?)");
        $stmt->execute([$name, $surname, $email, $contact_code]);
        return $this->db->pdo->lastInsertId();
    }
    public function getById($id) {
        $stmt = $this->db->pdo->prepare("SELECT * FROM contacts WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    public function update($id, $name, $surname, $email) {
        // Validate both name and surname
        $this->validateName($name, 'First name');
        $this->validateName($surname, 'Surname');
        
        // Basic email validation
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new ContactException("Invalid email address format");
        }
        
        $stmt = $this->db->pdo->prepare("UPDATE contacts SET name = ?, surname = ?, email = ? WHERE id = ?");
        $stmt->execute([$name, $surname, $email, $id]);
    }
    public function search($query) {
        $stmt = $this->db->pdo->prepare("SELECT * FROM contacts WHERE name LIKE ? OR surname LIKE ? OR email LIKE ? ORDER BY surname, name ASC");
        $like = "%$query%";
        $stmt->execute([$like, $like, $like]);
        return $stmt->fetchAll();
    }
    public function searchByEmail($query) {
        $stmt = $this->db->pdo->prepare("SELECT * FROM contacts WHERE email LIKE ? ORDER BY surname, name ASC");
        $like = "%$query%";
        $stmt->execute([$like]);
        return $stmt->fetchAll();
    }
}

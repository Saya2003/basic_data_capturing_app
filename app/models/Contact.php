<?php
require_once 'Database.php';
class Contact {
    private $db;
    public function __construct($db) {
        $this->db = $db;
    }
    public function getAll() {
        $stmt = $this->db->pdo->query("SELECT c.*, COUNT(cc.client_id) as client_count FROM contacts c LEFT JOIN client_contacts cc ON c.id = cc.contact_id GROUP BY c.id ORDER BY c.surname, c.name ASC");
        return $stmt->fetchAll();
    }
    public function create($name, $surname, $email) {
        $stmt = $this->db->pdo->prepare("INSERT INTO contacts (name, surname, email) VALUES (?, ?, ?)");
        $stmt->execute([$name, $surname, $email]);
        return $this->db->pdo->lastInsertId();
    }
    public function getById($id) {
        $stmt = $this->db->pdo->prepare("SELECT * FROM contacts WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    public function update($id, $name, $surname, $email) {
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

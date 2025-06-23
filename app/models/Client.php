<?php
require_once 'Database.php';

class ClientException extends Exception {
    public function __construct($message = "", $code = 0, Throwable $previous = null) {
        parent::__construct($message, $code, $previous);
    }
}

class Client {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function getAll() {
        try {            $query = "SELECT c.*, COUNT(cc.contact_id) as contact_count"
                    . " FROM clients c"
                    . " LEFT JOIN client_contacts cc ON c.id = cc.client_id"
                    . " GROUP BY c.id"
                    . " ORDER BY c.name ASC";
            $stmt = $this->db->execute($query);
            return $stmt->fetchAll();
        } catch (DatabaseException $e) {
            throw new ClientException("Failed to retrieve clients: " . $e->getMessage(), $e->getCode(), $e);
        }
    }

    public function create($name) {
        try {
            if (empty(trim($name))) {
                throw new ClientException("Client name cannot be empty");
            }

            $client_code = $this->generateClientCode($name);
            $query = "INSERT INTO clients (name, client_code) VALUES (?, ?)";            $this->db->execute($query, [$name, $client_code]);
            return $this->db->pdo->lastInsertId();
        } catch (DatabaseException $e) {
            throw new ClientException("Failed to create client: " . $e->getMessage(), $e->getCode(), $e);
        }
    }

    private function generateClientCode($name) {
        try {
            $alpha = strtoupper(preg_replace('/[^A-Z]/', '', substr($name, 0, 3)));
            if (strlen($alpha) < 3) {
                $alpha = str_pad($alpha, 3, 'A');
            }

            $query = "SELECT client_code FROM clients WHERE client_code LIKE ? ORDER BY client_code DESC LIMIT 1";
            $stmt = $this->db->execute($query, [$alpha.'%']);
            $last = $stmt->fetchColumn();
            $num = 1;
            if ($last) {
                $num = intval(substr($last, 3)) + 1;
            }
            return $alpha . str_pad($num, 3, '0', STR_PAD_LEFT);
        } catch (DatabaseException $e) {
            throw new ClientException("Failed to generate client code: " . $e->getMessage(), $e->getCode(), $e);
        }
    }

    public function getById($id) {
        try {
            if (!is_numeric($id)) {
                throw new ClientException("Invalid client ID");
            }

            $query = "SELECT * FROM clients WHERE id = ?";
            $stmt = $this->db->execute($query, [$id]);
            $client = $stmt->fetch();

            if (!$client) {
                throw new ClientException("Client not found", 404);
            }

            return $client;
        } catch (DatabaseException $e) {
            throw new ClientException("Failed to retrieve client: " . $e->getMessage(), $e->getCode(), $e);
        }
    }
}

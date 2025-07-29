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

    private function validateName($name) {
        if (empty(trim($name))) {
            throw new ClientException("Client name cannot be empty");
        }
        
        if (preg_match('/[^a-zA-Z0-9\s]/', $name)) {
            throw new ClientException("Client name cannot contain special characters. Only letters, numbers, and spaces are allowed.");
        }
    }

    public function deleteClients($clientIds) {
        if (empty($clientIds)) {
            throw new ClientException("No clients specified for deletion");
        }

        // Convert all IDs to integers to ensure type safety
        $clientIds = array_map('intval', $clientIds);
        $placeholders = str_repeat('?,', count($clientIds) - 1) . '?';

        try {
            // Start transaction
            $this->db->beginTransaction();

            // Delete from client_contacts first due to foreign key constraint
            $queryContacts = "DELETE FROM client_contacts WHERE client_id IN ($placeholders)";
            $this->db->execute($queryContacts, $clientIds);

            // Then delete from clients
            $queryClients = "DELETE FROM clients WHERE id IN ($placeholders)";
            $this->db->execute($queryClients, $clientIds);

            // Commit transaction
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            // Rollback on error
            if ($this->db && $this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw new ClientException("Failed to delete clients: " . $e->getMessage());
        }
    }

    public function create($name) {
        try {
            $this->validateName($name);
            $client_code = $this->generateClientCode($name);
            $query = "INSERT INTO clients (name, client_code) VALUES (?, ?)";            $this->db->execute($query, [$name, $client_code]);
            return $this->db->pdo->lastInsertId();
        } catch (DatabaseException $e) {
            throw new ClientException("Failed to create client: " . $e->getMessage(), $e->getCode(), $e);
        }
    }

    private function getAlphaFromWords($words) {
        // For single words like "Protea", use first 3 letters directly
        if (count($words) == 1) {
            return strtoupper(substr(preg_replace('/[^A-Za-z]/', '', $words[0]), 0, 3));
        }
        
        // Get first letter of each word
        $alpha = '';
        foreach ($words as $word) {
            if (strlen($word) > 0) {
                $alpha .= strtoupper(substr($word, 0, 1));
            }
        }
        
        // If we don't have 3 letters yet and it's not a 3+ word name
        if (strlen($alpha) < 3 && count($words) < 3) {
            // Add more letters from the first word
            $alpha .= strtoupper(substr(preg_replace('/[^A-Za-z]/', '', $words[0]), 1, 3 - strlen($alpha)));
        }
        
        return substr($alpha, 0, 3);
    }

    private function generateClientCode($name) {
        try {
            $words = explode(' ', trim($name));
            $alpha = $this->getAlphaFromWords($words);
            
            // Pad with 'A' if less than 3 characters
            if (strlen($alpha) < 3) {
                $alpha = str_pad($alpha, 3, 'A');
            }

            // Find the next available number for this prefix
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

    public function search($query) {
        // First, let's count how many matches we should have
        $countStmt = $this->db->pdo->prepare("SELECT COUNT(*) FROM clients WHERE LOWER(name) LIKE LOWER(?)");
        $like = "%$query%";
        $countStmt->execute([$like]);
        $count = $countStmt->fetchColumn();
        
        // Now do the actual search
        $stmt = $this->db->pdo->prepare("SELECT * FROM clients WHERE LOWER(name) LIKE LOWER(?) OR LOWER(client_code) LIKE LOWER(?) ORDER BY name ASC");
        $stmt->execute([$like, $like]);
        $results = $stmt->fetchAll();
        
        // Log the results for debugging
        error_log("Search for '$query' found $count matching names and " . count($results) . " total results");
        return $results;
    }

    public function searchByClientCode($query) {
        $stmt = $this->db->pdo->prepare("SELECT * FROM clients WHERE client_code LIKE ? ORDER BY name ASC");
        $like = "%$query%";
        $stmt->execute([$like]);
        return $stmt->fetchAll();
    }
}

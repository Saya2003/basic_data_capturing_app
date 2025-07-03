<?php
// User model for authentication
class User {
    private $db;
    public function __construct($db) {
        $this->db = $db;
    }
    public function register($username, $password) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $query = "INSERT INTO users (username, password) VALUES (?, ?)";
        try {
            $this->db->execute($query, [$username, $hash]);
            return true;
        } catch (Exception $e) {
            echo '<div class="error">Registration error: ' . htmlspecialchars($e->getMessage()) . '</div>';
            return false;
        }
    }
    public function userExists($username) {
        $query = "SELECT id FROM users WHERE username = ?";
        $stmt = $this->db->execute($query, [$username]);
        return $stmt->fetch() ? true : false;
    }
    public function login($username, $password) {
        $query = "SELECT * FROM users WHERE username = ?";
        $stmt = $this->db->execute($query, [$username]);
        $user = $stmt->fetch();
        if ($user && password_verify($password, $user['password'])) {
            return $user;
        }
        return false;
    }
}

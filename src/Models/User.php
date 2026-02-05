<?php
namespace App\Models;

use App\Core\Database;
use PDO;

class User {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function login($username, $password) {
        $stmt = $this->db->query("SELECT * FROM users WHERE username = :username LIMIT 1", [
            'username' => $username
        ]);
        
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            // Rimuovi la password dall'array per sicurezza
            unset($user['password']);
            return $user;
        }

        return false;
    }

    public function getById($id) {
        $stmt = $this->db->query("SELECT id, username, email, role FROM users WHERE id = :id", ['id' => $id]);
        return $stmt->fetch();
    }
}

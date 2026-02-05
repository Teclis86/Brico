<?php
namespace App\Models;

use App\Core\Database;
use PDO;

/**
 * Classe User
 * 
 * Gestisce l'autenticazione e il recupero dati degli utenti amministratori/staff.
 */
class User {
    // Riferimento all'istanza del database
    private $db;

    /**
     * Costruttore
     * 
     * Inizializza la connessione al database.
     */
    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * Verifica le credenziali di accesso.
     * 
     * @param string $username Nome utente
     * @param string $password Password in chiaro
     * @return array|false Restituisce i dati dell'utente (senza password) se successo, false altrimenti
     */
    public function login($username, $password) {
        // Cerca l'utente per username
        $stmt = $this->db->query("SELECT * FROM users WHERE username = :username LIMIT 1", [
            'username' => $username
        ]);
        
        $user = $stmt->fetch();

        // Verifica se l'utente esiste e la password (hash) corrisponde
        if ($user && password_verify($password, $user['password'])) {
            // Rimuove la password dall'array per sicurezza prima di restituirlo
            unset($user['password']);
            return $user;
        }

        return false;
    }

    /**
     * Recupera un utente tramite ID.
     * 
     * @param int $id ID utente
     * @return array|false Dati utente o false
     */
    public function getById($id) {
        $stmt = $this->db->query("SELECT id, username, email, role FROM users WHERE id = :id", ['id' => $id]);
        return $stmt->fetch();
    }

    // Metodi futuri: create, update, delete, resetPassword, etc.
}

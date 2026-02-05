<?php
namespace App\Controllers;

use App\Core\Database;

class UserController {
    
    public function index() {
        if ($_SESSION['role'] !== 'admin') {
            die("Accesso negato.");
        }
        
        $db = Database::getInstance();
        $users = $db->query("SELECT * FROM users ORDER BY username")->fetchAll();
        
        require __DIR__ . '/../../templates/users/index.php';
    }

    public function create() {
        if ($_SESSION['role'] !== 'admin') die("Accesso negato.");
        require __DIR__ . '/../../templates/users/form.php';
    }

    public function store() {
        if ($_SESSION['role'] !== 'admin') die("Accesso negato.");

        $username = $_POST['username'];
        $email = $_POST['email'];
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $role = $_POST['role'];

        $db = Database::getInstance();
        try {
            $db->query("INSERT INTO users (username, email, password, role) VALUES (:u, :e, :p, :r)", [
                'u' => $username, 'e' => $email, 'p' => $password, 'r' => $role
            ]);
            $_SESSION['success'] = "Utente creato.";
        } catch (\Exception $e) {
            $_SESSION['error'] = "Errore creazione utente (Username/Email duplicati?).";
        }
        
        header("Location: " . APP_URL . "/?page=users");
        exit;
    }

    public function edit() {
        if ($_SESSION['role'] !== 'admin') die("Accesso negato.");
        $id = $_GET['id'];
        $db = Database::getInstance();
        $user = $db->query("SELECT * FROM users WHERE id = :id", ['id' => $id])->fetch();
        
        require __DIR__ . '/../../templates/users/form.php';
    }

    public function update() {
        if ($_SESSION['role'] !== 'admin') die("Accesso negato.");
        
        $id = $_POST['id'];
        $username = $_POST['username'];
        $email = $_POST['email'];
        $role = $_POST['role'];
        
        $sql = "UPDATE users SET username = :u, email = :e, role = :r";
        $params = ['u' => $username, 'e' => $email, 'r' => $role, 'id' => $id];

        if (!empty($_POST['password'])) {
            $sql .= ", password = :p";
            $params['p'] = password_hash($_POST['password'], PASSWORD_DEFAULT);
        }
        $sql .= " WHERE id = :id";

        $db = Database::getInstance();
        $db->query($sql, $params);
        
        $_SESSION['success'] = "Utente aggiornato.";
        header("Location: " . APP_URL . "/?page=users");
        exit;
    }

    public function delete() {
        if ($_SESSION['role'] !== 'admin') die("Accesso negato.");
        $id = $_POST['id'];
        
        if ($id == $_SESSION['user_id']) {
            $_SESSION['error'] = "Non puoi eliminare te stesso.";
        } else {
            $db = Database::getInstance();
            $db->query("DELETE FROM users WHERE id = :id", ['id' => $id]);
            $_SESSION['success'] = "Utente eliminato.";
        }
        header("Location: " . APP_URL . "/?page=users");
        exit;
    }
}

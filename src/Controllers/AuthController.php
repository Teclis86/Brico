<?php
namespace App\Controllers;

use App\Models\User;
use App\Core\Database;

class AuthController {
    
    public function login() {
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';

        if (empty($username) || empty($password)) {
            $_SESSION['error'] = "Inserisci username e password.";
            header("Location: " . APP_URL . "/?page=login");
            exit;
        }

        $userModel = new User();
        $user = $userModel->login($username, $password);

        if ($user) {
            // Login successo
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            
            header("Location: " . APP_URL . "/?page=dashboard");
            exit;
        } else {
            // Login fallito
            $_SESSION['error'] = "Credenziali non valide.";
            header("Location: " . APP_URL . "/?page=login");
            exit;
        }
    }

    public function logout() {
        session_destroy();
        header("Location: " . APP_URL . "/?page=login");
        exit;
    }

    public function forgotPassword() {
        require __DIR__ . '/../../templates/auth/forgot.php';
    }

    public function sendResetLink() {
        $email = $_POST['email'] ?? '';
        $db = Database::getInstance();
        
        // Verifica se email esiste
        $user = $db->query("SELECT id FROM users WHERE email = :email", ['email' => $email])->fetch();
        
        if ($user) {
            // Genera token
            $token = bin2hex(random_bytes(32));
            $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));
            
            // Salva token
            $db->query("UPDATE users SET reset_token = :token, reset_token_expiry = :expiry WHERE id = :id", [
                'token' => $token,
                'expiry' => $expiry,
                'id' => $user['id']
            ]);

            // Invia Mail
            $link = APP_URL . "/?page=auth_reset&token=" . $token;
            $subject = "Recupero Password Brico";
            $message = "Clicca qui per resettare la tua password: " . $link;
            $headers = "From: no-reply@brico.local";

            // Nota: mail() richiede server SMTP configurato
            if (mail($email, $subject, $message, $headers)) {
                $_SESSION['success'] = "Email inviata! Controlla la tua casella (anche spam/folder locale).";
            } else {
                // In ambiente dev senza SMTP, stampiamo il link per debug
                $_SESSION['success'] = "DEV MODE: Email non inviata. Link Reset: <a href='$link'>$link</a>";
            }
        } else {
            // Non diciamo se l'email non esiste per sicurezza, o lo diciamo per UX interna
            $_SESSION['success'] = "Se l'email esiste, riceverai istruzioni.";
        }
        
        header("Location: " . APP_URL . "/?page=forgot_password");
        exit;
    }

    public function resetPassword() {
        $token = $_GET['token'] ?? '';
        $db = Database::getInstance();
        
        $user = $db->query("SELECT id FROM users WHERE reset_token = :token AND reset_token_expiry > NOW()", ['token' => $token])->fetch();

        if (!$user) {
            $_SESSION['error'] = "Link scaduto o non valido.";
            header("Location: " . APP_URL . "/?page=login");
            exit;
        }

        require __DIR__ . '/../../templates/auth/reset.php';
    }

    public function updatePassword() {
        $token = $_POST['token'] ?? '';
        $password = $_POST['password'] ?? '';
        
        if (strlen($password) < 6) {
            die("Password troppo corta.");
        }

        $db = Database::getInstance();
        $user = $db->query("SELECT id FROM users WHERE reset_token = :token AND reset_token_expiry > NOW()", ['token' => $token])->fetch();

        if ($user) {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $db->query("UPDATE users SET password = :p, reset_token = NULL, reset_token_expiry = NULL WHERE id = :id", [
                'p' => $hash,
                'id' => $user['id']
            ]);
            
            $_SESSION['success'] = "Password aggiornata! Ora puoi accedere.";
            header("Location: " . APP_URL . "/?page=login");
        } else {
            $_SESSION['error'] = "Errore validazione token.";
            header("Location: " . APP_URL . "/?page=login");
        }
        exit;
    }
}

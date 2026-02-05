<?php
namespace App\Controllers;

use App\Models\User;
use App\Core\Database;

/**
 * Classe AuthController
 * 
 * Gestisce l'autenticazione degli utenti (Login, Logout, Reset Password).
 */
class AuthController {
    
    /**
     * Gestisce il processo di login.
     * Verifica le credenziali inviate via POST e inizializza la sessione.
     */
    public function login() {
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';

        // Validazione input base
        if (empty($username) || empty($password)) {
            $_SESSION['error'] = "Inserisci username e password.";
            header("Location: " . APP_URL . "/?page=login");
            exit;
        }

        $userModel = new User();
        $user = $userModel->login($username, $password);

        if ($user) {
            // Login successo: salva dati utente in sessione
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            
            // Reindirizza alla dashboard
            header("Location: " . APP_URL . "/?page=dashboard");
            exit;
        } else {
            // Login fallito: mostra errore
            $_SESSION['error'] = "Credenziali non valide.";
            header("Location: " . APP_URL . "/?page=login");
            exit;
        }
    }

    /**
     * Esegue il logout distruggendo la sessione.
     */
    public function logout() {
        session_destroy();
        header("Location: " . APP_URL . "/?page=login");
        exit;
    }

    /**
     * Mostra il form per il recupero password.
     */
    public function forgotPassword() {
        require __DIR__ . '/../../templates/auth/forgot.php';
    }

    /**
     * Invia il link di reset password via email.
     * Genera un token temporaneo e lo salva nel DB.
     */
    public function sendResetLink() {
        $email = $_POST['email'] ?? '';
        $db = Database::getInstance();
        
        // Verifica se l'email esiste nel database
        $user = $db->query("SELECT id FROM users WHERE email = :email", ['email' => $email])->fetch();
        
        if ($user) {
            // Genera token crittograficamente sicuro
            $token = bin2hex(random_bytes(32));
            // Imposta scadenza a 1 ora
            $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));
            
            // Salva token e scadenza nel DB
            $db->query("UPDATE users SET reset_token = :token, reset_token_expiry = :expiry WHERE id = :id", [
                'token' => $token,
                'expiry' => $expiry,
                'id' => $user['id']
            ]);

            // Costruisce il link di reset
            $link = APP_URL . "/?page=auth_reset&token=" . $token;
            $subject = "Recupero Password Brico";
            $message = "Clicca qui per resettare la tua password: " . $link;
            $headers = "From: no-reply@brico.local";

            // Tenta l'invio della mail
            // Nota: in locale senza server SMTP configurato, mail() potrebbe fallire o non inviare nulla.
            // In produzione usare una libreria come PHPMailer.
            if (@mail($email, $subject, $message, $headers)) {
                $_SESSION['success'] = "Email inviata! Controlla la tua casella (anche spam).";
            } else {
                // Fallback per sviluppo locale: mostra il link direttamente
                $_SESSION['success'] = "DEV MODE: Email non inviata. Link Reset: <a href='$link'>$link</a>";
            }
        } else {
            // Messaggio generico per non rivelare se l'email esiste (sicurezza user enumeration)
            $_SESSION['success'] = "Se l'email esiste, riceverai istruzioni.";
        }
        
        header("Location: " . APP_URL . "/?page=forgot_password");
        exit;
    }

    /**
     * Mostra il form di reset password verificando il token.
     */
    public function resetPassword() {
        $token = $_GET['token'] ?? '';
        $db = Database::getInstance();
        
        // Verifica validità e scadenza token
        $user = $db->query("SELECT id FROM users WHERE reset_token = :token AND reset_token_expiry > NOW()", ['token' => $token])->fetch();

        if (!$user) {
            $_SESSION['error'] = "Link scaduto o non valido.";
            header("Location: " . APP_URL . "/?page=login");
            exit;
        }
        
        // Se valido, mostra la vista per inserire la nuova password
        require __DIR__ . '/../../templates/auth/reset.php';
    }

    /**
     * Aggiorna la password nel database.
     */
    public function updatePassword() {
        $token = $_POST['token'] ?? '';
        $password = $_POST['password'] ?? '';
        
        if (strlen($password) < 6) {
            $_SESSION['error'] = "La password deve essere di almeno 6 caratteri.";
            header("Location: " . APP_URL . "/?page=auth_reset&token=" . $token);
            exit;
        }

        $db = Database::getInstance();
        $user = $db->query("SELECT id FROM users WHERE reset_token = :token AND reset_token_expiry > NOW()", ['token' => $token])->fetch();

        if ($user) {
            // Hash della nuova password
            $hash = password_hash($password, PASSWORD_DEFAULT);
            
            // Aggiorna password e invalida il token usato
            $db->query("UPDATE users SET password = :pass, reset_token = NULL, reset_token_expiry = NULL WHERE id = :id", [
                'pass' => $hash,
                'id' => $user['id']
            ]);

            $_SESSION['success'] = "Password aggiornata con successo! Ora puoi accedere.";
            header("Location: " . APP_URL . "/?page=login");
        } else {
            $_SESSION['error'] = "Errore durante l'aggiornamento. Riprova.";
            header("Location: " . APP_URL . "/?page=login");
        }
        exit;
    }
}

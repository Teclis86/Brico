<?php
/**
 * Script di installazione Database.
 * Legge il file SQL di struttura e inizializza il database.
 * Utile per il primo deploy o per resettare l'ambiente.
 */

require_once __DIR__ . '/../config/config.php';

try {
    // Connessione al server MySQL senza selezionare il DB (per crearlo se non esiste)
    $pdo = new PDO("mysql:host=" . DB_HOST, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "Connessione al server MySQL riuscita.<br>";

    // Lettura del file SQL
    // Assicurati che full_backup.sql contenga la creazione del DB e lo USE
    $sqlFile = __DIR__ . '/full_backup.sql';
    if (!file_exists($sqlFile)) {
        die("File full_backup.sql non trovato in " . __DIR__);
    }
    
    $sql = file_get_contents($sqlFile);

    // Esecuzione delle query
    // Nota: PDO::exec potrebbe avere problemi con query multiple se non configurato,
    // ma solitamente MySQL driver lo supporta.
    $pdo->exec($sql);

    echo "Database e tabelle creati con successo!<br>";
    echo "Utente admin creato: <b>admin</b> / <b>admin123</b><br>";
    echo "<a href='" . APP_URL . "'>Vai alla Login</a>";

} catch (PDOException $e) {
    die("Errore durante l'installazione del database: " . $e->getMessage());
}

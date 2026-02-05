<?php
require_once __DIR__ . '/../config/config.php';

try {
    // Connessione al server MySQL senza selezionare il DB (per crearlo se non esiste)
    $pdo = new PDO("mysql:host=" . DB_HOST, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "Connessione al server MySQL riuscita.<br>";

    // Lettura del file SQL
    $sql = file_get_contents(__DIR__ . '/database.sql');

    // Esecuzione delle query
    $pdo->exec($sql);

    echo "Database e tabelle creati con successo!<br>";
    echo "Utente admin creato: <b>admin</b> / <b>admin123</b><br>";
    echo "<a href='" . APP_URL . "'>Vai alla Login</a>";

} catch (PDOException $e) {
    die("Errore durante l'installazione del database: " . $e->getMessage());
}

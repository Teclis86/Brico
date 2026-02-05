<?php
namespace App\Core;

use PDO;
use PDOException;

/**
 * Classe Database
 * 
 * Gestisce la connessione al database MySQL utilizzando il pattern Singleton.
 * Questo assicura che ci sia una sola istanza di connessione attiva per richiesta,
 * ottimizzando le risorse.
 */
class Database {
    // Istanza statica della classe (Singleton)
    private static $instance = null;
    
    // Oggetto PDO per la connessione
    private $pdo;

    /**
     * Costruttore privato.
     * 
     * Inizializza la connessione PDO. È privato per impedire la creazione diretta
     * di oggetti Database tramite 'new', forzando l'uso di getInstance().
     */
    private function __construct() {
        try {
            // Stringa di connessione (DSN) con charset utf8mb4 per supporto caratteri speciali
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
            
            // Creazione nuova istanza PDO
            $this->pdo = new PDO($dsn, DB_USER, DB_PASS);
            
            // Configurazione gestione errori: lancia eccezioni in caso di errore SQL
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Configurazione fetch default: restituisce array associativi
            $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            // In caso di errore fatale di connessione, termina lo script
            die("Errore di connessione al Database: " . $e->getMessage());
        }
    }

    /**
     * Restituisce l'istanza unica della classe Database.
     * 
     * @return Database L'istanza singleton
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Restituisce l'oggetto PDO nativo.
     * 
     * Utile quando servono funzionalità specifiche di PDO non wrappate.
     * 
     * @return PDO
     */
    public function getConnection() {
        return $this->pdo;
    }

    /**
     * Esegue una query SQL preparata.
     * 
     * Metodo helper per semplificare l'esecuzione di query parametrizzate
     * e proteggere da SQL Injection.
     * 
     * @param string $sql La query SQL con placeholder (es. :id o ?)
     * @param array $params Array di parametri da bindare
     * @return \PDOStatement Lo statement eseguito
     */
    public function query($sql, $params = []) {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }
}

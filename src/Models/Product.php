<?php
namespace App\Models;

use App\Core\Database;
use PDO;

/**
 * Classe Product
 * 
 * Gestisce tutte le operazioni CRUD (Create, Read, Update, Delete) sui prodotti.
 * Include gestione giacenze, prezzi e relazioni con categorie e fornitori.
 */
class Product {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * Recupera una lista di prodotti con filtri e paginazione.
     * 
     * @param string $search Termine di ricerca (nome o barcode)
     * @param int $limit Numero massimo di risultati
     * @param int $offset Offset per paginazione
     * @return array Lista dei prodotti trovati
     */
    public function getAll($search = '', $limit = 50, $offset = 0) {
        // Query base con JOIN per ottenere nomi categorie e fornitori
        $sql = "SELECT p.*, c.name as category_name, s.company_name as supplier_name 
                FROM products p 
                LEFT JOIN categories c ON p.category_id = c.id 
                LEFT JOIN suppliers s ON p.supplier_id = s.id 
                WHERE 1=1";
        
        $params = [];
        // Aggiunge filtro ricerca se presente
        if (!empty($search)) {
            $sql .= " AND (p.name LIKE :search OR p.barcode LIKE :search)";
            $params['search'] = "%$search%";
        }

        // Aggiunge ordinamento e limiti
        $sql .= " ORDER BY p.name ASC LIMIT $limit OFFSET $offset";
        
        $stmt = $this->db->query($sql, $params);
        return $stmt->fetchAll();
    }

    /**
     * Recupera un singolo prodotto per ID.
     * 
     * @param int $id ID prodotto
     * @return array|false Dati prodotto o false
     */
    public function getById($id) {
        $stmt = $this->db->query("SELECT * FROM products WHERE id = :id", ['id' => $id]);
        return $stmt->fetch();
    }

    /**
     * Recupera un prodotto tramite Barcode (EAN).
     * Utile per la scansione rapida in cassa.
     * 
     * @param string $barcode Codice a barre
     * @return array|false Dati prodotto o false
     */
    public function getByBarcode($barcode) {
        $stmt = $this->db->query("SELECT * FROM products WHERE barcode = :barcode", ['barcode' => $barcode]);
        return $stmt->fetch();
    }

    /**
     * Crea un nuovo prodotto.
     * 
     * @param array $data Dati del prodotto (barcode, name, prices, stock, etc.)
     * @return bool True se successo, False se errore (es. barcode duplicato)
     */
    public function create($data) {
        $sql = "INSERT INTO products (barcode, name, description, category_id, supplier_id, price_buy, price_sell, tax_rate, stock_quantity, min_stock_level) 
                VALUES (:barcode, :name, :description, :category_id, :supplier_id, :price_buy, :price_sell, :tax_rate, :stock_quantity, :min_stock_level)";
        
        try {
            $this->db->query($sql, $data);
            return true;
        } catch (\PDOException $e) {
            // Gestione errori (es. violazione unique constraint su barcode)
            return false;
        }
    }

    /**
     * Aggiorna un prodotto esistente.
     * 
     * @param int $id ID prodotto da aggiornare
     * @param array $data Nuovi dati
     * @return bool True se successo
     */
    public function update($id, $data) {
        $sql = "UPDATE products SET 
                barcode = :barcode, 
                name = :name, 
                description = :description, 
                category_id = :category_id, 
                supplier_id = :supplier_id, 
                price_buy = :price_buy, 
                price_sell = :price_sell, 
                tax_rate = :tax_rate, 
                stock_quantity = :stock_quantity, 
                min_stock_level = :min_stock_level 
                WHERE id = :id";
        
        $data['id'] = $id;
        
        try {
            $this->db->query($sql, $data);
            return true;
        } catch (\PDOException $e) {
            return false;
        }
    }

    /**
     * Elimina un prodotto.
     * 
     * @param int $id ID prodotto
     */
    public function delete($id) {
        $this->db->query("DELETE FROM products WHERE id = :id", ['id' => $id]);
    }
    
    /**
     * Recupera tutte le categorie disponibili.
     * 
     * @return array Lista categorie
     */
    public function getCategories() {
        return $this->db->query("SELECT * FROM categories ORDER BY name")->fetchAll();
    }
}

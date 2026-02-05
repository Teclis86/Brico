<?php
namespace App\Models;

use App\Core\Database;
use PDO;

class Product {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function getAll($search = '', $limit = 50, $offset = 0) {
        $sql = "SELECT p.*, c.name as category_name, s.company_name as supplier_name 
                FROM products p 
                LEFT JOIN categories c ON p.category_id = c.id 
                LEFT JOIN suppliers s ON p.supplier_id = s.id 
                WHERE 1=1";
        
        $params = [];
        if (!empty($search)) {
            $sql .= " AND (p.name LIKE :search OR p.barcode LIKE :search)";
            $params['search'] = "%$search%";
        }

        $sql .= " ORDER BY p.name ASC LIMIT $limit OFFSET $offset";
        
        $stmt = $this->db->query($sql, $params);
        return $stmt->fetchAll();
    }

    public function getById($id) {
        $stmt = $this->db->query("SELECT * FROM products WHERE id = :id", ['id' => $id]);
        return $stmt->fetch();
    }

    public function getByBarcode($barcode) {
        $stmt = $this->db->query("SELECT * FROM products WHERE barcode = :barcode", ['barcode' => $barcode]);
        return $stmt->fetch();
    }

    public function create($data) {
        $sql = "INSERT INTO products (barcode, name, description, category_id, supplier_id, price_buy, price_sell, tax_rate, stock_quantity, min_stock_level) 
                VALUES (:barcode, :name, :description, :category_id, :supplier_id, :price_buy, :price_sell, :tax_rate, :stock_quantity, :min_stock_level)";
        
        try {
            $this->db->query($sql, $data);
            return true;
        } catch (\PDOException $e) {
            return false;
        }
    }

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

    public function delete($id) {
        $this->db->query("DELETE FROM products WHERE id = :id", ['id' => $id]);
    }
    
    // Metodi per categorie (semplificati qui per ora)
    public function getCategories() {
        return $this->db->query("SELECT * FROM categories ORDER BY name")->fetchAll();
    }
}

<?php
namespace App\Models;

use App\Core\Database;
use PDO;

class Customer {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function getAll($search = '', $limit = 50) {
        $sql = "SELECT * FROM customers";
        $params = [];

        if (!empty($search)) {
            $sql .= " WHERE name LIKE :q OR phone LIKE :q OR vat_number LIKE :q";
            $params['q'] = "%$search%";
        }

        $sql .= " ORDER BY name ASC LIMIT $limit";
        return $this->db->query($sql, $params)->fetchAll();
    }

    public function getById($id) {
        return $this->db->query("SELECT * FROM customers WHERE id = :id", ['id' => $id])->fetch();
    }

    public function create($data) {
        $sql = "INSERT INTO customers (name, email, phone, address, vat_number, fiscal_code) 
                VALUES (:name, :email, :phone, :address, :vat, :cf)";
        
        $this->db->query($sql, [
            'name' => $data['name'],
            'email' => $data['email'] ?? null,
            'phone' => $data['phone'] ?? null,
            'address' => $data['address'] ?? null,
            'vat' => $data['vat_number'] ?? null,
            'cf' => $data['fiscal_code'] ?? null
        ]);
        
        return $this->db->getConnection()->lastInsertId();
    }

    public function update($id, $data) {
        $sql = "UPDATE customers SET 
                name = :name, email = :email, phone = :phone, address = :address, 
                vat_number = :vat, fiscal_code = :cf 
                WHERE id = :id";
        
        $this->db->query($sql, [
            'id' => $id,
            'name' => $data['name'],
            'email' => $data['email'] ?? null,
            'phone' => $data['phone'] ?? null,
            'address' => $data['address'] ?? null,
            'vat' => $data['vat_number'] ?? null,
            'cf' => $data['fiscal_code'] ?? null
        ]);
    }

    public function delete($id) {
        // Controlla se ha dipendenze (vendite o DDT)
        $hasSales = $this->db->query("SELECT COUNT(*) FROM sales WHERE customer_id = :id", ['id' => $id])->fetchColumn();
        $hasDDTs = $this->db->query("SELECT COUNT(*) FROM ddts WHERE customer_id = :id", ['id' => $id])->fetchColumn();

        if ($hasSales > 0 || $hasDDTs > 0) {
            return false; // Non eliminare se ha storico
        }

        $this->db->query("DELETE FROM customers WHERE id = :id", ['id' => $id]);
        return true;
    }
}

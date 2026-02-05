<?php
namespace App\Models;

use App\Core\Database;
use PDO;

class StockMovement {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function getAll($limit = 50) {
        $sql = "SELECT sm.*, p.name as product_name, p.barcode, u.username 
                FROM stock_movements sm 
                JOIN products p ON sm.product_id = p.id 
                LEFT JOIN users u ON sm.user_id = u.id 
                ORDER BY sm.created_at DESC 
                LIMIT $limit";
        return $this->db->query($sql)->fetchAll();
    }

    public function createMovement($productId, $userId, $type, $quantity, $documentRef = null, $notes = null) {
        $pdo = $this->db->getConnection();
        
        try {
            $pdo->beginTransaction();

            // 1. Registra il movimento
            $sql = "INSERT INTO stock_movements (product_id, user_id, type, quantity, document_ref, notes) 
                    VALUES (:pid, :uid, :type, :qty, :doc, :notes)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                'pid' => $productId,
                'uid' => $userId,
                'type' => $type,
                'qty' => $quantity,
                'doc' => $documentRef,
                'notes' => $notes
            ]);

            // 2. Aggiorna lo stock del prodotto
            $operator = ($type == 'in' || $type == 'return') ? '+' : '-';
            // Se è un aggiustamento (adjustment), bisogna capire se è positivo o negativo. 
            // Per semplicità qui assumiamo che adjustment sia gestito a monte con in/out o logica dedicata.
            // Ma per ora gestiamo i tipi standard.
            
            // Gestione segno per update
            if ($type == 'in' || $type == 'return') {
                $updateSql = "UPDATE products SET stock_quantity = stock_quantity + :qty WHERE id = :pid";
            } else {
                $updateSql = "UPDATE products SET stock_quantity = stock_quantity - :qty WHERE id = :pid";
            }

            $stmtUpdate = $pdo->prepare($updateSql);
            $stmtUpdate->execute(['qty' => $quantity, 'pid' => $productId]);

            $pdo->commit();
            return true;

        } catch (\Exception $e) {
            $pdo->rollBack();
            // Log error
            return false;
        }
    }
}

<?php
namespace App\Models;

use App\Core\Database;
use PDO;

/**
 * Classe StockMovement
 * 
 * Gestisce la tracciabilità dei movimenti di magazzino (carico, scarico, reso).
 * Mantiene lo storico di ogni variazione di quantità.
 */
class StockMovement {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * Recupera lo storico dei movimenti.
     * 
     * @param int $limit Numero massimo di movimenti da recuperare
     * @return array Lista movimenti con dettagli prodotto e utente
     */
    public function getAll($limit = 50) {
        $sql = "SELECT sm.*, p.name as product_name, p.barcode, u.username 
                FROM stock_movements sm 
                JOIN products p ON sm.product_id = p.id 
                LEFT JOIN users u ON sm.user_id = u.id 
                ORDER BY sm.created_at DESC 
                LIMIT $limit";
        return $this->db->query($sql)->fetchAll();
    }

    /**
     * Registra un nuovo movimento di magazzino.
     * Aggiorna automaticamente la giacenza del prodotto correlato.
     * 
     * @param int $productId ID prodotto
     * @param int $userId ID operatore
     * @param string $type Tipo movimento ('in', 'out', 'sale', 'return', 'adjustment')
     * @param float $quantity Quantità movimentata (sempre positiva)
     * @param string|null $documentRef Riferimento documento (es. num scontrino, num DDT)
     * @param string|null $notes Note aggiuntive
     * @return bool Esito operazione
     */
    public function createMovement($productId, $userId, $type, $quantity, $documentRef = null, $notes = null) {
        $pdo = $this->db->getConnection();
        
        try {
            $pdo->beginTransaction();

            // 1. Registra il movimento nella tabella di storico
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

            // 2. Aggiorna la giacenza attuale del prodotto (products.stock_quantity)
            // Determina se aggiungere o sottrarre in base al tipo
            if ($type == 'in' || $type == 'return') {
                // Carico o Reso -> Aumenta giacenza
                $updateSql = "UPDATE products SET stock_quantity = stock_quantity + :qty WHERE id = :pid";
            } else {
                // Scarico, Vendita o altro -> Diminuisci giacenza
                $updateSql = "UPDATE products SET stock_quantity = stock_quantity - :qty WHERE id = :pid";
            }

            $stmtUpdate = $pdo->prepare($updateSql);
            $stmtUpdate->execute(['qty' => $quantity, 'pid' => $productId]);

            $pdo->commit();
            return true;

        } catch (\Exception $e) {
            $pdo->rollBack();
            // In futuro: loggare l'errore su file
            return false;
        }
    }
}

<?php
namespace App\Models;

use App\Core\Database;
use PDO;

class Sale {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function createSale($userId, $items, $paymentMethod = 'cash', $customerId = null) {
        $pdo = $this->db->getConnection();
        
        try {
            $pdo->beginTransaction();

            // Calcola totale
            $totalAmount = 0;
            foreach ($items as $item) {
                $totalAmount += $item['price'] * $item['qty'];
            }

            // 1. Crea Record Vendita
            // Genera numero scontrino progressivo (formato YYYY-ID)
            // Nota: in produzione servirebbe un lock o sequenza più robusta
            $stmtSeq = $pdo->query("SELECT MAX(id) as last_id FROM sales");
            $lastId = $stmtSeq->fetch()['last_id'] ?? 0;
            $receiptNumber = date('Y') . '-' . str_pad($lastId + 1, 6, '0', STR_PAD_LEFT);

            $sqlSale = "INSERT INTO sales (user_id, total_amount, payment_method, receipt_number, customer_id) 
                        VALUES (:uid, :total, :method, :receipt, :cid)";
            $stmt = $pdo->prepare($sqlSale);
            $stmt->execute([
                'uid' => $userId,
                'total' => $totalAmount,
                'method' => $paymentMethod,
                'receipt' => $receiptNumber,
                'cid' => $customerId
            ]);
            $saleId = $pdo->lastInsertId();

            // 2. Inserisci Items e Scarica Magazzino
            $sqlItem = "INSERT INTO sale_items (sale_id, product_id, quantity, price_at_sale, cost_at_sale, subtotal) 
                        VALUES (:sid, :pid, :qty, :price, :cost, :sub)";
            $stmtItem = $pdo->prepare($sqlItem);

            $sqlStock = "UPDATE products SET stock_quantity = stock_quantity - :qty WHERE id = :pid";
            $stmtStock = $pdo->prepare($sqlStock);
            
            // Prepare statement per recuperare il costo attuale
            $stmtGetCost = $pdo->prepare("SELECT price_buy FROM products WHERE id = :pid");

            $sqlMove = "INSERT INTO stock_movements (product_id, user_id, type, quantity, document_ref, notes) 
                        VALUES (:pid, :uid, 'sale', :qty, :doc, 'Vendita POS')";
            $stmtMove = $pdo->prepare($sqlMove);

            foreach ($items as $item) {
                $subtotal = $item['price'] * $item['qty'];
                
                // Recupera costo
                $stmtGetCost->execute(['pid' => $item['id']]);
                $cost = $stmtGetCost->fetchColumn() ?: 0;

                // Salva dettaglio
                $stmtItem->execute([
                    'sid' => $saleId,
                    'pid' => $item['id'],
                    'qty' => $item['qty'],
                    'price' => $item['price'],
                    'cost' => $cost,
                    'sub' => $subtotal
                ]);

                // Aggiorna Stock
                $stmtStock->execute([
                    'qty' => $item['qty'],
                    'pid' => $item['id']
                ]);

                // Registra Movimento
                $stmtMove->execute([
                    'pid' => $item['id'],
                    'uid' => $userId,
                    'qty' => $item['qty'],
                    'doc' => $receiptNumber
                ]);
            }

            $pdo->commit();
            return ['success' => true, 'sale_id' => $saleId, 'receipt_number' => $receiptNumber];

        } catch (\Exception $e) {
            $pdo->rollBack();
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}

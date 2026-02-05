<?php
namespace App\Models;

use App\Core\Database;
use PDO;

/**
 * Classe Sale
 * 
 * Gestisce il processo di vendita (Checkout).
 * Si occupa di creare lo scontrino, salvare i dettagli e aggiornare il magazzino in un'unica transazione.
 */
class Sale {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * Registra una nuova vendita.
     * Esegue una transazione atomica che:
     * 1. Crea il record vendita (sales)
     * 2. Inserisce gli articoli (sale_items)
     * 3. Decrementa la giacenza magazzino (products)
     * 4. Registra il movimento di magazzino (stock_movements)
     * 
     * @param int $userId ID dell'operatore che effettua la vendita
     * @param array $items Array di prodotti nel carrello [['id', 'qty', 'price'], ...]
     * @param string $paymentMethod Metodo di pagamento ('cash', 'card')
     * @param int|null $customerId ID cliente opzionale
     * @return array Risultato operazione ['success' => bool, 'sale_id' => int, 'receipt_number' => string]
     */
    public function createSale($userId, $items, $paymentMethod = 'cash', $customerId = null) {
        $pdo = $this->db->getConnection();
        
        try {
            // Inizia transazione database per garantire consistenza
            $pdo->beginTransaction();

            // Calcola totale vendita
            $totalAmount = 0;
            foreach ($items as $item) {
                $totalAmount += $item['price'] * $item['qty'];
            }

            // 1. Crea Record Vendita
            // Genera numero scontrino progressivo (formato YYYY-ID)
            // Nota: uso di MAX(id) non è concorrenziale perfetto ma sufficiente per piccoli volumi
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

            // Preparazione query per dettagli e aggiornamenti magazzino
            // 2. Inserisci Items
            $sqlItem = "INSERT INTO sale_items (sale_id, product_id, quantity, price_at_sale, cost_at_sale, subtotal) 
                        VALUES (:sid, :pid, :qty, :price, :cost, :sub)";
            $stmtItem = $pdo->prepare($sqlItem);

            // 3. Aggiorna giacenza
            $sqlStock = "UPDATE products SET stock_quantity = stock_quantity - :qty WHERE id = :pid";
            $stmtStock = $pdo->prepare($sqlStock);
            
            // Query helper per recuperare il costo di acquisto attuale (per calcolo margini)
            $stmtGetCost = $pdo->prepare("SELECT price_buy FROM products WHERE id = :pid");

            // 4. Registra Movimento
            $sqlMove = "INSERT INTO stock_movements (product_id, user_id, type, quantity, document_ref, notes) 
                        VALUES (:pid, :uid, 'sale', :qty, :doc, 'Vendita POS')";
            $stmtMove = $pdo->prepare($sqlMove);

            foreach ($items as $item) {
                $subtotal = $item['price'] * $item['qty'];
                
                // Recupera costo storico al momento della vendita
                $stmtGetCost->execute(['pid' => $item['id']]);
                $cost = $stmtGetCost->fetchColumn() ?: 0;

                // Salva riga scontrino
                $stmtItem->execute([
                    'sid' => $saleId,
                    'pid' => $item['id'],
                    'qty' => $item['qty'],
                    'price' => $item['price'],
                    'cost' => $cost,
                    'sub' => $subtotal
                ]);

                // Scala quantità dal magazzino
                $stmtStock->execute([
                    'qty' => $item['qty'],
                    'pid' => $item['id']
                ]);

                // Registra tracciamento movimento
                $stmtMove->execute([
                    'pid' => $item['id'],
                    'uid' => $userId,
                    'qty' => $item['qty'],
                    'doc' => $receiptNumber
                ]);
            }

            // Conferma tutte le modifiche
            $pdo->commit();
            return ['success' => true, 'sale_id' => $saleId, 'receipt_number' => $receiptNumber];

        } catch (\Exception $e) {
            // In caso di errore, annulla tutte le modifiche fatte in questa transazione
            $pdo->rollBack();
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}

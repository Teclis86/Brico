<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../config/config.php';

// Autoloader
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $base_dir = __DIR__ . '/../src/';
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) return;
    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
    if (file_exists($file)) require $file;
});

use App\Core\Database;

$db = Database::getInstance();
$pdo = $db->getConnection();

echo "Inizio generazione Vendite Recenti (Ultimi 7 giorni)...\n";

try {
    $customers = $pdo->query("SELECT id FROM customers")->fetchAll(PDO::FETCH_COLUMN);
    $products = $pdo->query("SELECT id, price_sell, stock_quantity FROM products")->fetchAll(PDO::FETCH_ASSOC);

    if (empty($customers) || empty($products)) {
        die("Dati mancanti (clienti/prodotti).\n");
    }

    $pdo->beginTransaction();

    // Genera vendite per ogni giorno degli ultimi 7 giorni
    for ($d = 6; $d >= 0; $d--) {
        $date = date('Y-m-d', strtotime("-$d days"));
        // 5-15 vendite al giorno
        $dailySales = mt_rand(5, 15);
        
        for ($i = 0; $i < $dailySales; $i++) {
            $custId = (mt_rand(0, 100) > 30) ? $customers[array_rand($customers)] : null; // 70% registered customers
            
            // Create Sale
            $stmt = $pdo->prepare("INSERT INTO sales (user_id, customer_id, total_amount, payment_method, created_at) VALUES (1, :cid, 0, :pm, :created)");
            $stmt->execute([
                'cid' => $custId,
                'pm' => (mt_rand(0, 1) ? 'cash' : 'card'),
                'created' => $date . ' ' . sprintf("%02d:%02d:%02d", mt_rand(8, 19), mt_rand(0, 59), mt_rand(0, 59))
            ]);
            $saleId = $pdo->lastInsertId();
            
            $total = 0;
            $itemsCount = mt_rand(1, 8);
            
            for ($j = 0; $j < $itemsCount; $j++) {
                $product = $products[array_rand($products)];
                $qty = mt_rand(1, 5);
                $subtotal = $product['price_sell'] * $qty;
                
                // Sale Item
                $stmtItem = $pdo->prepare("INSERT INTO sale_items (sale_id, product_id, quantity, price_at_sale, subtotal) VALUES (:sid, :pid, :qty, :price, :sub)");
                $stmtItem->execute([
                    'sid' => $saleId,
                    'pid' => $product['id'],
                    'qty' => $qty,
                    'price' => $product['price_sell'],
                    'sub' => $subtotal
                ]);
                
                $total += $subtotal;
                
                // Update Stock
                $stmtStock = $pdo->prepare("UPDATE products SET stock_quantity = stock_quantity - :qty WHERE id = :pid");
                $stmtStock->execute(['qty' => $qty, 'pid' => $product['id']]);
                
                // Stock Movement
                $stmtMove = $pdo->prepare("INSERT INTO stock_movements (product_id, user_id, type, quantity, document_ref, notes, created_at) VALUES (:pid, 1, 'sale', :qty, :doc, 'Vendita #$saleId', :created)");
                $stmtMove->execute([
                    'pid' => $product['id'],
                    'qty' => $qty,
                    'doc' => "Scontrino #$saleId",
                    'created' => $date . ' 12:00:00'
                ]);
            }
            
            // Update Total
            $pdo->prepare("UPDATE sales SET total_amount = :tot WHERE id = :id")->execute(['tot' => $total, 'id' => $saleId]);
        }
        echo "Giorno $date: generate $dailySales vendite.\n";
    }

    $pdo->commit();
    echo "Completato.\n";

} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    echo "Errore: " . $e->getMessage() . "\n";
}

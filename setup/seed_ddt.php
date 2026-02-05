<?php
// Autoloader manuale per evitare dipendenze da Composer
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $base_dir = __DIR__ . '/../src/';
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) return;
    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
    if (file_exists($file)) require $file;
});

require_once __DIR__ . '/../config/config.php';

use App\Core\Database;

try {
    $db = Database::getInstance();
    $pdo = $db->getConnection();

    echo "Inizio generazione 50 DDT di esempio...\n";

    // 1. Recupera un utente (admin o altro)
    $userStmt = $pdo->query("SELECT id FROM users LIMIT 1");
    $userId = $userStmt->fetchColumn();

    if (!$userId) {
        die("Errore: Nessun utente trovato nel database. Crea prima un utente.");
    }

    // 2. Recupera tutti i prodotti
    $products = $pdo->query("SELECT id, price_sell FROM products")->fetchAll(PDO::FETCH_ASSOC);

    if (empty($products)) {
        die("Errore: Nessun prodotto trovato. Esegui prima seed_products.php.");
    }

    // 3. Recupera tutti i clienti
    // Nota: 'city' non esiste nella tabella customers, usiamo address come destinazione
    $customers = $pdo->query("SELECT id, name, address FROM customers")->fetchAll(PDO::FETCH_ASSOC);

    if (empty($customers)) {
        die("Errore: Nessun cliente trovato. Esegui prima seed_data.php.");
    }

    // Genera 50 DDT
    $pdo->beginTransaction();

    for ($i = 0; $i < 50; $i++) {
        // Seleziona cliente casuale
        $customer = $customers[array_rand($customers)];
        
        // Data casuale negli ultimi 30 giorni
        $date = date('Y-m-d', strtotime("-" . mt_rand(0, 30) . " days"));
        
        // Genera numero progressivo fittizio per il seed (es. 2024-001, etc.)
        // Per semplicità nel seed usiamo un random o un loop, ma cerchiamo di essere coerenti con l'anno
        $year = date('Y', strtotime($date));
        $prog = $i + 1; 
        $number = sprintf("%s-%03d", $year, $prog); // Attenzione: questo potrebbe duplicare se esistono già DDT reali

        // Inserisci DDT
        $stmt = $pdo->prepare("INSERT INTO ddts (number, date, customer_name, destination, user_id, customer_id, notes) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $number,
            $date,
            $customer['name'],
            $customer['address'] ?? 'Sede legale',
            $userId,
            $customer['id'],
            'DDT generato automaticamente'
        ]);
        $ddtId = $pdo->lastInsertId();

        // Aggiungi items al DDT (1-5 prodotti a caso)
        $numItems = mt_rand(1, 5);
        for ($j = 0; $j < $numItems; $j++) {
            $product = $products[array_rand($products)];
            $qty = mt_rand(1, 10);

            $stmtItem = $pdo->prepare("INSERT INTO ddt_items (ddt_id, product_id, quantity) VALUES (?, ?, ?)");
            $stmtItem->execute([$ddtId, $product['id'], $qty]);
            
            // Aggiorna magazzino (scarico per vendita/DDT)
            // Nota: Se il sistema gestisce lo scarico alla creazione del DDT, dovremmo inserire anche il movimento.
            // Assumiamo che il seed debba simulare anche il movimento di magazzino.
            $stmtMov = $pdo->prepare("INSERT INTO stock_movements (product_id, user_id, type, quantity, document_ref, notes, created_at) VALUES (?, ?, 'out', ?, ?, ?, ?)");
            $stmtMov->execute([
                $product['id'],
                $userId,
                $qty,
                "DDT $number",
                "Scarico automatico da Seed DDT",
                $date . " 12:00:00"
            ]);
            
            // Aggiorna stock prodotto
             $stmtUpdateStock = $pdo->prepare("UPDATE products SET stock_quantity = stock_quantity - ? WHERE id = ?");
             $stmtUpdateStock->execute([$qty, $product['id']]);
        }
    }

    $pdo->commit();
    echo "Generati 50 DDT e relativi movimenti di magazzino con successo.\n";

} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo "Errore: " . $e->getMessage() . "\n";
}

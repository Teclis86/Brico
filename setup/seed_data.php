<?php
require_once __DIR__ . '/../config/config.php';

// Aumentiamo limite di memoria e tempo per generazione massiva
ini_set('memory_limit', '256M');
set_time_limit(300);

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "<h1>Seed Dati Test</h1>";

    // 1. Assicuriamoci che ci siano prodotti
    $stmtProducts = $pdo->query("SELECT id, price_sell, price_buy FROM products");
    $products = $stmtProducts->fetchAll(PDO::FETCH_ASSOC);

    if (count($products) < 10) {
        die("Troppi pochi prodotti. Esegui prima seed_products.php o aggiungine manualmente.");
    }
    echo "Trovati " . count($products) . " prodotti.<br>";

    // 2. Generazione Clienti (100)
    echo "<h3>Generazione Clienti...</h3>";
    $names = ['Mario', 'Luigi', 'Giovanni', 'Paolo', 'Anna', 'Maria', 'Laura', 'Elena', 'Giuseppe', 'Antonio', 'Francesca', 'Sofia'];
    $surnames = ['Rossi', 'Bianchi', 'Verdi', 'Neri', 'Gialli', 'Ferrari', 'Russo', 'Esposito', 'Romano', 'Colombo', 'Ricci', 'Marino'];
    $cities = ['Roma', 'Milano', 'Napoli', 'Torino', 'Firenze', 'Bologna', 'Genova', 'Bari'];
    $streets = ['Via Roma', 'Via Garibaldi', 'Corso Italia', 'Piazza Duomo', 'Viale Kennedy'];

    $sqlCustomer = "INSERT INTO customers (name, email, phone, address, vat_number, fiscal_code, created_at) VALUES (:name, :email, :phone, :address, :vat, :fc, :created)";
    $stmtCustomer = $pdo->prepare($sqlCustomer);

    $customerIds = [];
    
    // Recupera clienti esistenti per non duplicare troppo o per usarli
    $existingCustomers = $pdo->query("SELECT id FROM customers")->fetchAll(PDO::FETCH_COLUMN);
    $customerIds = $existingCustomers;

    $customersToCreate = 100 - count($existingCustomers);
    if ($customersToCreate > 0) {
        for ($i = 0; $i < $customersToCreate; $i++) {
            $nome = $names[array_rand($names)];
            $cognome = $surnames[array_rand($surnames)];
            $fullname = "$nome $cognome";
            
            $email = strtolower($nome . "." . $cognome . rand(1, 999) . "@example.com");
            $phone = "3" . rand(20, 99) . rand(1000000, 9999999);
            $address = $streets[array_rand($streets)] . " " . rand(1, 200) . ", " . $cities[array_rand($cities)];
            
            // Generazione CF/P.IVA fittizi
            $fc = strtoupper(substr($cognome, 0, 3) . substr($nome, 0, 3) . rand(10, 99) . "A" . rand(10, 99) . "H" . rand(100, 999) . "X");
            $vat = (rand(0, 10) > 7) ? rand(10000000000, 99999999999) : null; // 30% possibilità di avere P.IVA

            $created = date('Y-m-d H:i:s', strtotime("-" . rand(1, 365) . " days"));

            $stmtCustomer->execute([
                'name' => $fullname,
                'email' => $email,
                'phone' => $phone,
                'address' => $address,
                'vat' => $vat,
                'fc' => $fc,
                'created' => $created
            ]);
            $customerIds[] = $pdo->lastInsertId();
        }
        echo "Creati $customersToCreate nuovi clienti.<br>";
    } else {
        echo "Clienti già sufficienti.<br>";
    }

    // 3. Generazione Vendite (100)
    echo "<h3>Generazione Vendite...</h3>";
    
    // Recupera ultimo ID vendita per numero scontrino
    $stmtSeq = $pdo->query("SELECT MAX(id) as last_id FROM sales");
    $lastId = $stmtSeq->fetch()['last_id'] ?? 0;

    $sqlSale = "INSERT INTO sales (user_id, customer_id, total_amount, payment_method, receipt_number, created_at) 
                VALUES (:uid, :cid, :total, :method, :receipt, :created)";
    $stmtSale = $pdo->prepare($sqlSale);

    $sqlItem = "INSERT INTO sale_items (sale_id, product_id, quantity, price_at_sale, cost_at_sale, subtotal) 
                VALUES (:sid, :pid, :qty, :price, :cost, :sub)";
    $stmtItem = $pdo->prepare($sqlItem);

    // Supponiamo user_id = 1 (Admin) per semplicità
    $userId = 1; 

    $salesCount = 100;
    
    $pdo->beginTransaction();
    
    for ($i = 0; $i < $salesCount; $i++) {
        $lastId++;
        // Data casuale negli ultimi 3 mesi
        $date = date('Y-m-d H:i:s', strtotime("-" . rand(0, 90) . " days -" . rand(0, 23) . " hours -" . rand(0, 59) . " minutes"));
        $receiptNumber = date('Y', strtotime($date)) . '-' . str_pad($lastId, 6, '0', STR_PAD_LEFT);
        
        // 50% possibilità di assegnare a un cliente
        $customerId = (rand(0, 1) == 1 && !empty($customerIds)) ? $customerIds[array_rand($customerIds)] : null;
        $paymentMethod = (rand(0, 10) > 4) ? 'card' : 'cash'; // 60% card

        // Genera items
        $numItems = rand(1, 8);
        $saleItems = [];
        $totalAmount = 0;

        for ($j = 0; $j < $numItems; $j++) {
            $prod = $products[array_rand($products)];
            $qty = rand(1, 5);
            $price = $prod['price_sell'];
            $cost = $prod['price_buy'];
            $subtotal = $price * $qty;

            $totalAmount += $subtotal;
            $saleItems[] = [
                'pid' => $prod['id'],
                'qty' => $qty,
                'price' => $price,
                'cost' => $cost,
                'sub' => $subtotal
            ];
        }

        // Inserisci Sale
        $stmtSale->execute([
            'uid' => $userId,
            'cid' => $customerId,
            'total' => $totalAmount,
            'method' => $paymentMethod,
            'receipt' => $receiptNumber,
            'created' => $date
        ]);
        $saleId = $pdo->lastInsertId();

        // Inserisci Items
        foreach ($saleItems as $item) {
            $stmtItem->execute([
                'sid' => $saleId,
                'pid' => $item['pid'],
                'qty' => $item['qty'],
                'price' => $item['price'],
                'cost' => $item['cost'],
                'sub' => $item['sub']
            ]);
        }
    }

    $pdo->commit();
    echo "Generate $salesCount vendite con successo!<br>";
    echo "<a href='" . APP_URL . "/index.php?page=dashboard'>Vai alla Dashboard</a>";

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    die("Errore durante il seeding: " . $e->getMessage());
}

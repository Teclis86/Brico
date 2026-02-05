<?php
require_once __DIR__ . '/../config/config.php';

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "Inizio popolamento database...<br>";

    $categories = [
        'Utensileria' => ['Martello', 'Cacciavite', 'Trapano', 'Smerigliatrice', 'Chiave Inglese', 'Pinza', 'Metro', 'Livella'],
        'Idraulica' => ['Tubo PVC', 'Rubinetto', 'Guarnizioni', 'Sifone', 'Doccetta', 'Colla PVC', 'Raccordo T', 'Valvola'],
        'Elettricità' => ['Cavo 1.5mm', 'Presa Schuko', 'Interruttore', 'Lampadina LED', 'Multipresa', 'Fascette', 'Nastro Isolante'],
        'Vernici' => ['Bianco Murale', 'Smalto Nero', 'Pennello', 'Rullo', 'Solvente', 'Stucco', 'Nastro Carta'],
        'Giardinaggio' => ['Rastrello', 'Pala', 'Terriccio 20L', 'Vaso Plastica', 'Forbici Potatura', 'Tubo Irrigazione'],
        'Legname' => ['Listello Abete', 'Tavola Multistrato', 'Pannello OSB', 'Battiscopa', 'Colla Legno']
    ];

    $stmCheck = $pdo->query("SELECT COUNT(*) FROM products");
    if ($stmCheck->fetchColumn() > 0) {
        echo "Prodotti già presenti. Salto il popolamento.<br>";
    } else {
        $stmt = $pdo->prepare("INSERT INTO products (name, description, barcode, price_buy, price_sell, stock_quantity, category) VALUES (:name, :desc, :barcode, :buy, :sell, :qty, :cat)");

        $count = 0;
        for ($i = 0; $i < 300; $i++) {
            $catName = array_rand($categories);
            $items = $categories[$catName];
            $baseName = $items[array_rand($items)];
            
            // Varianti per rendere i nomi unici
            $variants = ['Eco', 'Pro', 'Max', 'Basic', 'Ultra', 'X', 'Y', 'Z'];
            $variant = $variants[array_rand($variants)];
            $size = rand(1, 100) . (rand(0,1) ? 'mm' : 'cm');
            
            $name = "$baseName $variant $size";
            $description = "Descrizione per $name - Ottima qualità";
            $barcode = '800' . str_pad(mt_rand(0, 999999999), 9, '0', STR_PAD_LEFT); // EAN-13 fake
            
            $priceBuy = mt_rand(100, 5000) / 100; // 1.00 a 50.00
            $markup = mt_rand(20, 100) / 100; // 20% a 100%
            $priceSell = $priceBuy * (1 + $markup);
            
            $stock = mt_rand(5, 200);

            $stmt->execute([
                'name' => $name,
                'desc' => $description,
                'barcode' => $barcode,
                'buy' => $priceBuy,
                'sell' => $priceSell,
                'qty' => $stock,
                'cat' => $catName
            ]);
            $count++;
        }
        echo "Inseriti $count prodotti di test.<br>";
    }

} catch (PDOException $e) {
    die("Errore: " . $e->getMessage());
}

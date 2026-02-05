<?php
namespace App\Controllers;

use App\Core\Database;
use App\Models\Product;
use PDO;

/**
 * Controller per la gestione dei Documenti di Trasporto (DDT)
 * Gestisce il ciclo di vita dei DDT: creazione, stampa, modifica e annullamento.
 */
class DdtController {
    
    /**
     * Elenca i DDT recenti.
     * Ordinati per data e ID decrescente.
     */
    public function index() {
        $db = Database::getInstance();
        $ddts = $db->query("SELECT d.*, u.username 
                            FROM ddts d 
                            LEFT JOIN users u ON d.user_id = u.id 
                            ORDER BY d.date DESC, d.id DESC")->fetchAll();
        
        require __DIR__ . '/../../templates/ddt/index.php';
    }

    /**
     * Cerca un DDT tramite il suo numero (o barcode).
     * Se trovato, reindirizza al dettaglio.
     */
    public function searchByBarcode() {
        $barcode = $_GET['barcode'] ?? '';
        $barcode = trim($barcode);

        if (empty($barcode)) {
            header("Location: " . APP_URL . "/?page=ddt");
            exit;
        }

        $db = Database::getInstance();
        $pdo = $db->getConnection();

        // Cerca per numero esatto
        $stmt = $pdo->prepare("SELECT id FROM ddts WHERE number = :num");
        $stmt->execute(['num' => $barcode]);
        $ddt = $stmt->fetch();

        if ($ddt) {
            header("Location: " . APP_URL . "/?page=ddt&action=detail&id=" . $ddt['id']);
            exit;
        } else {
            $_SESSION['error'] = "DDT non trovato con codice: " . htmlspecialchars($barcode);
            header("Location: " . APP_URL . "/?page=ddt");
            exit;
        }
    }

    /**
     * Mostra i dettagli di un DDT specifico.
     * Include testata e righe articoli.
     * 
     * @param int $_GET['id'] ID del DDT
     */
    public function detail() {
        if (!isset($_GET['id'])) {
            header("Location: " . APP_URL . "/?page=ddt");
            exit;
        }
        
        $id = $_GET['id'];
        $db = Database::getInstance();
        $pdo = $db->getConnection();
        
        $stmt = $pdo->prepare("SELECT * FROM ddts WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $ddt = $stmt->fetch();
        
        if (!$ddt) {
            $_SESSION['error'] = "DDT non trovato";
            header("Location: " . APP_URL . "/?page=ddt");
            exit;
        }
        
        $stmtItems = $pdo->prepare("SELECT di.*, p.name, p.barcode 
                                    FROM ddt_items di 
                                    JOIN products p ON di.product_id = p.id 
                                    WHERE di.ddt_id = :id");
        $stmtItems->execute(['id' => $id]);
        $items = $stmtItems->fetchAll();
        
        require __DIR__ . '/../../templates/ddt/detail.php';
    }

    /**
     * Annulla un DDT.
     * Operazione irreversibile che:
     * 1. Controlla se il DDT è già annullato
     * 2. Ripristina le giacenze di magazzino per ogni articolo
     * 3. Crea movimenti di rettifica stock (tipo 'ddt_cancel')
     * 4. Imposta lo stato del DDT a 'cancelled'
     * 
     * Esegue tutto in una transazione atomica.
     */
    public function cancel() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_GET['id'])) {
            header("Location: " . APP_URL . "/?page=ddt");
            exit;
        }

        $id = $_GET['id'];
        $db = Database::getInstance();
        $pdo = $db->getConnection();

        try {
            $pdo->beginTransaction();

            // 1. Fetch DDT
            $stmt = $pdo->prepare("SELECT * FROM ddts WHERE id = :id FOR UPDATE");
            $stmt->execute(['id' => $id]);
            $ddt = $stmt->fetch();

            if (!$ddt) throw new \Exception("DDT non trovato");
            if (($ddt['status'] ?? 'confirmed') === 'cancelled') throw new \Exception("DDT già annullato");

            // 2. Fetch Items
            $stmtItems = $pdo->prepare("SELECT * FROM ddt_items WHERE ddt_id = :id");
            $stmtItems->execute(['id' => $id]);
            $items = $stmtItems->fetchAll();

            // 3. Revert Stock
            // Note: If items is empty, this loop is skipped, but we still cancel the DDT.
            $stmtUpdateStock = $pdo->prepare("UPDATE products SET stock_quantity = stock_quantity + :qty WHERE id = :pid");
            $stmtMove = $pdo->prepare("INSERT INTO stock_movements (product_id, user_id, type, quantity, document_ref, notes) 
                                       VALUES (:pid, :uid, 'ddt_cancel', :qty, :doc, :notes)");

            foreach ($items as $item) {
                // Update Product Stock
                $stmtUpdateStock->execute([
                    'qty' => $item['quantity'],
                    'pid' => $item['product_id']
                ]);

                // Log Movement
                $stmtMove->execute([
                    'pid' => $item['product_id'],
                    'uid' => $_SESSION['user_id'] ?? 1,
                    'qty' => $item['quantity'], // Positive because returning to stock
                    'doc' => "DDT " . $ddt['number'],
                    'notes' => "Annullamento DDT " . $ddt['number']
                ]);
            }

            // 4. Update DDT Status
            $stmtStatus = $pdo->prepare("UPDATE ddts SET status = 'cancelled' WHERE id = :id");
            $stmtStatus->execute(['id' => $id]);

            $pdo->commit();
            $_SESSION['success'] = "DDT Annullato con successo. Le giacenze sono state ripristinate.";

        } catch (\Exception $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            $_SESSION['error'] = "Errore durante l'annullamento: " . $e->getMessage();
            // Log error for debugging
            error_log("DDT Cancel Error: " . $e->getMessage());
        }

        header("Location: " . APP_URL . "/?page=ddt&action=detail&id=" . $id);
        exit;
    }

    /**
     * Mostra il form di creazione DDT.
     * Calcola automaticamente il prossimo numero progressivo (formato YYYY-NNN).
     */
    public function create() {
        $db = Database::getInstance();
        $pdo = $db->getConnection();
        
        // Calcolo numero progressivo (formato YYYY-NNN)
        $year = date('Y');
        $stmt = $pdo->prepare("SELECT number FROM ddts WHERE number LIKE :prefix ORDER BY id DESC LIMIT 1");
        $stmt->execute(['prefix' => "$year-%"]);
        $lastDdt = $stmt->fetch();
        
        if ($lastDdt) {
            $parts = explode('-', $lastDdt['number']);
            $nextNum = intval($parts[1] ?? 0) + 1;
        } else {
            $nextNum = 1;
        }
        $suggestedNumber = $year . '-' . str_pad($nextNum, 3, '0', STR_PAD_LEFT);

        $productModel = new Product();
        $products = $productModel->getAll('', 1000); 
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->store();
            return;
        }

        require __DIR__ . '/../../templates/ddt/create.php';
    }

    /**
     * Salva un nuovo DDT.
     * Transazione atomica che:
     * 1. Crea la testata del DDT
     * 2. Inserisce le righe articoli
     * 3. Scarica il magazzino (decrementa giacenza)
     * 4. Registra i movimenti di stock
     */
    public function store() {
        $db = Database::getInstance();
        $pdo = $db->getConnection();
        
        try {
            $pdo->beginTransaction();

            $number = $_POST['number'];
            $date = $_POST['date'];
            
            // Gestione cliente: se selezionato da ID, recupera nome, altrimenti usa input manuale
            $customerId = !empty($_POST['customer_id']) ? $_POST['customer_id'] : null;
            $customerName = $_POST['customer_name'];
            
            if ($customerId) {
                $stmtC = $pdo->prepare("SELECT name FROM customers WHERE id = :id");
                $stmtC->execute(['id' => $customerId]);
                $c = $stmtC->fetch();
                if ($c) {
                    $customerName = $c['name'];
                }
            }

            $dest = $_POST['destination'];
            $notes = $_POST['notes'];
            $items = $_POST['items'] ?? []; // Array of [product_id, qty]

            // 1. Crea DDT
            $stmt = $pdo->prepare("INSERT INTO ddts (number, date, customer_name, customer_id, destination, user_id, notes, status) 
                                   VALUES (:num, :date, :cust, :cid, :dest, :uid, :notes, 'confirmed')");
            $stmt->execute([
                'num' => $number,
                'date' => $date,
                'cust' => $customerName,
                'cid' => $customerId,
                'dest' => $dest,
                'uid' => $_SESSION['user_id'] ?? 1,
                'notes' => $notes
            ]);
            $ddtId = $pdo->lastInsertId();

            // 2. Inserisci Righe e Aggiorna Magazzino
            $stmtItem = $pdo->prepare("INSERT INTO ddt_items (ddt_id, product_id, quantity) VALUES (:did, :pid, :qty)");
            // Aggiornamento stock rimosso qui se si vuole gestire solo il movimento, ma per coerenza manteniamo update
            // Nota: Se la logica di magazzino è complessa, meglio spostare in un Service.
            
            $stmtMove = $pdo->prepare("INSERT INTO stock_movements (product_id, user_id, type, quantity, document_ref, notes) 
                                       VALUES (:pid, :uid, 'ddt', :qty, :doc, :notes)");
            
            $stmtUpdateStock = $pdo->prepare("UPDATE products SET stock_quantity = stock_quantity - :qty WHERE id = :pid");

            foreach ($items as $item) {
                if (empty($item['product_id']) || empty($item['qty'])) continue;

                // Salva riga
                $stmtItem->execute([
                    'did' => $ddtId,
                    'pid' => $item['product_id'],
                    'qty' => $item['qty']
                ]);
                
                // Aggiorna stock
                $stmtUpdateStock->execute([
                    'qty' => $item['qty'],
                    'pid' => $item['product_id']
                ]);

                // Crea movimento
                $stmtMove->execute([
                    'pid' => $item['product_id'],
                    'uid' => $_SESSION['user_id'] ?? 1,
                    'qty' => $item['qty'],
                    'doc' => "DDT $number",
                    'notes' => "Scarico per DDT $number"
                ]);
            }

            $pdo->commit();
            header("Location: " . APP_URL . "/?page=ddt"); // Redirect alla lista
            exit;

        } catch (\Exception $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            echo "Errore creazione DDT: " . $e->getMessage();
        }
    }

    /**
     * Prepara la vista per la stampa del DDT.
     * Recupera dati testata e righe.
     */
    public function print() {
        if (!isset($_GET['id'])) die("ID mancante");
        
        $id = $_GET['id'];
        $db = Database::getInstance();
        $pdo = $db->getConnection();
        
        // Testata
        $stmt = $pdo->prepare("SELECT * FROM ddts WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $ddt = $stmt->fetch();
        
        if (!$ddt) die("DDT non trovato");
        
        // Righe
        $stmtItems = $pdo->prepare("SELECT di.*, p.name, p.barcode 
                                    FROM ddt_items di 
                                    JOIN products p ON di.product_id = p.id 
                                    WHERE di.ddt_id = :id");
        $stmtItems->execute(['id' => $id]);
        $items = $stmtItems->fetchAll();
        
        require __DIR__ . '/../../templates/ddt/print.php';
    }

    /**
     * Mostra il form di modifica DDT.
     * Impedisce la modifica se il DDT è annullato.
     * Carica i dati esistenti e la lista prodotti.
     */
    public function edit() {
        if (!isset($_GET['id'])) {
            header("Location: " . APP_URL . "/?page=ddt");
            exit;
        }
        
        $id = $_GET['id'];
        $db = Database::getInstance();
        $pdo = $db->getConnection();
        
        $stmt = $pdo->prepare("SELECT * FROM ddts WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $ddt = $stmt->fetch();
        
        if (!$ddt) {
            $_SESSION['error'] = "DDT non trovato";
            header("Location: " . APP_URL . "/?page=ddt");
            exit;
        }

        if (($ddt['status'] ?? 'confirmed') === 'cancelled') {
             $_SESSION['error'] = "Impossibile modificare un DDT annullato.";
             header("Location: " . APP_URL . "/?page=ddt&action=detail&id=" . $id);
             exit;
        }
        
        $stmtItems = $pdo->prepare("SELECT di.*, p.name, p.barcode 
                                    FROM ddt_items di 
                                    JOIN products p ON di.product_id = p.id 
                                    WHERE di.ddt_id = :id");
        $stmtItems->execute(['id' => $id]);
        $items = $stmtItems->fetchAll();

        $productModel = new Product();
        $products = $productModel->getAll('', 1000);
        
        require __DIR__ . '/../../templates/ddt/edit.php';
    }

    /**
     * Aggiorna un DDT esistente.
     * Logica complessa che:
     * 1. Verifica che il DDT non sia annullato
     * 2. Annulla gli effetti dello stock precedente (riaccredita giacenza)
     * 3. Cancella le vecchie righe
     * 4. Aggiorna la testata
     * 5. Inserisce le nuove righe e aggiorna nuovamente lo stock (addebita giacenza)
     * 
     * Tutto in transazione.
     */
    public function update() {
        if (!isset($_GET['id'])) die("ID mancante");
        $id = $_GET['id'];

        $db = Database::getInstance();
        $pdo = $db->getConnection();
        
        try {
            $pdo->beginTransaction();

            // Check if cancelled
            $stmtCheck = $pdo->prepare("SELECT status, number FROM ddts WHERE id = :id FOR UPDATE");
            $stmtCheck->execute(['id' => $id]);
            $currentDdt = $stmtCheck->fetch();
            if (($currentDdt['status'] ?? 'confirmed') === 'cancelled') {
                throw new \Exception("Impossibile modificare un DDT annullato.");
            }

            // 1. Revert Old Items Stock
            $stmtOldItems = $pdo->prepare("SELECT * FROM ddt_items WHERE ddt_id = :id");
            $stmtOldItems->execute(['id' => $id]);
            $oldItems = $stmtOldItems->fetchAll();

            $stmtRevertStock = $pdo->prepare("UPDATE products SET stock_quantity = stock_quantity + :qty WHERE id = :pid");
            $stmtMoveRevert = $pdo->prepare("INSERT INTO stock_movements (product_id, user_id, type, quantity, document_ref, notes) VALUES (:pid, :uid, 'ddt_edit_revert', :qty, :doc, :notes)");
            
            foreach ($oldItems as $item) {
                $stmtRevertStock->execute(['qty' => $item['quantity'], 'pid' => $item['product_id']]);
                
                $stmtMoveRevert->execute([
                    'pid' => $item['product_id'],
                    'uid' => $_SESSION['user_id'] ?? 1,
                    'qty' => $item['quantity'],
                    'doc' => "DDT " . ($currentDdt['number'] ?? ''),
                    'notes' => "Ripristino pre-modifica DDT " . ($currentDdt['number'] ?? '')
                ]);
            }

            // 2. Delete Old Items
            $pdo->prepare("DELETE FROM ddt_items WHERE ddt_id = :id")->execute(['id' => $id]);

            // 3. Update Header
            $number = $_POST['number'];
            $date = $_POST['date'];
            $customerId = !empty($_POST['customer_id']) ? $_POST['customer_id'] : null;
            $customerName = $_POST['customer_name'];
            if ($customerId) {
                $stmtC = $pdo->prepare("SELECT name FROM customers WHERE id = :id");
                $stmtC->execute(['id' => $customerId]);
                $c = $stmtC->fetch();
                if ($c) $customerName = $c['name'];
            }
            $dest = $_POST['destination'];
            $notes = $_POST['notes'];

            $stmtUpdate = $pdo->prepare("UPDATE ddts SET number=:num, date=:date, customer_name=:cust, customer_id=:cid, destination=:dest, notes=:notes WHERE id=:id");
            $stmtUpdate->execute([
                'num' => $number,
                'date' => $date,
                'cust' => $customerName,
                'cid' => $customerId,
                'dest' => $dest,
                'notes' => $notes,
                'id' => $id
            ]);

            // 4. Insert New Items & Update Stock
            $items = $_POST['items'] ?? [];
            $stmtItem = $pdo->prepare("INSERT INTO ddt_items (ddt_id, product_id, quantity) VALUES (:did, :pid, :qty)");
            $stmtUpdateStock = $pdo->prepare("UPDATE products SET stock_quantity = stock_quantity - :qty WHERE id = :pid");
            $stmtMove = $pdo->prepare("INSERT INTO stock_movements (product_id, user_id, type, quantity, document_ref, notes) VALUES (:pid, :uid, 'ddt_edit', :qty, :doc, :notes)");

            foreach ($items as $item) {
                if (empty($item['product_id']) || empty($item['qty'])) continue;
                
                $stmtItem->execute(['did' => $id, 'pid' => $item['product_id'], 'qty' => $item['qty']]);
                $stmtUpdateStock->execute(['qty' => $item['qty'], 'pid' => $item['product_id']]);
                
                $stmtMove->execute([
                    'pid' => $item['product_id'],
                    'uid' => $_SESSION['user_id'] ?? 1,
                    'qty' => $item['qty'],
                    'doc' => "DDT $number",
                    'notes' => "Modifica DDT $number"
                ]);
            }

            $pdo->commit();
            $_SESSION['success'] = "DDT Aggiornato con successo.";
            header("Location: " . APP_URL . "/?page=ddt&action=detail&id=" . $id);
            exit;

        } catch (\Exception $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            echo "Errore aggiornamento DDT: " . $e->getMessage();
        }
    }
}

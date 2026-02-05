<?php
namespace App\Controllers;

use App\Models\Product;
use App\Models\Sale;

/**
 * Controller per il Punto Vendita (POS)
 * Gestisce l'interfaccia di vendita, la ricerca prodotti e il checkout.
 */
class PosController {
    
    /**
     * Mostra l'interfaccia principale del POS.
     * Carica il template HTML/JS per la gestione cassa.
     */
    public function index() {
        // Carica interfaccia POS
        require __DIR__ . '/../../templates/pos/index.php';
    }

    /**
     * API Endpoint per cercare prodotti via AJAX.
     * Chiamato dal frontend JS durante la digitazione o scansione barcode.
     * 
     * Logica:
     * 1. Cerca corrispondenza esatta del barcode
     * 2. Se non trovata, cerca per similarità nel nome
     * 
     * Restituisce JSON.
     */
    public function apiSearch() {
        header('Content-Type: application/json');
        
        $query = $_GET['q'] ?? '';
        // Minimo 3 caratteri per evitare query troppo pesanti
        if (strlen($query) < 3) {
            echo json_encode([]); 
            exit;
        }

        $productModel = new Product();
        // Cerca per barcode esatto prima (priorità massima per scanner)
        $exact = $productModel->getByBarcode($query);
        if ($exact) {
            echo json_encode([$exact]);
            exit;
        }

        // Altrimenti cerca per nome (ricerca parziale)
        $results = $productModel->getAll($query, 20);
        echo json_encode($results);
        exit;
    }

    /**
     * API Endpoint per processare la vendita (Checkout).
     * Riceve i dati del carrello in formato JSON.
     * 
     * Esegue:
     * 1. Validazione input
     * 2. Creazione transazione vendita (Model Sale)
     * 3. Aggiornamento magazzino
     * 4. Stampa scontrino (simulata)
     */
    public function processSale() {
        header('Content-Type: application/json');
        
        // Leggi JSON input dal corpo della richiesta
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input || empty($input['items'])) {
            echo json_encode(['success' => false, 'message' => 'Carrello vuoto']);
            exit;
        }

        $userId = $_SESSION['user_id'];
        $items = $input['items'];
        $paymentMethod = $input['paymentMethod'] ?? 'cash';
        $customerId = $input['customerId'] ?? null;

        $saleModel = new Sale();
        // Tenta di completare la vendita atomicamente
        $result = $saleModel->createSale($userId, $items, $paymentMethod, $customerId);

        if ($result['success']) {
            // Se la vendita è andata a buon fine, procedi con la stampa
            // Qui invocheremmo la classe per la stampante fiscale reale
            $this->printReceipt($result['sale_id'], $result['receipt_number'], $items);
            
            echo json_encode(['success' => true, 'receipt' => $result['receipt_number']]);
        } else {
            // In caso di errore (es. giacenza insufficiente)
            echo json_encode(['success' => false, 'message' => $result['error']]);
        }
        exit;
    }

    /**
     * Simula la stampa dello scontrino fiscale.
     * Crea un file di testo nella cartella 'spool' che un servizio di stampa potrebbe monitorare.
     * 
     * @param int $saleId ID vendita
     * @param string $receiptNumber Numero scontrino generato
     * @param array $items Articoli venduti
     */
    private function printReceipt($saleId, $receiptNumber, $items) {
        // Simulazione invio al Registratore di Cassa
        // P/N 9911002 (Modello generico)
        // Creiamo un file di testo in una cartella 'spool' che un driver esterno potrebbe monitorare
        
        $spoolDir = __DIR__ . '/../../public/spool';
        if (!is_dir($spoolDir)) {
            mkdir($spoolDir, 0777, true);
        }

        $content = "SCONTRINO FISCALE\n";
        $content .= "Brico Store\n";
        $content .= "Data: " . date('d/m/Y H:i') . "\n";
        $content .= "Scontrino N. " . $receiptNumber . "\n";
        $content .= "--------------------------------\n";
        
        $total = 0;
        foreach ($items as $item) {
            $sub = $item['price'] * $item['qty'];
            $total += $sub;
            $content .= substr($item['name'], 0, 20) . "\n";
            $content .= $item['qty'] . " x " . number_format($item['price'], 2) . " = " . number_format($sub, 2) . "\n";
        }
        
        $content .= "--------------------------------\n";
        $content .= "TOTALE: EUR " . number_format($total, 2) . "\n";
        $content .= "Arrivederci e Grazie!\n";

        // Salva il file scontrino
        file_put_contents($spoolDir . '/receipt_' . $receiptNumber . '.txt', $content);
    }
}

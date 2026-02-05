<?php
namespace App\Controllers;

use App\Models\Product;
use App\Models\Sale;

class PosController {
    
    public function index() {
        // Carica interfaccia POS
        require __DIR__ . '/../../templates/pos/index.php';
    }

    // API Endpoint per cercare prodotto via AJAX
    public function apiSearch() {
        header('Content-Type: application/json');
        
        $query = $_GET['q'] ?? '';
        if (strlen($query) < 3) {
            echo json_encode([]); 
            exit;
        }

        $productModel = new Product();
        // Cerca per barcode esatto prima
        $exact = $productModel->getByBarcode($query);
        if ($exact) {
            echo json_encode([$exact]);
            exit;
        }

        // Altrimenti cerca per nome
        $results = $productModel->getAll($query, 20);
        echo json_encode($results);
        exit;
    }

    // API Endpoint per processare la vendita
    public function processSale() {
        header('Content-Type: application/json');
        
        // Leggi JSON input
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
        $result = $saleModel->createSale($userId, $items, $paymentMethod, $customerId);

        if ($result['success']) {
            // Qui invocheremmo la classe per la stampante fiscale
            $this->printReceipt($result['sale_id'], $result['receipt_number'], $items);
            
            echo json_encode(['success' => true, 'receipt' => $result['receipt_number']]);
        } else {
            echo json_encode(['success' => false, 'message' => $result['error']]);
        }
        exit;
    }

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

        file_put_contents($spoolDir . '/receipt_' . $receiptNumber . '.txt', $content);
    }
}

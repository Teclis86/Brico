<?php
namespace App\Controllers;

use App\Models\StockMovement;
use App\Models\Product;

/**
 * Controller per la gestione del Magazzino
 * Gestisce i movimenti manuali di carico/scarico e visualizza lo storico movimenti.
 */
class InventoryController {
    
    /**
     * Elenca tutti i movimenti di magazzino.
     * Include vendite, acquisti, rettifiche manuali, DDT, ecc.
     */
    public function index() {
        $movementModel = new StockMovement();
        $movements = $movementModel->getAll();
        
        require __DIR__ . '/../../templates/inventory/index.php';
    }

    /**
     * Mostra il form per registrare un movimento manuale.
     * Utile per rettifiche inventariali, carichi merce, o scarichi per rottura/uso interno.
     */
    public function create() {
        // Mostra form per carico/scarico
        $productModel = new Product();
        // Se c'è un barcode passato via GET (es. da scanner), cercalo
        $preselectedProduct = null;
        if (isset($_GET['barcode'])) {
            $preselectedProduct = $productModel->getByBarcode($_GET['barcode']);
        }
        
        // Per la select list (attenzione se ci sono troppi prodotti, meglio ajax search)
        $products = $productModel->getAll('', 500); 

        require __DIR__ . '/../../templates/inventory/form.php';
    }

    /**
     * Salva il movimento manuale nel database.
     * Aggiorna automaticamente la giacenza del prodotto.
     */
    public function store() {
        $productId = $_POST['product_id'];
        $type = $_POST['type']; // in (carico), out (scarico)
        $quantity = (int)$_POST['quantity'];
        $documentRef = $_POST['document_ref'];
        $notes = $_POST['notes'];
        $userId = $_SESSION['user_id'];

        if ($quantity <= 0) {
            $_SESSION['error'] = "La quantità deve essere positiva.";
            header("Location: " . APP_URL . "/?page=inventory_create");
            exit;
        }

        $movementModel = new StockMovement();
        if ($movementModel->createMovement($productId, $userId, $type, $quantity, $documentRef, $notes)) {
            $_SESSION['success'] = "Movimento registrato con successo.";
            header("Location: " . APP_URL . "/?page=inventory");
        } else {
            $_SESSION['error'] = "Errore durante la registrazione del movimento.";
            header("Location: " . APP_URL . "/?page=inventory_create");
        }
        exit;
    }
}

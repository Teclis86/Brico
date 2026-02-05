<?php
namespace App\Controllers;

use App\Models\StockMovement;
use App\Models\Product;

class InventoryController {
    
    public function index() {
        $movementModel = new StockMovement();
        $movements = $movementModel->getAll();
        
        require __DIR__ . '/../../templates/inventory/index.php';
    }

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

    public function store() {
        $productId = $_POST['product_id'];
        $type = $_POST['type']; // in, out
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

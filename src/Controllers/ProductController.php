<?php
namespace App\Controllers;

use App\Models\Product;

/**
 * Controller per la gestione Prodotti
 * Gestisce le operazioni CRUD (Create, Read, Update, Delete) per il catalogo.
 */
class ProductController {
    
    /**
     * Elenca i prodotti.
     * Supporta filtro di ricerca opzionale via GET.
     */
    public function index() {
        $productModel = new Product();
        $search = $_GET['search'] ?? '';
        $products = $productModel->getAll($search);
        
        require __DIR__ . '/../../templates/products/list.php';
    }

    /**
     * Mostra il form di creazione nuovo prodotto.
     * Carica anche le categorie necessarie per il select.
     */
    public function create() {
        $productModel = new Product();
        $categories = $productModel->getCategories();
        require __DIR__ . '/../../templates/products/form.php';
    }

    /**
     * Salva un nuovo prodotto nel database.
     * Gestisce i dati inviati via POST dal form di creazione.
     */
    public function store() {
        // Raccolta dati dal form
        $data = [
            'barcode' => $_POST['barcode'],
            'name' => $_POST['name'],
            'description' => $_POST['description'],
            'category_id' => !empty($_POST['category_id']) ? $_POST['category_id'] : null,
            'supplier_id' => null, // TODO: Implementare gestione fornitori
            'price_buy' => $_POST['price_buy'],
            'price_sell' => $_POST['price_sell'],
            'tax_rate' => $_POST['tax_rate'],
            'stock_quantity' => $_POST['stock_quantity'],
            'min_stock_level' => $_POST['min_stock_level']
        ];

        $productModel = new Product();
        if ($productModel->create($data)) {
            $_SESSION['success'] = "Prodotto creato con successo!";
            header("Location: " . APP_URL . "/?page=products");
        } else {
            $_SESSION['error'] = "Errore durante la creazione. Barcode duplicato?";
            header("Location: " . APP_URL . "/?page=products_create");
        }
        exit;
    }

    /**
     * Mostra il form di modifica prodotto esistente.
     * 
     * @param int $_GET['id'] ID del prodotto da modificare
     */
    public function edit() {
        $id = $_GET['id'] ?? 0;
        $productModel = new Product();
        $product = $productModel->getById($id);
        $categories = $productModel->getCategories();

        if (!$product) {
            $_SESSION['error'] = "Prodotto non trovato";
            header("Location: " . APP_URL . "/?page=products");
            exit;
        }

        require __DIR__ . '/../../templates/products/form.php';
    }

    /**
     * Aggiorna un prodotto esistente nel database.
     * Gestisce i dati inviati via POST dal form di modifica.
     */
    public function update() {
        $id = $_POST['id'];
        $data = [
            'barcode' => $_POST['barcode'],
            'name' => $_POST['name'],
            'description' => $_POST['description'],
            'category_id' => !empty($_POST['category_id']) ? $_POST['category_id'] : null,
            'supplier_id' => null,
            'price_buy' => $_POST['price_buy'],
            'price_sell' => $_POST['price_sell'],
            'tax_rate' => $_POST['tax_rate'],
            'stock_quantity' => $_POST['stock_quantity'],
            'min_stock_level' => $_POST['min_stock_level']
        ];

        $productModel = new Product();
        if ($productModel->update($id, $data)) {
            $_SESSION['success'] = "Prodotto aggiornato!";
        } else {
            $_SESSION['error'] = "Errore aggiornamento.";
        }
        header("Location: " . APP_URL . "/?page=products");
        exit;
    }

    /**
     * Elimina un prodotto.
     * Attenzione: potrebbe fallire se il prodotto è referenziato in vendite passate.
     * (Vedi vincoli foreign key nel database)
     */
    public function delete() {
        $id = $_POST['id'] ?? 0;
        $productModel = new Product();
        $productModel->delete($id);
        $_SESSION['success'] = "Prodotto eliminato.";
        header("Location: " . APP_URL . "/?page=products");
        exit;
    }
}

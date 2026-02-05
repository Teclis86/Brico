<?php
namespace App\Controllers;

use App\Models\Product;

class ProductController {
    
    public function index() {
        $productModel = new Product();
        $search = $_GET['search'] ?? '';
        $products = $productModel->getAll($search);
        
        require __DIR__ . '/../../templates/products/list.php';
    }

    public function create() {
        $productModel = new Product();
        $categories = $productModel->getCategories();
        require __DIR__ . '/../../templates/products/form.php';
    }

    public function store() {
        $data = [
            'barcode' => $_POST['barcode'],
            'name' => $_POST['name'],
            'description' => $_POST['description'],
            'category_id' => !empty($_POST['category_id']) ? $_POST['category_id'] : null,
            'supplier_id' => null, // TODO: Implement suppliers
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

    public function delete() {
        $id = $_POST['id'] ?? 0;
        $productModel = new Product();
        $productModel->delete($id);
        $_SESSION['success'] = "Prodotto eliminato.";
        header("Location: " . APP_URL . "/?page=products");
        exit;
    }
}

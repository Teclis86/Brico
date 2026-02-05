<?php
namespace App\Controllers;

use App\Core\Database;
use App\Models\Customer;

class CustomerController {
    
    public function index() {
        $customerModel = new Customer();
        $search = $_GET['q'] ?? '';
        $customers = $customerModel->getAll($search);
        
        require __DIR__ . '/../../templates/customers/index.php';
    }

    public function create() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->store();
            return;
        }
        require __DIR__ . '/../../templates/customers/create.php';
    }

    public function store() {
        $customerModel = new Customer();
        $customerModel->create($_POST);
        header("Location: " . APP_URL . "/index.php?page=customers");
        exit;
    }

    public function detail() {
        $id = $_GET['id'] ?? 0;
        $customerModel = new Customer();
        $customer = $customerModel->getById($id);

        if (!$customer) {
            die("Cliente non trovato");
        }

        $db = Database::getInstance();
        
        // Storico Vendite (Scontrini)
        $sales = $db->query("SELECT s.*, u.username, 
                             (SELECT COUNT(*) FROM sale_items WHERE sale_id = s.id) as items_count 
                             FROM sales s 
                             JOIN users u ON s.user_id = u.id 
                             WHERE s.customer_id = :cid 
                             ORDER BY s.created_at DESC", ['cid' => $id])->fetchAll();

        // Storico DDT
        $ddts = $db->query("SELECT d.*, u.username 
                            FROM ddts d 
                            JOIN users u ON d.user_id = u.id 
                            WHERE d.customer_id = :cid 
                            ORDER BY d.date DESC", ['cid' => $id])->fetchAll();

        // Totale Speso
        $totalSpent = $db->query("SELECT SUM(total_amount) FROM sales WHERE customer_id = :cid", ['cid' => $id])->fetchColumn();

        require __DIR__ . '/../../templates/customers/detail.php';
    }

    public function apiSearch() {
        header('Content-Type: application/json');
        $q = $_GET['q'] ?? '';
        
        if (strlen($q) < 2) {
            echo json_encode([]);
            exit;
        }

        $customerModel = new Customer();
        $results = $customerModel->getAll($q, 20);
        echo json_encode($results);
        exit;
    }
}

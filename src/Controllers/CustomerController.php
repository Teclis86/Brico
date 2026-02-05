<?php
namespace App\Controllers;

use App\Core\Database;
use App\Models\Customer;

/**
 * Controller per la gestione Clienti
 * Gestisce l'anagrafica clienti e la visualizzazione dello storico acquisti.
 */
class CustomerController {
    
    /**
     * Elenco clienti.
     * Supporta filtro di ricerca testuale (nome, email, telefono).
     */
    public function index() {
        $customerModel = new Customer();
        $search = $_GET['q'] ?? '';
        $customers = $customerModel->getAll($search);
        
        require __DIR__ . '/../../templates/customers/index.php';
    }

    /**
     * Mostra il form di creazione cliente o processa il salvataggio.
     * Gestisce sia GET (mostra form) che POST (salva dati).
     */
    public function create() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->store();
            return;
        }
        require __DIR__ . '/../../templates/customers/create.php';
    }

    /**
     * Salva un nuovo cliente nel database.
     */
    public function store() {
        $customerModel = new Customer();
        $customerModel->create($_POST);
        header("Location: " . APP_URL . "/index.php?page=customers");
        exit;
    }

    /**
     * Mostra la scheda dettagliata del cliente.
     * Include:
     * - Dati anagrafici
     * - Storico scontrini
     * - Storico DDT
     * - Totale speso (Lifetime Value)
     * 
     * @param int $_GET['id'] ID cliente
     */
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

    /**
     * API Endpoint per la ricerca clienti via AJAX.
     * Utilizzato nei form (es. nel POS o nel DDT) per l'autocomplete.
     */
    public function apiSearch() {
        header('Content-Type: application/json');
        $q = $_GET['q'] ?? '';
        
        // Minimo 2 caratteri per la ricerca
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

<?php
namespace App\Controllers;

use App\Core\Database;
use PDO;

class DashboardController {
    private $db;
    private $pdo;

    public function __construct() {
        if (!isset($_SESSION['user_id'])) {
            header("Location: " . APP_URL . "/?page=login");
            exit;
        }
        $this->db = Database::getInstance();
        $this->pdo = $this->db->getConnection();
    }

    public function index() {
        // 1. KPIs
        // Vendite Oggi
        $today = date('Y-m-d');
        $stmt = $this->pdo->prepare("SELECT SUM(total_amount) as total, COUNT(*) as count FROM sales WHERE DATE(created_at) = ?");
        $stmt->execute([$today]);
        $salesToday = $stmt->fetch(PDO::FETCH_ASSOC);
        $totalSalesToday = $salesToday['total'] ?? 0;
        $countSalesToday = $salesToday['count'] ?? 0;

        // Sottoscorta
        $stmt = $this->pdo->query("SELECT COUNT(*) FROM products WHERE stock_quantity <= min_stock_level");
        $lowStockCount = $stmt->fetchColumn();

        // Prodotti Totali
        $stmt = $this->pdo->query("SELECT COUNT(*) FROM products");
        $totalProducts = $stmt->fetchColumn();

        // 2. Ultime Vendite
        $stmt = $this->pdo->query("SELECT s.*, u.username FROM sales s LEFT JOIN users u ON s.user_id = u.id ORDER BY s.created_at DESC LIMIT 5");
        $recentSales = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // 3. Dati per Grafici
        
        // Vendite ultimi 7 giorni
        $chartSalesLabels = [];
        $chartSalesData = [];
        for ($i = 6; $i >= 0; $i--) {
            $d = date('Y-m-d', strtotime("-$i days"));
            $chartSalesLabels[] = date('d/m', strtotime($d));
            
            $stmt = $this->pdo->prepare("SELECT SUM(total_amount) FROM sales WHERE DATE(created_at) = ?");
            $stmt->execute([$d]);
            $chartSalesData[] = $stmt->fetchColumn() ?? 0;
        }

        // Top 5 Prodotti (per quantità venduta)
        $stmt = $this->pdo->query("
            SELECT p.name, SUM(si.quantity) as total_qty 
            FROM sale_items si 
            JOIN products p ON si.product_id = p.id 
            GROUP BY si.product_id 
            ORDER BY total_qty DESC 
            LIMIT 5
        ");
        $topProducts = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $chartTopProductsLabels = array_column($topProducts, 'name');
        $chartTopProductsData = array_column($topProducts, 'total_qty');

        // Passa dati alla view
        require __DIR__ . '/../../templates/dashboard/index.php';
    }
}

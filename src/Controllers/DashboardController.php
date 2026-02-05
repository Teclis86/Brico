<?php
namespace App\Controllers;

use App\Core\Database;
use PDO;

/**
 * Classe DashboardController
 * 
 * Gestisce la visualizzazione della dashboard principale.
 * Raccoglie e prepara i KPI (Key Performance Indicators) e i dati per i grafici.
 */
class DashboardController {
    private $db;
    private $pdo;

    /**
     * Costruttore.
     * Verifica che l'utente sia loggato prima di accedere alla dashboard.
     */
    public function __construct() {
        if (!isset($_SESSION['user_id'])) {
            header("Location: " . APP_URL . "/?page=login");
            exit;
        }
        $this->db = Database::getInstance();
        $this->pdo = $this->db->getConnection();
    }

    /**
     * Mostra la pagina principale della dashboard.
     * Recupera:
     * - Vendite giornaliere
     * - Prodotti sottoscorta
     * - Totale prodotti
     * - Ultime vendite recenti
     * - Dati per grafico andamento settimanale
     * - Top 5 prodotti più venduti
     */
    public function index() {
        // 1. Calcolo KPI (Indicatori Chiave)
        
        // KPI: Vendite Oggi (Totale incasso e numero scontrini)
        $today = date('Y-m-d');
        $stmt = $this->pdo->prepare("SELECT SUM(total_amount) as total, COUNT(*) as count FROM sales WHERE DATE(created_at) = ?");
        $stmt->execute([$today]);
        $salesToday = $stmt->fetch(PDO::FETCH_ASSOC);
        $totalSalesToday = $salesToday['total'] ?? 0;
        $countSalesToday = $salesToday['count'] ?? 0;

        // KPI: Prodotti Sottoscorta (Stock <= Livello Minimo)
        $stmt = $this->pdo->query("SELECT COUNT(*) FROM products WHERE stock_quantity <= min_stock_level");
        $lowStockCount = $stmt->fetchColumn();

        // KPI: Totale Prodotti in anagrafica
        $stmt = $this->pdo->query("SELECT COUNT(*) FROM products");
        $totalProducts = $stmt->fetchColumn();

        // 2. Tabella Ultime Vendite (ultime 5)
        $stmt = $this->pdo->query("SELECT s.*, u.username FROM sales s LEFT JOIN users u ON s.user_id = u.id ORDER BY s.created_at DESC LIMIT 5");
        $recentSales = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // 3. Preparazione Dati per Grafici (Chart.js)
        
        // Grafico 1: Andamento Vendite ultimi 7 giorni
        $chartSalesLabels = [];
        $chartSalesData = [];
        for ($i = 6; $i >= 0; $i--) {
            // Calcola la data andando a ritroso
            $d = date('Y-m-d', strtotime("-$i days"));
            // Etichetta asse X (es. 05/02)
            $chartSalesLabels[] = date('d/m', strtotime($d));
            
            // Query somma vendite per quel giorno
            $stmt = $this->pdo->prepare("SELECT SUM(total_amount) FROM sales WHERE DATE(created_at) = ?");
            $stmt->execute([$d]);
            $chartSalesData[] = $stmt->fetchColumn() ?? 0;
        }

        // Grafico 2: Top 5 Prodotti Best Seller
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

        // Carica la vista e passa tutte le variabili
        require __DIR__ . '/../../templates/dashboard/index.php';
    }
}

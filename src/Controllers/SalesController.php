<?php
namespace App\Controllers;

use App\Core\Database;

class SalesController {
    
    public function index() {
        $db = Database::getInstance();
        
        // Query per lista vendite
        $sales = $db->query("SELECT s.*, u.username, c.name as customer_name,
                             (SELECT COUNT(*) FROM sale_items WHERE sale_id = s.id) as items_count 
                             FROM sales s 
                             JOIN users u ON s.user_id = u.id 
                             LEFT JOIN customers c ON s.customer_id = c.id
                             ORDER BY s.created_at DESC 
                             LIMIT 100")->fetchAll();

        require __DIR__ . '/../../templates/sales/index.php';
    }

    public function detail() {
        $id = $_GET['id'] ?? 0;
        $db = Database::getInstance();

        $sale = $db->query("SELECT s.*, u.username, c.name as customer_name, c.id as customer_id 
                            FROM sales s 
                            JOIN users u ON s.user_id = u.id 
                            LEFT JOIN customers c ON s.customer_id = c.id
                            WHERE s.id = :id", ['id' => $id])->fetch();
        
        if (!$sale) {
            die("Vendita non trovata");
        }

        // Recupera items con costo storico
        $items = $db->query("SELECT si.*, p.name, p.barcode 
                             FROM sale_items si 
                             JOIN products p ON si.product_id = p.id 
                             WHERE si.sale_id = :id", ['id' => $id])->fetchAll();

        require __DIR__ . '/../../templates/sales/detail.php';
    }

    public function report() {
        $db = Database::getInstance();
        
        $startDate = $_GET['start'] ?? date('Y-m-01');
        $endDate = $_GET['end'] ?? date('Y-m-d');
        
        // Summary Query
        $sqlSummary = "SELECT 
                        SUM(si.subtotal) as total_revenue,
                        SUM(si.quantity * COALESCE(si.cost_at_sale, 0)) as total_cost,
                        COUNT(DISTINCT s.id) as total_transactions
                       FROM sale_items si
                       JOIN sales s ON si.sale_id = s.id
                       WHERE DATE(s.created_at) BETWEEN :start AND :end";
        
        $summary = $db->query($sqlSummary, ['start' => $startDate, 'end' => $endDate])->fetch();
        
        $totalRevenue = $summary['total_revenue'] ?? 0;
        $totalCost = $summary['total_cost'] ?? 0;
        $totalMargin = $totalRevenue - $totalCost;
        $marginPercent = $totalRevenue > 0 ? ($totalMargin / $totalRevenue) * 100 : 0;

        // Dettaglio giornaliero
        $sqlDaily = "SELECT 
                        DATE(s.created_at) as date,
                        COUNT(DISTINCT s.id) as transactions,
                        SUM(si.subtotal) as revenue,
                        SUM(si.quantity * COALESCE(si.cost_at_sale, 0)) as cost
                     FROM sale_items si
                     JOIN sales s ON si.sale_id = s.id
                     WHERE DATE(s.created_at) BETWEEN :start AND :end
                     GROUP BY DATE(s.created_at)
                     ORDER BY date DESC";
                     
        $dailyStats = $db->query($sqlDaily, ['start' => $startDate, 'end' => $endDate])->fetchAll();

        require __DIR__ . '/../../templates/sales/report.php';
    }
}

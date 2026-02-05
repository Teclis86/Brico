<?php include __DIR__ . '/../layout/header.php'; ?>
<?php include __DIR__ . '/../layout/sidebar.php'; ?>

<div class="content flex-grow-1 overflow-auto">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">Dettaglio Scontrino: <?= htmlspecialchars($sale['receipt_number']) ?></h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <a href="?page=sales" class="btn btn-sm btn-secondary">
                <i class="fas fa-arrow-left"></i> Torna alla lista
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-light">Info Vendita</div>
                <div class="card-body">
                    <p><strong>Data:</strong> <?= date('d/m/Y H:i', strtotime($sale['created_at'])) ?></p>
                    <p><strong>Operatore:</strong> <?= htmlspecialchars($sale['username']) ?></p>
                    <p><strong>Cliente:</strong> 
                        <?php if(!empty($sale['customer_name'])): ?>
                            <a href="?page=customers&action=detail&id=<?= $sale['customer_id'] ?>">
                                <?= htmlspecialchars($sale['customer_name']) ?>
                            </a>
                        <?php else: ?>
                            <span class="text-muted">Nessuno (Cliente Occasionale)</span>
                        <?php endif; ?>
                    </p>
                    <p><strong>Metodo Pagamento:</strong> <?= ucfirst($sale['payment_method']) ?></p>
                    <hr>
                    <h3 class="text-primary text-end">€ <?= number_format($sale['total_amount'], 2, ',', '.') ?></h3>
                </div>
            </div>
        </div>
        
        <div class="col-md-8 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-light">Articoli</div>
                <div class="card-body p-0">
                    <table class="table mb-0">
                        <thead>
                            <tr>
                                <th>Prodotto</th>
                                <th class="text-end">Qta</th>
                                <th class="text-end">Prezzo Unit.</th>
                                <th class="text-end">Totale</th>
                                <th class="text-end text-muted">Margine (Stima)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $totalMargin = 0;
                            foreach($items as $item): 
                                $cost = ($item['cost_at_sale'] ?? 0) * $item['quantity'];
                                $revenue = $item['subtotal'];
                                // Calcolo margine semplice: Ricavo - Costo Storico
                                // Se cost_at_sale non è presente (vecchi record), usiamo 0 o andrebbe gestito
                                
                                $margin = $revenue - $cost;
                                $totalMargin += $margin;
                            ?>
                            <tr>
                                <td>
                                    <?= htmlspecialchars($item['name']) ?><br>
                                    <small class="text-muted"><?= $item['barcode'] ?></small>
                                </td>
                                <td class="text-end"><?= $item['quantity'] ?></td>
                                <td class="text-end">€ <?= number_format($item['price_at_sale'], 2) ?></td>
                                <td class="text-end fw-bold">€ <?= number_format($item['subtotal'], 2) ?></td>
                                <td class="text-end text-muted">
                                    <small>€ <?= number_format($margin, 2) ?></small>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr class="bg-light">
                                <td colspan="4" class="text-end"><strong>Margine Totale:</strong></td>
                                <td class="text-end fw-bold text-success">€ <?= number_format($totalMargin, 2) ?></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../layout/footer.php'; ?>

<?php include __DIR__ . '/../layout/header.php'; ?>
<?php include __DIR__ . '/../layout/sidebar.php'; ?>

<div class="content flex-grow-1 overflow-auto">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">Storico Vendite</h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <a href="?page=pos" class="btn btn-sm btn-success">
                <i class="fas fa-cash-register"></i> Vai al POS
            </a>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>Data</th>
                            <th>Scontrino N.</th>
                            <th>Cliente</th>
                            <th>Operatore</th>
                            <th>Articoli</th>
                            <th>Totale</th>
                            <th>Pagamento</th>
                            <th>Azioni</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(empty($sales)): ?>
                            <tr><td colspan="8" class="text-center">Nessuna vendita registrata.</td></tr>
                        <?php else: ?>
                            <?php foreach($sales as $s): ?>
                                <tr>
                                    <td><?= date('d/m/Y H:i', strtotime($s['created_at'])) ?></td>
                                    <td><?= htmlspecialchars($s['receipt_number'] ?? '') ?></td>
                                    <td>
                                        <?php if(!empty($s['customer_name'])): ?>
                                            <i class="fas fa-user text-muted"></i> <?= htmlspecialchars($s['customer_name'] ?? '') ?>
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </td>
                                    <td><?= htmlspecialchars($s['username'] ?? '') ?></td>
                                    <td><?= $s['items_count'] ?></td>
                                    <td class="fw-bold">€ <?= number_format($s['total_amount'], 2, ',', '.') ?></td>
                                    <td>
                                        <?php if($s['payment_method'] == 'cash'): ?>
                                            <span class="badge bg-success">Contanti</span>
                                        <?php else: ?>
                                            <span class="badge bg-info">Carta</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="?page=sales&action=detail&id=<?= $s['id'] ?>" class="btn btn-sm btn-primary"><i class="fas fa-eye"></i> Dettagli</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../layout/footer.php'; ?>

<?php include __DIR__ . '/../layout/header.php'; ?>
<?php include __DIR__ . '/../layout/sidebar.php'; ?>

<div class="content flex-grow-1 overflow-auto">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">Movimenti di Magazzino</h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <a href="?page=inventory_create" class="btn btn-sm btn-primary">
                <i class="fas fa-exchange-alt"></i> Nuovo Movimento
            </a>
        </div>
    </div>

    <?php if(isset($_SESSION['success'])): ?>
        <div class="alert alert-success"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
    <?php endif; ?>

    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>Data</th>
                            <th>Prodotto</th>
                            <th>Tipo</th>
                            <th>Quantità</th>
                            <th>Rif. Doc</th>
                            <th>Operatore</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(empty($movements)): ?>
                            <tr><td colspan="6" class="text-center">Nessun movimento registrato.</td></tr>
                        <?php else: ?>
                            <?php foreach($movements as $m): ?>
                                <tr>
                                    <td><?= date('d/m/Y H:i', strtotime($m['created_at'])) ?></td>
                                    <td>
                                        <?= htmlspecialchars($m['product_name']) ?>
                                        <br><small class="text-muted"><?= htmlspecialchars($m['barcode']) ?></small>
                                    </td>
                                    <td>
                                        <?php if($m['type'] == 'in'): ?>
                                            <span class="badge bg-success">Carico</span>
                                        <?php elseif($m['type'] == 'out'): ?>
                                            <span class="badge bg-danger">Scarico</span>
                                        <?php elseif($m['type'] == 'sale'): ?>
                                            <span class="badge bg-info">Vendita</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary"><?= $m['type'] ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="fw-bold <?= ($m['type'] == 'in' || $m['type'] == 'return') ? 'text-success' : 'text-danger' ?>">
                                        <?= ($m['type'] == 'in' || $m['type'] == 'return') ? '+' : '-' ?><?= $m['quantity'] ?>
                                    </td>
                                    <td><?= htmlspecialchars($m['document_ref'] ?? '-') ?></td>
                                    <td><?= htmlspecialchars($m['username']) ?></td>
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

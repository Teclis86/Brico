<?php include __DIR__ . '/../layout/header.php'; ?>
<?php include __DIR__ . '/../layout/sidebar.php'; // Creeremo questo file separato ?>

<div class="content flex-grow-1 overflow-auto">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">Gestione Prodotti</h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <a href="?page=products_create" class="btn btn-sm btn-primary">
                <i class="fas fa-plus"></i> Nuovo Prodotto
            </a>
        </div>
    </div>

    <?php if(isset($_SESSION['success'])): ?>
        <div class="alert alert-success"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
    <?php endif; ?>
    <?php if(isset($_SESSION['error'])): ?>
        <div class="alert alert-danger"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
    <?php endif; ?>

    <div class="card shadow-sm">
        <div class="card-body">
            <form action="" method="GET" class="mb-3 d-flex gap-2">
                <input type="hidden" name="page" value="products">
                <input type="text" name="search" class="form-control" placeholder="Cerca per nome o barcode..." value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
                <button type="submit" class="btn btn-secondary">Cerca</button>
            </form>

            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>Barcode</th>
                            <th>Nome</th>
                            <th>Prezzo Vendita</th>
                            <th>Stock</th>
                            <th>Azioni</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(empty($products)): ?>
                            <tr><td colspan="5" class="text-center">Nessun prodotto trovato.</td></tr>
                        <?php else: ?>
                            <?php foreach($products as $p): ?>
                                <tr>
                                    <td><?= htmlspecialchars($p['barcode']) ?></td>
                                    <td>
                                        <strong><?= htmlspecialchars($p['name']) ?></strong><br>
                                        <small class="text-muted"><?= htmlspecialchars($p['category_name'] ?? 'Nessuna Categoria') ?></small>
                                    </td>
                                    <td>€ <?= number_format($p['price_sell'], 2, ',', '.') ?></td>
                                    <td>
                                        <?php if($p['stock_quantity'] <= $p['min_stock_level']): ?>
                                            <span class="badge bg-danger"><?= $p['stock_quantity'] ?></span>
                                        <?php else: ?>
                                            <span class="badge bg-success"><?= $p['stock_quantity'] ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="?page=products_edit&id=<?= $p['id'] ?>" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i></a>
                                        <form action="?page=products_delete" method="POST" class="d-inline" onsubmit="return confirm('Sei sicuro di voler eliminare questo prodotto?');">
                                            <input type="hidden" name="id" value="<?= $p['id'] ?>">
                                            <button type="submit" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                                        </form>
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

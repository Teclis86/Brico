<?php include __DIR__ . '/../layout/header.php'; ?>
<?php include __DIR__ . '/../layout/sidebar.php'; ?>

<div class="content flex-grow-1 overflow-auto">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2"><?= isset($product) ? 'Modifica Prodotto' : 'Nuovo Prodotto' ?></h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <a href="?page=products" class="btn btn-sm btn-secondary">
                <i class="fas fa-arrow-left"></i> Torna alla lista
            </a>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <form action="?page=<?= isset($product) ? 'products_update' : 'products_store' ?>" method="POST">
                <?php if(isset($product)): ?>
                    <input type="hidden" name="id" value="<?= $product['id'] ?>">
                <?php endif; ?>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Codice a Barre (Barcode) *</label>
                        <input type="text" name="barcode" class="form-control" required value="<?= $product['barcode'] ?? '' ?>" autofocus>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Nome Prodotto *</label>
                        <input type="text" name="name" class="form-control" required value="<?= $product['name'] ?? '' ?>">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Descrizione</label>
                    <textarea name="description" class="form-control" rows="3"><?= $product['description'] ?? '' ?></textarea>
                </div>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Prezzo Acquisto (€)</label>
                        <input type="number" step="0.01" name="price_buy" class="form-control" value="<?= $product['price_buy'] ?? '0.00' ?>">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Prezzo Vendita Ivato (€) *</label>
                        <input type="number" step="0.01" name="price_sell" class="form-control" required value="<?= $product['price_sell'] ?? '0.00' ?>">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Aliquota IVA (%)</label>
                        <input type="number" step="0.01" name="tax_rate" class="form-control" value="<?= $product['tax_rate'] ?? '22.00' ?>">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Quantità in Stock</label>
                        <input type="number" name="stock_quantity" class="form-control" value="<?= $product['stock_quantity'] ?? '0' ?>">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Livello Sottoscorta (Allarme)</label>
                        <input type="number" name="min_stock_level" class="form-control" value="<?= $product['min_stock_level'] ?? '5' ?>">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Categoria</label>
                    <select name="category_id" class="form-select">
                        <option value="">-- Seleziona Categoria --</option>
                        <?php foreach($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>" <?= (isset($product) && $product['category_id'] == $cat['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($cat['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary btn-lg"><?= isset($product) ? 'Aggiorna Prodotto' : 'Salva Prodotto' ?></button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../layout/footer.php'; ?>

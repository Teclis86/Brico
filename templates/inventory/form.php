<?php include __DIR__ . '/../layout/header.php'; ?>
<?php include __DIR__ . '/../layout/sidebar.php'; ?>

<div class="content flex-grow-1 overflow-auto">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">Registra Movimento Magazzino</h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <a href="?page=inventory" class="btn btn-sm btn-secondary">
                <i class="fas fa-arrow-left"></i> Torna allo Storico
            </a>
        </div>
    </div>

    <?php if(isset($_SESSION['error'])): ?>
        <div class="alert alert-danger"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
    <?php endif; ?>

    <div class="card shadow-sm">
        <div class="card-body">
            <form action="?page=inventory_store" method="POST">
                
                <div class="mb-3 bg-light p-3 rounded border">
                    <label class="form-label"><i class="fas fa-barcode"></i> Barcode Scanner (Rapido)</label>
                    <div class="input-group">
                        <input type="text" id="barcodeInput" class="form-control" placeholder="Spara il barcode qui per selezionare il prodotto..." autofocus>
                        <button class="btn btn-primary" type="button" onclick="searchProductByBarcode()"><i class="fas fa-search"></i></button>
                    </div>
                    <div id="scanResult" class="mt-1 fw-bold"></div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Prodotto *</label>
                    <select name="product_id" id="productSelect" class="form-select" required>
                        <option value="">-- Seleziona Prodotto --</option>
                        <?php foreach($products as $p): ?>
                            <option value="<?= $p['id'] ?>" <?= (isset($preselectedProduct) && $preselectedProduct['id'] == $p['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($p['name']) ?> (<?= $p['barcode'] ?>) - Stock Attuale: <?= $p['stock_quantity'] ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

<script>
document.getElementById('barcodeInput').addEventListener('keypress', function (e) {
    if (e.key === 'Enter') {
        e.preventDefault();
        searchProductByBarcode();
    }
});

function searchProductByBarcode() {
    const code = document.getElementById('barcodeInput').value.trim();
    if(!code) return;
    
    const resultDiv = document.getElementById('scanResult');
    resultDiv.innerHTML = '<span class="text-muted">Ricerca in corso...</span>';
    
    fetch('<?php echo APP_URL; ?>/index.php?page=api_pos_search&q=' + encodeURIComponent(code))
        .then(res => res.json())
        .then(data => {
            if (data.length > 0) {
                // Prendi il primo risultato (o match esatto)
                const product = data[0]; // Assumiamo che la ricerca sia buona
                const select = document.getElementById('productSelect');
                
                // Cerca se esiste l'opzione
                let optionExists = false;
                for(let i=0; i<select.options.length; i++) {
                    if(select.options[i].value == product.id) {
                        select.selectedIndex = i;
                        optionExists = true;
                        break;
                    }
                }
                
                if(optionExists) {
                    resultDiv.innerHTML = '<span class="text-success"><i class="fas fa-check"></i> Prodotto selezionato: ' + product.name + '</span>';
                    document.getElementById('barcodeInput').value = '';
                    document.querySelector('input[name="quantity"]').focus();
                } else {
                    resultDiv.innerHTML = '<span class="text-warning"><i class="fas fa-exclamation-triangle"></i> Prodotto trovato (' + product.name + ') ma non presente in lista (lista limitata?).</span>';
                }
            } else {
                resultDiv.innerHTML = '<span class="text-danger"><i class="fas fa-times"></i> Prodotto non trovato.</span>';
            }
        })
        .catch(err => {
            console.error(err);
            resultDiv.innerHTML = '<span class="text-danger">Errore di rete.</span>';
        });
}
</script>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Tipo Movimento *</label>
                        <select name="type" class="form-select" required>
                            <option value="in">Carico (Entrata Merce)</option>
                            <option value="out">Scarico (Uscita/Danneggiato)</option>
                            <option value="return">Reso Cliente</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Quantità *</label>
                        <input type="number" name="quantity" class="form-control" min="1" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Riferimento Documento (DDT / Fattura)</label>
                    <input type="text" name="document_ref" class="form-control" placeholder="Es. DDT n. 123/2025">
                </div>

                <div class="mb-3">
                    <label class="form-label">Note</label>
                    <textarea name="notes" class="form-control" rows="2"></textarea>
                </div>

                <div class="d-grid">
                    <button type="submit" class="btn btn-primary btn-lg">Registra Movimento</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../layout/footer.php'; ?>

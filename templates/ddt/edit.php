<?php require_once __DIR__ . '/../layout/header.php'; ?>
<?php require_once __DIR__ . '/../layout/sidebar.php'; ?>

<div class="content flex-grow-1 overflow-auto">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">Modifica DDT: <?php echo htmlspecialchars($ddt['number']); ?></h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <a href="<?php echo APP_URL; ?>/index.php?page=ddt&action=detail&id=<?php echo $ddt['id']; ?>" class="btn btn-sm btn-secondary">
                <i class="fas fa-arrow-left"></i> Annulla
            </a>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <form action="<?php echo APP_URL; ?>/index.php?page=ddt&action=update&id=<?php echo $ddt['id']; ?>" method="POST">
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Numero DDT</label>
                        <input type="text" name="number" class="form-control" value="<?php echo htmlspecialchars($ddt['number']); ?>" required>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Data</label>
                        <input type="date" name="date" class="form-control" value="<?php echo date('Y-m-d', strtotime($ddt['date'])); ?>" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Cliente / Intestatario</label>
                        <div class="input-group">
                            <input type="text" name="customer_name" id="customer_name" class="form-control" required placeholder="Cerca cliente o scrivi nome..." autocomplete="off" value="<?php echo htmlspecialchars($ddt['customer_name']); ?>">
                            <input type="hidden" name="customer_id" id="customer_id" value="<?php echo $ddt['customer_id']; ?>">
                            <button type="button" class="btn btn-outline-secondary" onclick="resetCustomer()"><i class="fas fa-times"></i></button>
                        </div>
                        <div id="customerResults" class="list-group position-absolute" style="z-index: 1000; width: 95%; display:none;"></div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-12 mb-3">
                        <label class="form-label">Destinazione Merce</label>
                        <input type="text" name="destination" id="destination" class="form-control" required placeholder="Indirizzo di consegna" value="<?php echo htmlspecialchars($ddt['destination']); ?>">
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-12 mb-3">
                        <label class="form-label">Note</label>
                        <textarea name="notes" class="form-control" rows="2"><?php echo htmlspecialchars($ddt['notes']); ?></textarea>
                    </div>
                </div>

                <h4 class="mt-4 mb-3">Articoli</h4>
                <div class="table-responsive">
                    <table class="table table-bordered align-middle" id="itemsTable">
                        <thead class="table-light">
                            <tr>
                                <th>Prodotto</th>
                                <th width="150">Quantità</th>
                                <th width="50"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($items as $index => $item): ?>
                            <tr>
                                <td>
                                    <select name="items[<?php echo $index; ?>][product_id]" class="form-control product-select" required>
                                        <option value="">Seleziona prodotto...</option>
                                        <?php foreach ($products as $p): ?>
                                        <option value="<?php echo $p['id']; ?>" <?php echo ($p['id'] == $item['product_id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($p['barcode'] . ' - ' . $p['name'] . ' (Giac: ' . $p['stock_quantity'] . ')'); ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                                <td>
                                    <input type="number" name="items[<?php echo $index; ?>][qty]" class="form-control" min="1" required value="<?php echo $item['quantity']; ?>">
                                </td>
                                <td>
                                    <button type="button" class="btn btn-danger btn-sm remove-row"><i class="fas fa-trash"></i></button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            
                            <!-- Empty row template if no items (should not happen usually) -->
                            <?php if (empty($items)): ?>
                            <tr>
                                <td>
                                    <select name="items[0][product_id]" class="form-control product-select" required>
                                        <option value="">Seleziona prodotto...</option>
                                        <?php foreach ($products as $p): ?>
                                        <option value="<?php echo $p['id']; ?>">
                                            <?php echo htmlspecialchars($p['barcode'] . ' - ' . $p['name'] . ' (Giac: ' . $p['stock_quantity'] . ')'); ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                                <td>
                                    <input type="number" name="items[0][qty]" class="form-control" min="1" required>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-danger btn-sm remove-row"><i class="fas fa-trash"></i></button>
                                </td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                
                <button type="button" class="btn btn-secondary mb-3" id="addRow">
                    <i class="fas fa-plus"></i> Aggiungi Riga
                </button>
                
                <hr class="my-4">
                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="fas fa-save me-2"></i> Aggiorna DDT
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
let currentCustomerId = <?php echo $ddt['customer_id'] ? $ddt['customer_id'] : 'null'; ?>;

function resetCustomer() {
    document.getElementById('customer_name').value = '';
    document.getElementById('customer_id').value = '';
    document.getElementById('customerResults').style.display = 'none';
    currentCustomerId = null;
}

document.addEventListener('DOMContentLoaded', function() {
    const customerInput = document.getElementById('customer_name');
    const customerResults = document.getElementById('customerResults');

    customerInput.addEventListener('input', function() {
        const query = this.value;
        if (query.length < 2) {
            customerResults.style.display = 'none';
            return;
        }

        fetch('<?php echo APP_URL; ?>/index.php?page=api_customers_search&q=' + encodeURIComponent(query))
            .then(response => response.json())
            .then(data => {
                customerResults.innerHTML = '';
                if (data.length > 0) {
                    data.forEach(customer => {
                        const a = document.createElement('a');
                        a.href = '#';
                        a.className = 'list-group-item list-group-item-action';
                        a.innerHTML = `<strong>${customer.name}</strong> <small class='text-muted'>${customer.fiscal_code || ''} ${customer.vat_number || ''}</small>`;
                        a.onclick = function(e) {
                            e.preventDefault();
                            selectCustomer(customer);
                        };
                        customerResults.appendChild(a);
                    });
                    customerResults.style.display = 'block';
                } else {
                    customerResults.style.display = 'none';
                }
            });
    });

    // Chiudi lista se clicco fuori
    document.addEventListener('click', function(e) {
        if (e.target !== customerInput && e.target !== customerResults) {
            customerResults.style.display = 'none';
        }
    });
});

function selectCustomer(customer) {
    document.getElementById('customer_name').value = customer.name;
    document.getElementById('customer_id').value = customer.id;
    document.getElementById('customerResults').style.display = 'none';
    
    // Auto-fill destination if available
    if(customer.address) {
        document.getElementById('destination').value = customer.address + (customer.city ? ', ' + customer.city : '');
    }
    
    currentCustomerId = customer.id;
}
</script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    let rowCount = <?php echo count($items) > 0 ? count($items) : 1; ?>;
    
    const tableBody = document.querySelector('#itemsTable tbody');
    // Use the first row as template (assuming at least one row exists)
    // If we have existing items, the first row is populated. We need a clean template.
    // Hack: Create a hidden template or clean the cloned row.
    
    document.getElementById('addRow').addEventListener('click', function() {
        const firstRow = tableBody.rows[0];
        const newRow = firstRow.cloneNode(true);
        
        // Clean values
        newRow.innerHTML = newRow.innerHTML.replace(/items\[\d+\]/g, 'items[' + rowCount + ']');
        const inputs = newRow.querySelectorAll('input');
        inputs.forEach(input => input.value = '');
        const selects = newRow.querySelectorAll('select');
        selects.forEach(select => {
             select.value = '';
             // Remove 'selected' attribute from options in the clone
             const options = select.querySelectorAll('option');
             options.forEach(opt => opt.removeAttribute('selected'));
        });
        
        tableBody.appendChild(newRow);
        rowCount++;
    });

    tableBody.addEventListener('click', function(e) {
        if (e.target.closest('.remove-row')) {
            if (tableBody.rows.length > 1) {
                e.target.closest('tr').remove();
            }
        }
    });
});
</script>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>

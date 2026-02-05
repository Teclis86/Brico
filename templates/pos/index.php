<?php include __DIR__ . '/../layout/header.php'; ?>
<!-- Nascondiamo la sidebar standard nel POS per avere più spazio, o la teniamo ridotta -->
<style>
    .pos-container { height: calc(100vh - 50px); }
    .product-list { height: 60vh; overflow-y: auto; }
    .total-box { font-size: 2.5rem; font-weight: bold; text-align: right; }
</style>

<div class="d-flex flex-column w-100 p-3 pos-container">
    <div class="d-flex justify-content-between mb-3">
        <h3><i class="fas fa-cash-register"></i> Punto Vendita</h3>
        <a href="?page=dashboard" class="btn btn-outline-secondary">Esci dal POS</a>
    </div>

    <div class="row h-100">
        <!-- Sinistra: Carrello -->
        <div class="col-md-8 d-flex flex-column">
            
            <!-- Selezione Cliente -->
            <div class="card mb-3 shadow-sm">
                <div class="card-header bg-info text-white">
                    <i class="fas fa-user"></i> Cliente
                </div>
                <div class="card-body">
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                        <input type="text" id="customer_name" class="form-control" placeholder="Cerca cliente (Nome, Tel, P.IVA)..." autocomplete="off">
                        <input type="hidden" id="customer_id">
                        <button class="btn btn-outline-secondary" type="button" onclick="resetCustomer()">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <div id="customerResults" class="list-group position-absolute w-100" style="z-index: 1000; display:none;"></div>
                    <small class="text-muted" id="customerInfoDisplay"></small>
                </div>
            </div>

            <div class="card flex-grow-1 shadow-sm">
                <div class="card-header bg-dark text-white d-flex">
                    <input type="text" id="barcodeInput" class="form-control form-control-lg me-2" placeholder="Scansiona Barcode o Cerca Prodotto (Invio)" autofocus>
                    <button class="btn btn-primary" onclick="searchProduct()"><i class="fas fa-search"></i></button>
                </div>
                <div class="card-body p-0 product-list">
                    <table class="table table-striped mb-0">
                        <thead>
                            <tr>
                                <th>Prodotto</th>
                                <th width="100">Prezzo</th>
                                <th width="100">Qta</th>
                                <th width="100">Subtotale</th>
                                <th width="50"></th>
                            </tr>
                        </thead>
                        <tbody id="cartTable">
                            <!-- Items via JS -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Destra: Totali e Azioni -->
        <div class="col-md-4 d-flex flex-column">
            <div class="card bg-light shadow-sm mb-3">
                <div class="card-body">
                    <h5 class="text-muted">Totale da Pagare</h5>
                    <div class="total-box text-primary" id="totalDisplay">€ 0,00</div>
                </div>
            </div>

            <div class="card shadow-sm flex-grow-1">
                <div class="card-body d-flex flex-column gap-2">
                    <button class="btn btn-success btn-lg py-4" onclick="processPayment('cash')">
                        <i class="fas fa-money-bill-wave fa-2x d-block mb-2"></i> CONTANTI
                    </button>
                    <button class="btn btn-info btn-lg py-4 text-white" onclick="processPayment('card')">
                        <i class="fas fa-credit-card fa-2x d-block mb-2"></i> CARTA / POS
                    </button>
                    <button class="btn btn-danger mt-auto" onclick="clearCart()">Annulla Scontrino</button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Risultati Ricerca -->
<div class="modal fade" id="searchModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Seleziona Prodotto</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="list-group" id="searchResults"></div>
            </div>
        </div>
    </div>
</div>

<script>
let cart = [];
let currentCustomerId = null;

// Gestione Cliente
function resetCustomer() {
    document.getElementById('customer_name').value = '';
    document.getElementById('customer_id').value = '';
    document.getElementById('customerInfoDisplay').innerText = '';
    document.getElementById('customerResults').style.display = 'none';
    currentCustomerId = null;
}

document.addEventListener('DOMContentLoaded', function() {
    // Autocomplete Clienti
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

    // Barcode Listener
    document.getElementById('barcodeInput').addEventListener('keypress', function (e) {
        if (e.key === 'Enter') {
            searchProduct();
        }
    });
});

function selectCustomer(customer) {
    document.getElementById('customer_name').value = customer.name;
    document.getElementById('customer_id').value = customer.id;
    document.getElementById('customerInfoDisplay').innerText = `Tel: ${customer.phone || '-'} | Indirizzo: ${customer.address || '-'}`;
    document.getElementById('customerResults').style.display = 'none';
    currentCustomerId = customer.id;
}

function searchProduct() {
    const query = document.getElementById('barcodeInput').value;
    if (!query) return;

    fetch('?page=api_pos_search&q=' + encodeURIComponent(query))
        .then(res => res.json())
        .then(data => {
            if (data.length === 0) {
                alert('Prodotto non trovato!');
            } else if (data.length === 1) {
                addToCart(data[0]);
                document.getElementById('barcodeInput').value = '';
            } else {
                // Mostra modale scelta
                const list = document.getElementById('searchResults');
                list.innerHTML = '';
                data.forEach(p => {
                    const item = document.createElement('a');
                    item.className = 'list-group-item list-group-item-action';
                    item.innerHTML = `<strong>${p.name}</strong> <br> <small>${p.barcode} - € ${p.price_sell}</small>`;
                    item.onclick = () => {
                        addToCart(p);
                        const modal = bootstrap.Modal.getInstance(document.getElementById('searchModal'));
                        modal.hide();
                        document.getElementById('barcodeInput').value = '';
                    };
                    list.appendChild(item);
                });
                const modal = new bootstrap.Modal(document.getElementById('searchModal'));
                modal.show();
            }
        });
}

function addToCart(product) {
    const existing = cart.find(i => i.id === product.id);
    if (existing) {
        existing.qty++;
    } else {
        cart.push({
            id: product.id,
            name: product.name,
            price: parseFloat(product.price_sell),
            qty: 1
        });
    }
    renderCart();
}

function removeFromCart(index) {
    cart.splice(index, 1);
    renderCart();
}

function renderCart() {
    const tbody = document.getElementById('cartTable');
    tbody.innerHTML = '';
    let total = 0;

    cart.forEach((item, index) => {
        const subtotal = item.price * item.qty;
        total += subtotal;
        
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>${item.name}</td>
            <td>€ ${item.price.toFixed(2)}</td>
            <td>
                <input type="number" min="1" value="${item.qty}" class="form-control form-control-sm" style="width: 70px" onchange="updateQty(${index}, this.value)">
            </td>
            <td>€ ${subtotal.toFixed(2)}</td>
            <td><button class="btn btn-sm btn-danger" onclick="removeFromCart(${index})"><i class="fas fa-times"></i></button></td>
        `;
        tbody.appendChild(tr);
    });

    document.getElementById('totalDisplay').innerText = '€ ' + total.toFixed(2);
}

function updateQty(index, newQty) {
    if (newQty < 1) return;
    cart[index].qty = parseInt(newQty);
    renderCart();
}

function clearCart() {
    if(confirm('Svuotare il carrello?')) {
        cart = [];
        renderCart();
    }
}

function processPayment(method) {
    if (cart.length === 0) {
        alert('Carrello vuoto!');
        return;
    }

    if (!confirm('Confermare pagamento ' + (method === 'cash' ? 'CONTANTI' : 'CARTA') + '?')) return;

    fetch('?page=api_pos_process', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ 
            items: cart, 
            paymentMethod: method,
            customerId: currentCustomerId 
        })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alert('Vendita Registrata! Scontrino N. ' + data.receipt);
            cart = [];
            renderCart();
            // Reset cliente dopo vendita? Di solito sì.
            resetCustomer();
        } else {
            alert('Errore: ' + data.message);
        }
    })
    .catch(err => alert('Errore di comunicazione'));
}
</script>

<?php include __DIR__ . '/../layout/footer.php'; ?>

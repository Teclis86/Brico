<?php require_once __DIR__ . '/../layout/header.php'; ?>
<?php require_once __DIR__ . '/../layout/sidebar.php'; ?>

<div class="content flex-grow-1 overflow-auto">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">Scheda Cliente: <?php echo htmlspecialchars($customer['name']); ?></h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <a href="<?php echo APP_URL; ?>/index.php?page=customers" class="btn btn-sm btn-secondary">
                <i class="fas fa-arrow-left"></i> Torna alla lista
            </a>
        </div>
    </div>

<div class="row">
    <!-- Info Cliente -->
    <div class="col-md-4 mb-4">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-light">Dati Anagrafici</div>
            <div class="card-body">
                <p><strong>Email:</strong> <?php echo htmlspecialchars($customer['email'] ?? '-'); ?></p>
                <p><strong>Telefono:</strong> <?php echo htmlspecialchars($customer['phone'] ?? '-'); ?></p>
                <p><strong>Indirizzo:</strong><br><?php echo nl2br(htmlspecialchars($customer['address'] ?? '-')); ?></p>
                <p><strong>P.IVA:</strong> <?php echo htmlspecialchars($customer['vat_number'] ?? '-'); ?></p>
                <p><strong>Cod. Fiscale:</strong> <?php echo htmlspecialchars($customer['fiscal_code'] ?? '-'); ?></p>
                <hr>
                <h4 class="text-success">Totale Speso: € <?php echo number_format($totalSpent ?? 0, 2, ',', '.'); ?></h4>
            </div>
        </div>
    </div>

    <!-- Storico -->
    <div class="col-md-8 mb-4">
        <ul class="nav nav-tabs" id="myTab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="sales-tab" data-bs-toggle="tab" data-bs-target="#sales" type="button" role="tab">Scontrini / Vendite</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="ddt-tab" data-bs-toggle="tab" data-bs-target="#ddt" type="button" role="tab">DDT</button>
            </li>
        </ul>
        
        <div class="tab-content p-3 border border-top-0 bg-white" id="myTabContent">
            <!-- Tab Vendite -->
            <div class="tab-pane fade show active" id="sales" role="tabpanel">
                <?php if (empty($sales)): ?>
                    <p class="text-muted">Nessuna vendita registrata.</p>
                <?php else: ?>
                    <table class="table table-sm table-hover">
                        <thead>
                            <tr>
                                <th>Data</th>
                                <th>Scontrino</th>
                                <th>Totale</th>
                                <th>Pagamento</th>
                                <th>Dettagli</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($sales as $s): ?>
                            <tr>
                                <td><?php echo date('d/m/Y', strtotime($s['created_at'])); ?></td>
                                <td><?php echo htmlspecialchars($s['receipt_number']); ?></td>
                                <td class="fw-bold">€ <?php echo number_format($s['total_amount'], 2); ?></td>
                                <td><?php echo ucfirst($s['payment_method']); ?></td>
                                <td>
                                    <a href="<?php echo APP_URL; ?>/index.php?page=sales&action=detail&id=<?php echo $s['id']; ?>" class="btn btn-sm btn-outline-primary">Vedi</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>

            <!-- Tab DDT -->
            <div class="tab-pane fade" id="ddt" role="tabpanel">
                <?php if (empty($ddts)): ?>
                    <p class="text-muted">Nessun DDT registrato.</p>
                <?php else: ?>
                    <table class="table table-sm table-hover">
                        <thead>
                            <tr>
                                <th>Data</th>
                                <th>Numero</th>
                                <th>Destinazione</th>
                                <th>Azioni</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($ddts as $d): ?>
                            <tr>
                                <td><?php echo date('d/m/Y', strtotime($d['date'])); ?></td>
                                <td><?php echo htmlspecialchars($d['number']); ?></td>
                                <td><?php echo htmlspecialchars($d['destination']); ?></td>
                                <td>
                                    <a href="<?php echo APP_URL; ?>/index.php?page=ddt&action=print&id=<?php echo $d['id']; ?>" class="btn btn-sm btn-outline-secondary" target="_blank">Stampa</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

</div>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>

<?php require_once __DIR__ . '/../layout/header.php'; ?>
<?php require_once __DIR__ . '/../layout/sidebar.php'; ?>

<div class="content flex-grow-1 overflow-auto">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">Gestione DDT</h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <a href="<?php echo APP_URL; ?>/index.php?page=ddt&action=create" class="btn btn-sm btn-primary">
                <i class="fas fa-plus"></i> Nuovo DDT
            </a>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            
            <!-- Barcode Search Box -->
            <div class="mb-4 bg-light p-3 rounded border">
                <form action="<?php echo APP_URL; ?>/index.php" method="GET" class="d-flex align-items-center gap-2">
                    <input type="hidden" name="page" value="ddt">
                    <input type="hidden" name="action" value="search_barcode">
                    <i class="fas fa-barcode fa-2x text-muted"></i>
                    <input type="text" name="barcode" class="form-control form-control-lg" placeholder="Spara qui il barcode del DDT per aprirlo..." autofocus>
                    <button type="submit" class="btn btn-primary">Cerca</button>
                </form>
            </div>

            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>Numero</th>
                            <th>Data</th>
                            <th>Stato</th>
                            <th>Cliente</th>
                            <th>Destinazione</th>
                            <th>Creato da</th>
                            <th>Azioni</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(empty($ddts)): ?>
                            <tr><td colspan="7" class="text-center">Nessun DDT trovato.</td></tr>
                        <?php else: ?>
                            <?php foreach ($ddts as $ddt): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($ddt['number']); ?></strong></td>
                                <td><?php echo date('d/m/Y', strtotime($ddt['date'])); ?></td>
                                <td>
                                    <?php if(($ddt['status'] ?? 'confirmed') === 'cancelled'): ?>
                                        <span class="badge bg-danger">ANNULLATO</span>
                                    <?php else: ?>
                                        <span class="badge bg-success">CONFERMATO</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($ddt['customer_name']); ?></td>
                                <td><?php echo htmlspecialchars($ddt['destination']); ?></td>
                                <td><span class="badge bg-secondary"><?php echo htmlspecialchars($ddt['username']); ?></span></td>
                                <td>
                                    <a href="<?php echo APP_URL; ?>/index.php?page=ddt&action=detail&id=<?php echo $ddt['id']; ?>" class="btn btn-sm btn-info text-white">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="<?php echo APP_URL; ?>/index.php?page=ddt&action=print&id=<?php echo $ddt['id']; ?>" class="btn btn-sm btn-outline-secondary" target="_blank">
                                        <i class="fas fa-print"></i>
                                    </a>
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

<?php require_once __DIR__ . '/../layout/footer.php'; ?>

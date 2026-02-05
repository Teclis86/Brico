<?php require_once __DIR__ . '/../layout/header.php'; ?>
<?php require_once __DIR__ . '/../layout/sidebar.php'; ?>

<div class="content flex-grow-1 overflow-auto">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">Gestione Clienti</h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <a href="<?php echo APP_URL; ?>/index.php?page=customers&action=create" class="btn btn-sm btn-primary">
                <i class="fas fa-plus"></i> Nuovo Cliente
            </a>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <div class="mb-3">
                <form action="" method="GET" class="d-flex gap-2">
                    <input type="hidden" name="page" value="customers">
                    <input type="text" name="q" class="form-control me-2" placeholder="Cerca cliente..." value="<?php echo htmlspecialchars($search); ?>">
                    <button type="submit" class="btn btn-secondary">Cerca</button>
                </form>
            </div>

            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>Nome / Ragione Sociale</th>
                            <th>Telefono</th>
                            <th>Email</th>
                            <th>P.IVA / CF</th>
                            <th>Azioni</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(empty($customers)): ?>
                            <tr><td colspan="5" class="text-center">Nessun cliente trovato.</td></tr>
                        <?php else: ?>
                            <?php foreach ($customers as $c): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($c['name'] ?? ''); ?></strong></td>
                                <td><?php echo htmlspecialchars($c['phone'] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($c['email'] ?? ''); ?></td>
                                <td>
                                    <?php echo htmlspecialchars($c['vat_number'] ?? ''); ?>
                                    <?php if(!empty($c['vat_number']) && !empty($c['fiscal_code'])) echo ' / '; ?>
                                    <?php echo htmlspecialchars($c['fiscal_code'] ?? ''); ?>
                                </td>
                                <td>
                                    <a href="<?php echo APP_URL; ?>/index.php?page=customers&action=detail&id=<?php echo $c['id']; ?>" class="btn btn-sm btn-info text-white">
                                        <i class="fas fa-eye"></i> Scheda
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

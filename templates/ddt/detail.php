<?php require_once __DIR__ . '/../layout/header.php'; ?>
<?php require_once __DIR__ . '/../layout/sidebar.php'; ?>

<div class="content flex-grow-1 overflow-auto">
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">Dettaglio DDT: <?php echo htmlspecialchars($ddt['number']); ?></h1>
        <div class="btn-toolbar mb-2 mb-md-0 gap-2">
            <a href="<?php echo APP_URL; ?>/index.php?page=ddt" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Torna alla lista
            </a>
            
            <?php if ($ddt['status'] !== 'cancelled'): ?>
                <a href="<?php echo APP_URL; ?>/index.php?page=ddt&action=print&id=<?php echo $ddt['id']; ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                    <i class="fas fa-print"></i> Stampa
                </a>
                <a href="<?php echo APP_URL; ?>/index.php?page=ddt&action=edit&id=<?php echo $ddt['id']; ?>" class="btn btn-sm btn-outline-warning">
                    <i class="fas fa-edit"></i> Modifica
                </a>
                <form action="<?php echo APP_URL; ?>/index.php?page=ddt&action=cancel&id=<?php echo $ddt['id']; ?>" method="POST" onsubmit="return confirm('Sei sicuro di voler ANNULLARE questo DDT? Le quantità verranno ripristinate a magazzino.');" style="display:inline;">
                    <button type="submit" class="btn btn-sm btn-danger">
                        <i class="fas fa-ban"></i> Annulla DDT
                    </button>
                </form>
            <?php else: ?>
                <span class="badge bg-danger fs-6">ANNULLATO</span>
                <!-- Permetti stampa anche se annullato per vedere il watermark -->
                <a href="<?php echo APP_URL; ?>/index.php?page=ddt&action=print&id=<?php echo $ddt['id']; ?>" target="_blank" class="btn btn-sm btn-outline-secondary">
                    <i class="fas fa-print"></i> Stampa (Copia Annullata)
                </a>
            <?php endif; ?>
        </div>
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-header bg-light">
            <h5 class="mb-0">Informazioni Testata</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3">
                    <strong>Numero:</strong><br>
                    <?php echo htmlspecialchars($ddt['number']); ?>
                </div>
                <div class="col-md-3">
                    <strong>Data:</strong><br>
                    <?php echo date('d/m/Y', strtotime($ddt['date'])); ?>
                </div>
                <div class="col-md-3">
                    <strong>Cliente:</strong><br>
                    <?php echo htmlspecialchars($ddt['customer_name']); ?>
                </div>
                <div class="col-md-3">
                    <strong>Stato:</strong><br>
                    <?php if($ddt['status'] == 'cancelled'): ?>
                        <span class="text-danger fw-bold">ANNULLATO</span>
                    <?php else: ?>
                        <span class="text-success fw-bold">CONFERMATO</span>
                    <?php endif; ?>
                </div>
            </div>
            <div class="row mt-3">
                <div class="col-md-6">
                    <strong>Destinazione:</strong><br>
                    <?php echo htmlspecialchars($ddt['destination']); ?>
                </div>
                <div class="col-md-6">
                    <strong>Note:</strong><br>
                    <?php echo nl2br(htmlspecialchars($ddt['notes'])); ?>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header bg-light">
            <h5 class="mb-0">Righe Documento</h5>
        </div>
        <div class="table-responsive">
            <table class="table table-striped mb-0">
                <thead>
                    <tr>
                        <th>Barcode</th>
                        <th>Prodotto</th>
                        <th class="text-end">Quantità</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $item): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($item['barcode']); ?></td>
                        <td><?php echo htmlspecialchars($item['name']); ?></td>
                        <td class="text-end"><?php echo $item['quantity']; ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>

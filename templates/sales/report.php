<?php require_once __DIR__ . '/../layout/header.php'; ?>
<?php require_once __DIR__ . '/../layout/sidebar.php'; ?>

<div class="content flex-grow-1 overflow-auto">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">Report Vendite e Marginalità</h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <form class="row g-2 align-items-center" action="<?php echo APP_URL; ?>/index.php" method="GET">
                <input type="hidden" name="page" value="sales">
                <input type="hidden" name="action" value="report">
                
                <div class="col-auto">
                    <label class="col-form-label">Da:</label>
                </div>
                <div class="col-auto">
                    <input type="date" name="start" class="form-control form-control-sm" value="<?php echo $startDate; ?>">
                </div>
                
                <div class="col-auto">
                    <label class="col-form-label">A:</label>
                </div>
                <div class="col-auto">
                    <input type="date" name="end" class="form-control form-control-sm" value="<?php echo $endDate; ?>">
                </div>
                
                <div class="col-auto">
                    <button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-filter"></i> Filtra</button>
                </div>
            </form>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card text-white bg-primary mb-3 shadow-sm h-100">
                <div class="card-header"><i class="fas fa-euro-sign"></i> Incasso Totale (Lordo)</div>
                <div class="card-body">
                    <h3 class="card-title">€ <?php echo number_format($totalRevenue, 2, ',', '.'); ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-danger mb-3 shadow-sm h-100">
                <div class="card-header"><i class="fas fa-box-open"></i> Costo Merce (Stimato)</div>
                <div class="card-body">
                    <h3 class="card-title">€ <?php echo number_format($totalCost, 2, ',', '.'); ?></h3>
                    <small>Imponibile (no IVA)</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-success mb-3 shadow-sm h-100">
                <div class="card-header"><i class="fas fa-chart-line"></i> Margine Operativo</div>
                <div class="card-body">
                    <h3 class="card-title">€ <?php echo number_format($totalMargin, 2, ',', '.'); ?></h3>
                    <small>Incasso - Costo</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-info mb-3 shadow-sm h-100">
                <div class="card-header"><i class="fas fa-percentage"></i> Marginalità %</div>
                <div class="card-body">
                    <h3 class="card-title"><?php echo number_format($marginPercent, 1, ',', '.'); ?>%</h3>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header bg-light">
            <h5 class="mb-0"><i class="fas fa-calendar-alt"></i> Dettaglio Giornaliero</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>Data</th>
                            <th class="text-center">Transazioni</th>
                            <th class="text-end">Incasso</th>
                            <th class="text-end">Costo</th>
                            <th class="text-end">Margine</th>
                            <th class="text-end">%</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($dailyStats as $day): 
                            $margin = $day['revenue'] - $day['cost'];
                            $perc = $day['revenue'] > 0 ? ($margin / $day['revenue'] * 100) : 0;
                        ?>
                        <tr>
                            <td><?php echo date('d/m/Y', strtotime($day['date'])); ?></td>
                            <td class="text-center"><span class="badge bg-secondary"><?php echo $day['transactions']; ?></span></td>
                            <td class="text-end">€ <?php echo number_format($day['revenue'], 2, ',', '.'); ?></td>
                            <td class="text-end">€ <?php echo number_format($day['cost'], 2, ',', '.'); ?></td>
                            <td class="text-end fw-bold <?php echo $margin >= 0 ? 'text-success' : 'text-danger'; ?>">
                                € <?php echo number_format($margin, 2, ',', '.'); ?>
                            </td>
                            <td class="text-end">
                                <div class="progress" style="height: 20px;">
                                    <div class="progress-bar <?php echo $perc > 30 ? 'bg-success' : ($perc > 15 ? 'bg-warning' : 'bg-danger'); ?>" 
                                         role="progressbar" 
                                         style="width: <?php echo min(100, max(0, $perc)); ?>%;" 
                                         aria-valuenow="<?php echo $perc; ?>" 
                                         aria-valuemin="0" 
                                         aria-valuemax="100">
                                        <?php echo number_format($perc, 1, ',', '.'); ?>%
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>

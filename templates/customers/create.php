<?php require_once __DIR__ . '/../layout/header.php'; ?>
<?php require_once __DIR__ . '/../layout/sidebar.php'; ?>

<div class="content flex-grow-1 overflow-auto">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">Nuovo Cliente</h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <a href="<?php echo APP_URL; ?>/index.php?page=customers" class="btn btn-sm btn-secondary">
                <i class="fas fa-arrow-left"></i> Torna alla lista
            </a>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <form action="<?php echo APP_URL; ?>/index.php?page=customers&action=create" method="POST">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Nome / Ragione Sociale *</label>
                        <input type="text" name="name" class="form-control" required autofocus>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control">
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Telefono</label>
                        <input type="text" name="phone" class="form-control">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Partita IVA</label>
                        <input type="text" name="vat_number" class="form-control">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Codice Fiscale</label>
                        <input type="text" name="fiscal_code" class="form-control">
                    </div>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Indirizzo Completo</label>
                    <textarea name="address" class="form-control" rows="3"></textarea>
                </div>

                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="fas fa-save"></i> Salva Cliente
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>

<?php include __DIR__ . '/../layout/header.php'; ?>
<?php include __DIR__ . '/../layout/sidebar.php'; ?>

<div class="content flex-grow-1 overflow-auto">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2"><?= isset($user) ? 'Modifica Utente' : 'Nuovo Utente' ?></h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <a href="?page=users" class="btn btn-sm btn-secondary">
                <i class="fas fa-arrow-left"></i> Torna alla lista
            </a>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <form action="?page=<?= isset($user) ? 'users_update' : 'users_store' ?>" method="POST">
                <?php if(isset($user)): ?>
                    <input type="hidden" name="id" value="<?= $user['id'] ?>">
                <?php endif; ?>

                <div class="mb-3">
                    <label class="form-label">Username *</label>
                    <input type="text" name="username" class="form-control" required value="<?= $user['username'] ?? '' ?>">
                </div>

                <div class="mb-3">
                    <label class="form-label">Email *</label>
                    <input type="email" name="email" class="form-control" required value="<?= $user['email'] ?? '' ?>">
                </div>

                <div class="mb-3">
                    <label class="form-label">Password <?= isset($user) ? '(Lasciare vuoto per non cambiare)' : '*' ?></label>
                    <input type="password" name="password" class="form-control" <?= isset($user) ? '' : 'required' ?>>
                </div>

                <div class="mb-3">
                    <label class="form-label">Ruolo *</label>
                    <select name="role" class="form-select" required>
                        <option value="cashier" <?= (isset($user) && $user['role'] == 'cashier') ? 'selected' : '' ?>>Cassiere</option>
                        <option value="manager" <?= (isset($user) && $user['role'] == 'manager') ? 'selected' : '' ?>>Manager</option>
                        <option value="admin" <?= (isset($user) && $user['role'] == 'admin') ? 'selected' : '' ?>>Admin</option>
                    </select>
                </div>

                <div class="d-grid">
                    <button type="submit" class="btn btn-primary btn-lg"><?= isset($user) ? 'Aggiorna Utente' : 'Crea Utente' ?></button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../layout/footer.php'; ?>

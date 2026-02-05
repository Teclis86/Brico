<?php include __DIR__ . '/../layout/header.php'; ?>
<?php include __DIR__ . '/../layout/sidebar.php'; ?>

<div class="content flex-grow-1 overflow-auto">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">Gestione Utenti</h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <a href="?page=users_create" class="btn btn-sm btn-primary">
                <i class="fas fa-user-plus"></i> Nuovo Utente
            </a>
        </div>
    </div>

    <?php if(isset($_SESSION['success'])): ?>
        <div class="alert alert-success"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
    <?php endif; ?>
    <?php if(isset($_SESSION['error'])): ?>
        <div class="alert alert-danger"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
    <?php endif; ?>

    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Ruolo</th>
                            <th>Stato</th>
                            <th>Azioni</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($users as $u): ?>
                            <tr>
                                <td><?= $u['id'] ?></td>
                                <td><strong><?= htmlspecialchars($u['username']) ?></strong></td>
                                <td><?= htmlspecialchars($u['email']) ?></td>
                                <td>
                                    <?php if($u['role'] == 'admin'): ?>
                                        <span class="badge bg-danger">Admin</span>
                                    <?php elseif($u['role'] == 'manager'): ?>
                                        <span class="badge bg-warning text-dark">Manager</span>
                                    <?php else: ?>
                                        <span class="badge bg-info">Cassiere</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if($u['active']): ?>
                                        <span class="badge bg-success">Attivo</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Disattivato</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="?page=users_edit&id=<?= $u['id'] ?>" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i></a>
                                    <?php if($u['id'] != $_SESSION['user_id']): ?>
                                        <form action="?page=users_delete" method="POST" class="d-inline" onsubmit="return confirm('Eliminare questo utente?');">
                                            <input type="hidden" name="id" value="<?= $u['id'] ?>">
                                            <button type="submit" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                                        </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../layout/footer.php'; ?>

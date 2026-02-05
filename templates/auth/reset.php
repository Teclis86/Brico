<?php include __DIR__ . '/../layout/header.php'; ?>

<div class="container d-flex justify-content-center align-items-center" style="height: 100vh;">
    <div class="card shadow" style="width: 400px;">
        <div class="card-body">
            <h3 class="card-title text-center mb-4">Nuova Password</h3>
            
            <form action="<?= APP_URL ?>/?page=auth_update_password" method="POST">
                <input type="hidden" name="token" value="<?= htmlspecialchars($_GET['token'] ?? '') ?>">
                
                <div class="mb-3">
                    <label class="form-label">Nuova Password</label>
                    <input type="password" name="password" class="form-control" required minlength="6">
                </div>
                <div class="d-grid">
                    <button type="submit" class="btn btn-primary">Salva Password</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../layout/footer.php'; ?>

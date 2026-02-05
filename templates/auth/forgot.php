<?php include __DIR__ . '/../layout/header.php'; ?>

<div class="container d-flex justify-content-center align-items-center" style="height: 100vh;">
    <div class="card shadow" style="width: 400px;">
        <div class="card-body">
            <h3 class="card-title text-center mb-4">Recupero Password</h3>
            
            <?php if(isset($_SESSION['error'])): ?>
                <div class="alert alert-danger"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
            <?php endif; ?>
            <?php if(isset($_SESSION['success'])): ?>
                <div class="alert alert-success"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
            <?php endif; ?>

            <form action="<?= APP_URL ?>/?page=auth_send_reset" method="POST">
                <div class="mb-3">
                    <p class="small text-muted">Inserisci la tua email. Ti invieremo un link valido per 1 ora per reimpostare la password.</p>
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" required>
                </div>
                <div class="d-grid">
                    <button type="submit" class="btn btn-primary">Invia Link di Reset</button>
                </div>
                <div class="text-center mt-3">
                    <a href="?page=login" class="small">Torna al Login</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../layout/footer.php'; ?>

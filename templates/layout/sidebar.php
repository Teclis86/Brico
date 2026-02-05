<div class="sidebar p-3 d-flex flex-column flex-shrink-0 text-white bg-dark" style="width: 250px;">
    <a href="?page=dashboard" class="d-flex align-items-center mb-3 mb-md-0 me-md-auto text-white text-decoration-none">
        <span class="fs-4">Brico Panel</span>
    </a>
    <hr>
    <ul class="nav nav-pills flex-column mb-auto">
        <li class="nav-item">
            <a href="?page=dashboard" class="nav-link text-white <?= (!isset($_GET['page']) || $_GET['page'] == 'dashboard') ? 'active' : '' ?>">
                <i class="fas fa-tachometer-alt me-2"></i> Dashboard
            </a>
        </li>
        <li>
            <a href="?page=pos" class="nav-link text-white <?= (isset($_GET['page']) && $_GET['page'] == 'pos') ? 'active' : '' ?>">
                <i class="fas fa-cash-register me-2"></i> Cassa / POS
            </a>
        </li>
        <li>
            <a href="?page=products" class="nav-link text-white <?= (isset($_GET['page']) && strpos($_GET['page'], 'products') !== false) ? 'active' : '' ?>">
                <i class="fas fa-box me-2"></i> Prodotti
            </a>
        </li>
        <li>
            <a href="?page=inventory" class="nav-link text-white <?= (isset($_GET['page']) && strpos($_GET['page'], 'inventory') !== false) ? 'active' : '' ?>">
                <i class="fas fa-warehouse me-2"></i> Magazzino
            </a>
        </li>
        <li>
            <a href="?page=customers" class="nav-link text-white <?= (isset($_GET['page']) && $_GET['page'] == 'customers') ? 'active' : '' ?>">
                <i class="fas fa-address-book me-2"></i> Clienti
            </a>
        </li>
        <li>
            <a href="?page=sales" class="nav-link text-white <?= (isset($_GET['page']) && $_GET['page'] == 'sales' && (!isset($_GET['action']) || $_GET['action'] == 'index' || $_GET['action'] == 'detail')) ? 'active' : '' ?>">
                <i class="fas fa-shopping-cart me-2"></i> Vendite
            </a>
        </li>
        <li>
            <a href="?page=ddt" class="nav-link text-white <?= (isset($_GET['page']) && $_GET['page'] == 'ddt') ? 'active' : '' ?>">
                <i class="fas fa-truck me-2"></i> DDT
            </a>
        </li>
        <li>
            <a href="?page=sales&action=report" class="nav-link text-white <?= (isset($_GET['page']) && $_GET['page'] == 'sales' && isset($_GET['action']) && $_GET['action'] == 'report') ? 'active' : '' ?>">
                <i class="fas fa-chart-line me-2"></i> Report
            </a>
        </li>
        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
        <li>
            <a href="?page=users" class="nav-link text-white <?= (isset($_GET['page']) && $_GET['page'] == 'users') ? 'active' : '' ?>">
                <i class="fas fa-users me-2"></i> Utenti
            </a>
        </li>
        <?php endif; ?>
    </ul>
    <hr>
    <div class="dropdown">
        <a href="#" class="d-flex align-items-center text-white text-decoration-none dropdown-toggle" id="dropdownUser1" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="fas fa-user-circle fa-2x me-2"></i>
            <strong><?= $_SESSION['username'] ?? 'User' ?></strong>
        </a>
        <ul class="dropdown-menu dropdown-menu-dark text-small shadow" aria-labelledby="dropdownUser1">
            <li><a class="dropdown-item" href="#">Impostazioni</a></li>
            <li><a class="dropdown-item" href="#">Profilo</a></li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item" href="?page=logout">Esci</a></li>
        </ul>
    </div>
</div>

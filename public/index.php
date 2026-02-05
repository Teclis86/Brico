<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

// Carica configurazione
require_once __DIR__ . '/../config/config.php';

// Autoloader semplice per le classi (PSR-4 style manuale)
spl_autoload_register(function ($class) {
    // Prefix namespace base
    $prefix = 'App\\';
    $base_dir = __DIR__ . '/../src/';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

    if (file_exists($file)) {
        require $file;
    }
});

// Routing di base
$page = $_GET['page'] ?? 'login';

// Simple Router
switch ($page) {
    case 'login':
        require __DIR__ . '/../templates/auth/login.php';
        break;

    case 'auth_process':
        $controller = new \App\Controllers\AuthController();
        $controller->login();
        break;

    case 'forgot_password':
        $controller = new \App\Controllers\AuthController();
        $controller->forgotPassword();
        break;

    case 'auth_send_reset':
        $controller = new \App\Controllers\AuthController();
        $controller->sendResetLink();
        break;

    case 'auth_reset':
        $controller = new \App\Controllers\AuthController();
        $controller->resetPassword();
        break;

    case 'auth_update_password':
        $controller = new \App\Controllers\AuthController();
        $controller->updatePassword();
        break;
    
    case 'dashboard':
        $controller = new \App\Controllers\DashboardController();
        $controller->index();
        break;
        
    case 'logout':
        $controller = new \App\Controllers\AuthController();
        $controller->logout();
        break;

    // --- Prodotti ---
    case 'products':
        $controller = new \App\Controllers\ProductController();
        $controller->index();
        break;
    
    case 'products_create':
        $controller = new \App\Controllers\ProductController();
        $controller->create();
        break;

    case 'products_store':
        $controller = new \App\Controllers\ProductController();
        $controller->store();
        break;

    case 'products_edit':
        $controller = new \App\Controllers\ProductController();
        $controller->edit();
        break;

    case 'products_update':
        $controller = new \App\Controllers\ProductController();
        $controller->update();
        break;

    case 'products_delete':
        $controller = new \App\Controllers\ProductController();
        $controller->delete();
        break;

    // --- Magazzino ---
    case 'inventory':
        $controller = new \App\Controllers\InventoryController();
        $controller->index();
        break;
    
    case 'inventory_create':
        $controller = new \App\Controllers\InventoryController();
        $controller->create();
        break;

    case 'inventory_store':
        $controller = new \App\Controllers\InventoryController();
        $controller->store();
        break;

    // --- POS & API ---
    case 'pos':
        $controller = new \App\Controllers\PosController();
        $controller->index();
        break;
    
    case 'api_pos_search':
        $controller = new \App\Controllers\PosController();
        $controller->apiSearch();
        break;

    case 'api_pos_process':
        $controller = new \App\Controllers\PosController();
        $controller->processSale();
        break;

    // --- DDT ---
    case 'ddt':
        $controller = new \App\Controllers\DdtController();
        $action = $_GET['action'] ?? 'index';
        
        if ($action === 'create') {
            $controller->create();
        } elseif ($action === 'print') {
            $controller->print();
        } elseif ($action === 'search_barcode') {
            $controller->searchByBarcode();
        } elseif ($action === 'detail') {
            $controller->detail();
        } elseif ($action === 'cancel') {
            $controller->cancel();
        } elseif ($action === 'edit') {
            $controller->edit();
        } elseif ($action === 'update') {
            $controller->update();
        } else {
            $controller->index();
        }
        break;

    // --- Vendite e Report ---
    case 'sales':
        $controller = new \App\Controllers\SalesController();
        $action = $_GET['action'] ?? 'index';
        
        if ($action === 'detail') {
            $controller->detail();
        } elseif ($action === 'report') {
            $controller->report();
        } else {
            $controller->index();
        }
        break;

    // --- Utenti ---
    case 'users':
        $controller = new \App\Controllers\UserController();
        $controller->index();
        break;

    case 'users_create':
        $controller = new \App\Controllers\UserController();
        $controller->create();
        break;

    case 'users_store':
        $controller = new \App\Controllers\UserController();
        $controller->store();
        break;

    case 'users_edit':
        $controller = new \App\Controllers\UserController();
        $controller->edit();
        break;

    case 'users_update':
        $controller = new \App\Controllers\UserController();
        $controller->update();
        break;

    case 'users_delete':
        $controller = new \App\Controllers\UserController();
        $controller->delete();
        break;

    // --- Clienti ---
    case 'customers':
        $controller = new \App\Controllers\CustomerController();
        $action = $_GET['action'] ?? 'index';
        
        if ($action === 'create') {
            $controller->create();
        } elseif ($action === 'detail') {
            $controller->detail();
        } else {
            $controller->index();
        }
        break;
    
    case 'api_customers_search':
        $controller = new \App\Controllers\CustomerController();
        $controller->apiSearch();
        break;

    default:
        echo "404 - Pagina non trovata";
        break;
}

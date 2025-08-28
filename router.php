<?php
$request = $_SERVER['REQUEST_URI'];
$request = strtok($request, '?');

// Define routes
switch ($request) {
    case '/':
    case '/home':
        require __DIR__ . '/pages/home.php';
        break;

    case '/about':
        require __DIR__ . '/pages/about.php';
        break;

    case '/login':
        require __DIR__ . '/pages/login.php';
        break;

    case '/register':
        require __DIR__ . '/pages/register.php';
        break;

    // ======================
    // 🔹 Admin Routes
    // ======================
    case '/admin':
        require __DIR__ . '/pages/admin/admindashboard.php';
        break;

    case '/admin/infants':
        require __DIR__ . '/pages/admin/pages/infants.php';
        break;

    case '/admin/vaccines':
        require __DIR__ . '/pages/admin/pages/vaccines.php';
        break;

    case '/admin/midwives':
        require __DIR__ . '/pages/admin/pages/midwives.php';
        break;

    case '/admin/schedules':
        require __DIR__ . '/pages/admin/pages/schedules.php';
        break;
    case '/admin/puroks':
        require __DIR__ . '/pages/admin/pages/puroks.php';
        break;

    case '/logout':
        require __DIR__ . '/pages/admin/logout.php';
        break;

    case '/contact':
        require __DIR__ . '/pages/contact.php';
        break;

    default:
        http_response_code(404);
        echo "<h1>404 Not Found</h1>";
        break;
}

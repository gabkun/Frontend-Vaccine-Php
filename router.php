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
    case '/contact':
        require __DIR__ . '/pages/contact.php';
        break;

    default:
        http_response_code(404);
        echo "<h1>404 Not Found</h1>";
        break;
}

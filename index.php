<?php
// ============================
// ROUTE DETECTION
// ============================

$request = $_SERVER['REQUEST_URI'];
$request = strtok($request, '?'); // remove query string

// Detect dashboard routes
$isAdminRoute   = strpos($request, '/admin') !== false;
$isMidwifeRoute = strpos($request, '/midwife') !== false;

$isDashboardRoute = $isAdminRoute || $isMidwifeRoute;


// ============================
// ROUTER FUNCTION
// ============================
function loadPage($request)
{
    switch ($request) {

        // ======================
        // PUBLIC ROUTES
        // ======================

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


        // ======================
        // ADMIN ROUTES
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


        // ======================
        // MIDWIFE ROUTES
        // ======================

        case '/midwife':
            require __DIR__ . '/pages/midwife/midwifedashboard.php';
            break;

        case '/midwife/infants':
            require __DIR__ . '/pages/midwife/pages/infants.php';
            break;

        case '/midwife/vaccines':
            require __DIR__ . '/pages/midwife/pages/vaccine.php';
            break;

        case '/midwife/schedules':
            require __DIR__ . '/pages/midwife/pages/schedule.php';
            break;

        case '/midwifelogout':
            require __DIR__ . '/pages/midwife/logout.php';
            break;


        // ======================
        // 404
        // ======================

        default:
            http_response_code(404);
            echo "<h1>404 Not Found</h1>";
            break;
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
<meta charset="UTF-8">
<title>Vaccination System</title>

<!-- Google Fonts -->
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">

<?php if ($isAdminRoute): ?>

    <!-- ADMIN CSS -->
    <link rel="stylesheet" href="/src/admin/admin.css">

<?php elseif ($isMidwifeRoute): ?>

    <!-- MIDWIFE CSS -->
    <link rel="stylesheet" href="/src/midwife/midwife.css">

<?php else: ?>

    <!-- PUBLIC CSS -->
    <link rel="stylesheet" href="/components/navstyles.css">
    <link rel="stylesheet" href="/src/styles.css">
    <link rel="stylesheet" href="/src/home.css">
    <link rel="stylesheet" href="/src/login.css">

<?php endif; ?>

</head>

<body>

<!-- Hide navbar for dashboards -->
<?php if (!$isDashboardRoute): ?>
    <?php include 'components/nav.php'; ?>
<?php endif; ?>


<!-- PAGE CONTENT -->
<div class="main-content">
<?php loadPage($request); ?>
</div>

</body>
</html>
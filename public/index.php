<?php
session_start();
$timeout = 120; // 2 min
$flashMessage = '';

if (isset($_SESSION['last_activity'])) {
    $inactive = time() - $_SESSION['last_activity'];
    if ($inactive >= $timeout) {
        session_unset();
        session_destroy();
        $flashMessage = "You were logged out due to inactivity.";
        session_start(); // restart session to show message
        $_SESSION['flash'] = $flashMessage;
        header("Location: index.php");
        exit;
    }
}
$_SESSION['last_activity'] = time();

require_once __DIR__ . '/../app/config/Database.php';
require_once __DIR__ . '/../app/models/User.php';
require_once __DIR__ . '/../app/models/Task.php';
require_once __DIR__ . '/../app/controllers/AuthController.php';
require_once __DIR__ . '/../app/controllers/UserController.php';
require_once __DIR__ . '/../app/controllers/TaskController.php';
require_once __DIR__ . '/../app/controllers/DashboardController.php';

$database = new Database();
$db = $database->getConnection();

$authController = new AuthController($db);
$userController = new UserController($db);
$taskController = new TaskController($db);
$dashboardController = new DashboardController($db);

//Default controller
$controllerName = $_GET['controller'] ?? 'Auth';
$action = $_GET['action'] ?? 'login';

// --- Dispatch ---
switch ($controllerName) {
    case 'Auth':
        if (method_exists($authController, $action)) {
            $authController->$action();
        } else {
            echo "404: Auth action not found.";
        }
        break;

    case 'Task':
        if (method_exists($taskController, $action)) {
            $taskController->$action();
        } else {
            echo "404: Task action not found.";
        }
        break;

    case 'Dashboard':
        if (method_exists($dashboardController, $action)) {
            $dashboardController->$action();
        } else {
            echo "404: Dashboard action not found.";
        }
        break;

    case 'User':
        if ($action === 'teamsOverview') {
            $userController->teamsOverview();
        } elseif (method_exists($userController, $action)) {
            if (isset($_GET['id'])) {
                $userController->$action($_GET['id']);
            } else {
                $userController->$action();
            }
        } else {
            echo "404: User action not found.";
        }
        break;

    default:
        echo "404: Controller not found.";
        break;
}

// --- Show flash message if exists ---
if (!empty($_SESSION['flash'])) {
    echo "<div style='position:fixed; top:20px; right:20px; background:#f8d7da; color:#721c24; padding:10px 20px; border-radius:5px; border:1px solid #f5c6cb; font-weight:bold; z-index:1000;'>";
    echo $_SESSION['flash'];
    echo "</div>";
    unset($_SESSION['flash']);
}
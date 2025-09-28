<?php
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Task.php';
require_once __DIR__ . '/../helpers/flash.php';

class DashboardController
{
    private $userModel;
    private $taskModel;

    public function __construct($db)
    {
        $this->userModel = new User($db);
        $this->taskModel = new Task($db);
    }

    private function checkAccess(array $roles)
    {
        $role = $_SESSION['user']['role'] ?? '';
        if (!in_array($role, $roles)) {
            setFlash('error', 'Access denied');
            header("Location: index.php");
            exit;
        }
    }

    public function index()
    {
        $this->checkAccess(['admin']);

        try {
            $totalEmployees = $this->userModel->getEmployeeCount();
            $totalTasks = $this->taskModel->getTaskCount();
            $taskStatusCounts = $this->taskModel->getTaskStatusCounts();

            include __DIR__ . '/../views/dashboard/index.php';
        } catch (Exception $e) {
            error_log("DashboardController::index error: " . $e->getMessage());
            setFlash('error', 'Failed to load dashboard');
            header("Location: index.php");
            exit;
        }
    }
}
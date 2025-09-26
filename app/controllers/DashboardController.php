<?php
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Task.php';

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
            echo "<script>alert('Access denied'); window.history.back();</script>";
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
            echo "<script>alert('Failed to load dashboard'); window.history.back();</script>";
        }
    }
}
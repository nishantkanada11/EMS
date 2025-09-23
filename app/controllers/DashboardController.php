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

    public function index()
    {
        if ($_SESSION['user']['role'] !== 'admin') {
            echo "Access denied.";
            return;
        }

        $totalEmployees = $this->userModel->getEmployeeCount();
        $totalTasks = $this->taskModel->getTaskCount();
        $taskStatusCounts = $this->taskModel->getTaskStatusCounts();

        include __DIR__ . '/../views/dashboard/index.php';
    }
}

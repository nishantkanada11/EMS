<?php
require_once __DIR__ . '/../models/Task.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../helpers/email.php';

class TaskController
{
    private $taskModel;
    private $userModel;

    public function __construct($db)
    {
        $this->taskModel = new Task($db);
        $this->userModel = new User($db);
    }

    private function checkAccess(array $allowedRoles)
    {
        $role = $_SESSION['user']['role'] ?? '';
        if (!in_array($role, $allowedRoles)) {
            echo "<script>alert('Access denied'); window.history.back();</script>";
            exit;
        }
    }

    public function index()
    {
        $role = $_SESSION['user']['role'] ?? '';
        $userId = $_SESSION['user']['id'] ?? 0;

        $tasks = in_array($role, ['admin', 'tl'])
            ? $this->taskModel->all()
            : $this->taskModel->findByUser($userId);

        include __DIR__ . '/../views/tasks/index.php';
    }

    public function create()
    {
        $this->checkAccess(['admin', 'tl']);
        $employees = $this->userModel->getAssignableUsers();
        include __DIR__ . '/../views/tasks/create.php';
    }

    public function store()
    {
        $this->checkAccess(['admin', 'tl']);

        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $assigned_to = isset($_POST['assigned_to']) ? (int) $_POST['assigned_to'] : null;
        $start_date = $_POST['start_date'] ?? '';
        $due_date = $_POST['due_date'] ?? '';

        if (!$title || !$description || !$start_date || !$due_date) {
            echo "<script>alert('Please fill all required fields'); window.history.back();</script>";
            exit;
        }

        $this->taskModel->create($title, $description, $assigned_to, 'pending', $start_date, $due_date);

        if (!$assigned_to) {
            echo "<script>alert('Please assign the task to a user'); window.history.back();</script>";
            exit;
        }

        if ($assigned_to) {
            $user = $this->userModel->find($assigned_to);
            if ($user) {
                sendTaskAssignedEmail($user['email'], $user['name'], $title, $description, $start_date, $due_date);
            }
        }

        header("Location: index.php?controller=Task&action=index");
        exit;
    }

    public function edit()
    {
        $this->checkAccess(['admin', 'tl']);
        $id = (int) ($_GET['id'] ?? 0);
        $task = $this->taskModel->find($id);

        if (!$task) {
            echo "<script>alert('Task not found'); window.history.back();</script>";
            exit;
        }

        $employees = $this->userModel->getAssignableUsers();
        include __DIR__ . '/../views/tasks/edit.php';
    }

    public function update()
    {
        $this->checkAccess(['admin', 'tl']);
        $id = (int) ($_POST['id'] ?? 0);
        $task = $this->taskModel->find($id);

        if (!$task) {
            echo "<script>alert('Task not found'); window.history.back();</script>";
            exit;
        }

        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $assigned_to = isset($_POST['assigned_to']) ? (int) $_POST['assigned_to'] : null;
        $status = $_POST['status'] ?? 'pending';
        $start_date = $_POST['start_date'] ?? '';
        $due_date = $_POST['due_date'] ?? '';

        if (!$title || !$description || !$start_date || !$due_date) {
            echo "<script>alert('Please fill all required fields'); window.history.back();</script>";
            exit;
        }

        $this->taskModel->update($id, $title, $description, $assigned_to, $status, $start_date, $due_date);

        if ($assigned_to) {
            $user = $this->userModel->find($assigned_to);
            if ($user) {
                sendTaskAssignedEmail($user['email'], $user['name'], $title, $description, $start_date, $due_date);
            }
        }

        header("Location: index.php?controller=Task&action=index");
        exit;
    }

    public function updateStatus()
    {
        $id = (int) ($_POST['id'] ?? 0);
        $status = $_POST['status'] ?? 'pending';
        $task = $this->taskModel->find($id);

        if (!$task) {
            echo "<script>alert('Task not found'); window.history.back();</script>";
            exit;
        }

        $role = $_SESSION['user']['role'] ?? '';
        $userId = $_SESSION['user']['id'] ?? 0;

        if ($role === 'employee' && $task['assigned_to'] != $userId) {
            echo "<script>alert('Access denied'); window.history.back();</script>";
            exit;
        }

        $this->taskModel->updateStatus($id, $status);
        header("Location: index.php?controller=Task&action=index");
        exit;
    }

    public function delete()
    {
        $this->checkAccess(['admin', 'tl']);
        $id = (int) ($_GET['id'] ?? 0);
        $task = $this->taskModel->find($id);

        if (!$task) {
            echo "<script>alert('Task not found'); window.history.back();</script>";
            exit;
        }

        $this->taskModel->delete($id);
        header("Location: index.php?controller=Task&action=index");
        exit;
    }
}
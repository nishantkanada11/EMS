<?php
require_once __DIR__ . '/../models/Task.php';

class TaskController
{
    private $taskModel;
    private $conn;

    public function __construct($conn)
    {
        $this->conn = $conn;
        $this->taskModel = new Task($conn);
    }

    /**
     * Helper to check user access based on roles
     * @param array $allowedRoles
     */
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

        if (in_array($role, ['admin', 'tl'])) {
            $tasks = $this->taskModel->all();
        } else {
            $tasks = $this->taskModel->findByUser($userId);
        }

        include __DIR__ . '/../views/tasks/index.php';
    }

    public function create()
    {
        $this->checkAccess(['admin', 'tl']);

        $stmt = $this->conn->prepare("SELECT id, name FROM users WHERE role IN ('employee','tl')");
        $stmt->execute();
        $result = $stmt->get_result();
        $employees = $result->fetch_all(MYSQLI_ASSOC);

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
        $status = 'pending';

        if (!$title || !$description || !$start_date || !$due_date) {
            echo "<script>alert('Please fill all required fields'); window.history.back();</script>";
            exit;
        }

        $this->taskModel->create($title, $description, $assigned_to, $status, $start_date, $due_date);

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

        $stmt = $this->conn->prepare("SELECT id, name FROM users WHERE role IN ('employee','tl')");
        $stmt->execute();
        $result = $stmt->get_result();
        $employees = $result->fetch_all(MYSQLI_ASSOC);

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

        // Only admin/tl can update anyone, employee only own task
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
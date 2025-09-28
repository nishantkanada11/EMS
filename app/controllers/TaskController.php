<?php
require_once __DIR__ . '/../models/Task.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../helpers/email.php';
require_once __DIR__ . '/../helpers/flash.php';

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
            setFlash('error', 'Access denied');
            header("Location: index.php");
            exit;
        }
    }

public function index()
{
    $role = $_SESSION['user']['role'] ?? '';
    $userId = $_SESSION['user']['id'] ?? 0;

    $sort = $_GET['sort'] ?? 'id';
    $order = $_GET['order'] ?? 'ASC';

    // Only allow specific columns to prevent SQL injection
    $allowedSort = ['id','title','description','assigned_user','status','start_date','due_date'];
    $allowedOrder = ['ASC','DESC'];
    if (!in_array($sort, $allowedSort)) $sort = 'id';
    if (!in_array($order, $allowedOrder)) $order = 'ASC';

    $tasks = in_array($role, ['admin', 'tl'])
        ? $this->taskModel->all($sort, $order)
        : $this->taskModel->findByUser($userId, $sort, $order);

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
            setFlash('error', 'Please fill all required fields');
            header("Location: index.php?controller=Task&action=create");
            exit;
        }

        if (!$assigned_to) {
            setFlash('error', 'Please assign the task to a user');
            header("Location: index.php?controller=Task&action=create");
            exit;
        }

        try {
            $this->taskModel->create($title, $description, $assigned_to, 'pending', $start_date, $due_date);
            $user = $this->userModel->find($assigned_to);
            if ($user) {
                sendTaskAssignedEmail($user['email'], $user['name'], $title, $description, $start_date, $due_date);
            }

            setFlash('success', 'Task created and assigned successfully');
            header("Location: index.php?controller=Task&action=index");
            exit;
        } catch (Exception $e) {
            setFlash('error', 'Failed to create task');
            header("Location: index.php?controller=Task&action=create");
            exit;
        }
    }

    public function edit()
    {
        $this->checkAccess(['admin', 'tl']);
        $id = (int) ($_GET['id'] ?? 0);
        $task = $this->taskModel->find($id);

        if (!$task) {
            setFlash('error', 'Task not found');
            header("Location: index.php?controller=Task&action=index");
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
            setFlash('error', 'Task not found');
            header("Location: index.php?controller=Task&action=index");
            exit;
        }

        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $assigned_to = isset($_POST['assigned_to']) ? (int) $_POST['assigned_to'] : null;
        $status = $_POST['status'] ?? 'pending';
        $start_date = $_POST['start_date'] ?? '';
        $due_date = $_POST['due_date'] ?? '';

        if (!$title || !$description || !$start_date || !$due_date) {
            setFlash('error', 'Please fill all required fields');
            header("Location: index.php?controller=Task&action=edit&id=$id");
            exit;
        }

        try {
            $this->taskModel->update($id, $title, $description, $assigned_to, $status, $start_date, $due_date);
            $user = $this->userModel->find($assigned_to);
            if ($user) {
                sendTaskAssignedEmail($user['email'], $user['name'], $title, $description, $start_date, $due_date);
            }

            setFlash('success', 'Task updated successfully');
            header("Location: index.php?controller=Task&action=index");
            exit;
        } catch (Exception $e) {
            setFlash('error', 'Failed to update task');
            header("Location: index.php?controller=Task&action=edit&id=$id");
            exit;
        }
    }

    public function updateStatus()
    {
        $id = (int) ($_POST['id'] ?? 0);
        $status = $_POST['status'] ?? 'pending';
        $task = $this->taskModel->find($id);

        if (!$task) {
            setFlash('error', 'Task not found');
            header("Location: index.php?controller=Task&action=index");
            exit;
        }

        $role = $_SESSION['user']['role'] ?? '';
        $userId = $_SESSION['user']['id'] ?? 0;

        if ($role === 'employee' && $task['assigned_to'] != $userId) {
            setFlash('error', 'Access denied');
            header("Location: index.php?controller=Task&action=index");
            exit;
        }

        $this->taskModel->updateStatus($id, $status);
        setFlash('success', 'Task status updated');
        header("Location: index.php?controller=Task&action=index");
        exit;
    }

    public function delete()
    {
        $this->checkAccess(['admin', 'tl']);
        $id = (int) ($_GET['id'] ?? 0);
        $task = $this->taskModel->find($id);

        if (!$task) {
            setFlash('error', 'Task not found');
            header("Location: index.php?controller=Task&action=index");
            exit;
        }

        try {
            $this->taskModel->delete($id);
            setFlash('success', 'Task deleted successfully');
            header("Location: index.php?controller=Task&action=index");
            exit;
        } catch (Exception $e) {
            setFlash('error', 'Failed to delete task');
            header("Location: index.php?controller=Task&action=index");
            exit;
        }
    }
}
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

        $allowedSort = ['id', 'title', 'description', 'assigned_user', 'status', 'start_date', 'due_date'];
        $allowedOrder = ['ASC', 'DESC'];

        if (!in_array($sort, $allowedSort))
            $sort = 'id';
        if (!in_array(strtoupper($order), $allowedOrder))
            $order = 'ASC';

        try {
            $tasks = in_array($role, ['admin', 'tl'])
                ? $this->taskModel->getTasks(null, null, $sort, $order) // all tasks
                : $this->taskModel->getTasks(null, $userId, $sort, $order); // only user tasks

            include __DIR__ . '/../views/tasks/index.php';
        } catch (Exception $e) {
            setFlash('error', 'Failed to load tasks');
            header("Location: index.php");
            exit;
        }
    }

    public function create()
    {
        $this->checkAccess(['admin', 'tl']);

        try {
            $employees = $this->userModel->getUsers();
            include __DIR__ . '/../views/tasks/create.php';
        } catch (Exception $e) {
            setFlash('error', 'Failed to load employees');
            header("Location: index.php");
            exit;
        }
    }


    public function store()
    {
        $this->checkAccess(['admin', 'tl']);

        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $assigned_to = isset($_POST['assigned_to']) ? (int) $_POST['assigned_to'] : null;
        $start_date = $_POST['start_date'] ?? '';
        $due_date = $_POST['due_date'] ?? '';

        $_SESSION['old'] = [
            'title' => $title,
            'description' => $description,
            'assigned_to' => $assigned_to,
            'start_date' => $start_date,
            'due_date' => $due_date
        ];

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
            unset($_SESSION['old']); // Clear old input on success

            $user = $this->userModel->all($assigned_to);
            if ($user) {
                sendTaskAssignedEmail($user['email'], $user['name'], $title, $description, $start_date, $due_date);
            }

            setFlash('success', 'Task created and assigned successfully');
            header("Location: index.php?controller=Task&action=index");
            exit;
        } catch (Exception $e) {
            error_log("TaskController::store error: " . $e->getMessage());
            setFlash('error', 'Failed to create task');
            header("Location: index.php?controller=Task&action=create");
            exit;
        }
    }


    public function update()
    {
        $this->checkAccess(['admin', 'tl']);
        $id = (int) ($_POST['id'] ?? 0);

        try {
            $task = $this->taskModel->getTasks($id);

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

            $_SESSION['old'] = [
                'title' => $title,
                'description' => $description,
                'assigned_to' => $assigned_to,
                'status' => $status,
                'start_date' => $start_date,
                'due_date' => $due_date
            ];

            if (!$title || !$description || !$start_date || !$due_date) {
                setFlash('error', 'Please fill all required fields');
                header("Location: index.php?controller=Task&action=edit&id=$id");
                exit;
            }

            $this->taskModel->update($id, $title, $description, $assigned_to, $status, $start_date, $due_date);
            unset($_SESSION['old']); // clear old input on success

            // Send mail to assigned user
            $user = $this->userModel->all($assigned_to);
            if ($user) {
                sendTaskAssignedEmail($user['email'], $user['name'], $title, $description, $start_date, $due_date);
            }

            setFlash('success', 'Task updated successfully');
            header("Location: index.php?controller=Task&action=index");
            exit;

        } catch (Exception $e) {
            error_log("TaskController::update error: " . $e->getMessage());
            setFlash('error', 'Failed to update task');
            header("Location: index.php?controller=Task&action=edit&id=$id");
            exit;
        }
    }



    public function edit()
    {
        $this->checkAccess(['admin', 'tl']);
        $id = (int) ($_GET['id'] ?? 0);

        if (!$id) {
            setFlash('error', 'Invalid task ID');
            header("Location: index.php?controller=Task&action=index");
            exit;
        }

        try {
            // Fetch task
            $tasks = $this->taskModel->getTasks($id); // returns array of one task
            $task = $tasks[0] ?? null;

            if (!$task) {
                setFlash('error', 'Task not found');
                header("Location: index.php?controller=Task&action=index");
                exit;
            }

            // Fetch employees for assignment
            $employees = $this->userModel->getUsers();

            include __DIR__ . '/../views/tasks/edit.php';

        } catch (Exception $e) {
            error_log("TaskController::edit error: " . $e->getMessage());
            setFlash('error', 'Failed to load task');
            header("Location: index.php?controller=Task&action=index");
            exit;
        }
    }


    public function updateStatus()
    {
        $id = (int) ($_POST['id'] ?? 0);
        $status = $_POST['status'] ?? 'pending';

        try {
            $tasks = $this->taskModel->getTasks($id);
            $task = $tasks[0] ?? null;

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

            if (!$this->taskModel->updateStatus($id, $status)) {
                throw new Exception("Database update failed for task ID $id");
            }

            setFlash('success', 'Task status updated');
            header("Location: index.php?controller=Task&action=index");
            exit;

        } catch (Exception $e) {
            error_log("TaskController::updateStatus error: " . $e->getMessage());
            setFlash('error', 'Failed to update task status');
            header("Location: index.php?controller=Task&action=index");
            exit;
        }
    }


    public function delete()
    {
        $this->checkAccess(['admin', 'tl']);
        $id = (int) ($_GET['id'] ?? 0);

        try {
            $tasks = $this->taskModel->getTasks($id);
            $task = $tasks[0] ?? null;

            if (!$task) {
                setFlash('error', 'Task not found');
                header("Location: index.php?controller=Task&action=index");
                exit;
            }

            if (!$this->taskModel->delete($id)) {
                throw new Exception("Failed to delete task with ID $id");
            }

            setFlash('success', 'Task deleted successfully');
            header("Location: index.php?controller=Task&action=index");
            exit;

        } catch (Exception $e) {
            error_log("TaskController::delete error: " . $e->getMessage());
            setFlash('error', 'Failed to delete task');
            header("Location: index.php?controller=Task&action=index");
            exit;
        }
    }
}
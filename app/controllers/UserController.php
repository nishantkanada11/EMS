<?php
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../helpers/email.php';

class UserController
{
    private $userModel;

    public function __construct($db)
    {
        $this->userModel = new User($db);
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
        $currentUserId = $_SESSION['user']['id'];

        $sort = $_GET['sort'] ?? 'id';
        $order = $_GET['order'] ?? 'ASC';

        try {
            $users = $this->userModel->all($currentUserId, $sort, $order);
            include __DIR__ . '/../views/users/index.php';
        } catch (Exception $e) {
            echo "<script>alert('Failed to load users'); window.history.back();</script>";
        }
    }

    public function create()
    {
        $this->checkAccess(['admin', 'tl']);
        include __DIR__ . '/../views/users/create.php';
    }

    public function store()
    {
        $this->checkAccess(['admin', 'tl']);

        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $mobile = trim($_POST['mobile'] ?? '');
        $password = $_POST['password'] ?? '';
        $department = trim($_POST['department'] ?? '');
        $userRole = ($_SESSION['user']['role'] === 'tl') ? 'employee' : ($_POST['role'] ?? 'employee');

        if (!$name || !$email || !$mobile || !$password || !$department) {
            echo "<script>alert('Please fill all fields'); window.history.back();</script>";
            exit;
        }

        try {
            $result = $this->userModel->create($name, $email, $mobile, $password, $userRole, $department);

            if ($result === "exists") {
                echo "<script>alert('Email or mobile already exists'); window.history.back();</script>";
                exit;
            }

            sendUserCredentials($email, $name, $password);
            $redirect = ($_SESSION['user']['role'] === 'tl') ? "team" : "index";
            header("Location: index.php?controller=User&action=$redirect");
            exit;
        } catch (Exception $e) {
            echo "<script>alert('Failed to create user'); window.history.back();</script>";
        }
    }

    public function edit()
    {
        $id = (int) ($_GET['id'] ?? 0);
        $currentUserId = $_SESSION['user']['id'] ?? 0;
        $currentUserRole = $_SESSION['user']['role'] ?? '';

        if ($currentUserRole !== 'admin' && $id !== $currentUserId) {
            echo "<script>alert('Access denied'); window.history.back();</script>";
            exit;
        }

        try {
            $user = $this->userModel->find($id);
            if (!$user) {
                echo "<script>alert('User not found'); window.history.back();</script>";
                exit;
            }
            include __DIR__ . '/../views/users/edit.php';
        } catch (Exception $e) {
            echo "<script>alert('Failed to load user'); window.history.back();</script>";
        }
    }

    public function update()
    {
        $id = (int) ($_POST['id'] ?? 0);
        $currentUserId = $_SESSION['user']['id'] ?? 0;
        $currentUserRole = $_SESSION['user']['role'] ?? '';

        if ($currentUserRole !== 'admin' && $id !== $currentUserId) {
            echo "<script>alert('Access denied'); window.history.back();</script>";
            exit;
        }

        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $mobile = trim($_POST['mobile'] ?? '');
        $department = trim($_POST['department'] ?? '');
        $password = $_POST['password'] ?? '';
        $role = ($currentUserRole === 'admin') ? ($_POST['role'] ?? 'employee') : $this->userModel->find($id)['role'];

        if (!$name || !$email || !$mobile || !$department) {
            echo "<script>alert('Please fill all fields'); window.history.back();</script>";
            exit;
        }

        try {
            $this->userModel->update($id, $name, $email, $mobile, $role, $department);

            if (!empty($password)) {
                $this->userModel->updatePassword($id, $password);
            }

            if ($id === $currentUserId) {
                $_SESSION['user']['name'] = $name;
            }

            header("Location: index.php?controller=User&action=index");
            exit;
        } catch (Exception $e) {
            echo "<script>alert('Failed to update user'); window.history.back();</script>";
        }
    }

    public function delete()
    {
        $this->checkAccess(['admin', 'tl']);
        $id = (int) ($_GET['id'] ?? 0);

        try {
            $this->userModel->delete($id);
            header("Location: index.php?controller=User&action=index");
            exit;
        } catch (Exception $e) {
            echo "<script>alert('Failed to delete user'); window.history.back();</script>";
        }
    }

    //ADd member management
    public function team()
    {
        $this->checkAccess(['tl']);
        try {
            $employees = $this->userModel->getAllEmployees();
            include __DIR__ . '/../views/users/team.php';
        } catch (Exception $e) {
            echo "<script>alert('Failed to load team'); window.history.back();</script>";
        }
    }

    public function addToTeam()
    {
        $this->checkAccess(['tl']);
        $employeeId = (int) ($_GET['id'] ?? 0);
        if ($employeeId <= 0) {
            echo "<script>alert('Invalid employee ID'); window.history.back();</script>";
            exit;
        }

        try {
            $tlId = $_SESSION['user']['id'];
            $this->userModel->assignToTL($employeeId, $tlId);
            header("Location: index.php?controller=User&action=team");
            exit;
        } catch (Exception $e) {
            echo "<script>alert('Failed to add employee to team'); window.history.back();</script>";
        }
    }

    public function teamsOverview()
    {
        $this->checkAccess(['admin']);
        try {
            $teams = $this->userModel->getTeamsOverview();
            include __DIR__ . '/../views/users/teams_overview.php';
        } catch (Exception $e) {
            echo "<script>alert('Failed to load teams overview'); window.history.back();</script>";
        }
    }

    public function myTeam()
    {
        $this->checkAccess(['tl']);
        try {
            $tlId = $_SESSION['user']['id'];
            $employees = $this->userModel->getEmployeesByTL($tlId);
            include __DIR__ . '/../views/users/my_team.php';
        } catch (Exception $e) {
            echo "<script>alert('Failed to load your team'); window.history.back();</script>";
        }
    }
}
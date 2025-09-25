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
            echo "<script>alert('Access Denied!'); window.history.back();</script>";
            exit;
        }
    }

    public function index()
    {
        $this->checkAccess(['admin']);
        $currentUserId = $_SESSION['user']['id'];

        $sort = $_GET['sort'] ?? 'id';
        $order = $_GET['order'] ?? 'ASC';

        $nextOrder = ($order === 'ASC') ? 'DESC' : 'ASC';

        $users = $this->userModel->all($currentUserId, $sort, $order);

        include __DIR__ . '/../views/users/index.php';
    }

    public function create()
    {
        $this->checkAccess(['admin', 'tl']);
        include __DIR__ . '/../views/users/create.php';
    }


    public function store()
    {
        $this->checkAccess(['admin', 'tl']);

        // Validate inputs
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $mobile = trim($_POST['mobile'] ?? '');
        $password = $_POST['password'] ?? '';
        $department = trim($_POST['department'] ?? '');
        $userRole = $_POST['role'] ?? 'employee';

        if ($_SESSION['user']['role'] === 'tl') {
            $userRole = 'employee'; 
        }

        if (!$name || !$email || !$mobile || !$password || !$department) {
            echo "<script>alert('All fields are required!'); window.history.back();</script>";
            exit;
        }

        $result = $this->userModel->create($name, $email, $mobile, $password, $userRole, $department);

        if ($result === "exists") {
            echo "<script>alert('Email or Mobile already exists!'); window.history.back();</script>";
            exit;
        }

        if ($result) {
            $emailSent = sendUserCredentials($email, $name, $password);
            if (!$emailSent) {
                echo "<script>alert('User created but failed to send email.'); window.history.back();</script>";
                exit;
            }
        }

        if ($_SESSION['user']['role'] === 'tl') {
            header("Location: index.php?controller=User&action=team");
        } else {
            header("Location: index.php?controller=User&action=index");
        }
        exit;
    }

    public function edit()
    {
        $id = (int) ($_GET['id'] ?? 0);
        $currentUserId = $_SESSION['user']['id'] ?? 0;
        $currentUserRole = $_SESSION['user']['role'] ?? '';

        if ($currentUserRole !== 'admin' && $id !== $currentUserId) {
            echo "<script>alert('Access Denied!'); window.history.back();</script>";
            exit;
        }

        $user = $this->userModel->find($id);
        if (!$user) {
            echo "<script>alert('User not found!'); window.history.back();</script>";
            exit;
        }

        include __DIR__ . '/../views/users/edit.php';
    }

    public function update()
    {
        $id = (int) ($_POST['id'] ?? 0);
        $currentUserId = $_SESSION['user']['id'] ?? 0;
        $currentUserRole = $_SESSION['user']['role'] ?? '';

        if ($currentUserRole !== 'admin' && $id !== $currentUserId) {
            echo "<script>alert('Access Denied!'); window.history.back();</script>";
            exit;
        }

        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $mobile = trim($_POST['mobile'] ?? '');
        $department = trim($_POST['department'] ?? '');
        $password = $_POST['password'] ?? '';

        $role = ($currentUserRole === 'admin') ? ($_POST['role'] ?? 'employee') : $this->userModel->find($id)['role'];

        if (!$name || !$email || !$mobile || !$department) {
            echo "<script>alert('All fields are required!'); window.history.back();</script>";
            exit;
        }

        $this->userModel->update($id, $name, $email, $mobile, $role, $department);

        if (!empty($password)) {
            $this->userModel->updatePassword($id, $password);
        }

        if ($id === $currentUserId) {
            $_SESSION['user']['name'] = $name;
        }

        header("Location: index.php?controller=User&action=index");
        exit;
    }

    public function promote()
    {
        $this->checkAccess(['admin']);
        $id = (int) ($_GET['id'] ?? 0);
        $this->userModel->changeRole($id, 'tl');
        header("Location: index.php?controller=User&action=index");
        exit;
    }

    public function demote()
    {
        $this->checkAccess(['admin']);
        $id = (int) ($_GET['id'] ?? 0);
        $this->userModel->changeRole($id, 'employee');
        header("Location: index.php?controller=User&action=index");
        exit;
    }


    public function delete()
    {
        $this->checkAccess(['admin', 'tl']);
        $id = (int) ($_GET['id'] ?? 0);
        $this->userModel->delete($id);
        header("Location: index.php?controller=User&action=index");
        exit;
    }

    public function team()
    {
        $this->checkAccess(['tl']);
        $employees = $this->userModel->getAllEmployees();
        include __DIR__ . '/../views/users/team.php';
    }

    public function addToTeam()
    {
        $this->checkAccess(['tl']);
        $employeeId = (int) ($_GET['id'] ?? 0);
        if ($employeeId <= 0) {
            echo "<script>alert('Invalid request'); window.history.back();</script>";
            exit;
        }
        $tlId = $_SESSION['user']['id'];
        $this->userModel->assignToTL($employeeId, $tlId);
        header("Location: index.php?controller=User&action=team");
        exit;
    }


    public function teamsOverview()
    {
        $this->checkAccess(['admin']);
        $teams = $this->userModel->getTeamsOverview();
        include __DIR__ . '/../views/users/teams_overview.php';
    }

    // TL viewown team
    public function myTeam()
    {
        $this->checkAccess(['tl']);
        $tlId = $_SESSION['user']['id'];
        $employees = $this->userModel->getEmployeesByTL($tlId);
        include __DIR__ . '/../views/users/my_team.php';
    }


}
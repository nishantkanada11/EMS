<?php
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../helpers/email.php';
require_once __DIR__ . '/../helpers/flash.php';

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
            setFlash('error', 'Access denied');
            header("Location: index.php");
            exit;
        }
    }

public function index()
{
    $this->checkAccess(['admin']);
    $currentUserId = $_SESSION['user']['id'];

    $sort = $_GET['sort'] ?? 'id';
    $order = $_GET['order'] ?? 'ASC';

    $allowedSort = ['id', 'name', 'email', 'mobile', 'role', 'department'];
    $allowedOrder = ['ASC', 'DESC'];

    if (!in_array($sort, $allowedSort)) $sort = 'id';
    if (!in_array(strtoupper($order), $allowedOrder)) $order = 'ASC';

    $nextOrder = $order === 'ASC' ? 'DESC' : 'ASC';

    try {
        $users = $this->userModel->all($currentUserId, $sort, $order);
        include __DIR__ . '/../views/users/index.php';
    } catch (Exception $e) {
        setFlash('error', 'Failed to load users');
        header("Location: index.php");
        exit;
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
            setFlash('error', 'Please fill all fields');
            header("Location: index.php?controller=User&action=create");
            exit;
        }

        try {
            $result = $this->userModel->create($name, $email, $mobile, $password, $userRole, $department);

            if ($result === "exists") {
                setFlash('error', 'Email or mobile already exists');
                header("Location: index.php?controller=User&action=create");
                exit;
            }

            sendUserCredentials($email, $name, $password);
            setFlash('success', 'User created successfully and email sent');
            $redirect = ($_SESSION['user']['role'] === 'tl') ? "team" : "index";
            header("Location: index.php?controller=User&action=$redirect");
            exit;
        } catch (Exception $e) {
            setFlash('error', 'Failed to create user');
            header("Location: index.php?controller=User&action=create");
            exit;
        }
    }

    public function edit()
    {
        $id = (int) ($_GET['id'] ?? 0);
        $currentUserId = $_SESSION['user']['id'] ?? 0;
        $currentUserRole = $_SESSION['user']['role'] ?? '';

        if ($currentUserRole !== 'admin' && $id !== $currentUserId) {
            setFlash('error', 'Access denied');
            header("Location: index.php");
            exit;
        }

        try {
            $user = $this->userModel->find($id);
            if (!$user) {
                setFlash('error', 'User not found');
                header("Location: index.php?controller=User&action=index");
                exit;
            }
            include __DIR__ . '/../views/users/edit.php';
        } catch (Exception $e) {
            setFlash('error', 'Failed to load user');
            header("Location: index.php?controller=User&action=index");
            exit;
        }
    }

    public function update()
    {
        $id = (int) ($_POST['id'] ?? 0);
        $currentUserId = $_SESSION['user']['id'] ?? 0;
        $currentUserRole = $_SESSION['user']['role'] ?? '';

        if ($currentUserRole !== 'admin' && $id !== $currentUserId) {
            setFlash('error', 'Access denied');
            header("Location: index.php");
            exit;
        }

        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $mobile = trim($_POST['mobile'] ?? '');
        $department = trim($_POST['department'] ?? '');
        $password = $_POST['password'] ?? '';
        $role = ($currentUserRole === 'admin') ? ($_POST['role'] ?? 'employee') : $this->userModel->find($id)['role'];

        if (!$name || !$email || !$mobile || !$department) {
            setFlash('error', 'Please fill all fields');
            header("Location: index.php?controller=User&action=edit&id=$id");
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

            setFlash('success', 'User updated successfully');

            if ($currentUserRole === 'admin') {
                header("Location: index.php?controller=User&action=index");
            } else {
                header("Location: index.php?controller=Task&action=index&id=" . $currentUserId);
            }
            exit;
        } catch (Exception $e) {
            setFlash('error', 'Failed to update user');
            header("Location: index.php?controller=User&action=edit&id=$id");
            exit;
        }
    }

    public function delete()
    {
        $this->checkAccess(['admin', 'tl']);
        $id = (int) ($_GET['id'] ?? 0);

        try {
            $this->userModel->delete($id);
            setFlash('success', 'User deleted successfully');
            header("Location: index.php?controller=User&action=index");
            exit;
        } catch (Exception $e) {
            setFlash('error', 'Failed to delete user');
            header("Location: index.php?controller=User&action=index");
            exit;
        }
    }
    
    public function promote()
    {
        $this->checkAccess(['admin']);
        $id = (int) ($_GET['id'] ?? 0);
        if ($this->userModel->promote($id)) {
            setFlash('success', 'User promoted to Team Leader');
        } else {
            setFlash('error', 'Failed to promote user');
        }
        header("Location: index.php?controller=User&action=index");
        exit;
    }

    public function demote()
    {
        $this->checkAccess(['admin']);
        $id = (int) ($_GET['id'] ?? 0);
        if ($this->userModel->demote($id)) {
            setFlash('success', 'User demoted to Employee');
        } else {
            setFlash('error', 'Failed to demote user');
        }
        header("Location: index.php?controller=User&action=index");
        exit;
    }



    public function team()
    {
        $this->checkAccess(['tl']);
        try {
            $employees = $this->userModel->getAllEmployees();
            include __DIR__ . '/../views/users/team.php';
        } catch (Exception $e) {
            setFlash('error', 'Failed to load team');
            header("Location: index.php");
            exit;
        }
    }

    public function addToTeam()
    {
        $this->checkAccess(['tl']);
        $employeeId = (int) ($_GET['id'] ?? 0);
        if ($employeeId <= 0) {
            setFlash('error', 'Invalid employee ID');
            header("Location: index.php?controller=User&action=team");
            exit;
        }

        try {
            $tlId = $_SESSION['user']['id'];
            $this->userModel->assignToTL($employeeId, $tlId);
            setFlash('success', 'Employee added to your team');
            header("Location: index.php?controller=User&action=team");
            exit;
        } catch (Exception $e) {
            setFlash('error', 'Failed to add employee to team');
            header("Location: index.php?controller=User&action=team");
            exit;
        }
    }

    public function teamsOverview()
    {
        $this->checkAccess(['admin']);
        try {
            $teams = $this->userModel->getTeamsOverview();
            include __DIR__ . '/../views/users/teams_overview.php';
        } catch (Exception $e) {
            setFlash('error', 'Failed to load teams overview');
            header("Location: index.php");
            exit;
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
            setFlash('error', 'Failed to load your team');
            header("Location: index.php");
            exit;
        }
    }
}
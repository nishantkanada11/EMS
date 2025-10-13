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

        if (!in_array($sort, $allowedSort))
            $sort = 'id';
        if (!in_array(strtoupper($order), $allowedOrder))
            $order = 'ASC';

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
        $currentRole = $_SESSION['user']['role'];

        $profilePicName = null;

        if (!empty($_FILES['profile_picture']['name'])) {
            $targetDir = __DIR__ . '/../../public/uploads/';
            if (!is_dir($targetDir))
                mkdir($targetDir, 0777, true);

            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            $fileType = mime_content_type($_FILES['profile_picture']['tmp_name']);

            if (!in_array($fileType, $allowedTypes)) {
                setFlash('error', 'Invalid image format. Only JPG, PNG, GIF, and WEBP are allowed.');
                header("Location: " . $_SERVER['HTTP_REFERER']);
                exit;
            }

            $ext = strtolower(pathinfo($_FILES['profile_picture']['name'], PATHINFO_EXTENSION));
            $allowedExts = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

            if (!in_array($ext, $allowedExts)) {
                setFlash('error', 'Invalid file extension. Please upload only image files.');
                header("Location: " . $_SERVER['HTTP_REFERER']);
                exit;
            }

            $profilePicName = 'profile_' . time() . '.' . $ext;

            if (!move_uploaded_file($_FILES['profile_picture']['tmp_name'], $targetDir . $profilePicName)) {
                setFlash('error', 'Failed to upload image.');
                header("Location: " . $_SERVER['HTTP_REFERER']);
                exit;
            }
        }


        if (!$name || !$email || !$mobile || !$password || !$department) {
            setFlash('error', 'Please fill all fields');
            include __DIR__ . '/../views/users/create.php';
            return;
        }

        try {
            if ($currentRole === 'tl') {
                $result = $this->userModel->createEmployeeRequest(
                    $name,
                    $email,
                    $mobile,
                    $password,
                    $department,
                    $profilePicName,
                    $_SESSION['user']['id']
                );

                if ($result === "exists") {
                    setFlash('error', 'Email or mobile already exists in requests');
                } else {
                    setFlash('success', 'Request submitted to Admin for approval');
                }
                header("Location: index.php?controller=User&action=team");
                exit;

            } else {
                $result = $this->userModel->create($name, $email, $mobile, $password, 'employee', $department, $profilePicName);

                if ($result === "exists") {
                    setFlash('error', 'Email or mobile already exists');
                    include __DIR__ . '/../views/users/create.php';
                    return;
                }

                sendUserCredentials($email, $name, $password);
                setFlash('success', 'User created successfully');
                header("Location: index.php?controller=User&action=index");
                exit;
            }

        } catch (Exception $e) {
            setFlash('error', 'Failed to process request');
            include __DIR__ . '/../views/users/create.php';
            return;
        }
    }

    public function requests()
    {
        $this->checkAccess(['admin']);
        try {
            $requests = $this->userModel->getEmployeeRequests();
            include __DIR__ . '/../views/users/requests.php';
        } catch (Exception $e) {
            setFlash('error', 'Failed to load requests');
            header("Location: index.php");
            exit;
        }
    }

    public function approveRequest()
    {
        $this->checkAccess(['admin']);
        $id = (int) ($_GET['id'] ?? 0);
        if ($this->userModel->approveEmployeeRequest($id)) {
            setFlash('success', 'Employee request approved and user created');
        } else {
            setFlash('error', 'Failed to approve request');
        }
        header("Location: index.php?controller=User&action=requests");
        exit;
    }

    public function rejectRequest()
    {
        $this->checkAccess(['admin']);
        $id = (int) ($_GET['id'] ?? 0);
        if ($this->userModel->rejectEmployeeRequest($id)) {
            setFlash('success', 'Employee request rejected');
        } else {
            setFlash('error', 'Failed to reject request');
        }
        header("Location: index.php?controller=User&action=requests");
        exit;
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

        // Security check
        if ($currentUserRole !== 'admin' && $id !== $currentUserId) {
            setFlash('error', 'Access denied');
            header("Location: index.php");
            exit;
        }

        // Collect fields
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $mobile = trim($_POST['mobile'] ?? '');
        $department = trim($_POST['department'] ?? '');
        $password = $_POST['password'] ?? '';
        $role = ($currentUserRole === 'admin')
            ? ($_POST['role'] ?? 'employee')
            : $this->userModel->find($id)['role'];

        $profilePicName = null;

        if (!empty($_FILES['profile_picture']['name'])) {
            $targetDir = __DIR__ . '/../../public/uploads/';
            if (!is_dir($targetDir))
                mkdir($targetDir, 0777, true);

            // Allowed image types
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            $fileType = mime_content_type($_FILES['profile_picture']['tmp_name']);

            if (!in_array($fileType, $allowedTypes)) {
                setFlash('error', 'Invalid image format. Only JPG, PNG, GIF, and WEBP are allowed.');
                header("Location: " . $_SERVER['HTTP_REFERER']);
                exit;
            }

            $ext = strtolower(pathinfo($_FILES['profile_picture']['name'], PATHINFO_EXTENSION));
            $allowedExts = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

            if (!in_array($ext, $allowedExts)) {
                setFlash('error', 'Invalid file extension. Please upload only image files.');
                header("Location: " . $_SERVER['HTTP_REFERER']);
                exit;
            }

            // Generate unique image name
            $profilePicName = 'profile_' . time() . '.' . $ext;

            if (!move_uploaded_file($_FILES['profile_picture']['tmp_name'], $targetDir . $profilePicName)) {
                setFlash('error', 'Failed to upload image.');
                header("Location: " . $_SERVER['HTTP_REFERER']);
                exit;
            }
        }


        if (!$name || !$email || !$mobile || !$department) {
            setFlash('error', 'Please fill all fields');
            $user = [
                'id' => $id,
                'name' => $name,
                'email' => $email,
                'mobile' => $mobile,
                'department' => $department,
                'role' => $role
            ];
            include __DIR__ . '/../views/users/edit.php';
            return;
        }

        try {
            $this->userModel->update($id, $name, $email, $mobile, $role, $department, $profilePicName);

            if (!empty($password)) {
                $this->userModel->updatePassword($id, $password);
            }

            //update sess
            if ($id === $currentUserId) {
                $_SESSION['user']['name'] = $name;
                $_SESSION['user']['email'] = $email;
                $_SESSION['user']['mobile'] = $mobile;
                $_SESSION['user']['department'] = $department;
                $_SESSION['user']['role'] = $role;

                if ($profilePicName) {
                    $_SESSION['user']['profile_image'] = $profilePicName; // fixed key
                }
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
            $user = [
                'id' => $id,
                'name' => $name,
                'email' => $email,
                'mobile' => $mobile,
                'department' => $department,
                'role' => $role
            ];
            include __DIR__ . '/../views/users/edit.php';
            return;
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
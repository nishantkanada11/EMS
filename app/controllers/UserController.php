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
        try {
            $this->checkAccess(['admin']);

            $currentUserId = $_SESSION['user']['id'] ?? null;
            if (!$currentUserId) {
                throw new Exception("User session not found");
            }

            $sort = $_GET['sort'] ?? 'id';
            $order = strtoupper($_GET['order'] ?? 'ASC');

            $allowedSort = ['id', 'name', 'email', 'mobile', 'role', 'department'];
            $allowedOrder = ['ASC', 'DESC'];

            if (!in_array($sort, $allowedSort)) {
                throw new Exception("Invalid sort parameter");
            }

            if (!in_array($order, $allowedOrder)) {
                throw new Exception("Invalid order parameter");
            }

            $users = $this->userModel->all($currentUserId, $sort, $order);

            //Check if users were fetched
            if (!$users) {
                throw new Exception("No users found");
            }

            $view = __DIR__ . '/../views/users/index.php';
            if (!file_exists($view)) {
                throw new Exception("User list view not found");
            }

            include $view;

        } catch (Exception $e) {
            setFlash('error', $e->getMessage());
            header("Location: index.php");
            exit;
        }
    }


    public function create()
    {
        try {
            $this->checkAccess(['admin', 'tl']);

            $view = __DIR__ . '/../views/users/create.php';

            // Check iew file exists
            if (!file_exists($view)) {
                throw new Exception("User creation view not found");
            }

            include $view;

        } catch (Exception $e) {
            setFlash('error', $e->getMessage());
            header("Location: index.php");
            exit;
        }
    }


    public function store()
    {
        try {
            $this->checkAccess(['admin', 'tl']);

            $name = trim($_POST['name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $mobile = trim($_POST['mobile'] ?? '');
            $password = $_POST['password'] ?? '';
            $department = trim($_POST['department'] ?? '');
            $currentRole = $_SESSION['user']['role'] ?? '';

            if (!$name || !$email || !$mobile || !$password || !$department) {
                throw new Exception('Please fill all required fields');
            }

            $profilePicName = null;

            if (!empty($_FILES['profile_picture']['name'])) {
                $targetDir = __DIR__ . '/../../public/uploads/';
                if (!is_dir($targetDir))
                    mkdir($targetDir, 0777, true);

                $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                $fileType = mime_content_type($_FILES['profile_picture']['tmp_name']);

                if (!in_array($fileType, $allowedTypes)) {
                    throw new Exception('Invalid image format. Only JPG, PNG, GIF, and WEBP are allowed.');
                }

                $ext = strtolower(pathinfo($_FILES['profile_picture']['name'], PATHINFO_EXTENSION));
                $allowedExts = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

                if (!in_array($ext, $allowedExts)) {
                    throw new Exception('Invalid file extension. Please upload only image files.');
                }

                $profilePicName = 'profile_' . time() . '.' . $ext;

                if (!move_uploaded_file($_FILES['profile_picture']['tmp_name'], $targetDir . $profilePicName)) {
                    throw new Exception('Failed to upload image.');
                }
            }

            // Create user or TL request
            if ($currentRole === 'tl') {
                $result = $this->userModel->createUserOrRequest(
                    $name,
                    $email,
                    $mobile,
                    $password,
                    $department,
                    $profilePicName,
                    null,
                    $_SESSION['user']['id'],
                    true
                );

                if ($result === "exists") {
                    throw new Exception('Email or mobile already exists in requests');
                }

                setFlash('success', 'Request submitted to Admin for approval');
                header("Location: index.php?controller=User&action=team");
                exit;
            } else {
                $result = $this->userModel->createUserOrRequest($name, $email, $mobile, $password, $department, $profilePicName, 'employee', null, false);

                if ($result === "exists") {
                    throw new Exception('Email or mobile already exists');
                }

                sendUserCredentials($email, $name, $password);
                setFlash('success', 'User created successfully');
                header("Location: index.php?controller=User&action=index");
                exit;
            }
        } catch (Exception $e) {
            setFlash('error', $e->getMessage());
            include __DIR__ . '/../views/users/create.php';
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

    public function pendingRequests()
    {
        $this->checkAccess(['admin']);

        try {
            $requests = $this->userModel->getEmployeeRequests();
            include __DIR__ . '/../views/users/pending_requests.php';
        } catch (Exception $e) {
            error_log("UserController::pendingRequests error: " . $e->getMessage());
            setFlash('error', 'Failed to load pending requests');
            header("Location: index.php");
            exit;
        }
    }
    public function approveRequest()
    {
        $this->checkAccess(['admin']);
        $id = (int) ($_GET['id'] ?? 0);

        try {
            if ($this->userModel->processEmployeeRequest($id, 'approve')) {
                setFlash('success', 'Employee request approved and user created');
            } else {
                setFlash('error', 'Failed to approve request');
            }
        } catch (Exception $e) {
            setFlash('error', 'Error: ' . $e->getMessage());
        }

        header("Location: index.php?controller=User&action=requests");
        exit;
    }

    public function rejectRequest()
    {
        $this->checkAccess(['admin']);
        $id = (int) ($_GET['id'] ?? 0);

        try {
            if ($this->userModel->processEmployeeRequest($id, 'reject')) {
                setFlash('success', 'Employee request rejected');
            } else {
                setFlash('error', 'Failed to reject request');
            }
        } catch (Exception $e) {
            setFlash('error', 'Error: ' . $e->getMessage());
        }

        header("Location: index.php?controller=User&action=requests");
        exit;
    }

    public function edit()
    {
        $id = (int) ($_GET['id'] ?? 0);
        $currentUserId = $_SESSION['user']['id'] ?? 0;
        $currentUserRole = $_SESSION['user']['role'] ?? '';

        // Access check only admin or the user themselves
        if ($currentUserRole !== 'admin' && $id !== $currentUserId) {
            setFlash('error', 'Access denied');
            header("Location: index.php");
            exit;
        }

        try {
            // getuser data by id
            $userData = $this->userModel->all(id: $id);
            $user = $userData[0] ?? null;

            if (!$user) {
                setFlash('error', 'User not found');
                header("Location: index.php?controller=User&action=index");
                exit;
            }

            include __DIR__ . '/../views/users/edit.php';

        } catch (Exception $e) {
            setFlash('error', 'Error loading user: ' . $e->getMessage());
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

        try {
            $name = trim($_POST['name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $mobile = trim($_POST['mobile'] ?? '');
            $department = trim($_POST['department'] ?? '');
            $password = $_POST['password'] ?? '';
            $role = ($currentUserRole === 'admin') ? ($_POST['role'] ?? 'employee') : null;

            // Fetch current user
            $userData = $this->userModel->all(id: $id);
            $user = $userData[0] ?? null;

            if (!$user) {
                setFlash('error', 'User not found');
                header("Location: index.php?controller=User&action=index");
                exit;
            }

            $profilePicName = null;

            if (!empty($_FILES['profile_picture']['name'])) {
                $targetDir = __DIR__ . '/../../public/uploads/';
                if (!is_dir($targetDir))
                    mkdir($targetDir, 0777, true);

                $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                $fileType = mime_content_type($_FILES['profile_picture']['tmp_name']);
                $ext = strtolower(pathinfo($_FILES['profile_picture']['name'], PATHINFO_EXTENSION));

                if (!in_array($fileType, $allowedTypes) || !in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                    throw new Exception('Invalid image file. Allowed: JPG, PNG, GIF, WEBP.');
                }

                $profilePicName = 'profile_' . time() . '.' . $ext;
                if (!move_uploaded_file($_FILES['profile_picture']['tmp_name'], $targetDir . $profilePicName)) {
                    throw new Exception('Failed to upload image.');
                }
            }

            if (!$name || !$email || !$mobile || !$department) {
                throw new Exception('Please fill all required fields.');
            }

            $result = $this->userModel->update($id, $name, $email, $mobile, $role ?? $user['role'], $department, $profilePicName);

            if ($result === "exists") {
                throw new Exception('Email or mobile already exists.');
            }

            if (!empty($password)) {
                $this->userModel->updatePassword($id, $password);
            }

            if ($id === $currentUserId) {
                $_SESSION['user']['name'] = $name;
                $_SESSION['user']['email'] = $email;
                $_SESSION['user']['mobile'] = $mobile;
                $_SESSION['user']['department'] = $department;
                $_SESSION['user']['role'] = $role ?? $user['role'];
                if ($profilePicName)
                    $_SESSION['user']['profile_image'] = $profilePicName;
            }

            setFlash('success', 'User updated successfully');

            $redirect = ($currentUserRole === 'admin')
                ? "index.php?controller=User&action=index"
                : "index.php?controller=Task&action=index&id=" . $currentUserId;

            header("Location: $redirect");
            exit;

        } catch (Exception $e) {
            // Catch any errors and show the edit page with error
            error_log("UserController::update error: " . $e->getMessage());
            setFlash('error', $e->getMessage());
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

        try {
            if (!$id) {
                throw new Exception('Invalid user ID.');
            }

            if ($this->userModel->promote($id)) {
                setFlash('success', 'User promoted to Team Leader');
            } else {
                throw new Exception('Failed to promote user');
            }
        } catch (Exception $e) {
            error_log("UserController::promote error: " . $e->getMessage());
            setFlash('error', $e->getMessage());
        }

        header("Location: index.php?controller=User&action=index");
        exit;
    }

    public function demote()
    {
        $this->checkAccess(['admin']);
        $id = (int) ($_GET['id'] ?? 0);

        try {
            if (!$id) {
                throw new Exception('Invalid user ID.');
            }

            if ($this->userModel->demote($id)) {
                setFlash('success', 'User demoted to Employee');
            } else {
                throw new Exception('Failed to demote user');
            }
        } catch (Exception $e) {
            error_log("UserController::demote error: " . $e->getMessage());
            setFlash('error', $e->getMessage());
        }

        header("Location: index.php?controller=User&action=index");
        exit;
    }


    public function team()
    {
        $this->checkAccess(['tl']);

        try {
            $employees = $this->userModel->getUsers();
            include __DIR__ . '/../views/users/team.php';
        } catch (Exception $e) {
            error_log("UserController::team error: " . $e->getMessage());
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
            error_log("UserController::addToTeam error: " . $e->getMessage());
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
            error_log("UserController::teamsOverview error: " . $e->getMessage());
            setFlash('error', 'Failed to load teams overview');
            header("Location: index.php");
            exit;
        }
    }

    public function myTeam()
    {
        $this->checkAccess(['tl']);
        $tlId = $_SESSION['user']['id'];

        try {
            $teamMembers = $this->userModel->getUsers(['tl_id' => $tlId]);

            $unassignedEmployees = $this->userModel->getUsers(['role' => 'employee', 'tl_id' => null]);

            include __DIR__ . '/../views/users/my_team.php';
        } catch (Exception $e) {
            error_log("UserController::myTeam error: " . $e->getMessage());
            setFlash('error', 'Failed to load your team');
            header("Location: index.php");
            exit;
        }
    }

}
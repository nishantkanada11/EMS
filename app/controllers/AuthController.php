<?php
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../helpers/flash.php';

class AuthController
{
    private $userModel;

    public function __construct($db)
    {
        $this->userModel = new User($db);
    }

    public function login()
    {
        try {
            include __DIR__ . '/../views/auth/login.php';
        } catch (Exception $e) {
            setFlash('error', 'Failed to load login page');
            header("Location: index.php");
            exit;
        }
    }

    public function registerForm()
    {
        require __DIR__ . '/../views/auth/register.php';
    }

    public function registerRequest()
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        setFlash('error', 'Invalid request method');
        header("Location: index.php?action=registerForm");
        exit;
    }

    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $mobile = trim($_POST['mobile'] ?? '');
    $department = trim($_POST['department'] ?? '');
    $password = $_POST['password'] ?? '';
    $profilePicName = null;

    if (!$name || !$email || !$mobile || !$department || !$password) {
        setFlash('error', 'Please fill all required fields');
        header("Location: index.php?action=registerForm");
        exit;
    }

    if (!empty($_FILES['profile_picture']['name'])) {
        $targetDir = __DIR__ . '/../../public/uploads/';
        if (!is_dir($targetDir))
            mkdir($targetDir, 0777, true);

        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $fileType = mime_content_type($_FILES['profile_picture']['tmp_name']);
        if (!in_array($fileType, $allowedTypes)) {
            setFlash('error', 'Invalid image format. Only JPG, PNG, GIF, and WEBP are allowed.');
            header("Location: index.php?action=registerForm");
            exit;
        }

        $ext = strtolower(pathinfo($_FILES['profile_picture']['name'], PATHINFO_EXTENSION));
        $profilePicName = 'profile_' . time() . '.' . $ext;
        move_uploaded_file($_FILES['profile_picture']['tmp_name'], $targetDir . $profilePicName);
    }

    try {
        $result = $this->userModel->createEmployeeRequest(
            $name,
            $email,
            $mobile,
            $password,
            $department,
            $profilePicName,
            null // No TL Idpublic registration
        );

        if ($result === "exists") {
            setFlash('error', 'Email or mobile already exists in requests.');
            header("Location: index.php?action=registerForm");
            exit;
        }

        setFlash('success', 'Registration request sent to Admin for approval.');
        header("Location: index.php");
        exit;

    } catch (Exception $e) {
        setFlash('error', 'Failed to submit registration request.');
        header("Location: index.php?action=registerForm");
        exit;
    }
}

    public function authenticate()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if (!$email || !$password) {
            setFlash('error', 'Email and password are required');
            header("Location: index.php");
            exit;
        }

        try {
            $user = $this->userModel->findByEmail($email);

            if (!$user || !password_verify($password, $user['password'])) {
                setFlash('error', 'Invalid email or password');
                header("Location: index.php");
                exit;
            }

            $_SESSION['user'] = [
                'id' => $user['id'],
                'name' => $user['name'],
                'email' => $user['email'],
                'role' => $user['role'],
                'department' => $user['department'] ?? null,
                'profile_image' => $user['profile_image'] ?? null
            ];

            $redirect = in_array($user['role'], ['employee', 'tl'])
                ? "index.php?controller=Task&action=index"
                : "index.php?controller=User&action=index";

            header("Location: $redirect");
            exit;
        } catch (Exception $e) {
            setFlash('error', 'Login failed');
            header("Location: index.php");
            exit;
        }
    }

    public function logout()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        try {
            session_unset();
            session_destroy();
            header("Location: index.php");
            exit;
        } catch (Exception $e) {
            setFlash('error', 'Failed to logout');
            header("Location: index.php");
            exit;
        }
    }
}

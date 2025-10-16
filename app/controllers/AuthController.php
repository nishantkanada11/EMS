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
            $view = __DIR__ . '/../views/auth/login.php';
            if (!file_exists($view)) {
                throw new Exception("Login view not found");
            }
            include $view;
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage();
        }

    }

    public function registerForm()
    {
        try {
            $view = __DIR__ . '/../views/auth/register.php';
            if (!file_exists($view)) {
                throw new Exception("Register view not found");
            }
            include $view;
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage();
        }
    }

    public function registerRequest()
    {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception('Invalid request method');
            }

            $name = trim($_POST['name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $mobile = trim($_POST['mobile'] ?? '');
            $department = trim($_POST['department'] ?? '');
            $password = $_POST['password'] ?? '';
            $profilePicName = null;

            if (!$name || !$email || !$mobile || !$department || !$password) {
                throw new Exception('Please fill all required fields');
            }

            if (!empty($_FILES['profile_picture']['name'])) {
                $targetDir = __DIR__ . '/../../public/uploads/';
                if (!is_dir($targetDir)) {
                    mkdir($targetDir, 0777, true);
                }

                $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                $fileType = mime_content_type($_FILES['profile_picture']['tmp_name']);
                if (!in_array($fileType, $allowedTypes)) {
                    throw new Exception('Invalid image format. Only JPG, PNG, GIF, and WEBP are allowed.');
                }

                $ext = strtolower(pathinfo($_FILES['profile_picture']['name'], PATHINFO_EXTENSION));
                $profilePicName = 'profile_' . time() . '.' . $ext;
                move_uploaded_file($_FILES['profile_picture']['tmp_name'], $targetDir . $profilePicName);
            }

            $result = $this->userModel->createUserOrRequest($name, $email, $mobile, $password, $department, $profilePicName,null, null,true);

            if ($result === "exists") {
                throw new Exception('Email or mobile already exists in requests.');
            }

            setFlash('success', 'Registration request sent to Admin for approval.');
            header("Location: index.php");
            exit;
        } catch (Exception $e) {
            setFlash('error', $e->getMessage());
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

        try {
            if (!$email || !$password) {
                throw new Exception("Email and password are required");
            }

            // Find user by email
            $user = $this->userModel->findByEmail($email);

            if (!$user) {
                throw new Exception("User not found");
            }

            if (!password_verify($password, $user['password'])) {
                throw new Exception("Invalid email or password");
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
            setFlash('error', $e->getMessage());
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

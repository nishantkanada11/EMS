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
                'role' => $user['role']
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

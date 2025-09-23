<?php
require_once __DIR__ . '/../models/User.php';

class AuthController
{
    private $userModel;
    public function __construct($db)
    {
        $this->userModel = new User($db);
    }
    public function login()
    {
        include __DIR__ . '/../views/auth/login.php';
    }
    // Process login form
    public function authenticate()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $user = $this->userModel->findByEmail($email);
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user'] = [
                'id' => $user['id'],
                'name' => $user['name'],
                'email' => $user['email'],
                'role' => $user['role']
            ];

            if ($user['role'] === 'employee') {
                header("Location: index.php?controller=Task&action=index");
            } elseif ($user['role'] === 'tl') {
                header("Location: index.php?controller=Task&action=index");
            } else {
                header("Location: index.php?controller=User&action=index");
            }
            exit;
        } else {
            echo "<script>alert('Invalid email or password'); window.location='index.php';</script>";
            exit;
        }
    }
    public function logout()
    {
        session_unset();
        session_destroy();
        header("Location: index.php");
        exit;
    }
}
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
        try {
            include __DIR__ . '/../views/auth/login.php';
        } catch (Exception $e) {
            echo "<script>alert('Failed to load login page'); window.location='index.php';</script>";
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
            echo "<script>alert('Email and password are required'); window.location='index.php';</script>";
            exit;
        }

        try {
            $user = $this->userModel->findByEmail($email);

            if (!$user || !password_verify($password, $user['password'])) {
                echo "<script>alert('Invalid email or password'); window.location='index.php';</script>";
                exit;
            }

            $_SESSION['user'] = [
                'id' => $user['id'],
                'name' => $user['name'],
                'email' => $user['email'],
                'role' => $user['role']
            ];

            if (in_array($user['role'], ['employee', 'tl'])) {
                header("Location: index.php?controller=Task&action=index");
            } else {
                header("Location: index.php?controller=User&action=index");
            }
            exit;
        } catch (Exception $e) {
            echo "<script>alert('Login failed'); window.location='index.php';</script>";
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
            echo "<script>alert('Failed to logout'); window.location='index.php';</script>";
            exit;
        }
    }
}
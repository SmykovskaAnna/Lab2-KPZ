<?php
require_once '../models/User.php';
require_once '../core/Session.php';

class UserController
{
    private $pdo;
    private $userModel;

    public function __construct()
    {
        $config = require '../config.php';
        $dsn = sprintf(
            'mysql:host=%s;dbname=%s;charset=utf8mb4',
            $config['db']['host'],
            $config['db']['dbname']
        );

        $this->pdo = new PDO($dsn, $config['db']['user'], $config['db']['pass']);
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $this->userModel = new User($this->pdo);
        Session::start();
    }

    public function register()
    {
        $error = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = trim($_POST['username'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $password = trim($_POST['password'] ?? '');

            if (empty($username) || empty($email) || empty($password)) {
                $error = "Усі поля обов'язкові для заповнення.";
            } elseif ($this->userModel->register($username, $email, $password)) {
                header('Location: index.php?action=login');
                exit;
            } else {
                $error = "Ім’я користувача або Email вже зайняті.";
            }
        }

        include '../views/auth/register.php';
    }

    public function login()
    {
        $error = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = trim($_POST['username'] ?? '');
            $password = trim($_POST['password'] ?? '');

            if (empty($username) || empty($password)) {
                $error = "Логін і пароль обов’язкові.";
            } else {
                $user = $this->userModel->login($username, $password);
                if ($user) {
                    Session::set('user_id', $user['id']);
                    Session::set('username', $user['username']);
                    header('Location: index.php?action=start_game');
                    exit;
                } else {
                    $error = "Невірний логін або пароль.";
                }
            }
        }

        include '../views/auth/login.php';
    }

    public function logout()
    {
        Session::destroy();
        header('Location: index.php?action=login');
    }
}
?>
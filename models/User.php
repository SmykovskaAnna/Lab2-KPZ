<?php

class User
{
    // Приватна змінна для збереження об'єкта PDO
    private $pdo;

    // Конструктор приймає об'єкт PDO для взаємодії з базою даних
    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Реєстрація нового користувача
     * @param string $username
     * @param string $email
     * @param string $password
     * @return bool true, якщо успішно, false, якщо ім'я або email зайняті
     */
    public function register($username, $email, $password)
    {
        // Підготовка SQL-запиту для перевірки існуючого користувача
        $sql = 'SELECT id FROM users WHERE username = :username OR email = :email';
        
        $stmt = $this->pdo->prepare($sql);

        // Параметри запиту
        $params = [
            'username' => $username,
            'email' => $email
        ];

        $stmt->execute($params);

        // Якщо знайдено користувача — реєстрація не можлива
        $existingUser = $stmt->fetch();
        if ($existingUser) {
            return false;
        }

        // Хешування пароля перед збереженням
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // Підготовка SQL-запиту для вставки нового користувача
        $insertSql = 'INSERT INTO users (username, email, password) VALUES (:username, :email, :password)';
        $insertStmt = $this->pdo->prepare($insertSql);

        // Параметри для вставки
        $insertParams = [
            'username' => $username,
            'email' => $email,
            'password' => $hashedPassword
        ];

        // Виконання запиту
        $insertStmt->execute($insertParams);

        // Якщо дійшло сюди — реєстрація успішна
        return true;
    }

    /**
     * Вхід користувача за іменем та паролем
     * @param string $username
     * @param string $password
     * @return array|false Дані користувача або false, якщо невірні
     */
    public function login($username, $password)
    {
        // SQL-запит для пошуку користувача за ім'ям
        $sql = 'SELECT * FROM users WHERE username = :username';
        
        $stmt = $this->pdo->prepare($sql);

        $params = [
            'username' => $username
        ];

        $stmt->execute($params);

        // Отримання результату
        $user = $stmt->fetch();

        // Перевірка, чи існує користувач і пароль правильний
        if ($user && password_verify($password, $user['password'])) {
            return $user;
        }

        // Якщо щось не співпадає — повертаємо false
        return false;
    }
}

?>
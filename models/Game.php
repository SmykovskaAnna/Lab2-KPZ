<?php

class Game
{
    // Приватна змінна для збереження з'єднання з базою даних
    private $pdo;

    // Конструктор приймає об'єкт PDO для подальшого використання
    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Отримати статистику для певного користувача:
     * - перемоги
     * - нічиї
     * - поразки
     *
     * @param int $user_id
     * @return array Ассоціативний масив з ключами 'wins', 'draws', 'losses'
     */
    public function getUserStats($user_id)
    {
        // SQL-запит, що містить три підзапити:
        // перемоги, нічиї та поразки користувача
        $sql = '
            SELECT 
                (
                    SELECT COUNT(*) 
                    FROM games 
                    WHERE winner_id = :id
                ) AS wins,

                (
                    SELECT COUNT(*) 
                    FROM games 
                    WHERE 
                        (player1_id = :id OR player2_id = :id) 
                        AND winner_id IS NULL
                ) AS draws,

                (
                    SELECT COUNT(*) 
                    FROM games 
                    WHERE 
                        (player1_id = :id OR player2_id = :id) 
                        AND winner_id IS NOT NULL 
                        AND winner_id != :id
                ) AS losses
        ';

        $stmt = $this->pdo->prepare($sql);

        // Параметри запиту
        $params = [
            'id' => $user_id
        ];

        $stmt->execute($params);

        // Повертаємо результат як асоціативний масив
        $stats = $stmt->fetch(PDO::FETCH_ASSOC);

        return $stats;
    }

    /**
     * Зберегти результат гри в базу даних
     *
     * @param int $player1_id
     * @param int $player2_id
     * @param int|null $winner_id (null якщо нічия)
     * @param int $size Розмір ігрового поля
     * @return void
     */
    public function saveResult($player1_id, $player2_id, $winner_id, $size)
    {
        // SQL-запит на вставку нового запису про гру
        $sql = '
            INSERT INTO games 
                (player1_id, player2_id, winner_id, board_size) 
            VALUES 
                (:p1, :p2, :w, :size)
        ';

        $stmt = $this->pdo->prepare($sql);

        // Параметри для запису
        $params = [
            'p1' => $player1_id,
            'p2' => $player2_id,
            'w'  => $winner_id,
            'size' => $size
        ];

        $stmt->execute($params);

        // Дія виконана — результат збережено
    }
}

?>
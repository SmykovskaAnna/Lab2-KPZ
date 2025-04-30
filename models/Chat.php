<?php

class Chat
{
    // Приватна змінна для з'єднання з базою даних
    private $pdo;

    /**
     * Конструктор класу Chat
     * 
     * @param PDO $pdo З'єднання з базою даних
     */
    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Зберігає нове повідомлення в чаті до БД
     *
     * @param int $game_id Ідентифікатор гри
     * @param int $user_id Ідентифікатор користувача
     * @param string $message Текст повідомлення
     * @return void
     */
    public function saveMessage($game_id, $user_id, $message)
    {
        // SQL-запит на вставку повідомлення
        $sql = '
            INSERT INTO chat_messages 
                (game_id, user_id, message) 
            VALUES 
                (:game_id, :user_id, :message)
        ';

        $stmt = $this->pdo->prepare($sql);

        // Параметри запиту
        $params = [
            'game_id'  => $game_id,
            'user_id'  => $user_id,
            'message'  => $message
        ];

        $stmt->execute($params);

        // Повідомлення збережено
    }

    /**
     * Отримує всі повідомлення чату певної гри
     *
     * @param int $game_id Ідентифікатор гри
     * @return array Масив повідомлень (текст, дата створення, користувач)
     */
    public function getMessages($game_id)
    {
        // SQL-запит на отримання повідомлень з приєднаним ім'ям користувача
        $sql = '
            SELECT 
                m.message, 
                m.created_at, 
                u.username 
            FROM 
                chat_messages m 
            JOIN 
                users u 
            ON 
                m.user_id = u.id 
            WHERE 
                m.game_id = :game_id 
            ORDER BY 
                m.created_at ASC
        ';

        $stmt = $this->pdo->prepare($sql);

        $params = [
            'game_id' => $game_id
        ];

        $stmt->execute($params);

        // Отримуємо повідомлення у вигляді асоціативного масиву
        $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $messages;
    }
}

?>

<?php

require_once '../models/Game.php';
require_once '../core/Session.php';

class GameController
{
    private $pdo;
    private $gameModel;
    private $chatModel;

    public function __construct()
    {
        // Завантаження конфігурації
        $config = require '../config.php';

        // Ініціалізація PDO-з'єднання з базою даних
        $this->pdo = new PDO(
            "mysql:host={$config['db']['host']};dbname={$config['db']['dbname']};charset=utf8mb4",
            $config['db']['user'],
            $config['db']['pass']
        );
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Ініціалізація моделі гри
        $this->gameModel = new Game($this->pdo);

        // Запуск сесії
        Session::start();
    }

    // Метод для отримання повідомлень чату
    public function getChatMessages($game_id)
    {
        return $this->chatModel->getMessages($game_id);
    }

    // Метод для надсилання повідомлень у чат
    public function sendMessage()
    {
        // Перевірка автентифікації користувача
        if (!$this->isUserLoggedIn()) {
            $this->redirectToLogin();
        }

        // Перевірка методу запиту
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message'])) {
            $message = trim($_POST['message']);
            if ($message !== '') {
                $user_id = Session::get('user_id');
                $username = Session::get('username');

                // Отримання існуючого чату або створення нового
                $chat = Session::get('chat') ?? [];

                // Додавання нового повідомлення
                $chat[] = [
                    'user' => $username,
                    'message' => $message,
                    'timestamp' => time()
                ];

                // Оновлення сесії
                Session::set('chat', $chat);
            }
        }

        // Повернення до гри
        header('Location: index.php?action=play');
        exit;
    }

    // Метод для очищення чату
    public function clearChat()
    {
        if (!$this->isUserLoggedIn()) {
            $this->redirectToLogin();
        }

        // Очищення сесії чату
        Session::set('chat', []);

        // Перенаправлення назад до гри
        header('Location: index.php?action=play');
        exit;
    }

    // Відображення статистики користувача
    public function stats()
    {
        if (!$this->isUserLoggedIn()) {
            $this->redirectToLogin();
        }

        $userId = Session::get('user_id');
        $stats = $this->gameModel->getUserStats($userId);

        include '../views/game/stats.php';
    }

    // Метод для початку гри
    public function start()
    {
        if (!$this->isUserLoggedIn()) {
            $this->redirectToLogin();
        }

        // Ініціалізація чату
        Session::set('chat', []);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Отримання параметрів гри
            $mode = $_POST['mode'];
            $size = (int) $_POST['size'];
            $difficulty = $_POST['difficulty'] ?? 'easy';
            $playerSymbol = $_POST['player_symbol'] ?? 'X';

            // Символ бота визначається автоматично
            $botSymbol = $playerSymbol === 'X' ? 'O' : '#';

            // Збереження параметрів у сесії
            Session::set('mode', $mode);
            Session::set('board_size', $size);
            Session::set('difficulty', $difficulty);
            Session::set('player_symbol', $playerSymbol);
            Session::set('bot_symbol', $botSymbol);
            Session::set('board', array_fill(0, $size, array_fill(0, $size, '')));
            Session::set('current_turn', $playerSymbol);
            Session::set('turn_start_time', time());

            // Перенаправлення до гри
            header('Location: index.php?action=play');
            exit;
        }

        include '../views/game/start.php';
    }

    // Основний метод гри
    public function play()
    {
        if (!$this->isUserLoggedIn()) {
            $this->redirectToLogin();
        }

        $board = Session::get('board');
        $turn = Session::get('current_turn');
        $mode = Session::get('mode');
        $size = Session::get('board_size');
        $playerSymbol = Session::get('player_symbol', 'X');
        $botSymbol = Session::get('bot_symbol', 'O');

        $turnStart = Session::get('turn_start_time');
        $elapsed = time() - $turnStart;

        // Обробка тайм-ауту ходу
        if ($elapsed > 10) {
            if ($mode === 'bot' && $turn === $playerSymbol) {
                $this->gameModel->saveResult(Session::get('user_id'), null, null, $size);
                Session::set('message', 'Час на хід вичерпано! Бот переміг!');
                header('Location: index.php?action=result');
                exit;
            } else {
                $nextTurn = ($turn === $playerSymbol) ? $botSymbol : $playerSymbol;
                Session::set('current_turn', $nextTurn);
                Session::set('turn_start_time', time());
                Session::set('message', 'Час на хід вичерпано! Хід передано.');
                header('Location: index.php?action=play');
                exit;
            }
        }

        // Обробка ходу гравця
        if (isset($_GET['row']) && isset($_GET['col'])) {
            $row = (int) $_GET['row'];
            $col = (int) $_GET['col'];

            if ($board[$row][$col] === '') {
                $board[$row][$col] = $turn;
                Session::set('board', $board);
                Session::set('turn_start_time', time());

                if ($this->checkWin($board, $turn, $size)) {
                    $this->gameModel->saveResult(Session::get('user_id'), null, ($turn == 'X' ? Session::get('user_id') : null), $size);
                    Session::set('message', "$turn переміг!");
                    header('Location: index.php?action=result');
                    exit;
                } elseif ($this->checkDraw($board)) {
                    $this->gameModel->saveResult(Session::get('user_id'), null, null, $size);
                    Session::set('message', "Нічия!");
                    header('Location: index.php?action=result');
                    exit;
                }

                // Хід бота, якщо режим відповідний
                if ($mode === 'bot' && $turn === $playerSymbol) {
                    $this->botMove();
                    $board = Session::get('board');

                    if ($this->checkWin($board, $botSymbol, $size)) {
                        $this->gameModel->saveResult(Session::get('user_id'), null, null, $size);
                        Session::set('message', "Бот переміг!");
                        header('Location: index.php?action=result');
                        exit;
                    } elseif ($this->checkDraw($board)) {
                        $this->gameModel->saveResult(Session::get('user_id'), null, null, $size);
                        Session::set('message', "Нічия!");
                        header('Location: index.php?action=result');
                        exit;
                    }

                    Session::set('current_turn', $playerSymbol);
                    Session::set('turn_start_time', time());
                } else {
                    Session::set('current_turn', $turn === $playerSymbol ? $botSymbol : $playerSymbol);
                    Session::set('turn_start_time', time());
                }
            }
        }

        include '../views/game/board.php';
    }

    // Відображення результатів гри
    public function result()
    {
        include '../views/game/result.php';
    }

    // Допоміжний метод: перевірка, чи користувач увійшов
    private function isUserLoggedIn()
    {
        return Session::get('user_id') !== null;
    }

    // Допоміжний метод: перенаправлення на сторінку входу
    private function redirectToLogin()
    {
        header('Location: index.php?action=login');
        exit;
    }

    // Перевірка перемоги
    private function checkWin($board, $symbol, $size)
    {
        for ($i = 0; $i < $size; $i++) {
            if (count(array_unique($board[$i])) === 1 && $board[$i][0] === $symbol)
                return true;
            if (count(array_unique(array_column($board, $i))) === 1 && $board[0][$i] === $symbol)
                return true;
        }

        $diag1 = $diag2 = true;
        for ($i = 0; $i < $size; $i++) {
            if ($board[$i][$i] !== $symbol)
                $diag1 = false;
            if ($board[$i][$size - $i - 1] !== $symbol)
                $diag2 = false;
        }

        return $diag1 || $diag2;
    }

    // Перевірка нічиєї
    private function checkDraw($board)
    {
        foreach ($board as $row) {
            if (in_array('', $row)) {
                return false;
            }
        }
        return true;
    }

    // Хід бота
    private function botMove()
    {
        $board = Session::get('board');
        $botSymbol = Session::get('bot_symbol', 'O');

        $difficulty = Session::get('difficulty', 'easy');
        $strategy = $this->getBotStrategy($difficulty);
        $move = $strategy->getMove(Session::get('board'), Session::get('bot_symbol'));

        if ($move) {
            [$i, $j] = $move;
            $board[$i][$j] = $botSymbol;
            Session::set('board', $board);
        }
    }

    private function getBotStrategy($difficulty)
    {
        switch ($difficulty) {
            case 'easy':
                return new EasyBotStrategy();
            case 'medium':
                return new MediumBotStrategy();
            case 'hard':
                return new HardBotStrategy();
            default:
                return new EasyBotStrategy();
        }
    }

    private function firstAvailableMove($board)
    {
        $size = count($board);
        for ($i = 0; $i < $size; $i++) {
            for ($j = 0; $j < $size; $j++) {
                if ($board[$i][$j] === '') {
                    return [$i, $j];
                }
            }
        }
        return null;
    }

    private function mediumAI($board, $botSymbol, $userSymbol)
    {
        $size = count($board);
        for ($i = 0; $i < $size; $i++) {
            for ($j = 0; $j < $size; $j++) {
                if ($board[$i][$j] === '') {
                    $board[$i][$j] = $userSymbol;
                    if ($this->checkWin($board, $userSymbol, $size)) {
                        return [$i, $j];
                    }
                    $board[$i][$j] = '';
                }
            }
        }
        return $this->firstAvailableMove($board);
    }

    private function minimaxMove($board, $player)
    {
        $opponent = $player === 'X' ? 'O' : 'X';
        $bestScore = -INF;
        $bestMove = null;

        foreach ($this->getAvailableMoves($board) as [$i, $j]) {
            $board[$i][$j] = $player;
            $score = $this->minimax($board, false, $player, $opponent);
            $board[$i][$j] = '';
            if ($score > $bestScore) {
                $bestScore = $score;
                $bestMove = [$i, $j];
            }
        }

        return ['move' => $bestMove, 'score' => $bestScore];
    }

    private function minimax($board, $isMax, $player, $opponent)
    {
        $size = count($board);

        if ($this->checkWin($board, $player, $size))
            return 1;
        if ($this->checkWin($board, $opponent, $size))
            return -1;
        if (empty($this->getAvailableMoves($board)))
            return 0;

        $best = $isMax ? -INF : INF;

        foreach ($this->getAvailableMoves($board) as [$i, $j]) {
            $board[$i][$j] = $isMax ? $player : $opponent;
            $score = $this->minimax($board, !$isMax, $player, $opponent);
            $board[$i][$j] = '';
            $best = $isMax ? max($best, $score) : min($best, $score);
        }

        return $best;
    }

    private function getAvailableMoves($board)
    {
        $moves = [];
        $size = count($board);
        for ($i = 0; $i < $size; $i++) {
            for ($j = 0; $j < $size; $j++) {
                if ($board[$i][$j] === '') {
                    $moves[] = [$i, $j];
                }
            }
        }
        return $moves;
    }
}
?>
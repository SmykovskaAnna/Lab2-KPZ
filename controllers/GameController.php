<?php

require_once '../models/Game.php';
require_once '../core/Session.php';

// Підключаємо нові інтерфейси та стратегії
require_once '../core/interfaces/BotStrategyInterface.php';
require_once '../game/strategies/EasyBotStrategy.php';
require_once '../game/strategies/MediumBotStrategy.php';
require_once '../game/strategies/HardBotStrategy.php';
require_once '../game/helpers/GameRulesHelper.php'; // Підключаємо помічник для правил гри

use Core\Interfaces\BotStrategyInterface;
use Game\Strategies\EasyBotStrategy;
use Game\Strategies\MediumBotStrategy;
use Game\Strategies\HardBotStrategy;
use Game\Helpers\GameRulesHelper; // Використовуємо GameRulesHelper

class GameController
{
    private $pdo;
    private $gameModel;
    // private $chatModel; // Цей рядок можна прибрати, якщо Chat переноситься на базу даних,
    // але для цього PR залишаємо, якщо не міняємо Chat

    public function __construct()
    {
        $config = require '../config.php';

        $this->pdo = new PDO(
            "mysql:host={$config['db']['host']};dbname={$config['db']['dbname']};charset=utf8mb4",
            $config['db']['user'],
            $config['db']['pass']
        );
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $this->gameModel = new Game($this->pdo);
        // $this->chatModel = new Chat($this->pdo); // Якщо Chat модель використовується тут

        Session::start();
    }

    // ... інші методи ...

    private function getBotStrategy(string $difficulty): BotStrategyInterface
    {
        switch ($difficulty) {
            case 'medium':
                return new MediumBotStrategy();
            case 'hard':
                return new HardBotStrategy();
            default:
                return new EasyBotStrategy();
        }
    }

    private function botMove()
    {
        $board = Session::get('board');
        $size = Session::get('board_size');
        $difficulty = Session::get('difficulty', 'easy');
        $botSymbol = Session::get('bot_symbol');
        $userSymbol = Session::get('player_symbol');

        $strategy = $this->getBotStrategy($difficulty);
        $move = $strategy->makeMove($board, $botSymbol, $userSymbol, $size); // Передаємо size

        if ($move) {
            [$i, $j] = $move;
            $board[$i][$j] = $botSymbol;
            Session::set('board', $board);
        }
    }

    private function handlePlayerMove(int $row, int $col)
    {
        $board = Session::get('board');
        $turn = Session::get('current_turn');
        $mode = Session::get('mode');
        $size = Session::get('board_size');
        $playerSymbol = Session::get('player_symbol');
        $botSymbol = Session::get('bot_symbol');

        if ($board[$row][$col] === '') {
            $board[$row][$col] = $turn;
            Session::set('board', $board);
            Session::set('turn_start_time', time());

            // Використовуємо GameRulesHelper для перевірок
            if (GameRulesHelper::checkWin($board, $turn, $size)) {
                $this->endGame($turn === $playerSymbol ? Session::get('user_id') : null, "$turn переміг!");
            } elseif (GameRulesHelper::checkDraw($board)) {
                $this->endGame(null, "Нічия!");
            }

            if ($mode === 'bot' && $turn === $playerSymbol) {
                $this->botMove();
                $board = Session::get('board'); // Оновлюємо дошку після ходу бота

                // Використовуємо GameRulesHelper для перевірок
                if (GameRulesHelper::checkWin($board, $botSymbol, $size)) {
                    $this->endGame(null, "Бот переміг!");
                } elseif (GameRulesHelper::checkDraw($board)) {
                    $this->endGame(null, "Нічия!");
                }

                Session::set('current_turn', $playerSymbol);
            } else {
                Session::set('current_turn', $turn === $playerSymbol ? $botSymbol : $playerSymbol);
            }
        }
    }

    // ВИДАЛЕНІ МЕТОДИ З GameController:
    // private function checkWin(array $board, string $symbol, int $size): bool { ... }
    // private function checkDiagonals(array $board, string $symbol, int $size): bool { ... }
    // private function checkDraw(array $board): bool { ... }
    // private function firstAvailableMove(array $board): ?array { ... }
    // private function mediumAI(array $board, string $botSymbol, string $userSymbol): ?array { ... }
    // private function minimaxMove(array $board, string $player): array { ... }
    // private function minimax(array $board, bool $isMax, string $player, string $opponent): int { ... }
    // private function getAvailableMoves(array $board): array { ... }
}
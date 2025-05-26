<?php

namespace Game\Strategies;

use Core\Interfaces\BotStrategyInterface;
use Game\Helpers\GameRulesHelper; // Можливо, варто створити допоміжний клас для правил гри

class MediumBotStrategy implements BotStrategyInterface
{
    public function makeMove(array $board, string $botSymbol, string $playerSymbol, int $boardSize): ?array
    {
        // AI середнього рівня: намагається заблокувати перемогу гравця.
        // Якщо не може заблокувати, робить перший доступний хід.

        // Спроба заблокувати гравця
        for ($i = 0; $i < $boardSize; $i++) {
            for ($j = 0; $j < $boardSize; $j++) {
                if ($board[$i][$j] === '') {
                    $board[$i][$j] = $playerSymbol;
                    if (GameRulesHelper::checkWin($board, $playerSymbol, $boardSize)) {
                        $board[$i][$j] = ''; // Повертаємо дошку до початкового стану
                        return [$i, $j];
                    }
                    $board[$i][$j] = ''; // Повертаємо дошку до початкового стану
                }
            }
        }

        // Якщо не вдалося заблокувати, робимо перший доступний хід
        foreach ($board as $i => $row) {
            foreach ($row as $j => $cell) {
                if ($cell === '') {
                    return [$i, $j];
                }
            }
        }
        return null;
    }
}